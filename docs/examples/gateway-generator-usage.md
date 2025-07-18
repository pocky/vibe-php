# Gateway Maker: Practical Examples and Generated Code

This document demonstrates the new intelligent Gateway maker that generates concrete, operation-specific implementation code instead of TODO templates.

## Overview

The `make:application:gateway` command has been significantly enhanced to automatically detect operation types and generate complete, functional Processor implementations.

## Supported Operation Types

| Operation Pattern | Type | Generated Dependencies | CQRS Integration |
|-------------------|------|----------------------|------------------|
| `Create*` | Command | Handler + IdGenerator | Command/Handler |
| `Update*` | Command | Handler only | Command/Handler |
| `Delete*` | Command | Handler only | Command/Handler |
| `Get*` | Query | Handler only | Query/Handler |
| `List*` | Query | Handler only | Query/Handler |

## Complete Examples

### Example 1: Create Operation

#### Command
```bash
bin/console make:application:gateway BlogContext CreateArticle
```

#### Generated Processor (Automatic)
```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle\Middleware;

use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\BlogContext\Application\Operation\Command\CreateArticle\Command;
use App\BlogContext\Application\Operation\Command\CreateArticle\Handler;
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
        private ArticleIdGenerator $idGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Generate new article ID
        $articleId = $this->idGenerator->nextIdentity();

        // Create command
        $command = new Command(
            articleId: $articleId->getValue(),
            title: $request->title,
            content: $request->content,
            status: $request->status,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response with generated ID
        return new Response(
            articleId: $articleId->getValue(),
        );
    }
}
```

**Key Features**:
- ✅ Automatic ID generation with `ArticleIdGenerator`
- ✅ Command creation with proper field mapping
- ✅ Handler execution via `__invoke()` pattern
- ✅ Response with generated ID

---

### Example 2: Update Operation

#### Command
```bash
bin/console make:application:gateway BlogContext UpdateArticle
```

#### Generated Processor (Automatic)
```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle\Middleware;

use App\BlogContext\Application\Gateway\UpdateArticle\Request;
use App\BlogContext\Application\Gateway\UpdateArticle\Response;
use App\BlogContext\Application\Operation\Command\UpdateArticle\Command;
use App\BlogContext\Application\Operation\Command\UpdateArticle\Handler;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Create command
        $command = new Command(
            articleId: $request->articleId,
            title: $request->title,
            content: $request->content,
            status: $request->status,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response(
            articleId: $request->articleId,
        );
    }
}
```

**Key Features**:
- ✅ No ID Generator (uses existing ID)
- ✅ Command-based operation
- ✅ Uses existing `articleId` from request

---

### Example 3: List Operation (Query)

#### Command
```bash
bin/console make:application:gateway BlogContext ListArticles
```

#### Generated Processor (Automatic)
```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListArticles\Middleware;

use App\BlogContext\Application\Gateway\ListArticles\Request;
use App\BlogContext\Application\Gateway\ListArticles\Response;
use App\BlogContext\Application\Operation\Query\ListArticles\Handler;
use App\BlogContext\Application\Operation\Query\ListArticles\Query;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Create query
        $query = new Query(
            page: $request->page ?? 1,
            limit: $request->limit ?? 20,
        );

        // Execute query through handler
        $result = ($this->handler)($query);

        // Return response with collection data
        return new Response(
            articles: $result['items'] ?? [],
            total: $result['total'] ?? 0,
            page: $request->page ?? 1,
            limit: $request->limit ?? 20,
        );
    }
}
```

**Key Features**:
- ✅ Query-based operation (not Command)
- ✅ Pagination support built-in
- ✅ Collection response with metadata

---

### Example 4: Get Operation (Single Item Query)

#### Command
```bash
bin/console make:application:gateway BlogContext GetArticle
```

#### Generated Processor (Automatic)
```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle\Middleware;

use App\BlogContext\Application\Gateway\GetArticle\Request;
use App\BlogContext\Application\Gateway\GetArticle\Response;
use App\BlogContext\Application\Operation\Query\GetArticle\Handler;
use App\BlogContext\Application\Operation\Query\GetArticle\Query;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Create query
        $query = new Query(
            articleId: $request->articleId,
        );

        // Execute query through handler
        $result = ($this->handler)($query);

        // Return response with entity data
        return new Response(
            article: $result,
        );
    }
}
```

**Key Features**:
- ✅ Query-based for single item
- ✅ Simple ID-based lookup
- ✅ Direct entity response

---

### Example 5: Delete Operation

#### Command
```bash
bin/console make:application:gateway BlogContext DeleteArticle
```

#### Generated Processor (Automatic)
```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteArticle\Middleware;

use App\BlogContext\Application\Gateway\DeleteArticle\Request;
use App\BlogContext\Application\Gateway\DeleteArticle\Response;
use App\BlogContext\Application\Operation\Command\DeleteArticle\Command;
use App\BlogContext\Application\Operation\Command\DeleteArticle\Handler;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Create command
        $command = new Command(
            articleId: $request->articleId,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response(
            deleted: true,
        );
    }
}
```

**Key Features**:
- ✅ Command-based operation
- ✅ Minimal parameters (just ID)
- ✅ Boolean success response

---

## Different Entity Examples

### User Management

```bash
# Create user
bin/console make:application:gateway SecurityContext CreateUser

# Generated with UserIdGenerator injection
# Generates SecurityContext namespace
# Uses User entity naming
```

### Product Catalog

```bash
# List products
bin/console make:application:gateway CatalogContext ListProducts

# Generated with Query pattern
# Proper pluralization (Products not Productss)
# Pagination built-in
```

### Category Management

```bash
# Update category
bin/console make:application:gateway BlogContext UpdateCategory

# Generated without IdGenerator (Update operation)
# Uses existing categoryId
# Command-based operation
```

## Before vs After Comparison

### Old Generated Code (Before Improvements)
```php
public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
{
    /** @var Request $request */
    
    // TODO: Implement your business logic here
    // TODO: Create command or query based on your operation type
    
    // Return response
    return new Response(
        // Map your response data
    );
}
```

### New Generated Code (After Improvements)
```php
public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
{
    /** @var Request $request */

    // Generate new article ID
    $articleId = $this->idGenerator->nextIdentity();

    // Create command
    $command = new Command(
        articleId: $articleId->getValue(),
        title: $request->title,
        content: $request->content,
    );

    // Execute command through handler
    ($this->handler)($command);

    // Return response with generated ID
    return new Response(
        articleId: $articleId->getValue(),
    );
}
```

## What's Generated Automatically

For each gateway, the maker generates 4 files:

### 1. Gateway.php
```php
final class Gateway extends DefaultGateway
{
    public function __construct(
        DefaultLogger $logger,
        DefaultErrorHandler $errorHandler,
        Validation $validation,
        Processor $processor,
    ) {
        parent::__construct([
            $logger,
            $errorHandler,
            $validation,
            $processor,
        ]);
    }
}
```

### 2. Request.php
```php
final readonly class Request implements GatewayRequest
{
    public function __construct(
        // Properties based on operation type
    ) {}

    public static function fromData(array $data): self
    {
        return new self(
            // Automatic data mapping
        );
    }

    public function data(): array
    {
        return [
            // Serialization logic
        ];
    }
}
```

### 3. Response.php
```php
final readonly class Response implements GatewayResponse
{
    public function __construct(
        // Response properties based on operation
    ) {}

    public function data(): array
    {
        return [
            // Response serialization
        ];
    }
}
```

### 4. Middleware/Processor.php
The smart Processor shown in examples above with:
- Operation-specific implementation
- Correct dependency injection
- CQRS integration
- Proper imports

## Benefits of the New System

### ✅ Ready-to-Use Code
- No more manual implementation needed
- Functional code from generation
- Proper CQRS integration

### ✅ Intelligent Detection
- Automatic operation type recognition
- Smart dependency injection
- Correct import statements

### ✅ Consistent Patterns
- All operations follow same structure
- Standardized naming conventions
- Proper error handling

### ✅ Development Speed
- Immediate functionality after generation
- No boilerplate code writing
- Focus on business logic

## Custom Operations

The maker also supports custom operation patterns:

```bash
# These work automatically:
bin/console make:application:gateway BlogContext PublishArticle
bin/console make:application:gateway BlogContext ArchiveArticle
bin/console make:application:gateway BlogContext ApproveArticle
```

For unsupported patterns, it generates a generic template:
```php
// TODO: Implement your business logic here
// Create command or query based on your operation type

// Return response
return new Response(
    // Map your response data
);
```

## Next Steps After Generation

1. **Customize Request/Response** classes to match your data requirements
2. **Add validation logic** to the Request class constructor
3. **Create corresponding CQRS operations** (Command/Query + Handler)
4. **Run QA tools** to ensure generated code meets standards:
   ```bash
   composer qa
   ```

The Gateway maker now provides a solid foundation that requires minimal manual work to complete your implementation.