<?php

namespace App\Adapter\Parser;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class PDLWebContentParserTest extends TestCase
{

    public function testGetImageFromWebPageContentException()
    {
        $content = <<<HTML
            <div class="wp-block-image">
                <figure>
                <span>No images</span>
</figure>
</div>
HTML;

        $parser = new PDLWebContentParser();
        $url = 'my url';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot find comic image in the web page from url: my url');
        $parser->getImageFromWebPageContent($content, $url);
    }

    public function testGetImageFromWebPageContentValid()
    {
        $content = <<<HTML
            <div class="wp-block-image">
                <figure>
                <img src="//path/to/image" alt="my alt" />
</figure>
</div>
HTML;

        $parser = new PDLWebContentParser();
        $this->assertEquals(
            '//path/to/image',
            $parser->getImageFromWebPageContent($content, 'my url')
        );
    }
}
