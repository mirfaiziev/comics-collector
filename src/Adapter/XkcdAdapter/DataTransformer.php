<?php

namespace App\Adapter\XkcdAdapter;

use App\DTO\ComicDTO;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * NOTE: because there is no time of publishDate it will be transformed
 * the begging of the day, otherwise later it will not possible to sort
 *
 * Class DataTransformer
 * @package App\Adapter\XkcdAdapter
 */
class DataTransformer
{
    const WEB_URL = 'https://xkcd.com/%d/';
    const REQUIRED_PROPERTIES = [
        'img',
        'title',
        'year',
        'month',
        'day',
        'num'
    ];

    private string $missedProperty;

    /**
     * @param object $input
     * @return ComicDTO
     */
    public function transform(object $input): ComicDTO
    {
        if (!$this->isValidInput($input)){
            throw new InvalidArgumentException(
                sprintf("Invalid data to transform, property %s should be present", $this->missedProperty));
        }

        $comicDTO = new ComicDTO();
        $comicDTO->imageUrl = (string) $input->img;
        $comicDTO->title = (string) $input->title;
        $comicDTO->webUrl = sprintf(static::WEB_URL, (int) $input->num);
        $comicDTO->publishDate = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            sprintf('%s-%s-%s 00:00:00', (string) $input->year, (string) $input->month, (string) $input->day)
        );

        return $comicDTO;
    }

    /**
     * @param object $input
     * @return bool
     */
    private function isValidInput(object $input): bool
    {
        foreach (static::REQUIRED_PROPERTIES as $propertyName) {
            if (!property_exists($input, $propertyName)) {
                $this->missedProperty = $propertyName;
                return false;
            }
        }

        return true;
    }
}
