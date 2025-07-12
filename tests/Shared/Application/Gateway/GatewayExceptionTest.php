<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application\Gateway;

use App\Shared\Application\Gateway\GatewayException;
use PHPUnit\Framework\TestCase;

final class GatewayExceptionTest extends TestCase
{
    public function testConstructorBuildsMessageCorrectly(): void
    {
        $originalMessage = 'Test error message';
        $originalFile = '/path/to/file.php';
        $originalException = new \RuntimeException('Original error', 0);

        // Mock getFile() method by using reflection
        $reflection = new \ReflectionClass($originalException);
        $fileProperty = $reflection->getProperty('file');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($originalException, $originalFile);

        $gatewayException = new GatewayException($originalMessage, $originalException);

        $expectedMessage = sprintf(
            '%s in %s: %s',
            $originalMessage,
            $originalFile,
            $originalException->getMessage()
        );

        $this->assertSame($expectedMessage, $gatewayException->getMessage());
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
        $originalException = new \Exception('Database connection failed');
        $gatewayException = new GatewayException('User creation failed', $originalException);

        $this->assertStringContainsString('User creation failed', $gatewayException->getMessage());
        $this->assertStringContainsString('Database connection failed', $gatewayException->getMessage());
        $this->assertStringContainsString(' in ', $gatewayException->getMessage());
    }
}
