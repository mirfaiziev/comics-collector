<?php

namespace App\Adapter;

use App\Adapter\DataTransformer\PDLDataTransformer;
use App\DTO\ComicDTO;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * NOTE: feed return 10 images
 *
 * Class PDLAdapter
 * @package App\Adapter
 */
class PDLAdapter implements ApiAdapterInterface
{
    const FEED_URL = 'http://feeds.feedburner.com/PoorlyDrawnLines';

    private PDLDataTransformer $dataTransformer;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;

    /**
     * PDLAdapter constructor.
     * @param PDLDataTransformer $dataTransformer
     * @param HttpClientInterface $httpClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        PDLDataTransformer $dataTransformer,
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->dataTransformer = $dataTransformer;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    /**
     * @return ComicDTO[]
     */
    public function getComics(): array
    {
        $images = [];
        try {
            $rss = simplexml_load_file(static::FEED_URL);
            foreach ($rss->channel->item as $item) {
                $imageUrl = $this->grabPictureUrl((string) $item->guid);
                $images[] = $this->dataTransformer->transform($item, $imageUrl);
            }
        }catch (Exception $e) {
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

    private function grabPictureUrl($webUrl)
    {
        $response = $this->httpClient->request('GET', $webUrl);
        $content = $response->getContent();

        $re1 = '~<div class="wp-block-image">.*src="(.*)".*\</div>~siU';
        $pregMatchResult = preg_match($re1, $content, $matches);
        if ($pregMatchResult !== 1) {
            throw new \RuntimeException(sprintf("Cannot parse url %s for image", $webUrl));
        }

        return $matches[1];
    }
}
