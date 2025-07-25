<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateCategory;

use App\BlogContext\Application\Gateway\CreateCategory\Gateway;
use PHPUnit\Framework\TestCase;

final class GatewayTest extends TestCase
{
    public function testItExtendsDefaultGateway(): void
    {
        // Assert Gateway extends DefaultGateway
        $gateway = new Gateway([]);
        $this->assertInstanceOf(\App\Shared\Application\Gateway\DefaultGateway::class, $gateway);
    }

    public function testItImplementsGatewayAttribute(): void
    {
        $reflection = new \ReflectionClass(Gateway::class);
        $attributes = $reflection->getAttributes(\App\Shared\Application\Gateway\Attribute\AsGateway::class);

        $this->assertCount(1, $attributes);

        $attribute = $attributes[0]->newInstance();
        $this->assertEquals('blog', $attribute->context);
        $this->assertEquals('category', $attribute->domain);
        $this->assertEquals('create_category', $attribute->operation);
    }
}
