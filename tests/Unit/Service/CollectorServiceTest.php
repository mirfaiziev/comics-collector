<?php

namespace App\Test\Unit\Service;


use App\Service\CollectorService;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CollectorServiceTest extends KernelTestCase
{
    public function testConstructorExceptionWhenWrongArgumentsPassed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("All adapters passing to CollectorService should implement ApiAdapterInterface, but stdClass is not");

        $wrongAdapter = new \stdClass();
        new CollectorService([$wrongAdapter]);
    }
}