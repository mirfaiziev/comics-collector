<?php

namespace App\Adapter;

use App\DTO\ComicDTO;

interface ApiAdapterInterface
{
    /**
     * @return array|ComicDTO[]
     */
    public function getComics(): array;
}
