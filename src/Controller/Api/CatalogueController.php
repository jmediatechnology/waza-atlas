<?php

namespace App\Controller\Api;

use App\Api\JsonResponder;
use App\Api\TechniquePresenter;
use App\Entity\Family;
use App\Repository\TechniqueRepository;
use App\Repository\WazaCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', format: 'json')]
final class CatalogueController extends AbstractController
{
    use JsonResponder;

    public function __construct(private readonly TechniquePresenter $presenter)
    {
    }

    /** Families and their categories, with counts, for building the filter UI. */
    #[Route('/categories', name: 'api_categories', methods: ['GET'])]
    public function categories(WazaCategoryRepository $categories): JsonResponse
    {
        $families = [];
        foreach (Family::cases() as $f) {
            $families[$f->value] = ['slug' => $f->value, 'name' => $f->label(), 'kanji' => $f->kanji(), 'english' => $f->english(), 'techniques' => 0, 'categories' => []];
        }
        foreach ($categories->findAllWithCounts() as $row) {
            $c = $row['category'];
            $item = $this->presenter->category($c, $row['techniques'], $row['withMotion']);
            unset($item['family']);
            $families[$c->getFamily()->value]['categories'][] = $item;
            $families[$c->getFamily()->value]['techniques'] += $row['techniques'];
        }

        return $this->respond(['families' => array_values($families)]);
    }

    /**
     * Search and filter. All parameters are optional:
     *   q=te waza     matches romaji, English, kanji and category names, ignoring case, spaces and hyphens
     *   family=nage   nage | katame
     *   category=te   category slug from /api/categories
     *   motion=1      only techniques with a recorded motion
     */
    #[Route('/techniques', name: 'api_technique_index', methods: ['GET'])]
    public function index(Request $request, TechniqueRepository $techniques): JsonResponse
    {
        $q = $request->query->getString('q');
        if (mb_strlen($q) > 100) {
            throw new BadRequestHttpException('The search query is longer than 100 characters.');
        }
        $familyParam = $request->query->getString('family');
        $family = '' === $familyParam ? null : Family::tryFrom($familyParam);
        if ('' !== $familyParam && null === $family) {
            throw new BadRequestHttpException(sprintf('Unknown family "%s". Use "nage" or "katame".', $familyParam));
        }

        $results = $techniques->search($q, $family, $request->query->getString('category') ?: null, $request->query->getBoolean('motion'));

        return $this->respond([
            'count' => \count($results),
            'techniques' => array_map($this->presenter->summary(...), $results),
        ]);
    }

    #[Route('/techniques/{slug}', name: 'api_technique_show', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function show(string $slug, TechniqueRepository $techniques): JsonResponse
    {
        $technique = $techniques->findOneBySlugWithMotion($slug)
            ?? throw new NotFoundHttpException(sprintf('No technique called "%s".', $slug));

        return $this->respond($this->presenter->detail($technique));
    }
}
