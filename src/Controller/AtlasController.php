<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AtlasController extends AbstractController
{
    /** The single-page atlas. Technique pages are deep links into the same page. */
    #[Route('/', name: 'atlas', methods: ['GET'])]
    #[Route('/waza/{slug}', name: 'atlas_technique', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function index(?string $slug = null): Response
    {
        return $this->render('atlas.html.twig', ['initialSlug' => $slug]);
    }
}
