<?php

namespace App\Test\Unit\Adapter;

use App\Adapter\DataTransformer\PDLDataTransformer;
use App\Adapter\Parser\PDLWebContentParser;
use App\Adapter\PDLAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PDLAdapterTest extends TestCase
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

        $dataTransformer = $this->createMock(PDLDataTransformer::class);
        $webContentParser = $this->createMock(PDLWebContentParser::class);
        $adapter = new PDLAdapter($client, $logger, $dataTransformer, $webContentParser);
        $adapter->getComics();
    }

    public function testAdapterValid()
    {
        $content = <<<XML
            <xml>
                <channel>
                    <item>
                        <guid>https://example.com/myUrl0</guid>
                    </item>
                    <item>
                        <guid>https://example.com/myUrl1</guid>
                    </item>
                </channel>
            </xml>
XML;
        $responses[] = new MockResponse($content);

        $responses[] = new MockResponse('some response 1');
        $responses[] = new MockResponse('some response 2');

        $client = new MockHttpClient($responses);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $dataTransformer = $this->createMock(PDLDataTransformer::class);
        $dataTransformer->expects($this->exactly(2))
            ->method('transform');


        $webContentParser = $this->createMock(PDLWebContentParser::class);
        $webContentParser->expects($this->exactly(2))
            ->method('getImageFromWebPageContent');
        $webContentParser->expects($this->at(0))
            ->method('getImageFromWebPageContent')
            ->with('some response 1', 'https://example.com/myUrl0');
        $webContentParser->expects($this->at(1))
            ->method('getImageFromWebPageContent')
            ->with('some response 2', 'https://example.com/myUrl1');

        $adapter = new PDLAdapter($client, $logger, $dataTransformer, $webContentParser);
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
                'Cannot parse to xml the following response: \'\'',
            ],
            'corrupted xml' => [
                '<xml></xml',
                'RuntimeException',
                'Cannot parse to xml the following response: \'<xml></xml\'',
            ],
            'no channel property' => [
                '<xml></xml>',
                'RangeException',
                'Cannot find \'channel\' property in rss feed',
            ],
            'no item property in channel' => [
                '<xml><channel><key>value</key></channel></xml>',
                'RangeException',
                'Cannot find \'item\' property in the channel',
            ],
            'no guid in the item' => [
                '<xml><channel><item><key>Value</key></item></channel></xml>',
                'RangeException',
                'Cannot find \'guid\' in the item',
            ],
        ];
    }
}
