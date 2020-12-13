<?php

namespace App\Adapter;

use App\Adapter\DataTransformer\PDLDataTransformer;
use App\Adapter\Parser\PDLWebContentParser;
use App\DTO\ComicDTO;
use Psr\Log\LoggerInterface;
use RangeException;
use RuntimeException;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

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
    private PDLWebContentParser $webContentParser;

    /**
     * PDLAdapter constructor.
     * @param HttpClientInterface $httpClient
     * @param LoggerInterface $logger
     * @param PDLDataTransformer $dataTransformer
     * @param PDLWebContentParser $webContentParser
     */
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        PDLDataTransformer $dataTransformer,
        PDLWebContentParser $webContentParser
    )
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->dataTransformer = $dataTransformer;
        $this->webContentParser = $webContentParser;
    }

    /**
     * @return ComicDTO[]
     */
    public function getComics(): array
    {
        $comics = [];

        try {
            $response = $this->httpClient->request('GET', static::FEED_URL);
            $content = $response->getContent();
            $rss = @simplexml_load_string($content);

            if (!$rss instanceof SimpleXMLElement) {
                throw new RuntimeException(
                    sprintf('Cannot parse to xml the following response: \'%s\'', $content)
                );
            }

            $comics = $this->processRss($rss);
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
     * @param SimpleXMLElement $rss
     * @return ComicDTO[]
     * @throws TransportExceptionInterface
     */
    private function processRss(SimpleXMLElement $rss): array
    {
        $items = [];
        $comics = [];

        if (!property_exists($rss, 'channel')) {
            throw new RangeException('Cannot find \'channel\' property in rss feed');
        }
        if (!property_exists($rss->channel, 'item')) {
            throw new RangeException('Cannot find \'item\' property in the channel');
        }

        foreach ($rss->channel->item as $xmlItem) {
            if (!property_exists($xmlItem, 'guid')) {
                throw new RangeException("Cannot find 'guid' in the item");
            }
            $webUrl = (string)$xmlItem->guid;
            $items[] = [
                'xml' => $xmlItem,
                'webUrl' => $webUrl,
                'response' => $this->httpClient->request('GET', $webUrl)
            ];
        }

        foreach ($items as $item) {
            $imageUrl = $this->webContentParser
                ->getImageFromWebPageContent(
                    $item['response']->getContent(),
                    $item['webUrl']
                );
            $comics[] = $this->dataTransformer->transform($item['xml'], $imageUrl);
        }

        return $comics;
    }
}
