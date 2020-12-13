<?php

namespace App\Service;

use App\Adapter\ApiAdapterInterface;
use App\DTO\ComicDTO;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class CollectorService
 * @package App\Service
 */
class CollectorService
{
    /**
     * @var ApiAdapterInterface[]|array
     */
    private array $adapters = [];
    private SortComicsService $sortComicService;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    /**
     * CollectorService constructor.
     * @param ApiAdapterInterface[]|array $adapters
     * @param SortComicsService $sortComicService
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        array $adapters,
        SortComicsService $sortComicService,
        CacheInterface $cache,
        LoggerInterface $logger
    )
    {
        foreach ($adapters as $adapter) {
            if (!$adapter instanceof ApiAdapterInterface) {
                throw new InvalidArgumentException(
                    sprintf('All adapters passing to CollectorService should implement ApiAdapterInterface, but %s is not', get_class($adapter))
                );
            }

            $this->adapters[] = $adapter;
            $this->sortComicService = $sortComicService;
            $this->cache = $cache;
            $this->logger = $logger;
        }
    }

    /**
     * @return ComicDTO[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getComics(): array
    {
        try {
            return $this->cache->get('comics', function (ItemInterface $item) {
                return $this->collectComicsFromAdapters();
            });
        } catch (Exception $e) {
            $this->logger->error(
                sprintf(
                    "Exception %s was thrown, message: %s",
                    get_class($e),
                    $e->getMessage()
                )
            );

            return [];
        }
    }

    /**
     * @return ComicDTO[]
     */
    private function collectComicsFromAdapters(): array
    {
        $comics = [];

        foreach ($this->adapters as $adapter) {
            $comics = array_merge($comics, $adapter->getComics());
        }

        return $this->sortComicService->sortComics($comics);
    }
}
