# DDD Makers Quick Reference

## 🚀 Quick Commands

### Infrastructure Layer
```bash
# Create entity with repository and ID generator
bin/console make:infrastructure:entity BlogContext Article
```

### Domain Layer
```bash
# Create aggregate
bin/console make:domain:aggregate BlogContext CreateArticle Article

# Create value object
bin/console make:domain:value-object BlogContext Email --template=email
```

### Application Layer
```bash
# Create gateway
bin/console make:application:gateway BlogContext CreateArticle

# Create command
bin/console make:application:command BlogContext PublishArticle

# Create query
bin/console make:application:query BlogContext GetArticle
```

### UI Layer
```bash
# Create admin resource
bin/console make:admin:resource BlogContext Article

# Create API resource
bin/console make:api:resource BlogContext Article
```

## 📁 Generated File Structure

```
src/BlogContext/
├── Domain/
│   ├── CreateArticle/
│   │   ├── Creator.php
│   │   ├── CreatorInterface.php
│   │   ├── DataPersister/
│   │   │   └── Article.php
│   │   ├── Event/
│   │   │   └── ArticleCreated.php
│   │   └── Exception/
│   │       └── ArticleAlreadyExists.php
│   └── Shared/
│       ├── ValueObject/
│       │   └── ArticleId.php
│       └── Repository/
│           └── ArticleRepositoryInterface.php
├── Application/
│   ├── Gateway/
│   │   └── CreateArticle/
│   │       ├── Gateway.php
│   │       ├── Request.php
│   │       ├── Response.php
│   │       └── Middleware/
│   │           └── Processor.php
│   └── Operation/
│       ├── Command/
│       │   └── CreateArticle/
│       │       ├── Command.php
│       │       └── Handler.php
│       └── Query/
│           └── GetArticle/
│               ├── Query.php
│               └── Handler.php
├── Infrastructure/
│   ├── Identity/
│   │   └── ArticleIdGenerator.php
│   └── Persistence/
│       └── Doctrine/
│           └── ORM/
│               ├── Entity/
│               │   └── Article.php
│               └── ArticleRepository.php
└── UI/
    ├── Web/
    │   └── Admin/
    │       ├── Resource/
    │       │   └── ArticleResource.php
    │       ├── Grid/
    │       │   └── ArticleGrid.php
    │       ├── Form/
    │       │   └── ArticleType.php
    │       ├── Provider/
    │       │   ├── ArticleGridProvider.php
    │       │   └── ArticleItemProvider.php
    │       └── Processor/
    │           ├── CreateArticleProcessor.php
    │           ├── UpdateArticleProcessor.php
    │           └── DeleteArticleProcessor.php
    └── Api/
        └── Rest/
            ├── Resource/
            │   └── ArticleResource.php
            ├── Provider/
            │   ├── GetArticleProvider.php
            │   └── ListArticlesProvider.php
            └── Processor/
                ├── CreateArticleProcessor.php
                ├── UpdateArticleProcessor.php
                └── DeleteArticleProcessor.php
```

## 💡 Value Object Templates

| Template | Usage | Example |
|----------|-------|---------|
| `generic` | Default template | `make:domain:value-object BlogContext Status` |
| `email` | Email validation | `make:domain:value-object UserContext Email --template=email` |
| `money` | Amount with currency | `make:domain:value-object BillingContext Price --template=money` |
| `phone` | Phone number (E.164) | `make:domain:value-object UserContext Phone --template=phone` |
| `url` | URL validation | `make:domain:value-object BlogContext Website --template=url` |
| `percentage` | 0-100 percentage | `make:domain:value-object SalesContext Discount --template=percentage` |

## 🔄 Complete Feature Workflow

```bash
# 1. Domain layer
bin/console make:domain:value-object BlogContext ArticleId
bin/console make:domain:aggregate BlogContext CreateArticle Article

# 2. Infrastructure layer  
bin/console make:infrastructure:entity BlogContext Article

# 3. Application layer
bin/console make:application:gateway BlogContext CreateArticle
bin/console make:application:command BlogContext CreateArticle
bin/console make:application:query BlogContext GetArticle
bin/console make:application:query BlogContext ListArticles

# 4. UI layer - Admin
bin/console make:admin:resource BlogContext Article

# 5. UI layer - API
bin/console make:api:resource BlogContext Article

# 6. Database
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

## 🎯 Naming Conventions

| Type | Pattern | Examples |
|------|---------|----------|
| **Context** | `[Domain]Context` | `BlogContext`, `UserContext` |
| **Use Case** | `[Action][Entity]` | `CreateArticle`, `UpdateUser` |
| **Query** | `Get[Entity]` or `List[Entities]` | `GetArticle`, `ListArticles` |
| **Event** | `[Entity][PastTense]` | `ArticleCreated`, `UserUpdated` |
| **Exception** | `[Entity][Problem]` | `ArticleAlreadyExists`, `UserNotFound` |

## ⚡ Pro Tips

1. **Always start with value objects** - They define your domain language
2. **Create aggregates before entities** - Business logic first
3. **Use gateways for all operations** - Consistent entry points
4. **One command = one use case** - Keep it focused
5. **Queries can be complex** - But keep them read-only
6. **Test the domain layer first** - It's your business core
7. **Always run QA after generation** - `composer qa` to catch any issues
8. **Check generated namespaces** - Ensure context includes "Context" suffix