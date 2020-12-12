<?php

namespace App\Test\Unit\Service;


use App\Service\CollectorService;
use App\Service\SortComicsService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Contracts\Cache\CacheInterface;

class CollectorServiceTest extends TestCase
{
    public function testConstructorExceptionWhenWrongArgumentsPassed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("All adapters passing to CollectorService should implement ApiAdapterInterface, but stdClass is not");

        $wrongAdapter = new stdClass();
        $sortService = $this->createMock(SortComicsService::class);
        $cache = $this->createMock(CacheInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        new CollectorService([$wrongAdapter], $sortService, $cache, $logger);
    }
}
