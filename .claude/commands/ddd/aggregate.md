---
description: Create a DDD aggregate root with domain events
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# DDD Aggregate Creation

Create a domain aggregate root with event sourcing capabilities and repository.

## Usage
`/ddd:aggregate [context] [aggregate-name]`

Example: `/ddd:aggregate Blog Article`

## Symfony Maker Integration

This command complements the Symfony Maker bundle. You can generate the aggregate structure using:

```bash
# Generate aggregate root with domain events
docker compose exec app bin/console make:domain:aggregate [Context] [AggregateName]

# Example:
docker compose exec app bin/console make:domain:aggregate BlogContext Article
```

This Maker will create:
- Aggregate root class with event recording
- Creator service with `__invoke()` method
- Domain events for state changes
- Exception classes for business rule violations
- Repository interface
- Unit tests for the aggregate

## Process

1. **Design Aggregate Boundaries**
   - Identify aggregate invariants
   - Define consistency boundaries
   - List child entities and value objects
   - Plan domain events

2. **Create Aggregate Root**
   ```php
   final class Article
   {
       private array $events = [];
       
       public static function create(/* params */): self
       {
           // Factory method
           $article = new self(/* ... */);
           $article->recordEvent(new ArticleCreated(/* ... */));
           return $article;
       }
       
       public function publish(): void
       {
           // Business method with invariant checks
           if ($this->status !== ArticleStatus::DRAFT) {
               throw new InvalidArticleOperation('Only draft articles can be published');
           }
           $this->status = ArticleStatus::PUBLISHED;
           $this->recordEvent(new ArticlePublished(/* ... */));
       }
       
       public function releaseEvents(): array
       {
           $events = $this->events;
           $this->events = [];
           return $events;
       }
   }
   ```

3. **Create Domain Events**
   - Event per state change
   - Immutable event objects
   - Contains all relevant data
   - Follow naming: Past tense (ArticleCreated, ArticlePublished)

4. **Create Domain Service/Creator**
   - Entry point with `__invoke()` method
   - Orchestrates aggregate creation
   - Returns aggregate with unreleased events
   - Pure domain logic, no infrastructure

5. **Create Repository Implementation**
   - Implements domain interface
   - Maps between domain and infrastructure
   - Handles event persistence if needed
   - Transaction boundaries

6. **Create Tests**
   - Test aggregate invariants
   - Test event generation
   - Test business methods
   - Use in-memory repository for tests

7. **Integration with CQRS**
   - Command handlers use Creator
   - Handlers dispatch events via EventBus
   - Query handlers bypass aggregate for reads

## Architecture Patterns
- Follow @docs/reference/cqrs-pattern.md
- Apply @docs/reference/domain-layer-pattern.md
- Use event-driven communication

## Quality Standards
- Aggregates enforce all business rules
- Events represent business facts
- No infrastructure dependencies
- Complete test coverage

## Next Steps
1. Create commands/queries: `/spec:act`
2. Create gateway: `/ddd:gateway`
3. Create API endpoints: `/api:resource`