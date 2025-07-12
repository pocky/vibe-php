<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Integration\Infrastructure\Persistence\Doctrine\ORM\Repository;

use App\BlogContext\Domain\CreateArticle\DataPersister\Article as CreateArticle;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ArticleRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ArticleRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $managerRegistry = static::getContainer()->get(ManagerRegistry::class);
        $this->repository = new ArticleRepository($managerRegistry);

        // Clean database for each test
        $this->cleanDatabase();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanDatabase();
        parent::tearDown();
    }

    public function testSaveCreateArticleCreatesNewEntity(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Test Article Title');
        $content = new Content('This is test content for the article.');
        $slug = new Slug('test-article-title');
        $status = ArticleStatus::DRAFT;
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $article = new CreateArticle(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            createdAt: $createdAt,
        );

        // When
        $this->repository->save($article);

        // Then
        $foundData = $this->repository->findById($articleId);
        $this->assertNotNull($foundData);
        $this->assertTrue($foundData->id->equals($articleId));
        $this->assertTrue($foundData->title->equals($title));
        $this->assertTrue($foundData->content->equals($content));
        $this->assertTrue($foundData->slug->equals($slug));
        $this->assertTrue($foundData->status->equals($status));
        $this->assertEquals($createdAt, $foundData->createdAt);
        $this->assertNull($foundData->publishedAt);
        $this->assertNull($foundData->updatedAt);
    }

    public function testSaveCreateArticleUpdatesExistingEntity(): void
    {
        // Given: Create initial article
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440001');
        $initialTitle = new Title('Initial Title');
        $initialContent = new Content('Initial content.');
        $initialSlug = new Slug('initial-title');
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $initialArticle = new CreateArticle(
            id: $articleId,
            title: $initialTitle,
            content: $initialContent,
            slug: $initialSlug,
            status: ArticleStatus::DRAFT,
            createdAt: $createdAt,
        );

        $this->repository->save($initialArticle);

        // When: Update the article
        $updatedTitle = new Title('Updated Title');
        $updatedContent = new Content('Updated content for the article.');
        $updatedSlug = new Slug('updated-title');

        $updatedArticle = new CreateArticle(
            id: $articleId,
            title: $updatedTitle,
            content: $updatedContent,
            slug: $updatedSlug,
            status: ArticleStatus::DRAFT,
            createdAt: $createdAt,
        );

        $this->repository->save($updatedArticle);

        // Then
        $foundData = $this->repository->findById($articleId);
        $this->assertNotNull($foundData);
        $this->assertTrue($foundData->title->equals($updatedTitle));
        $this->assertTrue($foundData->content->equals($updatedContent));
        $this->assertTrue($foundData->slug->equals($updatedSlug));
        $this->assertEquals($createdAt, $foundData->createdAt);
        $this->assertNotNull($foundData->updatedAt);
    }

    public function testFindByIdReturnsNullWhenArticleNotFound(): void
    {
        // Given
        $nonExistentId = new ArticleId('550e8400-e29b-41d4-a716-446655440999');

        // When
        $result = $this->repository->findById($nonExistentId);

        // Then
        $this->assertNull($result);
    }

    public function testExistsBySlugReturnsTrueWhenSlugExists(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440002');
        $slug = new Slug('existing-article-slug');

        $article = new CreateArticle(
            id: $articleId,
            title: new Title('Existing Article'),
            content: new Content('Content long enough for validation rules'),
            slug: $slug,
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable(),
        );

        $this->repository->save($article);

        // When
        $exists = $this->repository->existsBySlug($slug);

        // Then
        $this->assertTrue($exists);
    }

    public function testExistsBySlugReturnsFalseWhenSlugDoesNotExist(): void
    {
        // Given
        $nonExistentSlug = new Slug('non-existent-slug');

        // When
        $exists = $this->repository->existsBySlug($nonExistentSlug);

        // Then
        $this->assertFalse($exists);
    }

    public function testRemoveDeletesArticleFromDatabase(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440003');
        $article = new CreateArticle(
            id: $articleId,
            title: new Title('Article To Remove'),
            content: new Content('Content to remove which is long enough'),
            slug: new Slug('article-to-remove'),
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable(),
        );

        $this->repository->save($article);

        // Verify article exists
        $this->assertNotNull($this->repository->findById($articleId));

        // When
        $this->repository->remove($article);

        // Then
        $this->assertNull($this->repository->findById($articleId));
    }

    public function testRemoveDoesNotFailWhenArticleDoesNotExist(): void
    {
        // Given
        $nonExistentId = new ArticleId('550e8400-e29b-41d4-a716-446655440999');
        $article = new CreateArticle(
            id: $nonExistentId,
            title: new Title('Non Existent'),
            content: new Content('Content long enough for validation'),
            slug: new Slug('non-existent'),
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable(),
        );

        // When & Then (should not throw exception)
        $this->repository->remove($article);
        $this->assertTrue(true); // If we reach here, no exception was thrown
    }

    public function testSaveThrowsExceptionForUnsupportedArticleType(): void
    {
        // Given
        $unsupportedArticle = new \stdClass();

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported article type: stdClass');

        // When
        $this->repository->save($unsupportedArticle);
    }

    /**
     * Test database integrity with unique slug constraint
     */
    public function testDatabaseEnforcesUniqueSlugConstraint(): void
    {
        // Given: First article with a slug
        $firstArticleId = new ArticleId('550e8400-e29b-41d4-a716-446655440004');
        $duplicateSlug = new Slug('duplicate-slug');

        $firstArticle = new CreateArticle(
            id: $firstArticleId,
            title: new Title('First Article'),
            content: new Content('First content long enough for validation'),
            slug: $duplicateSlug,
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable(),
        );

        $this->repository->save($firstArticle);

        // When & Then: Second article with same slug should throw exception
        $secondArticleId = new ArticleId('550e8400-e29b-41d4-a716-446655440005');
        $secondArticle = new CreateArticle(
            id: $secondArticleId,
            title: new Title('Second Article'),
            content: new Content('Second content long enough for validation'),
            slug: $duplicateSlug,
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable(),
        );

        $this->expectException(\Exception::class);
        $this->repository->save($secondArticle);
    }

    private function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('TRUNCATE TABLE blog_articles RESTART IDENTITY CASCADE');
    }
}
