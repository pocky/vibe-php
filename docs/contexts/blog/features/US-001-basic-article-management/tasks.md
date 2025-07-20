# US-001: Basic Article Management - Implementation Tasks

## Domain Layer Implementation

### Task 1: Update CreateArticle Creator with Business Logic
- [ ] Inject ArticleIdGenerator and SlugGenerator dependencies
- [ ] Implement article creation with proper validation
- [ ] Generate unique slug from title
- [ ] Ensure slug uniqueness via repository check
- [ ] Return created Article aggregate with events

### Task 2: Create UpdateArticle Domain Service
- [ ] Create UpdateArticle/Updater.php with business logic
- [ ] Validate that article exists
- [ ] Update only provided fields (title, content, slug)
- [ ] Regenerate slug if title changes
- [ ] Ensure slug uniqueness if changed
- [ ] Return updated Article with events

### Task 3: Create PublishArticle Domain Service  
- [ ] Create PublishArticle/Publisher.php
- [ ] Validate article exists and is in draft status
- [ ] Set published timestamp
- [ ] Change status to published
- [ ] Return published Article with events

### Task 4: Create DeleteArticle Domain Service
- [ ] Create DeleteArticle/Deleter.php
- [ ] Validate article exists
- [ ] Perform soft or hard delete based on requirements
- [ ] Return void (command pattern)

## Application Layer Implementation

### Task 5: Implement CreateArticle Command and Handler
- [ ] Define command properties: title, content, authorId, status
- [ ] Implement handler with Creator injection
- [ ] Dispatch domain events via EventBus
- [ ] Return void (pure command pattern)

### Task 6: Implement UpdateArticle Command and Handler
- [ ] Define command properties: articleId, title?, content?, slug?
- [ ] Implement handler with Updater injection
- [ ] Handle partial updates
- [ ] Dispatch domain events

### Task 7: Implement PublishArticle Command and Handler
- [ ] Define command properties: articleId, publishAt?
- [ ] Implement handler with Publisher injection
- [ ] Support scheduled publishing
- [ ] Dispatch domain events

### Task 8: Implement DeleteArticle Command and Handler
- [ ] Define command properties: articleId
- [ ] Implement handler with Deleter injection
- [ ] Dispatch domain events

### Task 9: Implement GetArticle Query and Handler
- [ ] Define query property: articleId
- [ ] Implement handler with repository injection
- [ ] Create ArticleView response model
- [ ] Handle not found cases

### Task 10: Implement ListArticles Query and Handler
- [ ] Define query properties: page, limit, status?, authorId?
- [ ] Implement handler with repository injection
- [ ] Create ArticleListView response model
- [ ] Support filtering and pagination

## Gateway Implementation

### Task 11: Implement CreateArticle Gateway
- [ ] Update Request with validation rules
- [ ] Update Response structure
- [ ] Implement Processor middleware
- [ ] Create validation middleware if needed

### Task 12: Implement UpdateArticle Gateway
- [ ] Update Request with partial update support
- [ ] Update Response structure
- [ ] Implement Processor middleware
- [ ] Handle not found errors

### Task 13: Implement PublishArticle Gateway
- [ ] Update Request with articleId and optional publishAt
- [ ] Update Response structure
- [ ] Implement Processor middleware
- [ ] Validate article status

### Task 14: Implement DeleteArticle Gateway
- [ ] Update Request with articleId
- [ ] Update Response structure (success/failure)
- [ ] Implement Processor middleware

### Task 15: Implement GetArticle Gateway
- [ ] Update Request with articleId
- [ ] Update Response with article data structure
- [ ] Implement Processor middleware
- [ ] Handle not found cases

### Task 16: Implement ListArticles Gateway  
- [ ] Update Request with filters
- [ ] Update Response with pagination
- [ ] Implement Processor middleware
- [ ] Support query parameters

## Infrastructure Layer Implementation

### Task 17: Create SlugGenerator Service
- [ ] Create Infrastructure/Service/SlugGenerator.php
- [ ] Use Cocur/Slugify library
- [ ] Implement unique slug generation with suffix support

### Task 18: Configure Service Container
- [ ] Register all domain services
- [ ] Configure command/query handlers
- [ ] Wire up gateways with dependencies
- [ ] Configure repository bindings

## Testing

### Task 19: Unit Tests for Domain Layer
- [ ] Test all value objects validation
- [ ] Test Article aggregate behavior
- [ ] Test domain services
- [ ] Test domain events

### Task 20: Integration Tests for Application Layer
- [ ] Test command handlers
- [ ] Test query handlers
- [ ] Test gateway integrations
- [ ] Test repository operations

## Completion Checklist

- [ ] All business rules from US-001 implemented
- [ ] Slug generation and uniqueness enforced
- [ ] Status management (draft/published)
- [ ] Timestamps automatically managed
- [ ] Published articles cannot revert to draft
- [ ] All validation rules enforced
- [ ] Domain events properly emitted
- [ ] Quality assurance passing