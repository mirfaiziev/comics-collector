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
        $dataTransformer->transform(new SimpleXMLElement('<xml/>'));
    }

    /**
     * @dataProvider getTestValidTransformData
     * @param array $input
     */
    public function testValidTransform(array $input)
    {
        $xmlInput = new SimpleXMLElement('<root/>');
        foreach ($input as $key => $value) {
            $xmlInput->addChild($key, $value);
        }

        $validator = $this->createMock(ObjectPropertiesValidator::class);
        $validator->expects($this->once())
            ->method('isValidObject')
            ->willReturn(true)
        ;
        $validator->expects($this->never())->method('getJoinedMissesProperties');

        $dataTransformer = new PDLDataTransformer($validator);
        $comicDTO = $dataTransformer->transform($xmlInput);

        $expectedComicDTO = new ComicDTO();
       // $expectedComicDTO->imageUrl = $input['img'];
        $expectedComicDTO->title = $input['title'];
        $expectedComicDTO->description = $input['description'];
        $expectedComicDTO->webUrl = $input['guid'];
        $expectedComicDTO->publishDate = Carbon::createFromTimeString($input['pubDate']);

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
                ]
            ]
        ];
    }
}
