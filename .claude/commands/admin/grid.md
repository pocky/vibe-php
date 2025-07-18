---
description: Create or customize a Sylius Grid for admin listings
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), TodoWrite
---

# Sylius Grid Configuration

Create or customize grid configurations for admin resource listings.

## Usage
`/admin:grid [context] [resource-name]`

Example: `/admin:grid Blog Category`

## Process

1. **Create Grid Class**
   ```php
   namespace App\[Context]Context\UI\Web\Admin\Grid;
   
   final class [Resource]Grid extends AbstractGrid implements ResourceAwareGridInterface
   {
       public static function getName(): string
       {
           return self::class;
       }
       
       public function buildGrid(GridBuilderInterface $gridBuilder): void
       {
           // Grid configuration
       }
       
       public function getResourceClass(): string
       {
           return [Resource]Resource::class;
       }
   }
   ```

2. **Configure Fields**
   ```php
   ->addField(
       StringField::create('name')
           ->setLabel('app.ui.name')
           ->setSortable(true)
   )
   ->addField(
       DateTimeField::create('createdAt')
           ->setLabel('app.ui.created_at')
           ->setSortable(true)
   )
   ->addField(
       BooleanField::create('active')
           ->setLabel('app.ui.active')
   )
   ```

3. **Add Filters**
   ```php
   ->addFilter(
       StringFilter::create('name')
           ->setLabel('app.ui.search_by_name')
   )
   ->addFilter(
       SelectFilter::create('status')
           ->setLabel('app.ui.status')
           ->setChoices([
               'active' => 'app.ui.active',
               'inactive' => 'app.ui.inactive',
           ])
   )
   ->addFilter(
       DateFilter::create('createdAt')
           ->setLabel('app.ui.created_date')
   )
   ```

4. **Configure Actions**
   ```php
   // Main actions (above grid)
   ->addActionGroup(
       MainActionGroup::create(
           CreateAction::create()
               ->setLabel('app.ui.create_[resource]')
               ->setIcon('tabler:plus')
       )
   )
   
   // Item actions (per row)
   ->addActionGroup(
       ItemActionGroup::create(
           ShowAction::create()
               ->setIcon('tabler:eye'),
           UpdateAction::create()
               ->setIcon('tabler:pencil'),
           DeleteAction::create()
               ->setIcon('tabler:trash')
       )
   )
   ```

5. **Custom Actions**
   ```php
   Action::create('approve', 'update')
       ->setLabel('app.ui.approve')
       ->setIcon('tabler:check')
       ->setOptions([
           'link' => [
               'route' => 'app_admin_[resource]_approve',
               'parameters' => [
                   'id' => 'resource.id',
               ],
           ],
       ])
   ```

6. **Pagination Settings**
   ```php
   ->setLimits([10, 20, 50, 100])
   ->setDefaultLimit(20)
   ```

## Field Types Available

- **StringField**: Text display
- **DateTimeField**: Date/time with formatting
- **BooleanField**: Yes/No display
- **NumberField**: Numeric display
- **TwigField**: Custom Twig template
- **BadgeField**: Status badges
- **ImageField**: Image preview

## Filter Types Available

- **StringFilter**: Text search
- **SelectFilter**: Dropdown selection
- **DateFilter**: Date range
- **BooleanFilter**: Yes/No/All
- **EntityFilter**: Related entity selection
- **RangeFilter**: Numeric range

## Advanced Features

### Bulk Actions
```php
->addBulkActionGroup(
    BulkActionGroup::create(
        DeleteBulkAction::create(),
        Action::create('bulk_publish', 'update')
            ->setLabel('app.ui.publish_selected')
    )
)
```

### Custom Provider
```php
->setProvider([Resource]GridProvider::class)
```

### Conditional Actions
```php
UpdateAction::create()
    ->setEnabled('resource.status !== "published"')
```

## Best Practices
- Always add translations for labels
- Use icons from Tabler icon set
- Keep grids focused and performant
- Add appropriate indexes for sortable fields
- Limit default items per page for performance

## Next Steps
1. Register grid with resource
2. Create GridProvider if using custom logic
3. Add translations for all labels
4. Test filtering and sorting functionality