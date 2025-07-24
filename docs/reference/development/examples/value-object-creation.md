# Value Object Creation Examples

## Overview

This document provides practical examples of creating value objects using the `/code/hexagonal/value-object` command.

## Basic Usage

### Creating a Simple Value Object

```bash
# Create a generic value object
/code/hexagonal/value-object BlogContext Title
```

This creates:
- `src/BlogContext/Domain/Shared/ValueObject/Title.php`
- Basic validation structure
- `getValue()` and `equals()` methods

### Using Specific Templates

#### Email Value Object
```bash
/code/hexagonal/value-object UserContext Email email
```

Features:
- Email format validation
- Normalization to lowercase
- `getDomain()` and `getLocalPart()` methods

#### Money Value Object
```bash
/code/hexagonal/value-object PaymentContext Price money
```

Features:
- Amount in cents (integer)
- Currency code validation
- Arithmetic operations support

#### Phone Number
```bash
/code/hexagonal/value-object ContactContext PhoneNumber phone
```

Features:
- Phone format validation
- Country code extraction
- Formatted display

## Complete Example: Building Article Entity

### Step 1: Create All Value Objects

```bash
# Article identifier
/code/hexagonal/value-object BlogContext ArticleId

# Article properties
/code/hexagonal/value-object BlogContext ArticleTitle
/code/hexagonal/value-object BlogContext ArticleContent  
/code/hexagonal/value-object BlogContext ArticleStatus
/code/hexagonal/value-object BlogContext Slug
/code/hexagonal/value-object BlogContext PublishedAt
```

### Step 2: Implement Validation with TDD

Create tasks for each value object:

```bash
/spec:tasks "Implement ArticleTitle validation"
```

Example tasks.md:
```markdown
## Task 1: ArticleTitle Validation
- Minimum length: 5 characters
- Maximum length: 200 characters
- Cannot contain only whitespace
- Must trim whitespace

## Task 2: Slug Validation
- Only lowercase letters, numbers, and hyphens
- Cannot start or end with hyphen
- No consecutive hyphens
- Maximum length: 250 characters

## Task 3: ArticleStatus Enum
- Valid values: draft, published, archived
- Add helper methods: isDraft(), isPublished(), canBePublished()
```

### Step 3: Implement with /act

```bash
/act
```

Follow Red-Green-Refactor for each validation rule.

### Step 4: Create the Entity

```bash
/code/hexagonal/entity BlogContext Article
```

### Step 5: Compose Entity with Value Objects

```php
final class Article
{
    public function __construct(
        private ArticleId $id,
        private ArticleTitle $title,
        private ArticleContent $content,
        private Slug $slug,
        private ArticleStatus $status,
        private ?PublishedAt $publishedAt = null,
    ) {}
}
```

## Common Patterns

### Identity Value Objects

Always create an ID value object for entities:

```bash
/code/hexagonal/value-object OrderContext OrderId
/code/hexagonal/value-object CustomerContext CustomerId
/code/hexagonal/value-object ProductContext ProductId
```

### Status/State Value Objects

Use enums or constrained strings:

```bash
/code/hexagonal/value-object OrderContext OrderStatus
/code/hexagonal/value-object UserContext AccountStatus
/code/hexagonal/value-object InventoryContext StockLevel
```

### Business Identifiers

```bash
/code/hexagonal/value-object OrderContext OrderNumber
/code/hexagonal/value-object ProductContext Sku
/code/hexagonal/value-object InvoiceContext InvoiceNumber
```

## Template Selection Guide

### When to Use Each Template

| Template | Use Cases | Example |
|----------|-----------|---------|
| generic | Custom validation rules | Title, Description, Code |
| email | Email addresses | UserEmail, ContactEmail |
| money | Monetary values | Price, Cost, Balance |
| phone | Phone numbers | CustomerPhone, SupportPhone |
| url | Web addresses | WebsiteUrl, ApiEndpoint |
| percentage | Percentage values (0-100) | DiscountRate, TaxRate |

### Custom Templates

For domain-specific patterns, use generic and customize:

```bash
# SKU with specific format
/code/hexagonal/value-object ProductContext Sku generic

# Then implement custom validation:
# - Format: XXX-000-XXX (letters-numbers-letters)
# - Uppercase only
# - Exactly 11 characters
```

## Integration Tips

### With Doctrine

Map value objects as embeddables or custom types:

```php
#[ORM\Embedded(class: Email::class)]
private Email $email;

#[ORM\Column(type: 'article_status')]
private ArticleStatus $status;
```

### With API Platform

Use in DTOs for automatic validation:

```php
final class CreateArticleRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,
        
        #[Assert\NotBlank]
        public string $content,
    ) {}
    
    public function toCommand(): CreateArticleCommand
    {
        return new CreateArticleCommand(
            title: new ArticleTitle($this->title),
            content: new ArticleContent($this->content),
        );
    }
}
```

### With Forms

Create custom form types:

```php
class ArticleTitleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn(?ArticleTitle $title) => $title?->getValue(),
            fn(?string $value) => $value ? new ArticleTitle($value) : null
        ));
    }
}
```

## Testing Value Objects

### Unit Test Structure

```php
class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('user@example.com');
        $this->assertEquals('user@example.com', $email->getValue());
    }
    
    public function testNormalizesToLowercase(): void
    {
        $email = new Email('User@EXAMPLE.com');
        $this->assertEquals('user@example.com', $email->getValue());
    }
    
    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        new Email('not-an-email');
    }
}
```

## Best Practices

1. **Create Value Objects First**: Before creating entities
2. **One Concept, One Value Object**: Don't combine unrelated validations
3. **Rich Behavior**: Add domain-specific methods
4. **Immutability**: Never add setters
5. **Fail Fast**: Validate in constructor
6. **Clear Errors**: Use specific translation keys

## Common Mistakes to Avoid

❌ **Creating generic "Value" objects**
```php
// Bad
class Value { 
    public function __construct(private mixed $value) {}
}
```

✅ **Creating specific value objects**
```php
// Good
class ProductPrice { 
    public function __construct(private int $cents) {}
}
```

❌ **Validation in entities**
```php
// Bad - validation in entity
class Product {
    public function setPrice(int $price): void {
        if ($price < 0) throw new \Exception();
        $this->price = $price;
    }
}
```

✅ **Validation in value objects**
```php
// Good - validation in value object
class Product {
    public function __construct(
        private ProductPrice $price
    ) {}
}
```

## Next Steps

After creating value objects:
1. Use them in entities and aggregates
2. Map them in repositories
3. Create form transformers
4. Add to API resources
5. Document business rules