<?php

declare(strict_types=1);

namespace App\Tests\Blog\Functional\UI\Api\Rest;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

final class TagResourceTest extends ApiTestCase
{
    public function testCreateTag(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/blog/tags', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'Technology',
                'slug' => 'technology',
            ],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            'name' => 'Technology',
            'slug' => 'technology',
        ]);
    }

    public function testGetTagCollection(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/blog/tags');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/api/contexts/Tag',
            '@id' => '/api/blog/tags',
            '@type' => 'hydra:Collection',
        ]);
    }

    public function testCreateTagWithValidation(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/blog/tags', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'name' => '',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertJsonContains([
            '@type' => 'ConstraintViolation',
            'title' => 'An error occurred',
        ]);
    }
}
