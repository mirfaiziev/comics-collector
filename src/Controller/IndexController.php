<?php

namespace App\Controller;

use App\Service\CollectorService;
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
     * @param CollectorService $collectorService
     * @return JsonResponse
     */
    public function index(CollectorService $collectorService): JsonResponse
    {
        return new JsonResponse($collectorService->getComics());
    }
}
