# Blog Context - Architecture Overview

## Introduction

This document provides a comprehensive architectural overview of the Blog Context implementation, designed following Domain-Driven Design (DDD), Hexagonal Architecture, and Clean Architecture principles. The Blog Context is responsible for all content management functionality including article creation, editorial workflows, media management, SEO optimization, and reader engagement features.

## Architectural Principles

### Core Design Patterns

1. **Domain-Driven Design (DDD)**
   - Business logic isolated in the Domain layer
   - Clear bounded context for blog/content management
   - Rich domain models with behavior (Article, Author, Category, etc.)

2. **Hexagonal Architecture (Ports & Adapters)**
   - Domain at the center, isolated from external concerns
   - Infrastructure adapters implement domain interfaces
   - Technology-agnostic business logic

3. **Clean Architecture**
   - Dependency inversion principle enforced
   - Inner layers know nothing about outer layers
   - Framework independence

4. **CQRS (Command Query Responsibility Segregation)**
   - Separate command and query buses via Symfony Messenger
   - Commands modify state and emit events
   - Queries provide read-only data access

5. **Event-Driven Architecture**
   - Domain events for state changes (ArticleCreated, ArticlePublished, etc.)
   - Inter-context communication via events
   - Asynchronous processing capabilities

## Architecture Layers

### Layer Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    UI Layer                                 │
│              API Platform Resources                         │
│          REST Controllers, GraphQL (Future)                 │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                  Application Layer                          │
│         Gateways, Commands, Queries, Handlers              │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Domain Layer                             │
│        Business Logic, Entities, Value Objects             │
└─────────────────────────────────────────────────────────────┘
                              ▲
                              │
┌─────────────────────────────────────────────────────────────┐
│                Infrastructure Layer                         │
│      Persistence, External Services, Implementations       │
└─────────────────────────────────────────────────────────────┘
```

### Dependency Rules

- **Domain**: No dependencies on any other layer
- **Application**: Depends only on Domain
- **Infrastructure**: Implements Domain interfaces
- **UI**: Depends on Application through Gateways

## Domain Layer Structure

### Use Case Organization

The domain is organized by use cases, each containing its own models and business logic:

```
BlogContext/Domain/
├── CreateArticle/       # Article creation logic
├── UpdateArticle/       # Article modification with autosave
├── PublishArticle/      # Publication workflow with SEO validation
├── DeleteArticle/       # Soft deletion with protection rules
├── DuplicateArticle/    # Template-based article creation
├── ReviewArticle/       # Editorial review workflow
├── ScheduleArticle/     # Editorial calendar management
├── CreateCategory/      # Hierarchical categorization
├── ManageTags/          # Flexible tagging system
├── UploadMedia/         # Media file management
├── ModerateComment/     # Comment moderation workflow
├── SearchArticle/       # Full-text search capabilities
├── AnalyzePerformance/  # Article analytics
├── ManageRevisions/     # Version history
└── Shared/              # Shared domain components
    ├── ValueObject/     # ArticleId, Title, Content, Slug, etc.
    ├── Repository/      # Repository interfaces
    └── Specification/   # Business rule specifications
```

### Domain Components

#### Entry Points
Each use case has a main entry point class following the [Action]er pattern:
- **Creator**: For creation operations
- **Updater**: For update operations
- **Publisher**: For state transitions
- **Deleter**: For removal operations
- **Reviewer**: For review processes

Example structure for CreateArticle:
```
CreateArticle/
├── Creator.php          # Main entry point with __invoke()
├── DataProvider/        # Input models
│   └── CreateArticleDataProvider.php
├── DataPersister/       # Output models
│   └── Article.php
│   └── ArticleBuilder.php
├── Event/              # Domain events
│   └── ArticleCreated.php
├── Exception/          # Business exceptions
│   └── ArticleAlreadyExists.php
└── Specification/      # Business rules
    └── UniqueSlugSpecification.php
```

#### Value Objects
Core value objects in the Blog Context:
- **ArticleId**: UUID v7 identifier for articles
- **Title**: Article title with 3-200 character validation
- **Content**: Article content with minimum length validation
- **Slug**: SEO-friendly URL segment with uniqueness
- **ArticleStatus**: Enum (DRAFT, PUBLISHED, ARCHIVED)
- **MetaDescription**: SEO meta with 120-160 character limit
- **CategoryName**: Hierarchical category identifier
- **Tag**: Normalized tag value
- **AuthorEmail**: Internal author identification

#### Domain Events
Key domain events emitted:
- **ArticleCreated**: When new article is created
- **ArticleUpdated**: When article content changes
- **ArticlePublished**: When article becomes public
- **ArticleArchived**: When article is removed from public
- **ArticleReviewed**: When editorial decision made
- **MediaUploaded**: When new media file added
- **CommentModerated**: When comment approved/rejected

#### Repository Interfaces
- **ArticleRepositoryInterface**: Article persistence
- **CategoryRepositoryInterface**: Category hierarchy
- **TagRepositoryInterface**: Tag management
- **MediaRepositoryInterface**: Media file storage
- **CommentRepositoryInterface**: Comment storage

## Application Layer Structure

### Gateway Pattern Implementation

The Gateway pattern serves as the primary entry point for external systems:

```php
// Gateway signature (MANDATORY)
public function __invoke(GatewayRequest $request): GatewayResponse
```

#### Gateway Responsibilities
- Transform GatewayRequest to domain objects
- Orchestrate use case execution
- Handle cross-cutting concerns via middleware pipeline
- Transform domain responses to GatewayResponse

#### Middleware Pipeline

Standard middleware pipeline:
1. **DefaultLogger**: Request/response instrumentation
2. **DefaultErrorHandler**: Exception handling and transformation
3. **Validation**: Business rule validation
4. **Processor**: Use case execution

### CQRS Implementation

#### Command Side (Write Operations)
```
Application/Operation/Command/
├── CreateArticle/
│   ├── Command.php      # DTO with article data
│   └── Handler.php      # Orchestrates Creator + EventBus
├── UpdateArticle/
│   ├── Command.php
│   └── Handler.php
├── PublishArticle/
│   ├── Command.php
│   └── Handler.php
└── AutoSaveArticle/
    ├── Command.php
    └── Handler.php
```

#### Query Side (Read Operations)
```
Application/Operation/Query/
├── GetArticle/
│   ├── Query.php        # Article ID parameter
│   ├── Handler.php      # Repository retrieval
│   └── ArticleView.php  # Response DTO
├── ListArticles/
│   ├── Query.php        # Filter parameters
│   ├── Handler.php      # Paginated retrieval
│   └── ArticleListView.php
└── SearchArticles/
    ├── Query.php
    ├── Handler.php
    └── SearchResultView.php
```

### Gateway Organization

#### Content Management Gateways
- **CreateArticleGateway**: New article creation with validation
- **UpdateArticleGateway**: Article modification
- **AutoSaveArticleGateway**: Draft auto-save functionality
- **PublishArticleGateway**: Publication with SEO validation
- **DeleteArticleGateway**: Soft deletion with rules

#### Editorial Gateways
- **ReviewArticleGateway**: Editorial review process
- **ScheduleArticleGateway**: Calendar management
- **BulkOperationGateway**: Mass article operations

#### Organization Gateways
- **CreateCategoryGateway**: Category hierarchy management
- **ManageTagsGateway**: Tag CRUD operations
- **AssignCategoryGateway**: Article categorization

#### Media Gateways
- **UploadMediaGateway**: File upload with optimization
- **SetFeaturedImageGateway**: Featured image assignment

## Infrastructure Layer

### Persistence Layer

#### Doctrine ORM Integration
```
Infrastructure/Persistence/Doctrine/
├── ORM/
│   ├── Entity/
│   │   ├── BlogArticle.php
│   │   ├── BlogCategory.php
│   │   ├── BlogTag.php
│   │   ├── BlogMedia.php
│   │   └── BlogComment.php
│   └── Repository/
│       ├── ArticleRepository.php
│       ├── CategoryRepository.php
│       └── TagRepository.php
└── Type/
    └── ArticleStatusType.php
```

#### Database Schema
- **blog_articles**: Main article storage with status, SEO fields
- **blog_categories**: Hierarchical category structure
- **blog_tags**: Tag definitions
- **blog_article_tags**: Many-to-many article-tag relations
- **blog_media**: Media file metadata
- **blog_comments**: User comments with moderation
- **blog_article_revisions**: Version history

### External Service Integration

#### Search Engine Integration
- **Service**: Elasticsearch/MeiliSearch
- **Purpose**: Full-text search, relevance scoring
- **Interface**: SearchEngineInterface

#### Media Processing
- **Service**: Image optimization pipeline
- **Purpose**: Resize, compress, format conversion
- **Interface**: MediaProcessorInterface

#### Analytics Collection
- **Service**: Custom analytics engine
- **Purpose**: View tracking, engagement metrics
- **Interface**: AnalyticsCollectorInterface

### Generator Pattern
- **ArticleIdGenerator**: UUID v7 generation for articles
- **SlugGenerator**: SEO-friendly URL generation
- **MediaIdGenerator**: Unique media identifiers

## Cross-Cutting Concerns

### Business Rules and Invariants

1. **Article Integrity**
   - Titles must be 3-200 characters
   - Content minimum 10 characters
   - Slugs must be unique across all articles
   - Published articles cannot be deleted (only archived)

2. **Editorial Workflow**
   - Only editors can approve articles
   - Authors can only edit their own drafts
   - Published articles require editor approval for changes

3. **SEO Requirements**
   - Meta descriptions 120-160 characters
   - Automatic slug generation from title
   - SEO score must exceed threshold for publication

4. **Media Constraints**
   - Maximum file size: 10MB
   - Supported formats: JPG, PNG, GIF, PDF, DOC
   - Alt text required for all images
   - Automatic optimization for web

### Security Requirements

1. **Access Control**
   - Role-based permissions (Admin, Editor, Author, Guest)
   - Authors can only modify own content
   - Editors have full content access
   - Guests have limited creation rights

2. **Data Validation**
   - XSS prevention in all content fields
   - File type validation for uploads
   - Spam detection for comments

3. **Audit Requirements**
   - All content changes logged
   - User actions tracked
   - Permission changes audited

### Performance Considerations

1. **Query Optimization**
   - Indexes on: status, slug, published_at, category_id
   - Eager loading for related data
   - Query result caching

2. **Scalability**
   - Support 100,000+ articles
   - 50+ concurrent editors
   - 100,000+ daily page views

## Testing Strategy

### Domain Testing
- **Unit Tests**: Pure domain logic testing
- **Test Coverage**: >95% for domain layer
- **Isolation**: No external dependencies

### Integration Testing
- **Repository Tests**: Database operations
- **Gateway Tests**: End-to-end workflows
- **API Tests**: REST endpoint validation

### Test Organization
```
tests/BlogContext/
├── Unit/
│   ├── Domain/
│   │   ├── CreateArticle/
│   │   ├── PublishArticle/
│   │   └── Shared/ValueObject/
│   └── Application/
│       ├── Gateway/
│       └── Operation/
├── Integration/
│   ├── Infrastructure/
│   └── UI/Api/
└── Functional/
    └── Features/
```

## Technology Stack

### Core Technologies
- **PHP 8.4+**: Property hooks, asymmetric visibility
- **Symfony 7.3**: Framework foundation
- **Doctrine ORM 3.5**: Database abstraction
- **API Platform 4.1**: REST API framework
- **Symfony Messenger**: CQRS/Event bus

### Blog-Specific Libraries
- **cocur/slugify**: Slug generation
- **Elasticsearch**: Full-text search
- **Intervention/image**: Image processing

### Development Tools
- **PHPUnit 12.2**: Testing framework
- **Behat**: BDD testing
- **PHPStan**: Static analysis (max level)
- **Foundry**: Test data factories

## Inter-Context Communication

### Published Events
Events this context publishes:
- **ArticlePublished**: When article goes live
  - Data: articleId, title, authorId, categoryId, tags
- **AuthorActivityRecorded**: For analytics
  - Data: authorId, action, timestamp

### Subscribed Events
Events this context listens to:
- **UserDeleted** (from Security Context)
  - Action: Anonymize author content
- **SystemMaintenanceScheduled** (from Admin Context)
  - Action: Pause scheduled publications

### Integration Points
- **Security Context**: User authentication and authorization
- **Notification Context**: Email alerts for editorial workflow
- **Analytics Context**: Performance data aggregation

## Deployment Considerations

### Environment Configuration
Required environment variables:
- `BLOG_MEDIA_PATH`: Media storage location
- `BLOG_MEDIA_MAX_SIZE`: Upload size limit
- `BLOG_SEARCH_ENGINE`: Search backend choice
- `BLOG_ANALYTICS_ENABLED`: Analytics toggle

### Monitoring
Key metrics to track:
- Article publication rate
- Editorial queue depth
- Search response times
- Media storage usage
- Comment moderation backlog

### Database
- Migration-first approach with Doctrine
- Regular index optimization
- Partition strategy for large tables

## Future Extensibility

### Planned Enhancements
- Multi-language content support
- Real-time collaborative editing
- AI-powered content suggestions
- Advanced workflow customization

### Extension Points
- Custom media processors
- Additional SEO analyzers
- New comment moderation strategies
- Plugin system for custom fields

### Technical Debt
- Optimize large category tree queries
- Implement media CDN integration
- Add GraphQL API support

## Glossary

- **Article**: Primary content unit in the blog
- **Draft**: Unpublished article visible only to author/editors
- **Featured Image**: Primary visual representation of article
- **SEO Score**: Calculated metric for search optimization
- **Editorial Queue**: Articles awaiting review
- **Tag**: Non-hierarchical content classifier
- **Category**: Hierarchical content organizer

## Conclusion

The Blog Context provides a comprehensive content management system built on solid architectural principles. Its domain-driven design ensures business logic remains pure and testable, while the hexagonal architecture allows for flexible infrastructure choices. The implementation demonstrates how complex business requirements can be elegantly handled through proper separation of concerns and event-driven communication.

---

**Document Status**: Current Implementation
**Last Updated**: 2025-07-15
**Version**: 1.0