# Behat Step Definition Consolidation Guide

## Overview

This guide documents advanced patterns for consolidating Behat step definitions to achieve better code maintainability, reduce duplication, and follow DRY principles. These patterns were developed and tested in this project to optimize Behat contexts.

## Core Principles

### 1. Multiple Attributes for Single Function

Instead of creating separate functions for similar operations, use multiple attributes on a single function:

```php
// ❌ Before: Separate functions for similar operations
#[\Behat\Step\Given('the following articles exist:')]
public function theFollowingArticlesExist(TableNode $table): void { }

#[\Behat\Step\Given('the following base articles exist:')]
public function theFollowingBaseArticlesExist(TableNode $table): void { }

#[\Behat\Step\Given('the following reference articles exist:')]
public function theFollowingReferenceArticlesExist(TableNode $table): void { }

// ✅ After: Single function with multiple attributes
#[\Behat\Step\Given('the following articles exist')]
#[\Behat\Step\Given('the following base articles exist')]
#[\Behat\Step\Given('the following reference articles exist')]
#[\Behat\Step\Given('the following articles are pending review')]
public function theFollowingArticlesExist(TableNode $table): void { }
```

### 2. Avoid Colons in Step Definitions

Remove ":" from step definitions as they are not a best practice:

```gherkin
# ❌ Before: Using colons
Given the following articles exist:
  | title | status |
  | Test  | draft  |

# ✅ After: No colons
Given the following articles exist
  | title | status |
  | Test  | draft  |
```

### 3. Generic Step Names for Reusability

Use generic names that can be applied across different contexts:

```php
// ✅ Generic and reusable
#[\Behat\Step\Given('the following articles exist')]
public function theFollowingArticlesExist(TableNode $table): void

// ❌ Too specific - limits reusability
#[\Behat\Step\Given('the following blog articles exist for editorial review')]
public function theFollowingBlogArticlesExistForEditorialReview(TableNode $table): void
```

## Consolidation Patterns

### Pattern 1: Table Data Creation

Consolidate all article creation steps into a single flexible function:

```php
#[\Behat\Step\Given('the following articles exist')]
#[\Behat\Step\Given('the following base articles exist')]
#[\Behat\Step\Given('the following reference articles exist')]
#[\Behat\Step\Given('the following articles are pending review')]
#[\Behat\Step\Given('there are articles pending review')]
public function theFollowingArticlesExist(TableNode $table): void
{
    foreach ($table->getHash() as $row) {
        $factory = BlogArticleFactory::new();

        // Handle title
        if (isset($row['title'])) {
            $factory = $factory->withTitle($row['title']);
        }

        // Handle slug
        if (isset($row['slug'])) {
            $factory = $factory->withSlug($row['slug']);
        }

        // Handle status with smart detection
        if (isset($row['status'])) {
            $factory = match($row['status']) {
                'draft' => $factory->draft(),
                'published' => $factory->published(),
                'pending_review' => $factory->pendingReview(),
                default => $factory->with(['status' => $row['status']])
            };
        }

        // Handle dates
        if (isset($row['createdAt'])) {
            $factory = $factory->with([
                'createdAt' => new \DateTimeImmutable($row['createdAt']),
            ]);
        }

        // Handle content
        if (isset($row['content'])) {
            $factory = $factory->with(['content' => $row['content']]);
        }

        // Handle review-specific fields
        if (isset($row['submittedAt'])) {
            $factory = $factory->with([
                'submittedAt' => new \DateTimeImmutable($row['submittedAt']),
            ]);
        }

        $factory->create();
    }
}
```

### Pattern 2: Count-Based Creation

Consolidate mass creation functions with flexible type support:

```php
#[\Behat\Step\Given(':count articles exist')]
#[\Behat\Step\Given(':count additional articles exist')]
#[\Behat\Step\Given(':count articles exist with alternating statuses')]
#[\Behat\Step\Given(':count additional articles exist with alternating statuses')]
#[\Behat\Step\Given(':count published articles exist')]
public function articlesExist(int $count, string $type = 'mixed'): void
{
    for ($i = 0; $i < $count; ++$i) {
        $id = Uuid::v7();
        
        // Determine status and naming based on type
        [$status, $titlePrefix, $slugPrefix] = match($type) {
            'published' => ['published', 'Published Article', 'published-article'],
            'alternating', 'mixed' => [
                0 === $i % 2 ? 'draft' : 'published',
                'Article',
                'article'
            ],
            default => ['draft', 'Article', 'article']
        };

        $articleData = [
            'id' => $id,
            'title' => sprintf('%s %d', $titlePrefix, $i + 1),
            'content' => sprintf('Content for %s %d', strtolower($titlePrefix), $i + 1),
            'slug' => sprintf('%s-%d', $slugPrefix, $i + 1),
            'status' => $status,
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ];

        if ('published' === $status) {
            $articleData['publishedAt'] = new \DateTimeImmutable();
        }

        BlogArticleFactory::createOne($articleData);
    }
}
```

### Pattern 3: Context-Aware Step Detection

The consolidated function can detect the context and adapt behavior automatically:

```php
public function theFollowingArticlesExist(TableNode $table): void
{
    foreach ($table->getHash() as $row) {
        $factory = BlogArticleFactory::new();

        // Auto-detect review articles based on available columns
        $isReviewArticle = isset($row['submittedAt']) || 
                          isset($row['reviewerId']) || 
                          $row['status'] === 'pending_review';
        
        if ($isReviewArticle) {
            $factory = $factory->pendingReview();
        }

        // Continue with normal processing...
    }
}
```

## Implementation Steps

### Step 1: Identify Duplication

Find all similar step definitions across contexts:

```bash
# Search for duplicated patterns
grep -r "Given.*articles.*exist" tests/
grep -r "there are.*articles" tests/
```

### Step 2: Choose Primary Context

Select the most appropriate context to host the consolidated step:
- **API contexts** for data creation steps
- **UI contexts** for interface-specific steps
- **Shared contexts** for truly generic steps

### Step 3: Add Multiple Attributes

```php
// Add all variations as attributes
#[\Behat\Step\Given('the following articles exist')]
#[\Behat\Step\Given('the following base articles exist')]
#[\Behat\Step\Given('the following reference articles exist')]
#[\Behat\Step\Given('the following articles are pending review')]
public function theFollowingArticlesExist(TableNode $table): void
```

### Step 4: Enhance Logic

Make the function flexible enough to handle all use cases:

```php
public function theFollowingArticlesExist(TableNode $table): void
{
    foreach ($table->getHash() as $row) {
        $factory = BlogArticleFactory::new();

        // Use null coalescing and smart defaults
        $title = $row['title'] ?? sprintf('Article %s', uniqid());
        $content = $row['content'] ?? sprintf('Content for %s with enough text for validation requirements.', $title);
        $slug = $row['slug'] ?? strtolower(str_replace(' ', '-', $title));
        
        $factory = $factory
            ->withTitle($title)
            ->withSlug($slug)
            ->with(['content' => $content]);

        // Handle all possible fields dynamically
        foreach ($row as $field => $value) {
            if (in_array($field, ['title', 'slug', 'content'])) {
                continue; // Already handled
            }
            
            $this->handleFactoryField($factory, $field, $value);
        }

        $factory->create();
    }
}
```

### Step 5: Remove Duplicates

Delete all the old functions that are now redundant:

```php
// Delete these duplicated functions
public function theFollowingBaseArticlesExist(TableNode $table): void { /* DELETE */ }
public function thereAreAdditionalArticles(TableNode $table): void { /* DELETE */ }
public function theFollowingArticlesArePendingReview(TableNode $table): void { /* DELETE */ }
```

### Step 6: Update Features

Update all feature files to use the consolidated step names:

```gherkin
# Update in all .feature files
- Given the following base articles exist:
+ Given the following articles exist

- Given the following reference articles exist:
+ Given the following articles exist

- Given the following articles are pending review:
+ Given the following articles exist
```

## Cross-Context Considerations

### Avoiding Conflicts

When consolidating across multiple contexts, be careful of step conflicts:

```php
// ❌ This creates conflicts if both contexts are loaded
// Context A
#[\Behat\Step\Given('there are articles pending review')]
public function methodA(TableNode $table): void { }

// Context B  
#[\Behat\Step\Given('there are articles pending review')]
public function methodB(TableNode $table): void { }
```

**Solutions:**
1. **Remove from one context** - choose the most appropriate one
2. **Use different step names** - make them more specific
3. **Combine contexts** - if they serve similar purposes

### Context Specialization

Keep specialized steps in their appropriate contexts:

```php
// ✅ Generic steps in API context
class BlogArticleApiContext
{
    #[\Behat\Step\Given('the following articles exist')]
    public function theFollowingArticlesExist(TableNode $table): void { }
}

// ✅ UI-specific steps in UI context  
class ManagingArticlesContext
{
    #[\Behat\Step\Then('I should see the articles grid')]
    public function iShouldSeeTheArticlesGrid(): void { }
    
    #[\Behat\Step\When('I change the limit to :limit')]
    public function iChangeTheLimitTo(string $limit): void { }
}
```

## Quality Validation

### Testing the Consolidation

```bash
# Run all tests to ensure nothing broke
docker compose exec app composer qa:behat

# Check for undefined steps
docker compose exec app vendor/bin/behat --dry-run

# Test specific scenarios
docker compose exec app vendor/bin/behat --name="Create new article"
```

### Expected Results

After consolidation, you should see:
- **✅ No undefined steps**
- **✅ No duplicate step definition conflicts** 
- **✅ All scenarios passing**
- **✅ Reduced lines of code**
- **✅ Better maintainability**

## Benefits Achieved

### 1. DRY Principle
- **Before**: 5+ functions doing similar article creation
- **After**: 1 function handling all variations

### 2. Maintainability
- **Before**: Update 5 functions when adding a field
- **After**: Update 1 function

### 3. Flexibility
- **Before**: Fixed step names for specific contexts
- **After**: Reusable steps across any context

### 4. Best Practices
- **Before**: Inconsistent step naming with colons
- **After**: Clean, uniform step names

## Advanced Patterns

### Dynamic Field Handling

```php
private function handleFactoryField(object $factory, string $field, mixed $value): object
{
    return match($field) {
        'status' => match($value) {
            'draft' => $factory->draft(),
            'published' => $factory->published(),
            'pending_review' => $factory->pendingReview(),
            default => $factory->with([$field => $value])
        },
        'createdAt', 'submittedAt', 'reviewedAt', 'publishedAt' => 
            $factory->with([$field => new \DateTimeImmutable($value)]),
        'authorId', 'reviewerId' => 
            $factory->with([$field => Uuid::fromString($value)]),
        default => 
            $factory->with([$field => $value])
    };
}
```

### Type Detection

```php
private function detectStepType(TableNode $table): string
{
    $firstRow = $table->getHash()[0] ?? [];
    
    if (isset($firstRow['submittedAt']) || isset($firstRow['reviewerId'])) {
        return 'review';
    }
    
    if (isset($firstRow['publishedAt'])) {
        return 'published';
    }
    
    return 'standard';
}
```

## Common Pitfalls

### 1. Over-Consolidation
Don't consolidate steps that serve fundamentally different purposes:

```php
// ❌ Don't consolidate these - they're too different
#[\Behat\Step\Given('the following articles exist')]
#[\Behat\Step\Given('I should see articles in the grid')]  // Different purpose!
public function badConsolidation(): void { }
```

### 2. Context Conflicts
Be careful when the same step exists in multiple loaded contexts.

### 3. Lost Specificity
Don't lose important business language in the process:

```php
// ✅ Keep business-meaningful names
#[\Behat\Step\Given('there are articles pending review')]
public function articlesForReview(TableNode $table): void { }

// ❌ Don't make everything too generic
#[\Behat\Step\Given('there are entities')]
public function entities(TableNode $table): void { }  // Too generic!
```

## Summary

Step definition consolidation is a powerful technique for maintaining clean, DRY Behat code. By following these patterns:

1. **Use multiple attributes** instead of separate functions
2. **Remove colons** from step definitions  
3. **Choose generic, reusable names**
4. **Handle fields dynamically**
5. **Test thoroughly** after consolidation

You can achieve significantly better maintainability while preserving all functionality and improving test reliability.

The result is cleaner, more maintainable test code that follows modern PHP and Behat best practices. 🚀