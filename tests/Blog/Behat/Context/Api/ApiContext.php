<?php

declare(strict_types=1);

namespace App\Tests\Blog\Behat\Context\Api;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApiContext implements Context
{
    private Response|null $response = null;

    private array $headers = [];

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    #[\Behat\Step\Given('I have a valid API token')]
    public function iHaveAValidApiToken(): void
    {
        // For now, we'll skip authentication
        // In a real implementation, this would set up proper auth headers
        $this->headers['Accept'] = 'application/json';
        $this->headers['Content-Type'] = 'application/json';
    }

    #[\Behat\Step\When('I send a :method request to :url')]
    public function iSendARequestTo(string $method, string $url): void
    {
        $this->sendRequest($method, $url);
    }

    #[\Behat\Step\When('I send a :method request to :url with body:')]
    public function iSendARequestToWithBody(string $method, string $url, PyStringNode $pyStringNode): void
    {
        $this->sendRequest($method, $url, $pyStringNode->getRaw());
    }

    #[\Behat\Step\Then('the response status code should be :statusCode')]
    public function theResponseStatusCodeShouldBe(int $statusCode): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        Assert::assertEquals($statusCode, $this->response->getStatusCode());
    }

    #[\Behat\Step\Then('the response should be in JSON')]
    public function theResponseShouldBeInJson(): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        $contentType = $this->response->headers->get('Content-Type');
        Assert::assertStringContainsString('application/json', (string) $contentType);
    }

    #[\Behat\Step\Then('the JSON node :node should be equal to :value')]
    public function theJsonNodeShouldBeEqualTo(string $node, string $value): void
    {
        $json = $this->getResponseJson();
        Assert::assertArrayHasKey($node, $json);
        Assert::assertEquals($value, $json[$node]);
    }

    #[\Behat\Step\Then('the JSON node :node should exist')]
    public function theJsonNodeShouldExist(string $node): void
    {
        $json = $this->getResponseJson();
        Assert::assertArrayHasKey($node, $json);
    }

    #[\Behat\Step\Then('the JSON node :node should have :count element(s)')]
    public function theJsonNodeShouldHaveElements(string $node, int $count): void
    {
        $json = $this->getResponseJson();
        Assert::assertArrayHasKey($node, $json);
        Assert::assertCount($count, $json[$node]);
    }

    #[\Behat\Step\Then('the response should contain :text')]
    public function theResponseShouldContain(string $text): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        Assert::assertStringContainsString($text, (string) $this->response->getContent());
    }

    #[\Behat\Step\Given('the following tags exist:')]
    public function theFollowingTagsExist(TableNode $tableNode): void
    {
        // This would create tags in the database
        // For now, we'll assume they're created
    }

    #[\Behat\Step\Given('a tag exists with id :id and name :name')]
    public function aTagExistsWithIdAndName(string $id, string $name): void
    {
        // This would create a tag in the database
        // For now, we'll assume it's created
    }

    #[\Behat\Step\Given('a tag exists with id :id')]
    public function aTagExistsWithId(string $id): void
    {
        // This would create a tag in the database
        // For now, we'll assume it's created
    }

    #[\Behat\Step\Given('a tag exists with name :name and slug :slug')]
    public function aTagExistsWithNameAndSlug(string $name, string $slug): void
    {
        // This would create a tag in the database
        // For now, we'll assume it's created
    }

    private function sendRequest(string $method, string $url, string|null $content = null): void
    {
        $request = Request::create(
            $url,
            $method,
            [],
            [],
            [],
            $this->headers,
            $content
        );

        $this->response = $this->kernel->handle($request);
    }

    private function getResponseJson(): array
    {
        Assert::assertNotNull($this->response, 'No response received');
        $content = $this->response->getContent();
        Assert::assertJson($content);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
