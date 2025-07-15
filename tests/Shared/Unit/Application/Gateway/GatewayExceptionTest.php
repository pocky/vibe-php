<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Application\Gateway;

use App\Shared\Application\Gateway\GatewayException;
use PHPUnit\Framework\TestCase;

final class GatewayExceptionTest extends TestCase
{
    public function testConstructorBuildsMessageCorrectly(): void
    {
        $message = 'Test error message';
        $originalException = new \RuntimeException('Original error', 0);

        $gatewayException = new GatewayException($message, $originalException);

        $this->assertStringContainsString($message, $gatewayException->getMessage());
        $this->assertStringContainsString('Original error', $gatewayException->getMessage());
        $this->assertSame($originalException, $gatewayException->getPrevious());
    }

    public function testExceptionChaining(): void
    {
        $originalException = new \InvalidArgumentException('Invalid input');
        $gatewayException = new GatewayException('Gateway error', $originalException);

        $this->assertInstanceOf(\Exception::class, $gatewayException);
        $this->assertSame($originalException, $gatewayException->getPrevious());
    }

    public function testMessageFormatting(): void
    {
        $message = 'User creation failed';
        $originalException = new \Exception('Database connection failed');
        $gatewayException = new GatewayException($message, $originalException);

        $this->assertStringContainsString($message, $gatewayException->getMessage());
        $this->assertStringContainsString('Database connection failed', $gatewayException->getMessage());
        $this->assertSame($originalException, $gatewayException->getPrevious());
    }
}
