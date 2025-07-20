# Sylius Admin UI Integration

## Overview

This document describes the integration of Sylius Admin UI components into the Vibe PHP project, providing a professional admin interface for managing blog articles while maintaining our DDD/Hexagonal architecture.

## Architecture Decision

### Why Sylius Admin UI?

1. **Professional UI**: Pre-built, tested admin components with Bootstrap 5
2. **Resource Management**: Powerful CRUD operations with minimal code
3. **Grid System**: Advanced filtering, sorting, and pagination with Pagerfanta
4. **Form Integration**: Seamless Symfony Forms integration with validation
5. **Extensibility**: Easy to customize and extend with custom actions
6. **Translation Support**: Built-in ICU MessageFormat translations
7. **Tabler Icons**: Modern icon set for better UX

### Integration Approach

We integrated Sylius Admin UI as a UI layer component, maintaining separation between:
- **UI Layer**: Sylius components (Resources, Grids, Forms, Menu)
- **Application Layer**: Our Gateways and CQRS handlers
- **Domain Layer**: Pure business logic

This approach ensures that Sylius is purely a presentation concern and doesn't contaminate our domain model.

## Implementation Structure

### Directory Organization

```
src/BlogContext/UI/Web/Admin/
├── Form/
│   ├── ArticleType.php          # Main article form with validation
│   ├── ApproveArticleType.php  # Editorial approval form
│   └── RejectArticleType.php   # Editorial rejection form
├── Grid/
│   ├── ArticleGrid.php          # Standard article listing grid
│   └── EditorialArticleGrid.php # Editorial review grid with custom actions
├── Menu/
│   └── MenuBuilder.php          # Admin menu configuration (decorator pattern)
├── Processor/
│   ├── CreateArticleProcessor.php    # Handles article creation
│   ├── DeleteArticleProcessor.php    # Handles article deletion
│   ├── UpdateArticleProcessor.php    # Handles article updates
│   ├── ApproveArticleProcessor.php   # Editorial approval workflow
│   └── RejectArticleProcessor.php    # Editorial rejection workflow
├── Provider/
│   ├── ArticleCollectionProvider.php      # Deprecated - use GridProvider
│   ├── ArticleGridProvider.php            # Provides data for article grid
│   ├── ArticleItemProvider.php            # Provides single article data
│   ├── EditorialArticleGridProvider.php   # Editorial review grid data
│   └── EditorialArticleItemProvider.php   # Editorial article details
└── Resource/
    ├── ArticleResource.php          # Main article resource definition
    └── EditorialArticleResource.php # Editorial workflow resource
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
#[Index(
    grid: ArticleGrid::class,
)]
#[Create(
    processor: CreateArticleProcessor::class,
    redirectToRoute: 'app_admin_article_index',
)]
#[Show(
    provider: ArticleItemProvider::class,
)]
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
{
    public function __construct(
        public string|null $id = null,
        public string|null $title = null,
        public string|null $content = null,
        public string|null $slug = null,
        public string|null $status = null,
        public \DateTimeInterface|null $createdAt = null,
        public \DateTimeInterface|null $updatedAt = null,
        public \DateTimeInterface|null $publishedAt = null,
    ) {
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
```

**Key Points**:
- Implements `ResourceInterface` for Sylius compatibility
- Uses PHP 8.4 property promotion
- All properties are nullable to support partial updates
- `getId()` method required by ResourceInterface

### 2. Grid Configuration

The `ArticleGrid` configures the listing page:

```php
final class ArticleGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(ArticleGridProvider::class)
            ->setLimits([10, 20, 50])
            ->addField(StringField::create('title'))
            ->addField(StringField::create('status'))
            ->addField(DateTimeField::create('createdAt'))
            ->addActionGroup(
                MainActionGroup::create(
                    CreateAction::create()
                )
            )
            ->addActionGroup(
                ItemActionGroup::create(
                    UpdateAction::create(),
                    DeleteAction::create()
                )
            );
    }

    public function getResourceClass(): string
    {
        return ArticleResource::class;
    }
}
```

**Editorial Grid with Custom Actions**:

```php
->addActionGroup(
    ItemActionGroup::create(
        Action::create('review', 'show')
            ->setLabel('Review')
            ->setIcon('tabler:eye'),
        Action::create('approve', 'update')
            ->setLabel('Approve')
            ->setIcon('tabler:check')
            ->setOptions([
                'link' => [
                    'route' => 'app_admin_editorial_update',
                    'parameters' => [
                        'id' => 'resource.id',
                        'name' => 'approve',
                    ],
                ],
            ]),
        Action::create('reject', 'update')
            ->setLabel('Reject')
            ->setIcon('tabler:x')
            ->setOptions([/* similar config */])
    )
)
```

### 3. Providers (Read Operations)

Providers bridge between Sylius and our Application layer:

#### Grid Provider Implementation

```php
final readonly class ArticleGridProvider implements DataProviderInterface
{
    public function __construct(
        private ListArticlesGateway $listArticlesGateway,
    ) {
    }

    public function getData(Grid $grid, Parameters $parameters): Pagerfanta
    {
        // Get current page and items per page from grid parameters
        $page = max(1, (int) $parameters->get('page', 1));
        $itemsPerPage = max(1, (int) $parameters->get('limit', 10));

        // Get criteria from parameters (for filtering)
        $criteria = $parameters->get('criteria', []);

        // Create gateway request
        $gatewayRequest = ListArticlesRequest::fromData([
            'page' => $page,
            'limit' => $itemsPerPage,
            // Add any filter criteria here if needed
        ]);

        // Execute gateway
        $gatewayResponse = ($this->listArticlesGateway)($gatewayRequest);
        $responseData = $gatewayResponse->data();

        // Transform response to ArticleResource objects
        $articles = [];
        if (isset($responseData['articles']) && is_array($responseData['articles'])) {
            foreach ($responseData['articles'] as $articleData) {
                if (is_array($articleData)) {
                    $articles[] = $this->transformToResource($articleData);
                }
            }
        }

        // Get total count from response
        $totalCount = $responseData['total'] ?? count($articles);

        // Create a FixedAdapter with the pre-paginated data
        $adapter = new FixedAdapter($totalCount, $articles);
        $pagerfanta = new Pagerfanta($adapter);

        // Set current page and max per page
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($itemsPerPage);

        return $pagerfanta;
    }

    private function transformToResource(array $data): ArticleResource
    {
        return new ArticleResource(
            id: $data['id'] ?? null,
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            slug: $data['slug'] ?? null,
            status: $data['status'] ?? null,
            createdAt: isset($data['created_at']) && $data['created_at']
                ? new \DateTimeImmutable($data['created_at'])
                : null,
            updatedAt: isset($data['updated_at']) && $data['updated_at']
                ? new \DateTimeImmutable($data['updated_at'])
                : null,
            publishedAt: isset($data['published_at']) && $data['published_at']
                ? new \DateTimeImmutable($data['published_at'])
                : null,
        );
    }
}
```

#### Item Provider Implementation

```php
public function provide(Operation $operation, array|object $context): ?object
{
    $articleId = $context['id'] ?? null;
    
    try {
        $request = GetArticleRequest::fromData(['id' => $articleId]);
        $response = ($this->getArticleGateway)($request);
        
        return $this->transformToResource($response->data());
    } catch (GatewayException $e) {
        if (str_contains($e->getMessage(), 'not found')) {
            return null;
        }
        throw $e;
    }
}
```

### 4. Processors (Write Operations)

Processors handle form submissions and call our Application Gateways:

```php
final readonly class CreateArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateArticleGateway $createArticleGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var ArticleResource $data */
        if (!$data instanceof ArticleResource) {
            throw new \InvalidArgumentException('Expected ArticleResource');
        }

        try {
            $gatewayRequest = CreateArticleRequest::fromData([
                'title' => $data->title,
                'content' => $data->content,
                'slug' => $data->slug,
                'status' => $data->status ?? 'draft',
                'createdAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]);

            $gatewayResponse = ($this->createArticleGateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource with generated data
            return new ArticleResource(
                id: $responseData['articleId'],
                title: $data->title,
                content: $data->content,
                slug: $responseData['slug'] ?? $data->slug,
                status: $responseData['status'] ?? $data->status,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
                publishedAt: null,
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (ArticleAlreadyExists $e) {
            throw new \RuntimeException('Article with this slug already exists', 409, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new \RuntimeException('Article with this slug already exists', 409, $e);
            }
            throw $e;
        }
    }
}
```

**Key Points**:
- Type check the incoming data
- Transform to Gateway request format
- Handle domain exceptions with appropriate HTTP codes
- Return updated resource with all fields populated

## Configuration

### 1. Bundle Registration

```php
// config/bundles.php
return [
    // ...
    Sylius\Bundle\GridBundle\SyliusGridBundle::class => ['all' => true],
    Sylius\Bundle\ResourceBundle\SyliusResourceBundle::class => ['all' => true],
    Sylius\AdminUi\Symfony\SyliusAdminUiBundle::class => ['all' => true],
    Sylius\TwigHooks\SyliusTwigHooksBundle::class => ['all' => true],
    Symfony\UX\Autocomplete\AutocompleteBundle::class => ['all' => true],
    Symfony\UX\Icons\UXIconsBundle::class => ['all' => true],
    Symfony\UX\LiveComponent\LiveComponentBundle::class => ['all' => true],
    Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
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

The admin menu is configured using a decorator pattern:

```php
#[AsDecorator(decorates: 'sylius_admin_ui.knp.menu_builder')]
final readonly class MenuBuilder implements MenuBuilderInterface
{
    public function __construct(
        private FactoryInterface $factory,
    ) {
    }

    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu
            ->addChild('dashboard', [
                'route' => 'sylius_admin_ui_dashboard',
            ])
            ->setLabel('sylius.ui.dashboard')
            ->setLabelAttribute('icon', 'tabler:dashboard')
        ;

        $this->addContentSubMenu($menu);

        return $menu;
    }

    private function addContentSubMenu(ItemInterface $menu): void
    {
        $content = $menu
            ->addChild('content')
            ->setLabel('app.ui.content')
            ->setLabelAttribute('icon', 'tabler:file-text')
        ;

        $content->addChild('articles', [
            'route' => 'app_admin_article_index',
        ])
            ->setLabel('app.ui.articles')
            ->setLabelAttribute('icon', 'tabler:article')
        ;

        $content->addChild('editorial', [
            'route' => 'app_admin_editorial_index',
        ])
            ->setLabel('app.ui.editorial_articles')
            ->setLabelAttribute('icon', 'tabler:article')
        ;
    }
}
```

**Key Points**:
- Uses `#[AsDecorator]` to replace default menu builder
- Supports nested menu structure
- Uses Tabler icons (prefix: `tabler:`)
- Translation keys for labels

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

The `ArticleType` form class defines field mappings with validation:

```php
final class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'app.ui.title',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'app.article.title.not_blank'),
                    new Assert\Length(
                        min: 3,
                        max: 200,
                        minMessage: 'app.article.title.min_length',
                        maxMessage: 'app.article.title.max_length',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'app.ui.enter_title',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'app.ui.slug',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'app.article.slug.not_blank'),
                    new Assert\Regex(
                        pattern: '/^[a-z0-9\-]+$/',
                        message: 'app.article.slug.invalid_format',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'app.ui.enter_slug',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'app.ui.content',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'app.article.content.not_blank'),
                    new Assert\Length(
                        min: 10,
                        minMessage: 'app.article.content.min_length',
                    ),
                ],
                'attr' => [
                    'rows' => 15,
                    'placeholder' => 'app.ui.enter_content',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'app.ui.status',
                'required' => true,
                'choices' => [
                    'app.ui.draft' => 'draft',
                    'app.ui.published' => 'published',
                    'app.ui.archived' => 'archived',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'app.article.status.not_blank'),
                    new Assert\Choice(
                        choices: ['draft', 'published', 'archived'],
                        message: 'app.article.status.invalid_choice',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticleResource::class,
            'translation_domain' => 'messages',
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'app_admin_article';
    }
}
```

**Key Features**:
- Translation keys for all labels and messages
- Comprehensive validation with constraints
- Placeholders for better UX
- Custom block prefix for form theming

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
        ->setLabel('app.ui.status')
        ->setSortable(true)
)
->addField(
    DateTimeField::create('publishedAt')
        ->setLabel('app.ui.published_at')
        ->setSortable(true)
)
```

### Adding a Filter

```php
->addFilter(
    StringFilter::create('status', ['pending_review'])
)
->addFilter(
    SelectFilter::create('status')
        ->setLabel('app.ui.status')
        ->setChoices([
            'draft' => 'app.ui.draft',
            'published' => 'app.ui.published',
        ])
)
```

### Custom Actions with Routes

```php
->addActionGroup(
    ItemActionGroup::create(
        Action::create('approve', 'update')
            ->setLabel('app.ui.approve')
            ->setIcon('tabler:check')
            ->setOptions([
                'link' => [
                    'route' => 'app_admin_editorial_update',
                    'parameters' => [
                        'id' => 'resource.id',
                        'name' => 'approve',
                    ],
                ],
            ]),
        Action::create('reject', 'update')
            ->setLabel('app.ui.reject')
            ->setIcon('tabler:x')
            ->setOptions([
                'link' => [
                    'route' => 'app_admin_editorial_update',
                    'parameters' => [
                        'id' => 'resource.id',
                        'name' => 'reject',
                    ],
                ],
            ])
    )
)
```

### Handling Different Form Types in Processors

```php
public function process(mixed $data, Operation $operation, Context $context): mixed
{
    $name = $context->getContext()['form_name'] ?? null;
    
    if ($name === 'approve') {
        // Handle approval logic
        $request = ApproveArticleRequest::fromData([
            'articleId' => $data->id,
            'approvedBy' => 'editor',
        ]);
        ($this->approveArticleGateway)($request);
    } elseif ($name === 'reject') {
        // Handle rejection logic
        $request = RejectArticleRequest::fromData([
            'articleId' => $data->id,
            'rejectedBy' => 'editor',
            'reason' => $data->reason,
        ]);
        ($this->rejectArticleGateway)($request);
    }
    
    return $data;
}
```

## Troubleshooting

### Common Issues

1. **Grid not loading**: 
   - Check that the GridProvider is properly configured
   - Verify the provider is returning a `Pagerfanta` instance
   - Check that the grid name matches the class name

2. **Forms not saving**: 
   - Verify Processor is calling the correct Gateway
   - Check form data_class matches the resource class
   - Ensure validation constraints are satisfied

3. **Menu not appearing**: 
   - Ensure MenuBuilder uses `#[AsDecorator]` attribute
   - Check that the decorator service is properly tagged
   - Verify route names are correct

4. **Routing errors**: 
   - Check that resources are registered in `sylius_resource.php`
   - Verify the resource path is included in mapping paths
   - Run `bin/console debug:router | grep admin` to see generated routes

5. **Translation keys not working**:
   - Ensure translation files exist in `translations/`
   - Check that translation_domain is set in forms
   - Clear cache after adding new translations

6. **Custom actions not working**:
   - Verify the route exists and accepts the correct parameters
   - Check that the action type matches the operation
   - Ensure the processor handles the custom form name

## Future Enhancements

1. **Batch Operations**: Add bulk publish/unpublish
   - Note: Bulk actions temporarily disabled due to template compatibility
2. **Advanced Filters**: Date range, author, category
3. **Export**: CSV/Excel export functionality
4. **Media Management**: Integrate image upload
5. **Preview**: Add preview before publishing
6. **Rich Text Editor**: Integrate CKEditor or TinyMCE
7. **Revision History**: Track all changes with diff view
8. **Workflow Engine**: Complex approval workflows
9. **Permissions**: Role-based access control per action
10. **API Integration**: Expose admin operations via API

## References

- [Sylius Resource Bundle Documentation](https://github.com/Sylius/SyliusResourceBundle)
- [Sylius Grid Bundle Documentation](https://github.com/Sylius/SyliusGridBundle)
- [Sylius Bootstrap Admin UI Bundle](https://github.com/Sylius/SyliusBootstrapAdminUiBundle)