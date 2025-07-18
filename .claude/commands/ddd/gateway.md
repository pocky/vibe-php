---
description: Create a gateway with middleware pipeline
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# Gateway Creation

Create an application gateway following the Gateway pattern with middleware pipeline.

## Usage
`/ddd:gateway [context] [use-case]`

Example: `/ddd:gateway Blog CreateArticle`

## Symfony Maker Integration

This command complements the Symfony Maker bundle. You can generate the gateway structure using:

```bash
# Generate complete gateway with middleware pipeline
docker compose exec app bin/console make:application:gateway [Context] [UseCase]

# Example:
docker compose exec app bin/console make:application:gateway BlogContext CreateArticle
```

This Maker will create:
- Gateway class extending DefaultGateway
- Request class implementing GatewayRequest
- Response class implementing GatewayResponse
- Validation middleware for business rules
- Processor middleware for CQRS execution
- Proper dependency injection configuration

## Process

1. **Create Gateway Structure**
   ```
   Application/Gateway/CreateArticle/
   ├── Gateway.php                  # Extends DefaultGateway
   ├── Request.php                  # Implements GatewayRequest
   ├── Response.php                 # Implements GatewayResponse
   └── Middleware/
       ├── Validation.php           # Business validation
       └── Processor.php            # Operation execution
   ```

2. **Implement Gateway**
   ```php
   final class Gateway extends DefaultGateway
   {
       public function __construct(
           DefaultLogger $logger,
           DefaultErrorHandler $errorHandler,
           Validation $validation,
           Processor $processor,
       ) {
           parent::__construct(
               $logger,
               $errorHandler,
               $validation,
               $processor,
           );
       }
   }
   ```

3. **Create Request Object**
   ```php
   final readonly class Request implements GatewayRequest
   {
       public function __construct(
           public string $title,
           public string $content,
           public string $authorId,
       ) {
           // Validation in constructor
       }
       
       public static function fromData(array $data): self
       {
           return new self(
               title: $data['title'] ?? '',
               content: $data['content'] ?? '',
               authorId: $data['authorId'] ?? '',
           );
       }
       
       public function data(): array
       {
           return [
               'title' => $this->title,
               'content' => $this->content,
               'authorId' => $this->authorId,
           ];
       }
   }
   ```

4. **Create Response Object**
   ```php
   final readonly class Response implements GatewayResponse
   {
       public function __construct(
           public string $articleId,
           public string $slug,
           public string $status,
       ) {}
       
       public function data(): array
       {
           return [
               'articleId' => $this->articleId,
               'slug' => $this->slug,
               'status' => $this->status,
           ];
       }
   }
   ```

5. **Implement Validation Middleware**
   - Business rule validation
   - Check required fields
   - Validate formats and constraints
   - Throw meaningful exceptions

6. **Implement Processor Middleware**
   - Create Command/Query from Request
   - Execute via MessageBus
   - Transform result to Response
   - Handle domain events if needed

7. **Create Tests**
   - Test gateway with mocked dependencies
   - Test each middleware separately
   - Test error scenarios
   - Verify middleware order

## Middleware Pipeline
1. DefaultLogger (instrumentation start)
2. DefaultErrorHandler (exception wrapper)
3. Validation (business rules)
4. Processor (operation execution)

## Integration Points
- Uses CQRS handlers for execution
- Integrates with EventBus for events
- Provides clean API for UI layer
- Supports instrumentation/monitoring

## Quality Standards
- Follow @docs/reference/gateway-pattern.md
- One gateway per use case
- Clear request/response contracts
- Comprehensive validation

## Next Steps
1. Create UI integration (API/Web)
2. Add Behat tests for gateway
3. Configure dependency injection