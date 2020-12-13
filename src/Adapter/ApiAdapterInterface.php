<?php

namespace App\Adapter;

use App\DTO\ComicDTO;

interface ApiAdapterInterface
{
    /**
     * @return ComicDTO[]
     */
    public function getComics(): array;
}
