---
description: Add items to the Sylius Admin UI menu
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), TodoWrite
---

# Admin Menu Configuration

Add or modify items in the Sylius Admin UI menu system.

## Usage
`/admin:menu`

This command helps you add menu items to the admin interface.

## Process

1. **Locate MenuBuilder**
   The menu is configured in `UI/Web/Admin/Menu/MenuBuilder.php` using a decorator pattern.

2. **Add Menu Items**
   ```php
   private function addContentSubMenu(ItemInterface $menu): void
   {
       $content = $menu
           ->addChild('content')
           ->setLabel('app.ui.content')
           ->setLabelAttribute('icon', 'tabler:file-text')
       ;
       
       $content->addChild('[resources]', [
           'route' => 'app_admin_[resource]_index',
       ])
           ->setLabel('app.ui.[resources]')
           ->setLabelAttribute('icon', 'tabler:[icon]')
       ;
   }
   ```

3. **Menu Structure Options**
   - **Top Level**: Main menu items
   - **Submenu**: Grouped items under a parent
   - **Divider**: Visual separation

4. **Menu Item Configuration**
   ```php
   $menu->addChild('unique_key', [
       'route' => 'route_name',
       'routeParameters' => ['param' => 'value'],
       'uri' => 'https://external-link.com', // For external links
       'linkAttributes' => ['target' => '_blank'],
   ])
       ->setLabel('translation.key')
       ->setLabelAttribute('icon', 'tabler:icon-name')
       ->setDisplay(true) // or false to hide
       ->setDisplayChildren(true) // for submenus
   ;
   ```

5. **Available Icons**
   Sylius Admin UI uses Tabler icons. Common ones:
   - `tabler:dashboard` - Dashboard
   - `tabler:file-text` - Documents
   - `tabler:users` - Users
   - `tabler:settings` - Settings
   - `tabler:shopping-cart` - E-commerce
   - `tabler:article` - Articles/Blog
   - `tabler:folder` - Categories
   - `tabler:tag` - Tags
   - `tabler:calendar` - Events
   - `tabler:chart-bar` - Analytics

6. **Conditional Menu Items**
   ```php
   if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
       $menu->addChild('admin_only', [
           'route' => 'admin_route',
       ])
           ->setLabel('app.ui.admin_feature')
           ->setLabelAttribute('icon', 'tabler:shield')
       ;
   }
   ```

7. **Menu Order**
   Items appear in the order they're added. To reorder:
   ```php
   $menu->reorderChildren([
       'dashboard',
       'content',
       'users',
       'settings',
   ]);
   ```

8. **Badges/Counters**
   ```php
   $menu->addChild('notifications')
       ->setLabel('app.ui.notifications')
       ->setLabelAttribute('icon', 'tabler:bell')
       ->setExtra('badge', [
           'value' => 5,
           'type' => 'danger', // success, warning, danger, info
       ])
   ;
   ```

## Menu Decorator Pattern

The MenuBuilder uses Symfony's decorator pattern:
```php
#[AsDecorator(decorates: 'sylius_admin_ui.knp.menu_builder')]
final readonly class MenuBuilder implements MenuBuilderInterface
{
    public function __construct(
        private FactoryInterface $factory,
        // Add other dependencies as needed
    ) {}
    
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        
        // Build your menu structure
        
        return $menu;
    }
}
```

## Best Practices
- Use translation keys for all labels
- Choose appropriate icons for clarity
- Group related items in submenus
- Keep menu structure shallow (max 2 levels)
- Add dividers to separate logical groups
- Consider user roles for menu visibility

## Common Menu Patterns

### Content Management
```php
$content = $menu->addChild('content')
    ->setLabel('app.ui.content')
    ->setLabelAttribute('icon', 'tabler:file-text');

$content->addChild('articles', ['route' => 'app_admin_article_index'])
    ->setLabel('app.ui.articles');
$content->addChild('categories', ['route' => 'app_admin_category_index'])
    ->setLabel('app.ui.categories');
$content->addChild('tags', ['route' => 'app_admin_tag_index'])
    ->setLabel('app.ui.tags');
```

### System Management
```php
$system = $menu->addChild('system')
    ->setLabel('app.ui.system')
    ->setLabelAttribute('icon', 'tabler:settings');

$system->addChild('users', ['route' => 'app_admin_user_index'])
    ->setLabel('app.ui.users');
$system->addChild('settings', ['route' => 'app_admin_settings'])
    ->setLabel('app.ui.settings');
```

## Next Steps
1. Add translation keys for menu labels
2. Clear cache after menu changes
3. Test menu visibility and permissions
4. Verify routes are correctly configured