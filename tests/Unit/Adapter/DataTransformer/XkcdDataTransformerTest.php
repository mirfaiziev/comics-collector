<?php
namespace App\Test\Unit\Adapter\DataTransformer;

use App\Adapter\DataTransformer\Validator\ObjectPropertiesValidator;
use App\Adapter\DataTransformer\XkcdDataTransformer;
use App\DTO\ComicDTO;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use RangeException;
use stdClass;

class XkcdDataTransformerTest extends TestCase
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

        $this->expectException(RangeException::class);

        $validator = $this->createMock(ObjectPropertiesValidator::class);
        $validator->expects($this->once())
            ->method('isValidObject')
            ->willReturn(false);
        $validator->expects($this->once())
            ->method('getJoinedMissesProperties')
            ->willReturn($missedPropertiesList);
        $dataTransformer = new XkcdDataTransformer($validator);
        $dataTransformer->transform(new stdClass());
    }

    /**
     * @dataProvider getTestValidTransformData
     * @param array $input
     */
    public function testValidTransform(array $input)
    {
        $inputObj = (object) $input;
        $validator = $this->createMock(ObjectPropertiesValidator::class);
        $validator->expects($this->once())
            ->method('isValidObject')
            ->willReturn(true)
        ;
        $validator->expects($this->never())->method('getJoinedMissesProperties');

        $dataTransformer = new XkcdDataTransformer($validator);
        $comicDTO = $dataTransformer->transform($inputObj);

        $this->assertInstanceOf(ComicDTO::class, $comicDTO);

        $expectedComicDTO = new ComicDTO();
        $expectedComicDTO->imageUrl = (string) $inputObj->img;
        $expectedComicDTO->title = (string) $inputObj->title;
        $expectedComicDTO->webUrl = sprintf(XkcdDataTransformer::WEB_URL, (int) $inputObj->num);
        $expectedComicDTO->publishDate = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            sprintf('%s-%s-%s 00:00:00', (string) $inputObj->year, (string) $inputObj->month, (string) $inputObj->day)
        );

        $this->assertEquals($expectedComicDTO, $comicDTO);
    }

    public function getTestMissedPropertyExceptionData(): array
    {
        return [
            ['img, test']
        ];
    }

    /**
     * @return array
     */
    public function getTestValidTransformData(): array
    {
        return [
            'valid dataset 1' => [
                [
                    'img'=>'my image',
                    'title' => 'my title',
                    'num' => 1,
                    'year' => 2020,
                    'month' => 12,
                    'day' => 12,
                ],
            ],
        ];
    }
}
