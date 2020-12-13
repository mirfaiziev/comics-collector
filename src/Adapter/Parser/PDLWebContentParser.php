<?php

namespace App\Adapter\Parser;


use RuntimeException;

class PDLWebContentParser
{
    /**
     * @param string $content
     * @param string $webUrl
     * @return string
     */
    public function getImageFromWebPageContent(string $content, string $webUrl): string
    {
        $regExp = '~<div class="wp-block-image".*src="(.*)".*\</div>~siU';
        $pregMatchResult = preg_match($regExp, $content, $matches);
        if ($pregMatchResult !== 1) {
            throw new RuntimeException(sprintf("Cannot find comic image in the web page from url: %s", $webUrl));
        }

        return $matches[1];
    }
}
