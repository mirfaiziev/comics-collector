<?php

namespace App\Controller;

use App\DTO\ComicDTO;
use App\Service\ComicsCollectorService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
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
     * @OA\Get(
     *      operationId="getComics",
     *      @OA\Response(
     *          response=200,
     *          description="get parsed comics",
     *          @OA\JsonContent(type="array",
     *               @OA\Items(ref=@Model(type=ComicDTO::class))
     *          )
     *     )
     * )
     * @param ComicsCollectorService $collectorService
     * @return JsonResponse
     */
    public function index(ComicsCollectorService $collectorService): JsonResponse
    {
        return $this->json($collectorService->getComics());
    }
}
