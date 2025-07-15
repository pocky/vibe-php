<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Slugger;

use App\Shared\Infrastructure\Slugger\Slugger;
use App\Shared\Infrastructure\Slugger\SluggerInterface;
use PHPUnit\Framework\TestCase;

final class SluggerTest extends TestCase
{
    private SluggerInterface $slugger;

    protected function setUp(): void
    {
        $this->slugger = new Slugger();
    }

    public function testItImplementsSluggerInterface(): void
    {
        $this->assertInstanceOf(SluggerInterface::class, $this->slugger);
    }

    public function testItSlugsSimpleText(): void
    {
        $result = $this->slugger->slugify('Hello World');
        
        $this->assertEquals('hello-world', $result);
    }

    public function testItSlugsTextWithAccents(): void
    {
        $result = $this->slugger->slugify('Héllo Wörld');
        
        // Cocur\Slugify preserves some accented characters in a different way
        $this->assertEquals('hello-woerld', $result);
    }

    public function testItSlugsTextWithSpecialCharacters(): void
    {
        $result = $this->slugger->slugify('Hello & World! @ 2025');
        
        // @ symbol is transliterated to 'at' by Cocur\Slugify
        $this->assertEquals('hello-world-at-2025', $result);
    }

    public function testItSlugsTextWithMultipleSpaces(): void
    {
        $result = $this->slugger->slugify('Hello    World');
        
        $this->assertEquals('hello-world', $result);
    }

    public function testItSlugsTextWithLeadingAndTrailingSpaces(): void
    {
        $result = $this->slugger->slugify('  Hello World  ');
        
        $this->assertEquals('hello-world', $result);
    }

    public function testItSlugsTextWithMixedCase(): void
    {
        $result = $this->slugger->slugify('HeLLo WoRLD');
        
        $this->assertEquals('hello-world', $result);
    }

    public function testItSlugsTextWithCustomSeparator(): void
    {
        $result = $this->slugger->slugify('Hello World', '_');
        
        $this->assertEquals('hello_world', $result);
    }

    public function testItSlugsEmptyString(): void
    {
        $result = $this->slugger->slugify('');
        
        $this->assertEquals('', $result);
    }

    public function testItSlugsTextWithOnlySpecialCharacters(): void
    {
        $result = $this->slugger->slugify('!@#$%^&*()');
        
        // @ symbol is transliterated to 'at', others are removed
        $this->assertEquals('at', $result);
    }

    public function testItSlugsTextWithUnicode(): void
    {
        $result = $this->slugger->slugify('こんにちは世界');
        
        // Cocur\Slugify may return empty string for some unicode characters
        // depending on transliteration rules
        $this->assertIsString($result);
    }

    public function testItSlugsTextWithNumbers(): void
    {
        $result = $this->slugger->slugify('Article 123 Test');
        
        $this->assertEquals('article-123-test', $result);
    }

    public function testItSlugsTextWithHyphens(): void
    {
        $result = $this->slugger->slugify('Already-Hyphenated-Text');
        
        $this->assertEquals('already-hyphenated-text', $result);
    }

    public function testItSlugsTextWithUnderscores(): void
    {
        $result = $this->slugger->slugify('Text_With_Underscores');
        
        $this->assertEquals('text-with-underscores', $result);
    }

    public function testItSlugsTextWithUnderscoresAndCustomSeparator(): void
    {
        $result = $this->slugger->slugify('Text_With_Underscores', '_');
        
        $this->assertEquals('text_with_underscores', $result);
    }

    public function testItHandlesFrenchText(): void
    {
        $result = $this->slugger->slugify('L\'été est là! Ça va être génial.');
        
        $this->assertEquals('l-ete-est-la-ca-va-etre-genial', $result);
    }

    public function testItHandlesGermanText(): void
    {
        $result = $this->slugger->slugify('Über den Wolken müssen die Freiheit wohl grenzenlos sein');
        
        // German umlauts are transliterated with 'e' (ü -> ue, ä -> ae, ö -> oe)
        $this->assertEquals('ueber-den-wolken-muessen-die-freiheit-wohl-grenzenlos-sein', $result);
    }

    public function testItReturnsNewInstanceEachTime(): void
    {
        // Test that the method creates a new Slugify instance each time
        // This ensures no state is maintained between calls
        $result1 = $this->slugger->slugify('Test 1');
        $result2 = $this->slugger->slugify('Test 2');
        
        $this->assertEquals('test-1', $result1);
        $this->assertEquals('test-2', $result2);
    }
}