<?php

namespace App\Adapter;

use App\Adapter\DataTransformer\XkcdDataTransformer;
use App\DTO\ComicDTO;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class XkcdAdapter
 * @package App\Adapter
 */
class XkcdAdapter implements ApiAdapterInterface
{
    const CURRENT_COMIC_URL = 'http://xkcd.com/info.0.json';
    const CONCRETE_COMIC_URL = 'http://xkcd.com/%d/info.0.json';

    const NUMBER_OF_PICTURES = 10;

    private HttpClientInterface $httpClient;
    private XkcdDataTransformer $dataTransformer;
    private LoggerInterface $logger;

    private int $currentComicId;

    /**
     * XkcdAdapter constructor.
     * @param HttpClientInterface $httpClient
     * @param XkcdDataTransformer $dataTransformer
     * @param LoggerInterface $logger
     */
    public function __construct(
        HttpClientInterface $httpClient,
        XkcdDataTransformer $dataTransformer,
        LoggerInterface $logger
    )
    {
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
            $images[] = $this->getComicDataFromUrl(self::CURRENT_COMIC_URL);

            for ($i = 1; $i < static::NUMBER_OF_PICTURES; $i++) {
                $images[] = $this->getComicDataFromUrl(
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
    private function getComicDataFromUrl(string $url): ComicDTO
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
