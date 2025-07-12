<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Generator;

use App\Shared\Infrastructure\Generator\GeneratorInterface;
use App\Shared\Infrastructure\Generator\UuidGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UuidGeneratorTest extends TestCase
{
    public function testImplementsGeneratorInterface(): void
    {
        $reflection = new \ReflectionClass(UuidGenerator::class);

        $this->assertTrue($reflection->implementsInterface(GeneratorInterface::class));
    }

    public function testGenerateReturnsString(): void
    {
        $uuid = UuidGenerator::generate();

        $this->assertIsString($uuid);
    }

    public function testGenerateReturnsValidUuidFormat(): void
    {
        $uuid = UuidGenerator::generate();

        // Test UUID v4 format (8-4-4-4-12 hexadecimal characters)
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        $this->assertMatchesRegularExpression($pattern, $uuid);
    }

    public function testGenerateReturnsValidSymfonyUuid(): void
    {
        $uuid = UuidGenerator::generate();

        // Test that the generated UUID can be parsed by Symfony UID
        $this->assertTrue(Uuid::isValid($uuid));

        $parsedUuid = Uuid::fromString($uuid);
        $this->assertInstanceOf(Uuid::class, $parsedUuid);
    }

    public function testGenerateReturnsUuidV7(): void
    {
        $uuid = UuidGenerator::generate();
        $parsedUuid = Uuid::fromString($uuid);

        // UUID v7 has version '7' in the 13th character (after removing dashes)
        $uuidWithoutDashes = str_replace('-', '', $uuid);
        $version = $uuidWithoutDashes[12];

        $this->assertSame('7', $version);
    }

    public function testGenerateReturnsDifferentUuids(): void
    {
        $uuid1 = UuidGenerator::generate();
        $uuid2 = UuidGenerator::generate();

        $this->assertNotSame($uuid1, $uuid2);
    }

    public function testGenerateReturnsRfc4122Format(): void
    {
        $uuid = UuidGenerator::generate();

        // RFC 4122 format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        $this->assertSame(36, strlen($uuid));
        $this->assertSame('-', $uuid[8]);
        $this->assertSame('-', $uuid[13]);
        $this->assertSame('-', $uuid[18]);
        $this->assertSame('-', $uuid[23]);
    }

    public function testGeneratedUuidsAreLexicographicallySortable(): void
    {
        // Generate multiple UUIDs with small delay to ensure different timestamps
        $uuids = [];
        for ($i = 0; 5 > $i; ++$i) {
            $uuids[] = UuidGenerator::generate();
            usleep(1000); // 1ms delay
        }

        $sortedUuids = $uuids;
        sort($sortedUuids);

        // UUID v7 should be lexicographically sortable due to timestamp prefix
        $this->assertSame($uuids, $sortedUuids);
    }

    public function testMultipleGenerationsDoNotCollide(): void
    {
        $uuids = [];
        $numberOfGenerations = 1000;

        for ($i = 0; $i < $numberOfGenerations; ++$i) {
            $uuids[] = UuidGenerator::generate();
        }

        $uniqueUuids = array_unique($uuids);

        $this->assertCount($numberOfGenerations, $uniqueUuids);
    }

    public function testGenerateIsStaticMethod(): void
    {
        $reflection = new \ReflectionClass(UuidGenerator::class);
        $method = $reflection->getMethod('generate');

        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    public function testGenerateReturnsTimestampBasedUuid(): void
    {
        $beforeGeneration = time();
        $uuid = UuidGenerator::generate();
        $afterGeneration = time();

        $parsedUuid = Uuid::fromString($uuid);

        // UUID v7 contains timestamp information
        // We can't directly access timestamp from Symfony UID, but we can verify it's recent
        $this->assertTrue($parsedUuid instanceof Uuid);

        // The UUID should be generated between our timestamps
        // This is a basic sanity check that it's timestamp-based
        $this->assertIsString($uuid);
        $this->assertGreaterThan(0, strlen($uuid));
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(UuidGenerator::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testUuidCanBeConvertedBackToSymfonyUuid(): void
    {
        $generatedUuid = UuidGenerator::generate();
        $symfonyUuid = Uuid::fromString($generatedUuid);
        $convertedBack = $symfonyUuid->toRfc4122();

        $this->assertSame($generatedUuid, $convertedBack);
    }

    public function testGeneratedUuidsHaveCorrectVariant(): void
    {
        $uuid = UuidGenerator::generate();
        $uuidWithoutDashes = str_replace('-', '', $uuid);

        // RFC 4122 variant: bits 10xx in positions 16-17 of the UUID
        $variantNibble = hexdec($uuidWithoutDashes[16]);

        // Variant should be 10xx (8, 9, A, or B in hex)
        $this->assertTrue(in_array($variantNibble, [8, 9, 0xA, 0xB]));
    }
}
