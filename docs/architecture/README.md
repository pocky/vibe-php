# Architecture Documentation

This directory contains architectural documentation for the application's bounded contexts following Domain-Driven Design principles.

## Files

- **bounded-context-template.md** - Template for documenting new bounded contexts
- **security-context-overview.md** - Complete architecture documentation for the Security Context

## Creating Architecture Documentation for a New Context

When implementing a new bounded context, follow these steps:

### 1. Copy the Template

```bash
cp docs/architecture/bounded-context-template.md docs/architecture/[your-context]-overview.md
```

### 2. Fill in the Sections

Replace all placeholders marked with `[brackets]`:
- `[Context Name]` - Your context name (e.g., "Billing", "Inventory", "Shipping")
- `[UseCase]` - Specific use cases (e.g., "CreateInvoice", "UpdateStock")
- `[Entity]` - Domain entities (e.g., "Invoice", "Product")
- `[ValueObject]` - Value objects (e.g., "Money", "Quantity")

### 3. Document as You Build

Update the documentation as you implement:
- Add new use cases to the domain structure
- Document value objects and their validation rules
- List domain events as you define them
- Update integration points with other contexts

### 4. Key Sections to Focus On

#### Domain Layer Structure
- List all use cases with clear descriptions
- Document the folder structure matching your implementation
- Specify value objects and their business rules

#### Gateway Pattern
- Document each gateway's purpose and access pattern
- List middleware requirements (validation, authorization, etc.)
- Specify request/response structures

#### Cross-Cutting Concerns
- Define business rules and invariants
- Specify security requirements
- Document performance considerations

#### Inter-Context Communication
- List events published by this context
- Document events this context subscribes to
- Describe integration points with other contexts

### 5. Review Checklist

Before finalizing your architecture document:

- [ ] All placeholders replaced with actual values
- [ ] Use case structure matches implementation
- [ ] Domain events are documented
- [ ] Repository interfaces are listed
- [ ] Gateway patterns are explained
- [ ] Integration points are clear
- [ ] Test strategy is defined
- [ ] Deployment considerations are addressed

## Best Practices

1. **Keep it Updated** - Update documentation as the context evolves
2. **Be Specific** - Replace generic descriptions with context-specific details
3. **Include Examples** - Add code snippets for complex patterns
4. **Document Decisions** - Explain why certain architectural choices were made
5. **Link to Code** - Reference actual implementation files where helpful

## Example

See `security-context-overview.md` for a complete example of a well-documented bounded context following all the patterns and principles outlined in the template.

---

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>