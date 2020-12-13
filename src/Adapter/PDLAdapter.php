<?php

namespace App\Adapter;

use App\Adapter\DataTransformer\PDLDataTransformer;
use App\DTO\ComicDTO;
use Exception;
use Psr\Log\LoggerInterface;
use RangeException;
use RuntimeException;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * NOTE: feed return 10 images, so no need to define it in constant
 * NOTE2: feed doesn't return image url, we had to grab it ourself by parsing web url
 *
 * Class PDLAdapter
 * @package App\Adapter
 */
class PDLAdapter implements ApiAdapterInterface
{
    const FEED_URL = 'http://feeds.feedburner.com/PoorlyDrawnLines';

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private PDLDataTransformer $dataTransformer;

    /**
     * PDLAdapter constructor.
     * @param HttpClientInterface $httpClient
     * @param LoggerInterface $logger
     * @param PDLDataTransformer $dataTransformer
     */
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        PDLDataTransformer $dataTransformer
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
            $response = $this->httpClient->request('GET', static::FEED_URL);
            $rss = simplexml_load_string($response->getContent());
            $comics = $this->processRss($rss);
        } catch (Exception $e) {
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
     * @param SimpleXMLElement $rss
     * @return ComicDTO[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function processRss(SimpleXMLElement $rss): array
    {
        $items = [];
        $comics = [];
        foreach ($rss->channel->item as $xmlItem) {
            if (!property_exists($xmlItem, 'guid')) {
                throw new RangeException("Cannot find 'guid' in the feed");
            }
            $webUrl = (string)$xmlItem->guid;
            $items[] = [
                'xml' => $xmlItem,
                'webUrl' => $webUrl,
                'response' => $this->httpClient->request('GET', $webUrl)
            ];
        }

        foreach ($items as $item) {
            $imageUrl = $this->parseImageUrl($item['response']->getContent(), $item['webUrl']);
            $comics[] = $this->dataTransformer->transform($item['xml'], $imageUrl);
        }

        return $comics;
    }

    /**
     * @param string $content
     * @param string $webUrl
     * @return string
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function parseImageUrl(string $content, string $webUrl): string
    {
        $re1 = '~<div class="wp-block-image">.*src="(.*)".*\</div>~siU';
        $pregMatchResult = preg_match($re1, $content, $matches);
        if ($pregMatchResult !== 1) {
            throw new RuntimeException(sprintf("Cannot find comic image in url %s", $webUrl));
        }

        return $matches[1];
    }
}
