<?php

namespace App\Service;

use App\Adapter\ApiAdapterInterface;
use App\DTO\ComicDTO;
use InvalidArgumentException;

/**
 * Class CollectorService
 * @package App\Service
 */
class CollectorService
{
    /**
     * @var array|ApiAdapterInterface[]
     */
    private array $adapters = [];
    private SortComicsService $sortComicService;

    /**
     * CollectorService constructor.
     * @param ApiAdapterInterface[]|array $adapters
     * @param SortComicsService $sortComicService
     */
    public function __construct(array $adapters, SortComicsService $sortComicService)
    {
        foreach ($adapters as $adapter) {
            if (!$adapter instanceof ApiAdapterInterface) {
                throw new InvalidArgumentException(
                  sprintf('All adapters passing to CollectorService should implement ApiAdapterInterface, but %s is not', get_class($adapter))
                );
            }

            $this->adapters[] = $adapter;
            $this->sortComicService = $sortComicService;
        }
    }

    /**
     * @return ComicDTO[]
     */
    public function getComics(): array
    {
        $comics = [];
        foreach ($this->adapters as $adapter) {
            $comics = array_merge($comics, $adapter->getComics());
        }

        return $this->sortComicService->sortComics($comics);
    }
}
