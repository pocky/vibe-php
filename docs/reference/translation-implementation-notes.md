# Translation System Implementation Notes

## Overview

This document captures the implementation details and lessons learned while integrating the Symfony Translation component with ICU MessageFormat support into the Vibe PHP project.

## Key Implementation Decisions

### 1. Domain Purity

The Domain layer remains completely pure and framework-agnostic:
- Domain exceptions contain translation keys, not translated messages
- No dependency on TranslatorInterface in Domain layer
- Fallback messages provided for debugging without translation system

### 2. Exception Architecture

Created `ValidationException` with translation support:
```php
ValidationException::withTranslationKey(
    translationKey: 'validation.article.title.empty',
    translationParameters: ['min_length' => 5, 'actual_length' => 2],
    translationDomain: 'messages',
    fallbackMessage: 'Title cannot be empty'
)
```

### 3. Translation Context

Translation happens only where needed:
- **UI Layer**: API Processors, Controllers (contextual translation)
- **Never in**: Domain layer, Application layer, Infrastructure repositories
- **Special contexts**: CLI commands and queue workers keep translation keys for debugging

## Challenges and Solutions

### 1. Nested Exceptions in API Platform

**Challenge**: Exceptions can be deeply nested through multiple layers (Domain → Handler → Gateway → API Platform)

**Solution**: Traverse all nested exceptions in UI Processors:
```php
$current = $e;
while ($current instanceof \Throwable) {
    // Check each exception type and handle appropriately
    $current = $current->getPrevious();
}
```

### 2. Validation Middleware Errors

**Challenge**: DefaultValidation middleware wraps validation errors with additional context

**Solution**: Parse error messages to extract translation keys:
```php
if (str_contains($e->getMessage(), 'validation.') && 
    str_contains($e->getMessage(), 'DefaultValidation.php')) {
    // Extract and translate validation keys
}
```

### 3. HTTP Status Code Mapping

**Challenge**: Ensuring correct HTTP status codes for different exception types

**Solution**: Map exceptions to appropriate codes:
- `ArticleAlreadyExists` → 409 Conflict
- `ValidationException` → 400 Bad Request  
- Validation constraint violations → 422 Unprocessable Entity
- `ArticleNotFound` → 404 Not Found

### 4. Test Adaptations

**Challenge**: Tests were checking for English messages instead of translation keys

**Solution**: 
- Unit tests verify translation keys and parameters
- Integration tests check HTTP status codes
- Mock validators for isolated unit testing

## Implementation Patterns

### 1. Value Object Validation

All Value Objects follow this pattern:
```php
if (condition_fails) {
    throw ValidationException::withTranslationKey(
        translationKey: 'validation.article.field.error_type',
        translationParameters: [
            'min_length' => self::MIN_LENGTH,
            'actual_length' => strlen($value),
        ],
        fallbackMessage: 'Human-readable fallback'
    );
}
```

### 2. UI Layer Translation

Processors handle translation contextually:
```php
catch (ValidationException $e) {
    $message = $this->translator->trans(
        $e->getTranslationKey(),
        $e->getTranslationParameters(),
        $e->getTranslationDomain()
    );
    throw new BadRequestHttpException($message, $e);
}
```

### 3. ICU MessageFormat Usage

Advanced features implemented:
- **Pluralization**: `{count, plural, =0 {no articles} =1 {one article} other {# articles}}`
- **Ordinals**: `{position, selectordinal, one {#st} two {#nd} few {#rd} other {#th}}`
- **Date/Time**: `{date, date, full}`, `{time, time, short}`
- **Conditions**: `{status, select, draft {Draft} published {Published} other {Unknown}}`
- **Numbers**: `{amount, number, currency}`, `{value, number, percent}`

## Testing Strategy

### 1. Unit Tests
- Test translation keys, not messages
- Verify translation parameters
- Use mocks for dependencies with final classes

### 2. Integration Tests  
- Test complete translation flow
- Verify HTTP responses contain translated messages
- Check correct status codes

### 3. Behat Tests
- Adjusted scenarios for validation changes
- Test API error responses
- Verify translation in UI context

## Known Limitations

1. **SEO Meta Description**: The SEO validation expects a `meta_description` field that doesn't exist in the current entity structure
2. **Final Classes**: Cannot mock final Gateway classes in unit tests, requiring workarounds
3. **Deeply Nested Exceptions**: Some framework exceptions may be wrapped multiple times

## Future Improvements

1. Add `meta_description` field to article entity
2. Create interfaces for Gateways to improve testability
3. Implement translation caching for performance
4. Add translation coverage metrics to CI/CD
5. Create management commands for translation key validation

## Migration Guide

For developers migrating existing code:

1. Replace hardcoded messages with `ValidationException::withTranslationKey()`
2. Add translation keys to `messages+intl-icu.{locale}.yaml`
3. Update tests to check translation keys instead of messages
4. Add TranslatorInterface to UI layer components
5. Test with multiple locales

## Best Practices Summary

1. **Always** use translation keys in exceptions
2. **Never** translate in Domain or Application layers
3. **Always** provide fallback messages for debugging
4. **Always** include necessary ICU parameters
5. **Test** translation keys, not translated messages
6. **Document** new translation keys immediately
7. **Use** hierarchical key naming: `context.entity.field.error_type`

## Conclusion

The translation system successfully maintains domain purity while providing rich internationalization capabilities. The implementation demonstrates clean architecture principles with contextual translation only where user-facing messages are needed.