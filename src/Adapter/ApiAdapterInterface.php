<?php

namespace App\Adapter;

interface ApiAdapterInterface
{
    /**
     * @return array[App\DTO\ComicDTO]
     */
    public function getComics(): array;
}
