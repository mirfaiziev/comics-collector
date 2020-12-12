<?php

namespace App\Test\Unit\Service;

use App\DTO\ComicDTO;
use App\Service\SortComicsService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class SortComicsServiceTest extends TestCase
{
    public function testSortComics()
    {
        $comic1 = new ComicDTO();
        $comic1->publishDate = Carbon::createFromTimeString('2020-12-11 00:00:00');
        $comic2 = new ComicDTO();
        $comic2->publishDate = Carbon::createFromTimeString('2020-12-12 00:00:00');

        $sortComicService = new SortComicsService();

        $this->assertEquals(
            [$comic2, $comic1],
            $sortComicService->sortComics([$comic1, $comic2])
        );
    }
}
