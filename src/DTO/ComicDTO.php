<?php

namespace App\DTO;

use Carbon\Carbon;

class ComicDTO
{
    public string $imageUrl;
    public string $title;
    public string $description;
    public string $webUrl;
    public Carbon $publishDate;
}
