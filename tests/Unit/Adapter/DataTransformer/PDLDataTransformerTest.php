<?php

namespace App\Test\Unit\Adapter\DataTransformer;


use App\Adapter\DataTransformer\PDLDataTransformer;
use App\Adapter\DataTransformer\Validator\ObjectPropertiesValidator;
use App\DTO\ComicDTO;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * Class PDLDataTransformerTest
 * @package App\Test\Unit\Adapter\DataTransformer
 */
class PDLDataTransformerTest extends TestCase
{
    /**
     * @dataProvider getTestMissedPropertyExceptionData
     * @param string $missedPropertiesList
     */
    public function testMissedPropertyException(
        string $missedPropertiesList
    )
    {
        $this->expectExceptionMessage(
            sprintf(
                "Invalid data to transform, the following properties should be present in the object: %s",
                $missedPropertiesList
            )
        );
        $this->expectException(InvalidArgumentException::class);

        $validator = $this->createMock(ObjectPropertiesValidator::class);
        $validator->expects($this->once())
            ->method('isValidObject')
            ->willReturn(false)
        ;
        $validator->expects($this->once())
            ->method('getJoinedMissesProperties')
            ->willReturn($missedPropertiesList)
        ;
        $dataTransformer = new PDLDataTransformer($validator);
        $dataTransformer->transform(new SimpleXMLElement('<xml/>'), '');
    }

    /**
     * @dataProvider getTestValidTransformData
     * @param array $inputItem
     */
    public function testValidTransform(array $inputItem, string $imgUrl)
    {
        $xmlInput = new SimpleXMLElement('<root/>');
        foreach ($inputItem as $key => $value) {
            $xmlInput->addChild($key, $value);
        }

        $validator = $this->createMock(ObjectPropertiesValidator::class);
        $validator->expects($this->once())
            ->method('isValidObject')
            ->willReturn(true)
        ;
        $validator->expects($this->never())->method('getJoinedMissesProperties');

        $dataTransformer = new PDLDataTransformer($validator);
        $comicDTO = $dataTransformer->transform($xmlInput, $imgUrl);

        $expectedComicDTO = new ComicDTO();
        $expectedComicDTO->imageUrl = $imgUrl;
        $expectedComicDTO->title = $inputItem['title'];
        $expectedComicDTO->description = $inputItem['description'];
        $expectedComicDTO->webUrl = $inputItem['guid'];
        $expectedComicDTO->publishDate = Carbon::createFromTimeString($inputItem['pubDate']);

        $this->assertEquals($expectedComicDTO, $comicDTO);
    }

    public function getTestMissedPropertyExceptionData(): array
    {
        return [
            ['img, test']
        ];
    }

    public function getTestValidTransformData(): array
    {
        return [
            'valid data set 1' => [
                [
                    'title' => 'my title',
                    'description' => 'my description',
                    'guid' => 'my url',
                    'pubDate' => 'Wed, 09 Dec 2020 20:52:12 +0000'
                ],
                'my url',
            ],
        ];
    }
}
