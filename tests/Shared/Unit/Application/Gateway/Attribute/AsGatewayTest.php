<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Application\Gateway\Attribute;

use App\Shared\Application\Gateway\Attribute\AsGateway;
use PHPUnit\Framework\TestCase;

final class AsGatewayTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $context = 'UserContext';
        $domain = 'User';
        $operation = 'create';
        $middlewares = ['AuthMiddleware', 'ValidationMiddleware'];

        $asGateway = new AsGateway($context, $domain, $operation, $middlewares);

        $this->assertSame($context, $asGateway->context);
        $this->assertSame($domain, $asGateway->domain);
        $this->assertSame($operation, $asGateway->operation);
        $this->assertSame($middlewares, $asGateway->middlewares);
    }

    public function testConstructorWithEmptyMiddlewares(): void
    {
        $asGateway = new AsGateway('TestContext', 'TestDomain', 'read', []);

        $this->assertSame('TestContext', $asGateway->context);
        $this->assertSame('TestDomain', $asGateway->domain);
        $this->assertSame('read', $asGateway->operation);
        $this->assertSame([], $asGateway->middlewares);
    }

    public function testIsReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(AsGateway::class);

        $this->assertTrue($reflection->isReadonly());
    }

    public function testPropertiesArePublicAndReadonly(): void
    {
        $reflection = new \ReflectionClass(AsGateway::class);

        $contextProperty = $reflection->getProperty('context');
        $domainProperty = $reflection->getProperty('domain');
        $operationProperty = $reflection->getProperty('operation');
        $middlewaresProperty = $reflection->getProperty('middlewares');

        $this->assertTrue($contextProperty->isPublic());
        $this->assertTrue($contextProperty->isReadonly());

        $this->assertTrue($domainProperty->isPublic());
        $this->assertTrue($domainProperty->isReadonly());

        $this->assertTrue($operationProperty->isPublic());
        $this->assertTrue($operationProperty->isReadonly());

        $this->assertTrue($middlewaresProperty->isPublic());
        $this->assertTrue($middlewaresProperty->isReadonly());
    }

    public function testIsAttributeClass(): void
    {
        $reflectionClass = new \ReflectionClass(AsGateway::class);
        $attributes = $reflectionClass->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);

        $attribute = $attributes[0]->newInstance();
        $this->assertSame(\Attribute::TARGET_CLASS, $attribute->flags);
    }

    public function testCanBeUsedAsClassAttribute(): void
    {
        // Test that the attribute can be applied to a class
        $testClass = new class {
            // Empty test class for attribute testing
        };

        $reflection = new \ReflectionClass($testClass);

        // This test validates that AsGateway could be used as an attribute
        // by checking its attribute configuration
        $asGatewayReflection = new \ReflectionClass(AsGateway::class);
        $attributeInstances = $asGatewayReflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributeInstances);

        $attributeInstance = $attributeInstances[0]->newInstance();
        $this->assertInstanceOf(\Attribute::class, $attributeInstance);
        $this->assertSame(\Attribute::TARGET_CLASS, $attributeInstance->flags);
    }

    public function testConstructorWithDifferentMiddlewareTypes(): void
    {
        $middlewares = [
            'StringMiddleware',
            123, // This should be allowed as the array type is not strictly enforced
            [
                'nested' => 'array',
            ],
        ];

        $asGateway = new AsGateway('Context', 'Domain', 'operation', $middlewares);

        $this->assertSame($middlewares, $asGateway->middlewares);
        $this->assertCount(3, $asGateway->middlewares);
    }

    public function testImmutabilityOfProperties(): void
    {
        $originalMiddlewares = ['Middleware1', 'Middleware2'];
        $asGateway = new AsGateway('Context', 'Domain', 'operation', $originalMiddlewares);

        // Modify the original array
        $originalMiddlewares[] = 'Middleware3';

        // The AsGateway instance should not be affected
        $this->assertCount(2, $asGateway->middlewares);
        $this->assertSame(['Middleware1', 'Middleware2'], $asGateway->middlewares);
    }
}
