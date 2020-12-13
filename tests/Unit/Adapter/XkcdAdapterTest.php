<?php

namespace App\Test\Unit\Adapter;

use App\Adapter\DataTransformer\XkcdDataTransformer;
use App\Adapter\XkcdAdapter;
use App\DTO\ComicDTO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class XkcdAdapterTest extends TestCase
{
    /**
     * @dataProvider getTestAdapterExceptionsData
     * @param string $content
     * @param string $exceptionClassName
     * @param string $expectedLogMessage
     */
    public function testAdapterExceptions(
        string $content,
        string $exceptionClassName,
        string $expectedLogMessage
    )
    {
        $responses = [
            new MockResponse($content)
        ];

        $client = new MockHttpClient($responses);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                sprintf('Exception %s was thrown, message: %s',
                    $exceptionClassName,
                    $expectedLogMessage
                )
            );

        $dataTransformer = $this->createMock(XkcdDataTransformer::class);

        $adapter = new XkcdAdapter($client, $logger, $dataTransformer);
        $adapter->getComics();
    }

    public function testValidExecution()
    {
        $firstResponse = ['num' => 100, 'url' => 'url0'];
        $responses[] = new MockResponse(json_encode($firstResponse));
        $comicObjs[] = (object)$firstResponse;

        for ($i = 1; $i < 10; $i++) {
            $response = ['url' => 'url' . $i];
            $responses[] = new MockResponse(json_encode(($response)));
            $comicObjs[] = (object)$response;
        }
        $client = new MockHttpClient($responses);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');
        $dataTransformer = $this->createMock(XkcdDataTransformer::class);
        $dataTransformer->expects($this->exactly(10))->method('transform');

        foreach ($comicObjs as $index => $comicObj) {
            $dataTransformer->expects($this->at($index))
                ->method('transform')
                ->with($comicObj)
                ->willReturn(new ComicDTO());
        }

        $adapter = new XkcdAdapter($client, $logger, $dataTransformer);
        $adapter->getComics();
    }

    /**
     * @return array
     */
    public function getTestAdapterExceptionsData(): array
    {
        return [
            'empty content' => [
                '',
                'RuntimeException',
                'Cannot decode response: \'\'',
            ],
            'not a json content' => [
                '{werwe:dfs',
                'RuntimeException',
                'Cannot decode response: \'{werwe:dfs\'',
            ],
            'correct json, but no num' => [
                '{"a":"b"}',
                'RangeException',
                'Cannot find \'num\' in the feed',
            ],
        ];
    }
}
