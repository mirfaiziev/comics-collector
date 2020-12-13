<?php

namespace App\Adapter;

use App\Adapter\DataTransformer\XkcdDataTransformer;
use App\DTO\ComicDTO;
use Psr\Log\LoggerInterface;
use RangeException;
use RuntimeException;
use stdClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

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
    private LoggerInterface $logger;
    private XkcdDataTransformer $dataTransformer;

    private int $currentComicId;

    /**
     * XkcdAdapter constructor.
     * @param HttpClientInterface $httpClient
     * @param LoggerInterface $logger
     * @param XkcdDataTransformer $dataTransformer
     */
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        XkcdDataTransformer $dataTransformer
    )
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->dataTransformer = $dataTransformer;
    }

    /**
     * @return ComicDTO[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getComics(): array
    {
        $comics = [];

        try {
            $comics[] = $this->getCurrentComic();
            $comics = array_merge($comics, $this->getRestComics());
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    "Exception %s was thrown, message: %s",
                    get_class($e),
                    $e->getMessage()
                )
            );
        }

        return $comics;
    }

    /**
     * @return ComicDTO
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getCurrentComic(): ComicDTO
    {
        $response = $this->httpClient->request(
            'GET',
            static::CURRENT_COMIC_URL
        );

        $comicObj = $this->parseResponse($response);

        if (!property_exists($comicObj, 'num')) {
            throw new RangeException("Cannot find 'num' in the feed");
        }

        $this->currentComicId = (int)$comicObj->num;

        return $this->dataTransformer->transform($comicObj);
    }

    /**
     * @return ComicDTO[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getRestComics(): array
    {
        $responses = [];
        $comics = [];

        for ($i = 1; $i < static::NUMBER_OF_PICTURES; $i++) {
            $responses[] = $this->httpClient->request(
                'GET',
                sprintf(static::CONCRETE_COMIC_URL, $this->currentComicId - $i)
            );
        }

        foreach ($responses as $response) {
            $comicObj = $this->parseResponse($response);
            $comics[] = $this->dataTransformer->transform($comicObj);
        }

        return $comics;
    }

    /**
     * @param ResponseInterface $response
     * @return stdClass
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function parseResponse(ResponseInterface $response): stdClass
    {
        $content = $response->getContent();
        $comicObj = json_decode($content);
        if (is_null($comicObj)) {
            throw new RuntimeException(
                sprintf('Cannot decode response: \'%s\'', $content)
            );
        }
        return $comicObj;
    }
}
