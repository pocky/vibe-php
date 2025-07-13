<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\BlogContext\Application\Gateway\CreateArticle\Gateway as CreateArticleGateway;
use App\BlogContext\Application\Gateway\CreateArticle\Request as CreateArticleRequest;
use App\BlogContext\Application\Gateway\GetArticle\Gateway as GetArticleGateway;
use App\BlogContext\Application\Gateway\GetArticle\Request as GetArticleRequest;
use App\BlogContext\Application\Gateway\PublishArticle\Gateway as PublishArticleGateway;
use App\BlogContext\Application\Gateway\PublishArticle\Request as PublishArticleRequest;
use App\BlogContext\Application\Gateway\UpdateArticle\Gateway as UpdateArticleGateway;
use App\BlogContext\Application\Gateway\UpdateArticle\Request as UpdateArticleRequest;
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;
use App\Shared\Infrastructure\Generator\UuidGenerator;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class BlogApiContext implements Context
{
    private KernelBrowser $client;
    private Response|null $response = null;
    private readonly ArticleIdGenerator $articleIdGenerator;

    /** @var array<string, string> Maps expected IDs to actual created IDs */
    private array $articleIdMap = [];

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly CreateArticleGateway $createArticleGateway,
        private readonly GetArticleGateway $getArticleGateway,
        private readonly UpdateArticleGateway $updateArticleGateway,
        private readonly PublishArticleGateway $publishArticleGateway,
    ) {
        $this->articleIdGenerator = new ArticleIdGenerator(new UuidGenerator());
    }

    #[BeforeScenario]
    public function setUp(BeforeScenarioScope $scope): void
    {
        $this->client = new KernelBrowser($this->kernel);
        $this->articleIdMap = [];
    }

    #[Given('the database is empty')]
    public function theDatabaseIsEmpty(): void
    {
        // Database is automatically cleaned by DoctrineORMContext @BeforeScenario hook
        // No manual cleanup needed
    }

    #[Given('an article exists with the following data:')]
    public function anArticleExistsWithTheFollowingData(TableNode $table): void
    {
        $data = $table->getRowsHash();

        // Ensure content is at least 10 characters
        $content = $data['content'];
        if (10 > strlen($content)) {
            // Add meaningful content instead of just padding
            $content .= ' (extended for test)';
        }

        // Create the article with basic data
        $createRequest = CreateArticleRequest::fromData([
            'title' => $data['title'],
            'content' => $content,
            'slug' => $data['slug'],
        ]);

        $createResponse = ($this->createArticleGateway)($createRequest);
        $createdId = $createResponse->data()['articleId'];

        // Store the mapping if an expected ID was provided
        if (isset($data['id'])) {
            $this->articleIdMap[$data['id']] = $createdId;
        }

        // If status is published, use PublishArticleGateway for proper publishing
        if (isset($data['status']) && 'published' === $data['status']) {
            $publishRequest = PublishArticleRequest::fromData([
                'articleId' => $createdId,
            ]);

            ($this->publishArticleGateway)($publishRequest);
        }
        // If status is not draft and not published, use UpdateArticleGateway
        elseif (isset($data['status']) && 'draft' !== $data['status']) {
            $updateRequest = UpdateArticleRequest::fromData([
                'articleId' => $createdId,
                'title' => $data['title'],
                'content' => $content,
                'slug' => $data['slug'],
                'status' => $data['status'],
            ]);

            ($this->updateArticleGateway)($updateRequest);
        }
    }

    #[Given('an article exists with slug :slug')]
    public function anArticleExistsWithSlug(string $slug): void
    {
        $createRequest = CreateArticleRequest::fromData([
            'title' => 'Existing Article with SEO Title',
            'content' => 'Content with at least 10 characters and enough content for SEO validation requirements.',
            'slug' => $slug,
        ]);

        ($this->createArticleGateway)($createRequest);
    }

    #[Given(':count articles exist with alternating statuses')]
    public function articlesExistWithAlternatingStatuses(int $count): void
    {
        for ($i = 1; $i <= $count; ++$i) {
            $createRequest = CreateArticleRequest::fromData([
                'title' => "Article {$i} with SEO Title",
                'content' => "Content for article {$i} that is long enough to meet SEO requirements and pass validation checks.",
                'slug' => "article-{$i}",
            ]);

            $createResponse = ($this->createArticleGateway)($createRequest);

            // Publish even-numbered articles
            if (0 === $i % 2) {
                $publishRequest = PublishArticleRequest::fromData([
                    'articleId' => $createResponse->data()['articleId'],
                ]);

                ($this->publishArticleGateway)($publishRequest);
            }
        }
    }

    #[Given(':count published articles exist')]
    public function publishedArticlesExist(int $count): void
    {
        for ($i = 1; $i <= $count; ++$i) {
            $createRequest = CreateArticleRequest::fromData([
                'title' => "Article {$i} with SEO Title",
                'content' => "Content {$i} that is long enough to pass validation and meet all SEO requirements.",
                'slug' => "article-{$i}",
            ]);

            $createResponse = ($this->createArticleGateway)($createRequest);

            // Publish the article
            $publishRequest = PublishArticleRequest::fromData([
                'articleId' => $createResponse->data()['articleId'],
            ]);

            ($this->publishArticleGateway)($publishRequest);
        }
    }

    #[When('I make a :method request to :uri')]
    public function iMakeARequestTo(string $method, string $uri): void
    {
        // Replace any mapped IDs in the URI
        $uri = $this->replaceMappedIds($uri);

        $this->client->request($method, $uri);
        $this->response = $this->client->getResponse();
    }

    #[When('I make a :method request to :uri with JSON:')]
    public function iMakeARequestToWithJson(string $method, string $uri, PyStringNode $json): void
    {
        // Replace any mapped IDs in the URI
        $uri = $this->replaceMappedIds($uri);

        // Replace any mapped IDs in the JSON content
        $jsonContent = $this->replaceMappedIds($json->getRaw());

        $server = [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT' => 'application/ld+json',
        ];

        $this->client->request($method, $uri, [], [], $server, $jsonContent);
        $this->response = $this->client->getResponse();
    }

    #[Then('the response should have status code :statusCode')]
    public function theResponseShouldHaveStatusCode(int $statusCode): void
    {
        Assert::assertNotNull($this->response, 'No request has been made');

        // If we get a 500 error, show the content for debugging
        if (500 === $this->response->getStatusCode() && 500 !== $statusCode) {
            $content = $this->response->getContent();

            // Try to extract error information from JSON response first
            $decoded = json_decode($content, true);
            if ($decoded && isset($decoded['detail'])) {
                throw new \Exception("Server Error 500: {$decoded['detail']}");
            }

            // If not JSON, try to extract from HTML
            if (str_contains($content, 'Exception')) {
                preg_match('/<title>(.*?)<\/title>/', $content, $matches);
                $errorTitle = $matches[1] ?? 'Unknown error';
                throw new \Exception("Server Error 500: {$errorTitle}");
            }

            // Show raw content if can't extract anything meaningful
            throw new \Exception('Server Error 500: ' . substr($content, 0, 500));
        }

        Assert::assertEquals($statusCode, $this->response->getStatusCode());
    }

    #[Then('the response should have header :header with value :value')]
    public function theResponseShouldHaveHeaderWithValue(string $header, string $value): void
    {
        Assert::assertNotNull($this->response);
        Assert::assertEquals($value, $this->response->headers->get($header));
    }

    #[Then('the response should contain JSON:')]
    public function theResponseShouldContainJson(PyStringNode $expectedJson): void
    {
        Assert::assertNotNull($this->response);

        // Replace mapped IDs in expected JSON
        $expectedJsonContent = $this->replaceMappedIds($expectedJson->getRaw());
        $expected = json_decode($expectedJsonContent, true);
        $actual = json_decode($this->response->getContent(), true);

        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual);
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    Assert::assertArrayHasKey($subKey, $actual[$key]);
                    Assert::assertEquals($subValue, $actual[$key][$subKey]);
                }
            } else {
                Assert::assertEquals($value, $actual[$key]);
            }
        }
    }

    #[Then('the response should contain a non-empty :property property')]
    public function theResponseShouldContainANonEmptyProperty(string $property): void
    {
        Assert::assertNotNull($this->response);

        $data = json_decode($this->response->getContent(), true);
        Assert::assertArrayHasKey($property, $data);
        Assert::assertNotEmpty($data[$property]);
    }

    #[Then('the collection should contain :count items')]
    public function theCollectionShouldContainItems(int $count): void
    {
        Assert::assertNotNull($this->response);

        $data = json_decode($this->response->getContent(), true);
        Assert::assertArrayHasKey('member', $data);
        Assert::assertCount($count, $data['member']);
    }

    #[Then('the article should be updated in the database')]
    public function theArticleShouldBeUpdatedInTheDatabase(): void
    {
        $data = json_decode($this->response->getContent(), true);
        $articleId = $data['id'];

        // Use GetArticleGateway to verify the update
        $getRequest = GetArticleRequest::fromData([
            'id' => $articleId,
        ]);
        $getResponse = ($this->getArticleGateway)($getRequest);

        $articleData = $getResponse->data();
        Assert::assertEquals('Updated Title', $articleData['title']);
        Assert::assertEquals('Updated content', $articleData['content']);
        Assert::assertEquals('updated-slug', $articleData['slug']);
        Assert::assertEquals('published', $articleData['status']);
    }

    #[Then('the article should still exist in the database')]
    public function theArticleShouldStillExistInTheDatabase(): void
    {
        // Get the actual ID from our mapping
        $expectedId = '550e8400-e29b-41d4-a716-446655440000';
        $actualId = $this->articleIdMap[$expectedId] ?? $expectedId;

        // Use GetArticleGateway to verify existence
        try {
            $getRequest = GetArticleRequest::fromData([
                'id' => $actualId,
            ]);
            ($this->getArticleGateway)($getRequest);
            // If no exception, the article exists
        } catch (\Exception) {
            Assert::fail('The article should still exist but was not found');
        }
    }

    /**
     * Replace mapped IDs in a string
     */
    private function replaceMappedIds(string $content): string
    {
        foreach ($this->articleIdMap as $expectedId => $actualId) {
            $content = str_replace($expectedId, $actualId, $content);
        }

        return $content;
    }
}
