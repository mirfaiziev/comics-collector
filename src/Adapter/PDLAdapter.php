<?php

namespace App\Adapter;

use App\Adapter\DataTransformer\PDLDataTransformer;
use App\DTO\ComicDTO;
use Exception;
use Psr\Log\LoggerInterface;

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

    /**
     * PDLAdapter constructor.
     * @param PDLDataTransformer $dataTransformer
     * @param LoggerInterface $logger
     */
    public function __construct(
        PDLDataTransformer $dataTransformer,
        LoggerInterface $logger
    ) {
        $this->dataTransformer = $dataTransformer;
        $this->logger = $logger;
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
                $images[] = $this->dataTransformer->transform($item);
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
}
