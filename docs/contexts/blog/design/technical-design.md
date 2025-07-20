# Technical Design - Blog Context

## 1. Architecture Overview

### 1.1 System Context
The Blog context is a bounded context within the application that handles all blog-related functionality including articles, categories, tags, and authors.

**Bounded Context Boundaries:**
- **Blog Context**: Article management, categorization, tagging, and authorship
- **External Dependencies**: None (self-contained context)
- **Communication**: Event-driven for future integration with other contexts

**Integration Points:**
- Admin UI for content management
- REST API for content delivery
- Future: Event bus for cross-context communication

### 1.2 Architecture Decisions

**Pattern Choices:**
- **Domain-Driven Design (DDD)**: Clear separation of business logic from infrastructure
- **Hexagonal Architecture**: Ports and adapters for flexibility
- **CQRS**: Separate read/write models for scalability
- **Gateway Pattern**: Unified entry points with middleware pipeline
- **Repository Pattern**: Abstract persistence layer

**Technology Stack:**
- **PHP 8.4**: Modern PHP features (enums, readonly classes, property hooks)
- **Symfony 7.3**: Framework foundation
- **Doctrine ORM**: Persistence layer
- **API Platform**: REST API generation
- **MySQL/PostgreSQL**: Relational database

**Trade-off Analysis:**
- **Simplicity vs Features**: Focus on core blogging without authentication complexity
- **Performance vs Flexibility**: CQRS adds complexity but enables future scaling
- **DDD Overhead**: More initial setup but better maintainability

## 2. Domain Model Design

### 2.1 Aggregates

#### Article Aggregate (Root)
- **Boundaries**: Article with its content and metadata
- **Invariants**: 
  - Must have a title and content
  - Slug must be unique
  - Can only be published once
  - Must have an author
- **Transaction Boundary**: Article modifications are atomic

#### Category Aggregate
- **Boundaries**: Category hierarchy
- **Invariants**:
  - Name and slug must be unique
  - Maximum 2-level hierarchy
  - Cannot be deleted if articles are assigned
- **Transaction Boundary**: Category tree modifications

#### Author Aggregate
- **Boundaries**: Author profile
- **Invariants**:
  - Email must be unique
  - Name is required
  - Cannot be deleted if articles exist
- **Transaction Boundary**: Author profile updates

#### Tag (Not an Aggregate)
- Simple value object collection
- Created on-demand
- No complex business rules

### 2.2 Entities and Value Objects

**Entities:**
```
Article (Aggregate Root)
├── ArticleId (VO)
├── Title (VO)
├── Content (VO)
├── Slug (VO)
├── ArticleStatus (Enum)
├── PublishedAt (VO)
├── AuthorId (VO)
└── Timestamps (VO)

Category (Aggregate Root)
├── CategoryId (VO)
├── CategoryName (VO)
├── CategorySlug (VO)
├── Description (VO)
├── ParentId (VO, nullable)
└── Timestamps (VO)

Author (Aggregate Root)
├── AuthorId (VO)
├── AuthorName (VO)
├── Email (VO)
├── Bio (VO)
└── Timestamps (VO)
```

**Value Objects:**
- `ArticleId`, `CategoryId`, `AuthorId`: UUID-based identifiers
- `Title`: 1-200 characters, required
- `Content`: Rich text, required
- `Slug`: URL-safe, unique, auto-generated
- `CategoryName`: 2-100 characters, unique
- `AuthorName`: 2-100 characters
- `Email`: Valid email format
- `Bio`: Optional text
- `Timestamps`: CreatedAt, UpdatedAt

**Enums:**
- `ArticleStatus`: DRAFT, PUBLISHED

### 2.3 Domain Events

```
ArticleCreated
├── articleId: string
├── title: string
├── authorId: string
├── status: string
└── createdAt: string

ArticlePublished
├── articleId: string
├── publishedAt: string
└── slug: string

ArticleUpdated
├── articleId: string
├── changes: array
└── updatedAt: string

ArticleDeleted
├── articleId: string
└── deletedAt: string

CategoryCreated
├── categoryId: string
├── name: string
├── slug: string
└── parentId: ?string

CategoryUpdated
├── categoryId: string
├── changes: array
└── updatedAt: string

CategoryDeleted
├── categoryId: string
└── deletedAt: string

AuthorCreated
├── authorId: string
├── name: string
├── email: string
└── createdAt: string

AuthorUpdated
├── authorId: string
├── changes: array
└── updatedAt: string
```

## 3. Application Layer Design

### 3.1 Commands and Handlers

**Article Commands:**
```php
CreateArticleCommand
├── title: string
├── content: string
├── authorId: string
├── categoryIds: array
├── tagNames: array
└── status: string

UpdateArticleCommand
├── articleId: string
├── title: ?string
├── content: ?string
├── categoryIds: ?array
├── tagNames: ?array
└── status: ?string

PublishArticleCommand
├── articleId: string
└── publishAt: ?string

DeleteArticleCommand
└── articleId: string
```

**Category Commands:**
```php
CreateCategoryCommand
├── name: string
├── description: ?string
└── parentId: ?string

UpdateCategoryCommand
├── categoryId: string
├── name: ?string
├── description: ?string
└── parentId: ?string

DeleteCategoryCommand
└── categoryId: string
```

**Author Commands:**
```php
CreateAuthorCommand
├── name: string
├── email: string
└── bio: ?string

UpdateAuthorCommand
├── authorId: string
├── name: ?string
├── email: ?string
└── bio: ?string

DeleteAuthorCommand
└── authorId: string
```

### 3.2 Queries and Handlers

**Article Queries:**
```php
GetArticleQuery
└── articleId: string

ListArticlesQuery
├── page: int
├── limit: int
├── status: ?string
├── authorId: ?string
├── categoryId: ?string
└── tagName: ?string

SearchArticlesQuery
├── searchTerm: string
├── page: int
└── limit: int
```

**Category Queries:**
```php
GetCategoryQuery
└── categoryId: string

ListCategoriesQuery
├── page: int
├── limit: int
└── parentId: ?string

GetCategoryTreeQuery
└── rootId: ?string
```

**Author Queries:**
```php
GetAuthorQuery
└── authorId: string

ListAuthorsQuery
├── page: int
└── limit: int

GetAuthorArticlesQuery
├── authorId: string
├── page: int
└── limit: int
```

### 3.3 Gateways

**Gateway Structure:**
```
Application/Gateway/
├── CreateArticle/
│   ├── Gateway.php
│   ├── Request.php
│   ├── Response.php
│   └── Middleware/
│       ├── Validation.php
│       └── Processor.php
├── UpdateArticle/
├── PublishArticle/
├── DeleteArticle/
├── GetArticle/
├── ListArticles/
├── CreateCategory/
├── UpdateCategory/
├── DeleteCategory/
├── GetCategory/
├── ListCategories/
├── CreateAuthor/
├── UpdateAuthor/
├── DeleteAuthor/
├── GetAuthor/
└── ListAuthors/
```

**Middleware Pipeline:**
1. DefaultLogger (logging/instrumentation)
2. DefaultErrorHandler (exception handling)
3. Validation (business rules validation)
4. Processor (command/query execution)

## 4. Infrastructure Design

### 4.1 Persistence

**Repository Interfaces:**
```php
interface ArticleRepositoryInterface
{
    public function save(Article $article): void;
    public function findById(ArticleId $id): ?Article;
    public function findBySlug(Slug $slug): ?Article;
    public function delete(Article $article): void;
}

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;
    public function findById(CategoryId $id): ?Category;
    public function findBySlug(CategorySlug $slug): ?Category;
    public function findByParentId(?CategoryId $parentId): array;
    public function delete(Category $category): void;
}

interface AuthorRepositoryInterface
{
    public function save(Author $author): void;
    public function findById(AuthorId $id): ?Author;
    public function findByEmail(Email $email): ?Author;
    public function delete(Author $author): void;
}
```

**Doctrine Entities:**
- Separate from domain models
- Located in `Infrastructure/Persistence/Doctrine/Entity/`
- Mapping between domain and persistence models

### 4.2 External Services

**Event Bus:**
- Implements EventBusInterface
- Synchronous for now, async-ready
- Publishes domain events

**Slug Generator:**
- Implements SlugGeneratorInterface
- Uses Cocur/Slugify
- Ensures uniqueness

### 4.3 Security

**Input Validation:**
- Symfony Validator constraints
- XSS prevention in content
- SQL injection prevention via Doctrine

**API Security:**
- Rate limiting middleware
- CORS configuration
- Input sanitization

## 5. API Design

### 5.1 REST API

**Article Endpoints:**
```yaml
GET /api/articles:
  parameters:
    - page: integer
    - limit: integer (max: 50)
    - status: string (draft|published)
    - author: uuid
    - category: uuid
    - tag: string
  response:
    - articles: array
    - total: integer
    - page: integer
    - pages: integer

GET /api/articles/{id}:
  response:
    - id: uuid
    - title: string
    - content: string
    - slug: string
    - status: string
    - publishedAt: ?datetime
    - author: object
    - categories: array
    - tags: array
    - createdAt: datetime
    - updatedAt: datetime

POST /api/articles:
  body:
    - title: string (required)
    - content: string (required)
    - authorId: uuid (required)
    - categoryIds: array
    - tags: array
    - status: string (default: draft)
  response:
    - id: uuid
    - slug: string
    - createdAt: datetime

PUT /api/articles/{id}:
  body:
    - title: string
    - content: string
    - categoryIds: array
    - tags: array
    - status: string
  response:
    - id: uuid
    - updatedAt: datetime

DELETE /api/articles/{id}:
  response: 204 No Content
```

**Category Endpoints:**
```yaml
GET /api/categories:
  parameters:
    - page: integer
    - limit: integer
    - parent: uuid
  response:
    - categories: array
    - total: integer

GET /api/categories/{id}:
  response:
    - id: uuid
    - name: string
    - slug: string
    - description: string
    - parent: ?object
    - children: array
    - articleCount: integer

POST /api/categories:
  body:
    - name: string (required)
    - description: string
    - parentId: ?uuid
  response:
    - id: uuid
    - slug: string

PUT /api/categories/{id}:
  body:
    - name: string
    - description: string
    - parentId: ?uuid
  response:
    - id: uuid
    - updatedAt: datetime

DELETE /api/categories/{id}:
  response: 204 No Content
```

**Author Endpoints:**
```yaml
GET /api/authors:
  parameters:
    - page: integer
    - limit: integer
  response:
    - authors: array
    - total: integer

GET /api/authors/{id}:
  response:
    - id: uuid
    - name: string
    - email: string
    - bio: ?string
    - articleCount: integer

POST /api/authors:
  body:
    - name: string (required)
    - email: string (required)
    - bio: ?string
  response:
    - id: uuid

PUT /api/authors/{id}:
  body:
    - name: string
    - email: string
    - bio: ?string
  response:
    - id: uuid
    - updatedAt: datetime

DELETE /api/authors/{id}:
  response: 204 No Content
```

### 5.2 Event Contracts

**Event Publishing:**
- Events published to event bus after successful operations
- Async processing ready for future features

**Event Schemas:**
```json
{
  "type": "article.published",
  "data": {
    "articleId": "uuid",
    "title": "string",
    "slug": "string",
    "authorId": "uuid",
    "publishedAt": "datetime"
  },
  "metadata": {
    "timestamp": "datetime",
    "version": "1.0"
  }
}
```

## 6. Admin UI Design

### 6.1 Overview

The Admin UI provides a web-based interface for content management, built using Sylius Bootstrap Admin UI components for consistency and rapid development.

**Key Features:**
- Article management (create, edit, publish, delete)
- Category hierarchy management
- Author profile management
- Tag management
- Bulk operations
- Content search and filtering
- Dashboard with statistics

### 6.2 Architecture

**Technology Stack:**
- **Sylius Bootstrap Admin UI**: Pre-built admin components
- **Twig**: Template engine
- **Symfony Forms**: Form handling and validation
- **Turbo/Stimulus**: Progressive enhancement
- **Bootstrap 5**: CSS framework

**Component Structure:**
```
UI/
└── Web/
    ├── Controller/
    │   ├── Admin/
    │   │   ├── ArticleController.php
    │   │   ├── CategoryController.php
    │   │   ├── AuthorController.php
    │   │   └── DashboardController.php
    │   └── SecurityController.php
    ├── Form/
    │   ├── Type/
    │   │   ├── ArticleType.php
    │   │   ├── CategoryType.php
    │   │   └── AuthorType.php
    │   └── DataTransformer/
    │       └── TagsTransformer.php
    └── Template/
        └── Admin/
            ├── Article/
            │   ├── index.html.twig
            │   ├── create.html.twig
            │   ├── edit.html.twig
            │   └── _form.html.twig
            ├── Category/
            ├── Author/
            └── Dashboard/
```

### 6.3 Admin Routes

```yaml
# Article Management
GET    /admin/articles                 # List all articles
GET    /admin/articles/new             # Create article form
POST   /admin/articles/new             # Create article
GET    /admin/articles/{id}/edit       # Edit article form
POST   /admin/articles/{id}/edit       # Update article
POST   /admin/articles/{id}/publish    # Publish article
POST   /admin/articles/{id}/delete     # Delete article
POST   /admin/articles/bulk-action     # Bulk operations

# Category Management
GET    /admin/categories               # Category tree view
GET    /admin/categories/new           # Create category form
POST   /admin/categories/new           # Create category
GET    /admin/categories/{id}/edit     # Edit category form
POST   /admin/categories/{id}/edit     # Update category
POST   /admin/categories/{id}/delete   # Delete category

# Author Management  
GET    /admin/authors                  # List all authors
GET    /admin/authors/new              # Create author form
POST   /admin/authors/new              # Create author
GET    /admin/authors/{id}/edit        # Edit author form
POST   /admin/authors/{id}/edit        # Update author
POST   /admin/authors/{id}/delete      # Delete author

# Dashboard
GET    /admin                          # Dashboard home
GET    /admin/dashboard                # Dashboard with stats
```

### 6.4 UI Components

#### Dashboard
- **Statistics Widget**: Article count, published vs draft, popular categories
- **Recent Articles**: Last 10 articles with quick actions
- **Quick Actions**: Create article, manage categories
- **Activity Feed**: Recent changes and publications

#### Article Management
- **List View**:
  - Sortable columns (title, author, status, date)
  - Inline status indicators
  - Quick publish/unpublish actions
  - Bulk selection checkboxes
  - Search and filters (status, author, category, date range)
  - Pagination

- **Create/Edit Form**:
  - Rich text editor for content (TinyMCE or similar)
  - Live slug generation from title
  - Category selection (hierarchical dropdown)
  - Tag input with autocomplete
  - Author assignment dropdown
  - Save as draft / Publish buttons
  - Preview functionality

#### Category Management
- **Tree View**:
  - Drag-and-drop hierarchy management
  - Expand/collapse nodes
  - Inline edit for names
  - Article count badges

- **Create/Edit Form**:
  - Name and description fields
  - Parent category selection
  - Slug customization

#### Author Management
- **List View**:
  - Author profiles with article count
  - Email and bio preview
  - Quick edit actions

- **Create/Edit Form**:
  - Name, email, bio fields
  - Article association display

### 6.5 Form Design

#### Article Form Type
```php
class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['maxlength' => 200],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 200])
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr' => ['class' => 'rich-text-editor', 'rows' => 15],
                'constraints' => [new NotBlank()]
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => false,
                'attr' => ['class' => 'slug-input']
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'multiple' => true,
                'expanded' => false,
                'attr' => ['class' => 'select2-categories']
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags',
                'required' => false,
                'attr' => ['data-role' => 'tagsinput']
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Draft' => ArticleStatus::DRAFT,
                    'Published' => ArticleStatus::PUBLISHED
                ]
            ])
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'placeholder' => 'Select an author'
            ]);
    }
}
```

### 6.6 Controller Implementation

#### ArticleController Example
```php
#[Route('/admin/articles', name: 'admin_article_')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        ListArticlesGateway $gateway,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $filters = $request->query->all();
        
        $response = $gateway(new ListArticlesRequest([
            'page' => $page,
            'limit' => 20,
            'filters' => $filters
        ]));
        
        return $this->render('admin/article/index.html.twig', [
            'articles' => $response->articles,
            'pagination' => $response->pagination
        ]);
    }
    
    #[Route('/new', name: 'create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        CreateArticleGateway $gateway
    ): Response {
        $form = $this->createForm(ArticleType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $response = $gateway(new CreateArticleRequest($data));
                
                $this->addFlash('success', 'Article created successfully');
                return $this->redirectToRoute('admin_article_edit', [
                    'id' => $response->articleId
                ]);
            } catch (GatewayException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }
        
        return $this->render('admin/article/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
```

### 6.7 Template Design

#### Base Admin Layout
```twig
{# templates/admin/layout.html.twig #}
{% extends '@SyliusBootstrapAdminUi/layout.html.twig' %}

{% block title %}Blog Admin - {% block page_title %}{% endblock %}{% endblock %}

{% block sidebar %}
    {% include 'admin/_sidebar.html.twig' %}
{% endblock %}

{% block content %}
    <div class="ui segment">
        <h1 class="ui header">
            {% block header_title %}{% endblock %}
            {% block header_actions %}{% endblock %}
        </h1>
        
        {% include 'admin/_flashes.html.twig' %}
        
        {% block main_content %}{% endblock %}
    </div>
{% endblock %}
```

#### Article List Template
```twig
{# templates/admin/article/index.html.twig #}
{% extends 'admin/layout.html.twig' %}

{% block page_title %}Articles{% endblock %}
{% block header_title %}Articles Management{% endblock %}

{% block header_actions %}
    <div class="ui right floated buttons">
        <a href="{{ path('admin_article_create') }}" class="ui primary button">
            <i class="plus icon"></i> New Article
        </a>
    </div>
{% endblock %}

{% block main_content %}
    {# Filters #}
    <div class="ui segment">
        {% include 'admin/article/_filters.html.twig' %}
    </div>
    
    {# Articles table #}
    <table class="ui celled table">
        <thead>
            <tr>
                <th><input type="checkbox" class="select-all"></th>
                <th>Title</th>
                <th>Author</th>
                <th>Categories</th>
                <th>Status</th>
                <th>Published</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {% for article in articles %}
                <tr>
                    <td><input type="checkbox" name="articles[]" value="{{ article.id }}"></td>
                    <td>{{ article.title }}</td>
                    <td>{{ article.author.name }}</td>
                    <td>
                        {% for category in article.categories %}
                            <span class="ui label">{{ category.name }}</span>
                        {% endfor %}
                    </td>
                    <td>
                        {% if article.status == 'published' %}
                            <span class="ui green label">Published</span>
                        {% else %}
                            <span class="ui yellow label">Draft</span>
                        {% endif %}
                    </td>
                    <td>{{ article.publishedAt|date('Y-m-d H:i') }}</td>
                    <td>
                        <div class="ui buttons">
                            <a href="{{ path('admin_article_edit', {id: article.id}) }}" 
                               class="ui button">Edit</a>
                            <button class="ui red button delete-article" 
                                    data-id="{{ article.id }}">Delete</button>
                        </div>
                    </td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
    
    {# Pagination #}
    {% include 'admin/_pagination.html.twig' with {pagination: pagination} %}
{% endblock %}
```

### 6.8 JavaScript Enhancement

#### Article Form Enhancement
```javascript
// Auto-generate slug from title
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('article_title');
    const slugInput = document.getElementById('article_slug');
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerate === 'true') {
            const slug = titleInput.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        }
    });
    
    // Rich text editor initialization
    tinymce.init({
        selector: '.rich-text-editor',
        height: 400,
        plugins: 'link image lists',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image'
    });
    
    // Tags input
    $('#article_tags').tagsinput({
        trimValue: true,
        confirmKeys: [13, 44]
    });
});
```

### 6.9 Security Considerations

**Access Control:**
- No authentication required as per PRD
- Consider IP whitelist for admin routes
- CSRF protection on all forms
- Rate limiting for admin actions

**Input Validation:**
- Server-side validation using Symfony Forms
- Client-side validation for better UX
- XSS prevention in templates
- Content sanitization before storage

### 6.10 Performance Optimization

**Frontend:**
- Asset compilation with Webpack Encore
- Lazy loading for images
- Pagination for large datasets
- AJAX for inline operations

**Backend:**
- Query optimization for list views
- Eager loading of relationships
- Caching for category trees
- Bulk operations using single queries

## 7. Data Model

### 7.1 Database Schema

**Tables:**
```sql
-- Articles table
CREATE TABLE blog_articles (
    id CHAR(36) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT NOT NULL,
    slug VARCHAR(250) UNIQUE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    author_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_articles_status (status),
    INDEX idx_articles_slug (slug),
    INDEX idx_articles_author_id (author_id),
    INDEX idx_articles_published_at (published_at),
    FOREIGN KEY (author_id) REFERENCES blog_authors(id)
);

-- Categories table
CREATE TABLE blog_categories (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    description TEXT NULL,
    parent_id CHAR(36) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categories_slug (slug),
    INDEX idx_categories_parent_id (parent_id),
    UNIQUE KEY unique_category_name (name),
    FOREIGN KEY (parent_id) REFERENCES blog_categories(id) ON DELETE SET NULL
);

-- Authors table
CREATE TABLE blog_authors (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    bio TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_authors_email (email)
);

-- Tags table
CREATE TABLE blog_tags (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(60) UNIQUE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tags_slug (slug)
);

-- Article-Category relationship
CREATE TABLE blog_article_categories (
    article_id CHAR(36) NOT NULL,
    category_id CHAR(36) NOT NULL,
    PRIMARY KEY (article_id, category_id),
    FOREIGN KEY (article_id) REFERENCES blog_articles(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id)
);

-- Article-Tag relationship
CREATE TABLE blog_article_tags (
    article_id CHAR(36) NOT NULL,
    tag_id CHAR(36) NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    FOREIGN KEY (article_id) REFERENCES blog_articles(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
);
```

### 7.2 Data Flow

**Create Article Flow:**
1. Receive request through Gateway
2. Validate input data
3. Generate ArticleId (UUID v7)
4. Generate unique slug
5. Create Article aggregate
6. Save through repository
7. Create category associations
8. Create/associate tags
9. Publish ArticleCreated event
10. Return response

**Query Article Flow:**
1. Receive query through Gateway
2. Execute optimized query
3. Join related data (author, categories, tags)
4. Transform to view model
5. Apply caching if enabled
6. Return response

## 8. Performance Design

### 8.1 Scalability

**Horizontal Scaling:**
- Stateless application design
- Database read replicas
- Load balancer ready

**Database Optimization:**
- Indexed columns for common queries
- Query optimization for listings
- Pagination for large datasets

### 8.2 Caching

**Cache Layers:**
1. **Query Result Cache**: 5-minute TTL for listings
2. **Object Cache**: Individual articles/categories
3. **HTTP Cache**: API responses

**Cache Invalidation:**
- On create/update/delete operations
- Event-driven invalidation
- Tag-based invalidation

## 9. Security Design

### 9.1 Authentication
- No authentication as per requirements
- Future-ready for auth integration

### 9.2 Authorization
- Admin interface access control (future)
- API rate limiting per IP
- Write operation restrictions

**Security Measures:**
- Input validation on all endpoints
- XSS prevention in content
- CSRF protection for admin forms
- SQL injection prevention
- Rate limiting: 100 req/min for reads, 10 req/min for writes

## 10. Testing Strategy

### 10.1 Unit Testing

**Domain Layer:**
- Value object validation
- Aggregate business rules
- Domain event generation
- 95% coverage target

**Application Layer:**
- Command handler logic
- Query handler logic
- Gateway middleware
- 90% coverage target

### 10.2 Integration Testing

**API Testing:**
- Endpoint functionality
- Request/response validation
- Error handling
- Performance benchmarks

**Database Testing:**
- Repository operations
- Transaction handling
- Migration testing

## 11. Deployment Architecture

### 11.1 Infrastructure

**Container Strategy:**
- Docker containers
- PHP-FPM for application
- Nginx for web server
- MySQL/PostgreSQL container

**Environment Configuration:**
- Environment variables for config
- Secrets management
- Health checks

### 11.2 CI/CD Pipeline

**Build Process:**
1. Code checkout
2. Dependency installation
3. Run tests
4. Build Docker image
5. Security scanning

**Deployment Stages:**
1. Development environment
2. Staging environment
3. Production deployment
4. Post-deployment tests

## 12. Risk Assessment

### 12.1 Technical Risks

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Performance degradation with large datasets | High | Medium | Implement caching, pagination, query optimization |
| Slug collision on high traffic | Low | Low | Retry mechanism with numeric suffix |
| Data loss during operations | High | Low | Transaction support, audit logging |
| API abuse without auth | Medium | High | Rate limiting, monitoring, IP blocking |

### 12.2 Mitigation Strategies

**Performance:**
- Implement caching strategy
- Database query optimization
- Lazy loading for relations
- Pagination for all listings

**Data Integrity:**
- Database transactions
- Validation at multiple layers
- Audit trail for changes
- Regular backups

**Security:**
- Rate limiting implementation
- Input validation
- Monitoring and alerting
- Regular security audits

## Component Mapping to Requirements

### Article Management (REQ-001 to REQ-005)
**Components:**
- Domain: Article aggregate, ArticleId, Title, Content, Slug VOs
- Application: CreateArticle, UpdateArticle, PublishArticle commands
- Infrastructure: ArticleRepository, SlugGenerator
- API: /api/articles endpoints

### Category Management (REQ-010 to REQ-013)
**Components:**
- Domain: Category aggregate, CategoryId, CategoryName VOs
- Application: CreateCategory, UpdateCategory, DeleteCategory commands
- Infrastructure: CategoryRepository
- API: /api/categories endpoints

### Tag Management (REQ-020 to REQ-023)
**Components:**
- Domain: Tag value object
- Application: Tag handling in Article commands
- Infrastructure: TagRepository
- API: Tag filtering in article endpoints

### Author Management (REQ-030 to REQ-033)
**Components:**
- Domain: Author aggregate, AuthorId, AuthorName, Email VOs
- Application: CreateAuthor, UpdateAuthor, DeleteAuthor commands
- Infrastructure: AuthorRepository
- API: /api/authors endpoints

## Implementation Priority

1. **Phase 1 - Foundation (US-001)**
   - Article domain model
   - Basic CRUD operations
   - Core validations
   - Database schema

2. **Phase 2 - Categories & Authors (US-002, US-003)**
   - Category management
   - Author management
   - Relationships

3. **Phase 3 - Tags & API (US-004, US-008)**
   - Tag system
   - REST API endpoints
   - API Platform integration

4. **Phase 4 - Admin UI (US-005, US-006, US-007)**
   - Admin interfaces
   - Bulk operations
   - Search functionality

This technical design provides a solid foundation for implementing the blog system while maintaining flexibility for future enhancements.