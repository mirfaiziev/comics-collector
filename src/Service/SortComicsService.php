<?php

namespace App\Service;

use App\DTO\ComicDTO;

/**
 * Class SortComicsService
 * @package App\Service
 */
class SortComicsService
{
    /**
     * @param ComicDTO[] $comics
     * @return ComicDTO[]
     */
    public function sortComics(array $comics): array
    {
        usort($comics, function(ComicDTO $a, ComicDTO $b) {
            if ($a->publishDate->equalTo($b->publishDate)) {
                return 0;
            }

            return $a->publishDate->greaterThan($b->publishDate) ? -1 : 1;
        });

        return $comics;
    }
}
