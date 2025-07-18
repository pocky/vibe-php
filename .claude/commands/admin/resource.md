---
description: Create a complete Sylius Admin UI resource
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# Sylius Admin Resource Creation

Create a complete admin interface for managing domain entities using Sylius Admin UI.

## Usage
`/admin:resource [context] [resource-name]`

Example: `/admin:resource Blog Category`


## Symfony Maker Integration

This command complements the Symfony Maker bundle. You can generate the complete admin resource structure using:

```bash
# Generate all admin components at once
docker compose exec app bin/console make:admin:resource [Context] [Entity]

# Example:
docker compose exec app bin/console make:admin:resource BlogContext Category
```

This Maker will create:
- Resource class with all operations configured
- Grid configuration with data provider
- Form type with validation
- Item and Grid providers
- Create, Update, and Delete processors
- All properly namespaced and following project conventions

## Process

1. **Create Resource Structure**
   ```
   UI/Web/Admin/
   ├── Resource/
   │   └── CategoryResource.php
   ├── Grid/
   │   └── CategoryGrid.php
   ├── Form/
   │   └── CategoryType.php
   ├── Provider/
   │   ├── CategoryGridProvider.php
   │   └── CategoryItemProvider.php
   └── Processor/
       ├── CreateCategoryProcessor.php
       ├── UpdateCategoryProcessor.php
       └── DeleteCategoryProcessor.php
   ```

2. **Create Resource Class**
   ```php
   #[AsResource(
       alias: 'app.category',
       section: 'admin',
       formType: CategoryType::class,
       templatesDir: '@SyliusAdminUi/crud',
       routePrefix: '/admin',
       driver: 'doctrine/orm',
   )]
   #[Index(grid: CategoryGrid::class)]
   #[Create(
       processor: CreateCategoryProcessor::class,
       redirectToRoute: 'app_admin_category_index',
   )]
   #[Show(provider: CategoryItemProvider::class)]
   #[Update(
       provider: CategoryItemProvider::class,
       processor: UpdateCategoryProcessor::class,
       redirectToRoute: 'app_admin_category_index',
   )]
   #[Delete(
       provider: CategoryItemProvider::class,
       processor: DeleteCategoryProcessor::class,
   )]
   final class CategoryResource implements ResourceInterface
   ```

3. **Create Grid Configuration**
   - Define fields (string, date, boolean)
   - Add sorting capabilities
   - Configure actions (create, update, delete)
   - Set pagination limits

4. **Create Form Type**
   - Symfony form with validation
   - Translation labels
   - Custom field types as needed
   - Constraints for business rules

5. **Create Providers**
   - **GridProvider**: Lists with pagination using Pagerfanta
   - **ItemProvider**: Single item retrieval
   - Transform Gateway responses to Resources

6. **Create Processors**
   - **CreateProcessor**: Handle POST via Gateway
   - **UpdateProcessor**: Handle PUT via Gateway
   - **DeleteProcessor**: Handle DELETE via Gateway
   - Exception handling with proper HTTP codes

7. **Update Menu**
   - Add new menu item to MenuBuilder
   - Configure icon and translation key
   - Set proper route

8. **Configure Routes**
   - Resource auto-generates routes:
     - GET /admin/categories (index)
     - GET/POST /admin/categories/new (create)
     - GET /admin/categories/{id} (show)
     - GET/PUT /admin/categories/{id}/edit (update)
     - DELETE /admin/categories/{id} (delete)

## Integration Points
- Uses Application Gateways for all operations
- Transforms between Resources and Gateway requests/responses
- Handles validation at form level
- Manages translations for UI

## Quality Standards
- Follow @docs/reference/sylius-admin-ui-integration.md
- Implement all CRUD operations
- Use proper exception handling
- Add comprehensive validation
- Support translations

## Generated Routes
The resource will automatically generate:
- `app_admin_[resource]_index`
- `app_admin_[resource]_create`
- `app_admin_[resource]_show`
- `app_admin_[resource]_update`
- `app_admin_[resource]_delete`

## Next Steps
1. Run migrations if new entity
2. Add translations in `translations/messages.en.yaml`
3. Clear cache: `docker compose exec app bin/console cache:clear`
4. Test admin interface at `/admin/[resources]`
