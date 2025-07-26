---
description: Create Behat feature tests for API endpoints
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# Behat Feature Creation

## 📋 STRUCTURED WORKFLOW

**ℹ️ No maker available - Follow the structured template workflow**

See the complete workflow diagram: [API Behat Workflow](../workflow-graphs.md#apibehat---behat-tests-for-api)

```bash
# Manual creation required - Use Gherkin scenarios
# Follow existing API test patterns
```

Create comprehensive Behat tests for API endpoints and functional scenarios.

## Usage
`/api:behat [context] [feature-name]`

Example: `/api:behat Blog article-management`

## Process

1. **Create Feature File**
   ```
   features/
   └── blog/
       └── article-management.feature
   ```

2. **Write Feature Scenarios**
   ```gherkin
   Feature: Article Management
     In order to manage blog content
     As an API client
     I need to be able to create, read, update and delete articles
     
     Background:
       Given I am authenticated as an admin
       And the following categories exist:
         | id                                   | name       |
         | 550e8400-e29b-41d4-a716-446655440000 | Technology |
         | 550e8400-e29b-41d4-a716-446655440001 | Business   |
   
     Scenario: Create a new article
       When I send a POST request to "/api/articles" with:
         """
         {
           "title": "Introduction to DDD",
           "content": "Domain-Driven Design is...",
           "categoryId": "550e8400-e29b-41d4-a716-446655440000"
         }
         """
       Then the response status code should be 201
       And the response should contain "Introduction to DDD"
       And the JSON node "status" should be equal to "draft"
       And the JSON node "id" should exist
   
     Scenario: List published articles
       Given the following articles exist:
         | title          | status    | publishedAt |
         | Article One    | published | 2024-01-01  |
         | Article Two    | draft     | null        |
         | Article Three  | published | 2024-01-02  |
       When I send a GET request to "/api/articles?status=published"
       Then the response status code should be 200
       And the JSON node "[0].title" should be equal to "Article Three"
       And the JSON node "[1].title" should be equal to "Article One"
       And the JSON node "[2]" should not exist
   
     Scenario: Update article status
       Given an article exists with id "550e8400-e29b-41d4-a716-446655440000"
       When I send a PATCH request to "/api/articles/550e8400-e29b-41d4-a716-446655440000" with:
         """
         {
           "status": "published"
         }
         """
       Then the response status code should be 200
       And the JSON node "status" should be equal to "published"
       And the JSON node "publishedAt" should not be null
   
     Scenario: Delete an article
       Given an article exists with id "550e8400-e29b-41d4-a716-446655440000"
       When I send a DELETE request to "/api/articles/550e8400-e29b-41d4-a716-446655440000"
       Then the response status code should be 204
       When I send a GET request to "/api/articles/550e8400-e29b-41d4-a716-446655440000"
       Then the response status code should be 404
   ```

3. **Create Context Classes**
   ```php
   namespace App\Tests\BlogContext\Behat\Context;
   
   use Behat\Behat\Context\Context;
   use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
   
   final class ArticleContext implements Context
   {
       /**
        * @Given an article exists with id :id
        */
       public function anArticleExistsWithId(string $id): void
       {
           $article = ArticleFactory::createOne([
               'id' => Uuid::fromString($id),
               'title' => 'Test Article',
               'status' => 'draft',
           ]);
       }
       
       /**
        * @Given the following articles exist:
        */
       public function theFollowingArticlesExist(TableNode $table): void
       {
           foreach ($table->getHash() as $row) {
               ArticleFactory::createOne($row);
           }
       }
   }
   ```

4. **Test Error Scenarios**
   ```gherkin
   Scenario: Create article with invalid data
     When I send a POST request to "/api/articles" with:
       """
       {
         "title": "A",
         "content": "Too short"
       }
       """
     Then the response status code should be 422
     And the JSON node "violations[0].propertyPath" should be equal to "title"
     And the JSON node "violations[0].message" should contain "at least 3 characters"
   
   Scenario: Access unauthorized resource
     Given I am not authenticated
     When I send a POST request to "/api/articles" with:
       """
       {"title": "Test", "content": "Content"}
       """
     Then the response status code should be 401
   ```

5. **Test Pagination**
   ```gherkin
   Scenario: Paginate article list
     Given 25 articles exist
     When I send a GET request to "/api/articles?page=2&itemsPerPage=10"
     Then the response status code should be 200
     And the JSON node "hydra:totalItems" should be equal to 25
     And the JSON node "hydra:view.@id" should contain "page=2"
     And I should see 10 items in the collection
   ```

6. **Test Filters**
   ```gherkin
   Scenario: Search articles by keyword
     Given the following articles exist:
       | title                | content                    |
       | Introduction to DDD  | Domain-Driven Design...    |
       | API Best Practices   | RESTful API patterns...    |
       | Clean Architecture   | Architecture patterns...   |
     When I send a GET request to "/api/articles?search=DDD"
     Then the response status code should be 200
     And I should see 2 items in the collection
   ```

7. **Run Tests**
   ```bash
   # Run all Behat tests
   docker compose exec app vendor/bin/behat
   
   # Run specific feature
   docker compose exec app vendor/bin/behat features/blog/article-management.feature
   
   # Run with specific tags
   docker compose exec app vendor/bin/behat --tags @api
   ```

## Best Practices

### Scenario Organization
- Group related scenarios in features
- Use Background for common setup
- Keep scenarios focused and independent
- Test happy path and error cases

### Data Management
- Use factories for test data
- Clean database between scenarios
- Use meaningful test data
- Avoid hardcoded IDs when possible

### Assertions
- Test status codes first
- Verify response structure
- Check business rules
- Test side effects

## Quality Standards
- Follow @docs/testing/behat-guide.md
- Use descriptive scenario names
- Cover all API operations
- Test edge cases and errors

## 🚨 IMPORTANT: Implementation Guidelines

When creating Behat tests:

1. **Design API operations** first
2. **Create comprehensive test scenarios** for:
   - All API endpoints
   - Validation rules
   - Error handling
   - Edge cases
   - Performance scenarios
3. **Implement API features** with proper validation

**Create comprehensive Behat tests to ensure API quality and reliability.**

### Example Workflow

```bash
# 1. Create feature file (this command)
/code/api:behat BlogContext article-management

# 2. Create implementation tasks
/spec:tasks "Implement article API endpoints"

# 3. Implement API features
# Create all necessary endpoints with:
- Proper validation
- Error handling
- Status codes
- Response formatting
- Security checks
```

## Next Steps
1. Run tests in CI/CD pipeline
2. Add performance scenarios
3. Create integration tests
4. Monitor API usage patterns