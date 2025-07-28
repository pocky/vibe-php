# Repository Pattern Migration Guide

## Overview

This guide helps developers migrate from the deprecated `save()` method to explicit `add()` and `update()` methods in repository interfaces. This change improves code clarity by making the operation intent explicit and aligns better with Domain-Driven Design principles.

## Why This Change?

### Problems with `save()`
- **Ambiguous intent**: Does it create or modify?
- **Hidden complexity**: Developer must guess behavior
- **DDD misalignment**: Creation and modification are distinct business operations
- **Type safety issues**: No guarantee about entity existence

### Benefits of Explicit Methods
- **Clear intent**: `add()` creates, `update()` modifies
- **Better DDD alignment**: Operations match business intent
- **Type safety**: Explicit about entity lifecycle
- **Easier testing**: Clearer method contracts

## Migration Steps

### Step 1: Update Repository Interfaces

**Before:**
```php
interface AuthorWriteRepositoryInterface
{
    public function save(Author $author): void;
    public function remove(Author $author): void;
}
```

**After:**
```php
interface AuthorWriteRepositoryInterface
{
    public function add(Author $author): void;
    public function update(Author $author): void;
    public function remove(Author $author): void;
}
```

### Step 2: Update Repository Implementations

**Before:**
```php
class AuthorWriteRepository extends DoctrineRepository implements AuthorWriteRepositoryInterface
{
    public function save(Author $author): void
    {
        $entity = $this->findEntityOrCreate($author);
        $this->mapAggregateToEntity($author, $entity);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
    
    private function findEntityOrCreate(Author $author): DoctrineAuthor
    {
        $entity = $this->find(Uuid::fromString($author->getId()->getValue()));
        return $entity ?? new DoctrineAuthor();
    }
}
```

**After:**
```php
class AuthorWriteRepository extends DoctrineRepository implements AuthorWriteRepositoryInterface
{
    public function add(Author $author): void
    {
        $entity = new DoctrineAuthor(
            id: Uuid::fromString($author->getId()->getValue()),
            name: $author->getName()->getValue(),
            email: $author->getEmail()->getValue(),
            bio: $author->getBio()->getValue(),
        );
        
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function update(Author $author): void
    {
        $entity = $this->find(Uuid::fromString($author->getId()->getValue()));
        if (null === $entity) {
            throw new \RuntimeException(sprintf('Author not found: %s', $author->getId()->getValue()));
        }
        
        $entity->name = $author->getName()->getValue();
        $entity->email = $author->getEmail()->getValue();
        $entity->bio = $author->getBio()->getValue();
        
        $this->getEntityManager()->flush();
    }
}
```

### Step 3: Update Domain Services

**Before:**
```php
class AuthorCreator
{
    public function create(AuthorName $name, AuthorEmail $email, AuthorBio $bio): Author
    {
        if ($this->authorRepository->existsByEmail($email)) {
            throw new AuthorAlreadyExists();
        }

        $author = Author::create($name, $email, $bio);
        $this->authorRepository->save($author); // Ambiguous - is this create or update?

        return $author;
    }
}

class AuthorUpdater
{
    public function updateName(AuthorId $authorId, AuthorName $newName): void
    {
        $author = $this->authorRepository->findById($authorId);
        if (!$author) {
            throw new AuthorNotFound();
        }

        $author->updateName($newName);
        $this->authorRepository->save($author); // Ambiguous - is this create or update?
    }
}
```

**After:**
```php
class AuthorCreator
{
    public function create(AuthorName $name, AuthorEmail $email, AuthorBio $bio): Author
    {
        if ($this->authorRepository->existsByEmail($email)) {
            throw new AuthorAlreadyExists();
        }

        $author = Author::create($name, $email, $bio);
        $this->authorRepository->add($author); // Clear - this creates new entity

        return $author;
    }
}

class AuthorUpdater
{
    public function updateName(AuthorId $authorId, AuthorName $newName): void
    {
        $author = $this->authorRepository->findById($authorId);
        if (!$author) {
            throw new AuthorNotFound();
        }

        $author->updateName($newName);
        $this->authorRepository->update($author); // Clear - this modifies existing entity
    }
}
```

### Step 4: Update Command Handlers

**Before:**
```php
class CreateAuthorHandler
{
    public function __invoke(CreateAuthorCommand $command): void
    {
        $author = Author::create(
            new AuthorName($command->name),
            new AuthorEmail($command->email),
            new AuthorBio($command->bio)
        );

        $this->authorRepository->save($author); // Which operation?
    }
}

class UpdateAuthorHandler
{
    public function __invoke(UpdateAuthorCommand $command): void
    {
        $author = $this->authorRepository->findById(
            new AuthorId($command->authorId)
        );
        
        if (!$author) {
            throw new AuthorNotFound();
        }

        $author->updateName(new AuthorName($command->name));
        $this->authorRepository->save($author); // Which operation?
    }
}
```

**After:**
```php
class CreateAuthorHandler
{
    public function __invoke(CreateAuthorCommand $command): void
    {
        $author = Author::create(
            new AuthorName($command->name),
            new AuthorEmail($command->email),
            new AuthorBio($command->bio)
        );

        $this->authorRepository->add($author); // Clearly creating
    }
}

class UpdateAuthorHandler
{
    public function __invoke(UpdateAuthorCommand $command): void
    {
        $author = $this->authorRepository->findById(
            new AuthorId($command->authorId)
        );
        
        if (!$author) {
            throw new AuthorNotFound();
        }

        $author->updateName(new AuthorName($command->name));
        $this->authorRepository->update($author); // Clearly updating
    }
}
```

### Step 5: Update Tests

**Before:**
```php
class AuthorRepositoryTest extends TestCase
{
    public function testSaveNewAuthor(): void
    {
        $author = Author::create(
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Test bio')
        );

        $this->repository->save($author); // Unclear operation

        $found = $this->repository->findById($author->getId());
        $this->assertNotNull($found);
    }

    public function testSaveExistingAuthor(): void
    {
        $author = $this->createPersistedAuthor();
        
        $author->updateName(new AuthorName('Updated Name'));
        $this->repository->save($author); // Same method for different operation

        $updated = $this->repository->findById($author->getId());
        $this->assertEquals('Updated Name', $updated->getName()->getValue());
    }
}
```

**After:**
```php
class AuthorRepositoryTest extends TestCase
{
    public function testAddNewAuthor(): void
    {
        $author = Author::create(
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Test bio')
        );

        $this->repository->add($author); // Clear this adds new entity

        $found = $this->repository->findById($author->getId());
        $this->assertNotNull($found);
    }

    public function testUpdateExistingAuthor(): void
    {
        $author = $this->createPersistedAuthor();
        
        $author->updateName(new AuthorName('Updated Name'));
        $this->repository->update($author); // Clear this updates existing entity

        $updated = $this->repository->findById($author->getId());
        $this->assertEquals('Updated Name', $updated->getName()->getValue());
    }

    public function testUpdateNonExistentAuthorThrowsException(): void
    {
        $author = Author::create(
            new AuthorName('Non Existent'),
            new AuthorEmail('nonexistent@example.com'),
            new AuthorBio('Bio')
        );

        $this->expectException(\RuntimeException::class);
        $this->repository->update($author); // Now we can test this specific case
    }
}
```

## Automated Migration Tools

### Search and Replace Patterns

Use these patterns to help migrate your codebase:

```bash
# Find all save() method calls in domain services
grep -r "->save(" src/*/Domain/

# Find all save() method calls in command handlers
grep -r "->save(" src/*/Application/Operation/Command/

# Find repository interface definitions with save()
grep -r "public function save(" src/*/Domain/*/Repository/
```

### Replacement Rules

1. **Creating new entities** → Replace `save()` with `add()`
2. **Modifying existing entities** → Replace `save()` with `update()`
3. **Repository interfaces** → Remove `save()`, add `add()` and `update()`

## Decision Matrix

Use this matrix to decide which method to use:

| Scenario | Method | Reason |
|----------|--------|---------|
| New aggregate creation | `add()` | Entity doesn't exist yet |
| Aggregate modification | `update()` | Entity exists and is being changed |
| Aggregate state change | `update()` | Business state transition |
| Aggregate deletion | `remove()` | Clear removal intent |

## Common Pitfalls

### ❌ Wrong: Guessing the Operation
```php
// Don't do this - unclear intent
public function processOrder(Order $order): void
{
    $this->orderRepository->save($order); // Create or update?
}
```

### ✅ Right: Explicit Operations
```php
// Do this - clear intent
public function createOrder(CreateOrderData $data): void
{
    $order = Order::create($data->customerId, $data->items);
    $this->orderRepository->add($order); // Clearly creating
}

public function updateOrderStatus(OrderId $orderId, OrderStatus $status): void
{
    $order = $this->orderRepository->findById($orderId);
    $order->updateStatus($status);
    $this->orderRepository->update($order); // Clearly updating
}
```

### ❌ Wrong: Not Handling Update Failures
```php
// Don't do this - no error handling
public function update(Author $author): void
{
    $entity = $this->find($author->getId());
    // What if entity is null?
    $entity->name = $author->getName();
}
```

### ✅ Right: Proper Error Handling
```php
// Do this - explicit error handling
public function update(Author $author): void
{
    $entity = $this->find($author->getId());
    if (null === $entity) {
        throw new \RuntimeException(sprintf('Author not found: %s', $author->getId()));
    }
    
    $entity->name = $author->getName();
}
```

## Validation Checklist

After migration, verify:

- [ ] All `save()` method calls are replaced with `add()` or `update()`
- [ ] Repository interfaces define explicit methods
- [ ] Domain services use appropriate methods based on operation intent
- [ ] Command handlers clearly indicate create vs modify operations
- [ ] Tests cover both add and update scenarios separately
- [ ] Error handling is in place for update operations on non-existent entities
- [ ] All QA tools pass (`composer qa`)

## Benefits After Migration

### Improved Code Clarity
- **Self-documenting**: Method names explain intent
- **Easier onboarding**: New developers understand operations immediately
- **Better debugging**: Clear stack traces showing operation type

### Enhanced Type Safety
- **Compile-time checks**: TypeScript/PHPStan can verify correct usage
- **Runtime validation**: Update operations can check entity existence
- **Error handling**: Specific exceptions for each operation type

### Better Testing
- **Isolated tests**: Test creation and modification separately
- **Edge case coverage**: Test update failures explicitly
- **Clearer assertions**: Tests match business operations

### DDD Alignment
- **Business language**: Operations match domain concepts
- **Aggregate lifecycle**: Clear entity lifecycle management
- **Domain services**: Services express business intent correctly

## Conclusion

The migration from `save()` to explicit `add()`/`update()` methods significantly improves code quality by:

1. **Making intent explicit** - No more guesswork about operations
2. **Improving error handling** - Better failure modes and debugging
3. **Enhancing testability** - Clearer test scenarios and assertions
4. **Aligning with DDD** - Operations match business concepts

This change requires initial effort but pays dividends in maintainability, clarity, and developer experience.