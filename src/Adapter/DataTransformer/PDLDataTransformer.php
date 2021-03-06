<?php

namespace App\Adapter\DataTransformer;

use App\Adapter\DataTransformer\Validator\ObjectPropertiesValidator;
use App\DTO\ComicDTO;
use Carbon\Carbon;
use RangeException;
use SimpleXMLElement;

/**
 * Class PDLDataTransformer
 * @package App\Adapter\DataTransformer
 */
class PDLDataTransformer
{
    const REQUIRED_PROPERTIES = [
        'title',
        'guid',
        'description',
        'pubDate',
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
     * @param SimpleXMLElement $input
     * @param string $imageUrl
     * @return ComicDTO
     */
    public function transform(SimpleXMLElement $input, string $imageUrl): ComicDTO
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
        $comicDTO->imageUrl = $imageUrl;
        $comicDTO->title = (string) $input->title;
        $comicDTO->webUrl = (string) $input->guid;
        $comicDTO->description = (string) $input->description;
        $comicDTO->publishDate = Carbon::createFromTimeString($input->pubDate);

        return $comicDTO;
    }
}
