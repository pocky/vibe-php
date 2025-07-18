# Behat Feature Template

## Feature File Structure

```gherkin
Feature: [Entity] Management
  In order to manage [entities] in the system
  As an API client
  I need to be able to create, read, update and delete [entities]

  Background:
    Given I am authenticated as an admin
    And the following test data exists:
      | id                                   | name           |
      | 550e8400-e29b-41d4-a716-446655440000 | Test Category  |
      | 550e8400-e29b-41d4-a716-446655440001 | Test Category2 |

  Scenario: Create a new [entity]
    When I send a POST request to "/api/[entities]" with:
      """
      {
        "name": "New [Entity]",
        "description": "This is a test [entity]"
      }
      """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node "name" should be equal to "New [Entity]"
    And the JSON node "status" should be equal to "draft"
    And the JSON node "id" should exist
    And the JSON node "createdAt" should exist

  Scenario: Get a single [entity]
    Given a [entity] exists with id "550e8400-e29b-41d4-a716-446655440000"
    When I send a GET request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440000"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "id" should be equal to "550e8400-e29b-41d4-a716-446655440000"
    And the JSON node "name" should exist
    And the JSON node "status" should exist

  Scenario: List all [entities]
    Given the following [entities] exist:
      | id                                   | name          | status  |
      | 550e8400-e29b-41d4-a716-446655440002 | [Entity] One  | active  |
      | 550e8400-e29b-41d4-a716-446655440003 | [Entity] Two  | draft   |
      | 550e8400-e29b-41d4-a716-446655440004 | [Entity] Three| active  |
    When I send a GET request to "/api/[entities]"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a collection
    And I should see 3 items in the collection

  Scenario: Filter [entities] by status
    Given the following [entities] exist:
      | name          | status   |
      | Active One    | active   |
      | Draft One     | draft    |
      | Active Two    | active   |
      | Archived One  | archived |
    When I send a GET request to "/api/[entities]?status=active"
    Then the response status code should be 200
    And I should see 2 items in the collection
    And the JSON node "[0].status" should be equal to "active"
    And the JSON node "[1].status" should be equal to "active"

  Scenario: Search [entities] by name
    Given the following [entities] exist:
      | name                    | description                |
      | Important Document      | Contains important info    |
      | Regular File           | Just a regular file        |
      | Important Notes        | Important meeting notes    |
    When I send a GET request to "/api/[entities]?search=Important"
    Then the response status code should be 200
    And I should see 2 items in the collection

  Scenario: Update a [entity]
    Given a [entity] exists with:
      | id     | 550e8400-e29b-41d4-a716-446655440000 |
      | name   | Original Name                         |
      | status | draft                                 |
    When I send a PUT request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440000" with:
      """
      {
        "name": "Updated Name",
        "description": "Updated description",
        "status": "active"
      }
      """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "Updated Name"
    And the JSON node "description" should be equal to "Updated description"
    And the JSON node "status" should be equal to "active"
    And the JSON node "updatedAt" should not be equal to the original value

  Scenario: Partially update a [entity]
    Given a [entity] exists with id "550e8400-e29b-41d4-a716-446655440000"
    When I send a PATCH request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440000" with:
      """
      {
        "status": "active"
      }
      """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "active"

  Scenario: Delete a [entity]
    Given a [entity] exists with id "550e8400-e29b-41d4-a716-446655440000"
    When I send a DELETE request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440000"
    Then the response status code should be 204
    When I send a GET request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440000"
    Then the response status code should be 404

  # Error Scenarios

  Scenario: Create [entity] with invalid data
    When I send a POST request to "/api/[entities]" with:
      """
      {
        "name": "A",
        "description": ""
      }
      """
    Then the response status code should be 422
    And the response should be in JSON
    And the JSON node "violations" should exist
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "at least 2 characters"

  Scenario: Create [entity] with duplicate name
    Given a [entity] exists with name "Existing [Entity]"
    When I send a POST request to "/api/[entities]" with:
      """
      {
        "name": "Existing [Entity]",
        "description": "Duplicate name test"
      }
      """
    Then the response status code should be 409
    And the JSON node "detail" should contain "already exists"

  Scenario: Get non-existent [entity]
    When I send a GET request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440999"
    Then the response status code should be 404
    And the JSON node "detail" should contain "not found"

  Scenario: Update non-existent [entity]
    When I send a PUT request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440999" with:
      """
      {
        "name": "Updated Name",
        "status": "active"
      }
      """
    Then the response status code should be 404

  Scenario: Delete non-existent [entity]
    When I send a DELETE request to "/api/[entities]/550e8400-e29b-41d4-a716-446655440999"
    Then the response status code should be 404

  # Authentication Scenarios

  Scenario: Access without authentication
    Given I am not authenticated
    When I send a GET request to "/api/[entities]"
    Then the response status code should be 401

  Scenario: Create [entity] without proper permissions
    Given I am authenticated as a regular user
    When I send a POST request to "/api/[entities]" with:
      """
      {
        "name": "New [Entity]"
      }
      """
    Then the response status code should be 403

  # Pagination Scenarios

  Scenario: Paginate [entity] list
    Given 50 [entities] exist
    When I send a GET request to "/api/[entities]?page=2&itemsPerPage=20"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 50
    And I should see 20 items in the collection
    And the JSON node "hydra:view.@id" should contain "page=2"
    And the JSON node "hydra:view.hydra:first" should contain "page=1"
    And the JSON node "hydra:view.hydra:last" should contain "page=3"
    And the JSON node "hydra:view.hydra:next" should contain "page=3"
    And the JSON node "hydra:view.hydra:previous" should contain "page=1"

  Scenario: Request page beyond available data
    Given 10 [entities] exist
    When I send a GET request to "/api/[entities]?page=5&itemsPerPage=20"
    Then the response status code should be 200
    And I should see 0 items in the collection
```

## Context Class Template

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Context;

use App\Tests\Shared\Behat\ApiContext;
use App\[Context]Context\Infrastructure\Persistence\Doctrine\ORM\Entity\[Context][Entity];
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Test\Factories;

final class [Entity]Context implements Context
{
    use Factories;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiContext $apiContext,
    ) {}

    /**
     * @Given a [entity] exists with id :id
     */
    public function a[Entity]ExistsWithId(string $id): void
    {
        $[entity] = new [Context][Entity](
            id: Uuid::fromString($id),
            name: 'Test [Entity]',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($[entity]);
        $this->entityManager->flush();
    }

    /**
     * @Given a [entity] exists with:
     */
    public function a[Entity]ExistsWith(TableNode $table): void
    {
        $data = $table->getRowsHash();
        
        $[entity] = new [Context][Entity](
            id: isset($data['id']) ? Uuid::fromString($data['id']) : Uuid::v7(),
            name: $data['name'],
            status: $data['status'] ?? 'draft',
            description: $data['description'] ?? null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($[entity]);
        $this->entityManager->flush();
    }

    /**
     * @Given a [entity] exists with name :name
     */
    public function a[Entity]ExistsWithName(string $name): void
    {
        $[entity] = new [Context][Entity](
            id: Uuid::v7(),
            name: $name,
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($[entity]);
        $this->entityManager->flush();
    }

    /**
     * @Given the following [entities] exist:
     */
    public function theFollowing[Entity]sExist(TableNode $table): void
    {
        foreach ($table->getHash() as $row) {
            $[entity] = new [Context][Entity](
                id: isset($row['id']) ? Uuid::fromString($row['id']) : Uuid::v7(),
                name: $row['name'],
                status: $row['status'] ?? 'draft',
                description: $row['description'] ?? null,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            );

            $this->entityManager->persist($[entity]);
        }
        
        $this->entityManager->flush();
    }

    /**
     * @Given :count [entities] exist
     */
    public function [entity]sExist(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $[entity] = new [Context][Entity](
                id: Uuid::v7(),
                name: sprintf('[Entity] %d', $i),
                status: $i % 3 === 0 ? 'archived' : ($i % 2 === 0 ? 'active' : 'draft'),
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            );

            $this->entityManager->persist($[entity]);
        }
        
        $this->entityManager->flush();
    }

    /**
     * @Then I should see :count items in the collection
     */
    public function iShouldSeeItemsInTheCollection(int $count): void
    {
        $response = json_decode($this->apiContext->getResponse()->getContent(), true);
        
        if (isset($response['hydra:member'])) {
            // Hydra collection format
            $actualCount = count($response['hydra:member']);
        } else {
            // Plain array format
            $actualCount = count($response);
        }
        
        if ($actualCount !== $count) {
            throw new \Exception(sprintf(
                'Expected %d items in collection, but got %d',
                $count,
                $actualCount
            ));
        }
    }

    /**
     * @Then the JSON should be a collection
     */
    public function theJsonShouldBeACollection(): void
    {
        $response = json_decode($this->apiContext->getResponse()->getContent(), true);
        
        if (!is_array($response) && !isset($response['hydra:member'])) {
            throw new \Exception('Response is not a collection');
        }
    }
}
```

## Behat Configuration Addition

Add to `behat.dist.php`:

```php
'[context]' => [
    'paths' => ['%paths.base%/features/[context]'],
    'contexts' => [
        App\Tests\Shared\Behat\ApiContext::class,
        App\Tests\Shared\Behat\HookContext::class,
        App\Tests\[Context]Context\Behat\Context\[Entity]Context::class,
    ],
],
```