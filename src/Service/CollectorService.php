<?php

namespace App\Service;

use App\Adapter\ApiAdapterInterface;
use App\DTO\ComicDTO;
use InvalidArgumentException;

class CollectorService
{
    /**
     * @var array|ApiAdapterInterface[]
     */
    private array $adapters = [];

    /**
     * CollectorService constructor.
     * @param ApiAdapterInterface[]|array $adapters
     */
    public function __construct(array $adapters)
    {
        foreach ($adapters as $adapter) {
            if (!$adapter instanceof ApiAdapterInterface) {
                throw new InvalidArgumentException(
                  sprintf('All adapters passing to CollectorService should implement ApiAdapterInterface, but %s is not', get_class($adapter))
                );
            }

            $this->adapters[] = $adapter;
        }
    }

    /**
     * @return array|ComicDTO
     */
    public function getComics(): array
    {
        $comics = [];
        foreach ($this->adapters as $adapter) {
            $comics = array_merge($comics, $adapter->getComics());
        }

        return $this->sortComics($comics);
    }

    /**
     * TODO: need to be implemented
     * @param array|ComicDTO $comics
     * @return array|ComicDTO
     */
    private function sortComics(array $comics): array
    {
        return $comics;
    }
}
