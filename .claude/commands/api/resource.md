---
description: Create API Platform resource with state providers/processors
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# API Platform Resource Creation

Create a complete API Platform resource following DDD architecture.

## Usage
`/api:resource [context] [resource-name]`

Example: `/api:resource Blog Article`

## Symfony Maker Integration

This command complements the Symfony Maker bundle. You can generate the API resource structure using:

```bash
# Generate all API components at once
docker compose exec app bin/console make:api:resource [Context] [Entity]

# Example:
docker compose exec app bin/console make:api:resource BlogContext Article
```

This Maker will create:
- API Resource class with all operations configured
- State Providers for read operations (Get and List)
- State Processors for write operations (Create, Update, Delete)
- All components properly integrated with Application Gateways
- Following project's DDD architecture

## Process

1. **Create Resource Structure**
   ```
   UI/Api/Rest/
   ├── Resource/
   │   └── ArticleResource.php
   ├── Provider/
   │   ├── GetArticleProvider.php      # Single item
   │   └── ListArticlesProvider.php    # Collection
   ├── Processor/
   │   ├── CreateArticleProcessor.php  # POST
   │   ├── UpdateArticleProcessor.php  # PUT/PATCH
   │   └── DeleteArticleProcessor.php  # DELETE
   └── Filter/
       └── ArticleSearchFilter.php      # Optional
   ```

2. **Create API Resource**
   ```php
   #[ApiResource(
       shortName: 'Article',
       operations: [
           new Get(
               uriTemplate: '/articles/{id}',
               provider: GetArticleProvider::class,
           ),
           new GetCollection(
               uriTemplate: '/articles',
               provider: ListArticlesProvider::class,
           ),
           new Post(
               uriTemplate: '/articles',
               processor: CreateArticleProcessor::class,
           ),
           new Put(
               uriTemplate: '/articles/{id}',
               provider: GetArticleProvider::class,
               processor: UpdateArticleProcessor::class,
           ),
           new Delete(
               uriTemplate: '/articles/{id}',
               processor: DeleteArticleProcessor::class,
           ),
       ],
   )]
   final class ArticleResource
   {
       public function __construct(
           public ?string $id = null,
           public ?string $title = null,
           public ?string $content = null,
           public ?string $status = null,
       ) {}
   }
   ```

3. **Create State Providers**
   - GetProvider: Fetch single resource via Gateway
   - ListProvider: Fetch collection with pagination
   - Transform Gateway responses to Resources
   - Handle not found cases

4. **Create State Processors**
   - Create: Handle POST requests
   - Update: Handle PUT/PATCH requests
   - Delete: Handle DELETE requests
   - Use Gateways for all operations
   - Transform exceptions to HTTP errors

5. **Add Validation**
   ```php
   final class ArticleResource
   {
       #[Assert\NotBlank]
       #[Assert\Length(min: 3, max: 200)]
       public ?string $title = null;
       
       #[Assert\NotBlank]
       #[Assert\Length(min: 10)]
       public ?string $content = null;
   }
   ```

6. **Create Behat Tests**
   ```gherkin
   Feature: Article API
     Scenario: Create new article
       When I send a POST request to "/api/articles" with:
         """
         {
           "title": "New Article",
           "content": "Article content"
         }
         """
       Then the response status code should be 201
       And the response should contain "New Article"
   ```

7. **Configure Security**
   - Add security attributes to operations
   - Create voters if needed
   - Test authorization scenarios

## Integration Points
- Uses Application Gateways
- Follows CQRS pattern
- Transforms domain objects to DTOs
- Handles errors gracefully

## Quality Standards
- Follow @docs/agent/instructions/api-platform-integration.md
- One provider/processor per operation
- Clear HTTP status codes
- Comprehensive error messages

## OpenAPI Documentation
- Resource properties become schema
- Validation rules appear in docs
- Examples in resource annotations
- Clear operation descriptions

## Next Steps
1. Test with Postman/curl
2. Add filters if needed
3. Create admin UI: `/ddd:admin`
4. Add more Behat scenarios