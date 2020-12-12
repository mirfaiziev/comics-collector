<?php

namespace App\Adapter;

use App\DTO\ComicDTO;

/**
 * Class PDLAdapter
 * @package App\Adapter
 */
class PDLAdapter implements ApiAdapterInterface
{
    /**
     * @return ComicDTO[]
     */
    public function getComics(): array
    {
        return [['a'=>'b']];
    }
}
