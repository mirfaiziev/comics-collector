<?php

namespace App\DTO;

use Carbon\Carbon;

/**
 * Class ComicDTO
 * @package App\DTO
 */
class ComicDTO
{
    public string $imageUrl;
    public string $title;
    public string $description;
    public string $webUrl;
    public Carbon $publishDate;
}
