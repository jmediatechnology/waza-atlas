<?php

namespace App\Controller\Api;

use App\Api\JsonResponder;
use App\Api\TechniquePresenter;
use App\Entity\MotionFormat;
use App\Entity\Technique;
use App\Motion\InvalidMotionException;
use App\Motion\MotionInspector;
use App\Motion\MotionStorage;
use App\Repository\TechniqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/techniques/{slug}/motion', requirements: ['slug' => '[a-z0-9-]+'])]
final class MotionController extends AbstractController
{
    use JsonResponder;

    public function __construct(
        private readonly TechniqueRepository $techniques,
        private readonly MotionStorage $storage,
        private readonly MotionInspector $inspector,
        private readonly TechniquePresenter $presenter,
        private readonly string $uploadToken,
    ) {
    }

    /** The motion file itself: keyframe JSON, BVH text or C3D binary. */
    #[Route('', name: 'api_motion_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $motion = $this->technique($slug)->getMotion()
            ?? throw new NotFoundHttpException(sprintf('No motion has been recorded for "%s" yet.', $slug));
        $path = $this->storage->path($motion);
        if (!is_file($path)) {
            throw new NotFoundHttpException('The motion file is missing on disk. Run "bin/console app:seed --replace-motions" or upload it again.');
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $motion->getFormat()->contentType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $motion->getFilename());
        $response->setPublic();
        $response->setMaxAge(300);
        $response->setLastModified($motion->getUpdatedAt());

        return $response;
    }

    /**
     * Upload or replace a motion. Send multipart/form-data with:
     *   file    .json (keyframes), .bvh or .c3d
     *   source  optional, e.g. "Rokoko suit, Judo Almere, 2026-10"
     *   phases  optional JSON list of {"t": 1.2, "name": "Kuzushi", "en": "Breaking balance", "text": "..."}
     * and the header "Authorization: Bearer <MOTION_UPLOAD_TOKEN>".
     */
    #[Route('', name: 'api_motion_upload', methods: ['POST'])]
    public function upload(string $slug, Request $request): JsonResponse
    {
        $this->denyUnlessUploader($request);
        $technique = $this->technique($slug);

        $file = $request->files->get('file');
        if (null === $file || !$file->isValid()) {
            throw new UnprocessableEntityHttpException('Attach the motion as a "file" field (multipart/form-data).');
        }
        $format = MotionFormat::fromFilename($file->getClientOriginalName())
            ?? throw new UnprocessableEntityHttpException('Upload a .json keyframe file, a .bvh file or a .c3d file.');
        if ($file->getSize() > MotionInspector::MAX_BYTES) {
            throw new UnprocessableEntityHttpException('The file is larger than 20 MB.');
        }

        try {
            $phases = null;
            if ('' !== $raw = $request->request->getString('phases')) {
                $phases = $this->inspector->phases(json_decode($raw, true, 8, \JSON_THROW_ON_ERROR));
            }
            $motion = $this->storage->store($technique, $format, (string) file_get_contents($file->getPathname()), $request->request->getString('source') ?: null, $phases);
        } catch (InvalidMotionException|\JsonException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return $this->respond($this->presenter->motion($motion), Response::HTTP_CREATED);
    }

    #[Route('', name: 'api_motion_delete', methods: ['DELETE'])]
    public function delete(string $slug, Request $request): Response
    {
        $this->denyUnlessUploader($request);
        $this->storage->remove($this->technique($slug));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function technique(string $slug): Technique
    {
        return $this->techniques->findOneBySlugWithMotion($slug)
            ?? throw new NotFoundHttpException(sprintf('No technique called "%s".', $slug));
    }

    private function denyUnlessUploader(Request $request): void
    {
        if ('' === $this->uploadToken) {
            throw new AccessDeniedHttpException('Uploads are switched off. Set MOTION_UPLOAD_TOKEN in .env.local to enable them.');
        }
        $given = (string) preg_replace('/^Bearer\s+/i', '', (string) $request->headers->get('Authorization'));
        if (!hash_equals($this->uploadToken, $given)) {
            throw new AccessDeniedHttpException('A valid upload token is required (Authorization: Bearer <token>).');
        }
    }
}
