<?php

namespace App\Controller;

use App\Service\ComicsCollectorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     * @param ComicsCollectorService $collectorService
     * @return JsonResponse
     */
    public function index(ComicsCollectorService $collectorService): JsonResponse
    {
        return $this->json($collectorService->getComics());
    }
}
