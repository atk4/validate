<?php

declare(strict_types=1);

namespace Atk4\Validate\Tests;

use Atk4\Data\Schema\TestCase;

class DepTest extends TestCase
{
    public function testA(): void
    {
        // this throws depreciation notice starting PHP 8.1
        // Deprecated: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated
        // self::expectUserDeprecationMessage('Deprecated: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated');
        self::expectException(\TypeError::class);
        $a = strtotime($_ENV['foo'] ?? null);
    }

    public function testB(): void
    {
        // this throws depreciation notice starting PHP 8.1
        // Deprecated: date_parse_from_format(): Passing null to parameter #2 ($datetime) of type string is deprecated
        self::expectException(\TypeError::class);
        $a = date_parse_from_format('Y-m-d', $_ENV['foo'] ?? null);
    }
}
