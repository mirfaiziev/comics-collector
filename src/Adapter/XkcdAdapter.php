<?php

namespace App\Adapter;

use App\Adapter\XkcdAdapter\DataTransformer;
use App\DTO\ComicDTO;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class XkcdAdapter
 * @package App\Adapter
 */
class XkcdAdapter implements ApiAdapterInterface
{
    const CURRENT_COMIC_URL = 'http://xkcd.com/info.0.json';
    const CONCRETE_COMIC_URL = 'http://xkcd.com/%d/info.0.json';

    private HttpClientInterface $httpClient;
    private DataTransformer $dataTransformer;
    private LoggerInterface $logger;

    private int $numberOfPictures;
    private int $currentComicId;

    /**
     * XkcdAdapter constructor.
     * @param ContainerBagInterface $params
     * @param HttpClientInterface $httpClient
     * @param DataTransformer $dataTransformer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContainerBagInterface $params,
        HttpClientInterface $httpClient,
        DataTransformer $dataTransformer,
        LoggerInterface $logger
    ) {
        $this->numberOfPictures = $params->get('app.xkcd_adapter.number_of_pictures');
        $this->httpClient = $httpClient;
        $this->dataTransformer = $dataTransformer;
        $this->logger = $logger;
    }

    /**
     * @return ComicDTO[]
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getComics(): array
    {
        $images = [];

        try {
            $images[] = $this->getImageDataFromUrl(self::CURRENT_COMIC_URL);

            for ($i = 1; $i < $this->numberOfPictures; $i++) {
                $images[] = $this->getImageDataFromUrl(
                    sprintf(self::CONCRETE_COMIC_URL, $this->currentComicId - $i)
                );
            }
        } catch (Exception $e) {
            $this->logger->error(
                sprintf(
                    "Exception %s was thrown, message: %s",
                    get_class($e),
                    $e->getMessage()
                )
            );
        }

        return $images;
    }

    /**
     * @param string $url
     * @return ComicDTO
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getImageDataFromUrl(string $url): ComicDTO
    {
        $response = $this->httpClient->request(
            'GET',
            $url
        );

        $comicObj = json_decode($response->getContent());

        if ($url === self::CURRENT_COMIC_URL) {
            $this->currentComicId = $comicObj->num;
        }

        return $this->dataTransformer->transform($comicObj);
    }
}
