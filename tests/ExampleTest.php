<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true);
        $this->assertSame(4, 2 + 2);
    }

    public function testStringOperations(): void
    {
        $string = 'Hello World';

        $this->assertStringContainsString('World', $string);
        $this->assertStringStartsWith('Hello', $string);
        $this->assertSame(11, strlen($string));
    }
}
