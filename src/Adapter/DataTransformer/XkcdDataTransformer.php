<?php

namespace App\Adapter\DataTransformer;

use App\Adapter\DataTransformer\Validator\ObjectPropertiesValidator;
use App\DTO\ComicDTO;
use Carbon\Carbon;
use RangeException;

/**
 * NOTE: because there is no time of publishDate it will be transformed
 * the begging of the day, otherwise later it will not possible to sort
 *
 * Class XkcdDataTransformer
 * @package App\Adapter\DataTransformer
 */
class XkcdDataTransformer
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

    private ObjectPropertiesValidator $objectPropertiesValidator;

    /**
     * XkcdDataTransformer constructor.
     * @param ObjectPropertiesValidator $objectPropertiesValidator
     */
    public function __construct(ObjectPropertiesValidator $objectPropertiesValidator)
    {
        $this->objectPropertiesValidator = $objectPropertiesValidator;
    }


    /**
     * @param object $input
     * @return ComicDTO
     */
    public function transform(object $input): ComicDTO
    {
        if (!$this->objectPropertiesValidator->isValidObject($input, static::REQUIRED_PROPERTIES)){
            throw new RangeException(
                sprintf(
                    "Invalid data to transform, the following properties should be present in the object: %s",
                    $this->objectPropertiesValidator->getJoinedMissesProperties()
                )
            );
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
}
