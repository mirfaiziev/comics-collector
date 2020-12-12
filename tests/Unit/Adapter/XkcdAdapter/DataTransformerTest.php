<?php
namespace App\Test\Unit\Adapter\XkcdAdapter;

use App\Adapter\XkcdAdapter\DataTransformer;
use App\DTO\ComicDTO;
use Carbon\Carbon;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DataTransformerTest extends KernelTestCase
{
    /**
     * @dataProvider getTestMissedPropertyExceptionData
     * @param array $input
     * @param string $missedPropertyName
     */
    public function testMissedPropertyException(array $input, string $missedPropertyName)
    {
        $this->expectExceptionMessage(
            sprintf("Invalid data to transform, property %s should be present", $missedPropertyName)
        );
        $this->expectException(InvalidArgumentException::class);

        $inputObj = (object) $input;

        $dataTransformer = new DataTransformer();
        $dataTransformer->transform($inputObj);
    }

    /**
     * @dataProvider getTestValidTransformData
     * @param array $input
     */
    public function testValidTransform(array $input)
    {
        $inputObj = (object) $input;
        $dataTransformer = new DataTransformer();
        $comicDTO = $dataTransformer->transform($inputObj);

        $this->assertInstanceOf(ComicDTO::class, $comicDTO);

        $expectedComicDTO = new ComicDTO();
        $expectedComicDTO->imageUrl = (string) $inputObj->img;
        $expectedComicDTO->title = (string) $inputObj->title;
        $expectedComicDTO->webUrl = sprintf(DataTransformer::WEB_URL, (int) $inputObj->num);
        $expectedComicDTO->publishDate = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            sprintf('%s-%s-%s 00:00:00', (string) $inputObj->year, (string) $inputObj->month, (string) $inputObj->day)
        );

        $this->assertEquals($expectedComicDTO, $comicDTO);
    }

    /**
     * @return array[]
     */
    public function getTestMissedPropertyExceptionData(): array
    {
        return [
            'no data' => [
                [],
                'img',
            ],
            'only image' => [
                [
                    'img' => 'my img',
                ],
                'title',
            ],
            'no month' => [
                [
                    'img'=>'my image',
                    'title' => 'my title',
                    'num' => 'my num',
                    'year' => 'my year',
                    'day' => 'my day',
                ],
                'month',
            ],
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
