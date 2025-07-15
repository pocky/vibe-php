# Sylius Admin UI Integration

## Overview

This document describes the integration of Sylius Admin UI components into the Vibe PHP project, providing a professional admin interface for managing blog articles while maintaining our DDD/Hexagonal architecture.

## Architecture Decision

### Why Sylius Admin UI?

1. **Professional UI**: Pre-built, tested admin components
2. **Resource Management**: Powerful CRUD operations with minimal code
3. **Grid System**: Advanced filtering, sorting, and pagination
4. **Form Integration**: Seamless Symfony Forms integration
5. **Extensibility**: Easy to customize and extend

### Integration Approach

We integrated Sylius Admin UI as a UI layer component, maintaining separation between:
- **UI Layer**: Sylius components (Resources, Grids, Forms)
- **Application Layer**: Our Gateways and CQRS handlers
- **Domain Layer**: Pure business logic

## Implementation Structure

### Directory Organization

```
src/BlogContext/UI/Web/Admin/
├── Form/
│   └── ArticleType.php          # Symfony form for article editing
├── Grid/
│   └── ArticleGrid.php          # Grid configuration for article listing
├── Menu/
│   └── MenuBuilder.php          # Admin menu configuration
├── Processor/
│   ├── CreateArticleProcessor.php
│   ├── DeleteArticleProcessor.php
│   └── UpdateArticleProcessor.php
├── Provider/
│   ├── ArticleCollectionProvider.php  # For grid/index pages
│   ├── ArticleGridProvider.php        # Grid-specific provider
│   └── ArticleItemProvider.php        # For single item pages
└── Resource/
    └── ArticleResource.php      # Resource definition with metadata
```

## Key Components

### 1. Resource Definition

The `ArticleResource` defines the admin interface configuration:

```php
#[AsResource(
    alias: 'app.article',
    section: 'admin',
    formType: ArticleType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(grid: ArticleGrid::class)]
#[Create(processor: CreateArticleProcessor::class)]
#[Show(provider: ArticleItemProvider::class)]
#[Update(
    provider: ArticleItemProvider::class,
    processor: UpdateArticleProcessor::class,
    redirectToRoute: 'app_admin_article_index',
)]
#[Delete(
    provider: ArticleItemProvider::class,
    processor: DeleteArticleProcessor::class,
)]
final class ArticleResource implements ResourceInterface
```

### 2. Grid Configuration

The `ArticleGrid` configures the listing page:

```php
public function buildGrid(GridBuilder $gridBuilder): void
{
    $gridBuilder
        ->setLimits([20, 50, 100])
        ->orderBy('updatedAt', direction: 'desc')
        ->addField(
            StringField::create('title')
                ->setLabel('Title')
                ->setSortable(true)
        )
        ->addField(
            DateTimeField::create('createdAt')
                ->setLabel('Created')
                ->setSortable(true)
        )
        ->addActionGroup(
            MainActionGroup::create(
                ShowAction::create(),
                UpdateAction::create(),
                DeleteAction::create(),
            )
        );
}
```

### 3. Providers (Read Operations)

Providers bridge between Sylius and our Application layer:

```php
public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
{
    $articleId = $uriVariables['id'] ?? null;
    
    $request = GetArticleRequest::fromData(['id' => $articleId]);
    $response = ($this->getArticleGateway)($request);
    
    return $this->transformToResource($response->data());
}
```

### 4. Processors (Write Operations)

Processors handle form submissions and call our Application Gateways:

```php
public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
{
    $request = CreateArticleRequest::fromData([
        'title' => $data->title,
        'content' => $data->content,
        'slug' => $data->slug,
        'status' => $data->status ?? 'draft',
    ]);

    $response = ($this->createArticleGateway)($request);
    
    return $this->transformToResource($response->data());
}
```

## Configuration

### 1. Bundle Registration

```php
// config/bundles.php
return [
    // ...
    Sylius\Bundle\GridBundle\SyliusGridBundle::class => ['all' => true],
    Sylius\Bundle\ResourceBundle\SyliusResourceBundle::class => ['all' => true],
    winzou\Bundle\StateMachineBundle\winzouStateMachineBundle::class => ['all' => true],
    BabDev\PagerfantaBundle\BabDevPagerfantaBundle::class => ['all' => true],
    Sylius\Bundle\BootstrapAdminUiBundle\SyliusBootstrapAdminUiBundle::class => ['all' => true],
];
```

### 2. Routes Configuration

```php
// config/routes/sylius_admin_ui.php
return static function (RoutingConfigurator $routes): void {
    $routes->import('@SyliusBootstrapAdminUiBundle/config/routing/auth.php', 'sylius_yaml')
        ->prefix('/admin');
};
```

### 3. Menu Configuration

The admin menu is configured in `MenuBuilder`:

```php
public function createMenu(FactoryInterface $factory): ItemInterface
{
    $menu = $factory->createItem('root');

    $menu
        ->addChild('blog', ['route' => 'app_admin_article_index'])
        ->setLabel('Blog')
        ->setLabelAttribute('icon', 'file alternate');

    return $menu;
}
```

## Routes Generated

The Sylius Resource Bundle automatically generates these routes:

- `GET /admin/articles` - List articles (index)
- `GET /admin/articles/new` - Show create form
- `POST /admin/articles/new` - Create article
- `GET /admin/articles/{id}` - Show article
- `GET /admin/articles/{id}/edit` - Show edit form
- `PUT /admin/articles/{id}/edit` - Update article
- `DELETE /admin/articles/{id}` - Delete article

## Integration Points

### 1. Gateway Integration

All operations go through our Application Gateways:
- Providers use Query Gateways (GetArticle, ListArticles)
- Processors use Command Gateways (CreateArticle, UpdateArticle, DeleteArticle)

### 2. Form Handling

The `ArticleType` form class defines field mappings:

```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('title', TextType::class, [
            'label' => 'Title',
            'required' => true,
        ])
        ->add('slug', TextType::class, [
            'label' => 'Slug',
            'required' => true,
        ])
        ->add('content', TextareaType::class, [
            'label' => 'Content',
            'required' => true,
        ])
        ->add('status', ChoiceType::class, [
            'label' => 'Status',
            'choices' => [
                'Draft' => 'draft',
                'Published' => 'published',
                'Archived' => 'archived',
            ],
        ]);
}
```

### 3. Template Customization

While we use default Sylius templates, they can be overridden:

```
templates/
└── @SyliusAdminUi/
    └── crud/
        ├── index.html.twig
        ├── create.html.twig
        ├── update.html.twig
        └── show.html.twig
```

## Benefits

1. **Rapid Development**: Full admin CRUD in ~300 lines of code
2. **Professional UI**: Bootstrap-based responsive design
3. **Feature-Rich**: Sorting, filtering, pagination out of the box
4. **Maintainable**: Clear separation between UI and business logic
5. **Extensible**: Easy to add custom actions, filters, and fields

## Common Patterns

### Adding a New Field to Grid

```php
->addField(
    StringField::create('status')
        ->setLabel('Status')
        ->setSortable(true)
        ->setPath('status')
)
```

### Adding a Filter

```php
->addFilter(
    SelectFilter::create('status')
        ->setLabel('Status')
        ->setChoices([
            'draft' => 'Draft',
            'published' => 'Published',
        ])
)
```

### Custom Actions

```php
->addActionGroup(
    ItemActionGroup::create(
        Action::create('publish', 'publish')
            ->setLabel('Publish')
            ->setIcon('check')
    )
)
```

## Troubleshooting

### Common Issues

1. **Grid not loading**: Check that the GridProvider is properly configured
2. **Forms not saving**: Verify Processor is calling the correct Gateway
3. **Menu not appearing**: Ensure MenuBuilder is tagged as `sylius.menu_builder`
4. **Routing errors**: Check that routes are imported in `config/routes.php`

## Future Enhancements

1. **Batch Operations**: Add bulk publish/unpublish
2. **Advanced Filters**: Date range, author, category
3. **Export**: CSV/Excel export functionality
4. **Media Management**: Integrate image upload
5. **Preview**: Add preview before publishing

## References

- [Sylius Resource Bundle Documentation](https://github.com/Sylius/SyliusResourceBundle)
- [Sylius Grid Bundle Documentation](https://github.com/Sylius/SyliusGridBundle)
- [Sylius Bootstrap Admin UI Bundle](https://github.com/Sylius/SyliusBootstrapAdminUiBundle)