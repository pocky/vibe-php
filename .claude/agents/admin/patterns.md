# Admin Agent Patterns

## Resource Patterns

### Admin Resource Pattern
```php
namespace App\{Context}Context\UI\Web\Admin\Resource;

use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Show;
use Sylius\Resource\Model\ResourceInterface;

#[AsResource(
    alias: 'app.{entity}',
    section: 'admin',
    formType: {Entity}Type::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(
    grid: {Entity}Grid::class
)]
#[Create(
    processor: Create{Entity}Processor::class,
    redirectRoute: 'app_admin_{entity}_index',
)]
#[Show(
    provider: {Entity}ItemProvider::class
)]
#[Update(
    provider: {Entity}ItemProvider::class,
    processor: Update{Entity}Processor::class,
    redirectRoute: 'app_admin_{entity}_index',
)]
#[Delete(
    provider: {Entity}ItemProvider::class,
    processor: Delete{Entity}Processor::class,
)]
final class {Entity}Resource implements ResourceInterface
{
    public function __construct(
        private ?string $id = null,
        // Resource properties
    ) {}

    public function getId(): ?string
    {
        return $this->id;
    }

    // Getters and setters
}
```

### Grid Configuration Pattern
```php
namespace App\{Context}Context\UI\Web\Admin\Grid;

use Sylius\Resource\Grid\Metadata\Resource\Grid;
use Sylius\Resource\Grid\Metadata\Resource\Field;
use Sylius\Resource\Grid\Metadata\Resource\Action;
use Sylius\Resource\Grid\Metadata\Resource\ActionGroup;
use Sylius\Resource\Grid\Metadata\Resource\Filter;

final class {Entity}Grid extends Grid
{
    public function __construct()
    {
        parent::__construct(
            resource: {Entity}Resource::class,
            provider: {Entity}GridProvider::class,
        );

        $this->addField(
            Field::create('id', 'sylius.ui.id')
                ->sortable()
        );

        $this->addField(
            Field::create('name', 'sylius.ui.name')
                ->sortable()
        );

        $this->addField(
            Field::create('status', 'sylius.ui.status')
                ->sortable()
                ->template('@SyliusAdminUi/grid/field/label.html.twig')
        );

        $this->addField(
            Field::create('createdAt', 'sylius.ui.created_at')
                ->sortable()
                ->template('@SyliusAdminUi/grid/field/datetime.html.twig')
        );

        $this->addActionGroup(
            ActionGroup::create('item', 'sylius.ui.actions')
                ->addAction(Action::create('show', 'sylius.ui.show'))
                ->addAction(Action::create('update', 'sylius.ui.edit'))
                ->addAction(Action::create('delete', 'sylius.ui.delete'))
        );

        $this->addActionGroup(
            ActionGroup::create('main', 'sylius.ui.actions')
                ->addAction(Action::create('create', 'sylius.ui.create'))
        );

        $this->addFilter(
            Filter::create('search', 'sylius.ui.search')
                ->formType(SearchFilterType::class)
        );
    }
}
```

### Form Type Pattern
```php
namespace App\{Context}Context\UI\Web\Admin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class {Entity}Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'app.form.{entity}.name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 3, 'max' => 255]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'app.form.{entity}.description',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'app.form.{entity}.status',
                'choices' => [
                    'app.form.{entity}.status.active' => 'active',
                    'app.form.{entity}.status.inactive' => 'inactive',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => {Entity}Resource::class,
            'validation_groups' => ['Default', 'app_{entity}'],
        ]);
    }
}
```

### Provider Pattern (Grid)
```php
namespace App\{Context}Context\UI\Web\Admin\Provider;

use App\{Context}Context\Application\Gateway\List{Entities}\Gateway;
use App\{Context}Context\Application\Gateway\List{Entities}\Request;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Resource\Grid\Provider\GridProviderInterface;
use Sylius\Resource\Grid\View\ResourceGridView;

final readonly class {Entity}GridProvider implements GridProviderInterface
{
    public function __construct(
        private List{Entities}Gateway $listGateway,
    ) {}

    public function provide(array $parameters = []): ResourceGridView
    {
        $page = $parameters['page'] ?? 1;
        $limit = $parameters['limit'] ?? 10;
        $sorting = $parameters['sorting'] ?? [];
        $filtering = $parameters['criteria'] ?? [];

        $request = Request::fromData([
            'page' => $page,
            'limit' => $limit,
            'sort' => $sorting,
            'filters' => $filtering,
        ]);

        $response = ($this->listGateway)($request);
        $data = $response->data();

        $resources = array_map(
            fn (array $item) => $this->transformToResource($item),
            $data['{entities}'] ?? []
        );

        $pagerfanta = new Pagerfanta(new ArrayAdapter($resources));
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return new ResourceGridView($pagerfanta, $parameters);
    }

    private function transformToResource(array $data): {Entity}Resource
    {
        return new {Entity}Resource(
            id: $data['id'],
            // Map properties
        );
    }
}
```

### Provider Pattern (Item)
```php
namespace App\{Context}Context\UI\Web\Admin\Provider;

use App\{Context}Context\Application\Gateway\Get{Entity}\Gateway;
use App\{Context}Context\Application\Gateway\Get{Entity}\Request;
use Sylius\Resource\State\ProviderInterface;

final readonly class {Entity}ItemProvider implements ProviderInterface
{
    public function __construct(
        private Get{Entity}Gateway $getGateway,
    ) {}

    public function provide(mixed $resource, array $context = []): ?object
    {
        $id = $context['id'] ?? null;
        if (!$id) {
            return null;
        }

        try {
            $request = Request::fromData(['id' => $id]);
            $response = ($this->getGateway)($request);
            
            $data = $response->data();
            return $this->transformToResource($data['{entity}']);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function transformToResource(array $data): {Entity}Resource
    {
        return new {Entity}Resource(
            id: $data['id'],
            // Map properties
        );
    }
}
```

### Processor Pattern (Create)
```php
namespace App\{Context}Context\UI\Web\Admin\Processor;

use App\{Context}Context\Application\Gateway\Create{Entity}\Gateway;
use App\{Context}Context\Application\Gateway\Create{Entity}\Request;
use Sylius\Resource\State\ProcessorInterface;

final readonly class Create{Entity}Processor implements ProcessorInterface
{
    public function __construct(
        private Create{Entity}Gateway $createGateway,
    ) {}

    public function process(mixed $resource, mixed $data = null, array $context = []): mixed
    {
        /** @var {Entity}Resource $resource */
        try {
            $request = Request::fromData([
                // Map resource to request
            ]);

            $response = ($this->createGateway)($request);
            
            return $resource;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create {entity}: ' . $e->getMessage());
        }
    }
}
```

### Menu Builder Pattern
```php
namespace App\{Context}Context\UI\Web\Admin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\AdminUi\Menu\MenuBuilderInterface;

final class {Entity}MenuBuilder implements MenuBuilderInterface
{
    public function build(ItemInterface $menu): void
    {
        $catalogMenu = $menu->getChild('catalog') ?? $menu->addChild('catalog', [
            'route' => 'app_admin_dashboard',
        ])->setLabel('sylius.ui.catalog');

        $catalogMenu
            ->addChild('{entities}', [
                'route' => 'app_admin_{entity}_index',
            ])
            ->setLabel('app.ui.{entities}')
            ->setLabelAttribute('icon', 'tags');
    }
}
```

## Template Patterns

### Custom Grid Field Template
```twig
{# templates/admin/grid/field/status.html.twig #}
{% set value = row[field.name] %}

{% if value == 'active' %}
    <span class="ui green label">{{ 'app.ui.active'|trans }}</span>
{% elseif value == 'inactive' %}
    <span class="ui red label">{{ 'app.ui.inactive'|trans }}</span>
{% else %}
    <span class="ui label">{{ value }}</span>
{% endif %}
```

### Custom Form Theme
```twig
{# templates/admin/form/{entity}_theme.html.twig #}
{% extends '@SyliusAdminUi/form/theme.html.twig' %}

{% block _{entity}_type_widget %}
    <div class="ui segment">
        {{ form_row(form.name) }}
        {{ form_row(form.description) }}
        {{ form_row(form.status) }}
    </div>
{% endblock %}
```

## Translation Patterns

### Messages Translation
```yaml
# translations/messages.en.yaml
app:
    ui:
        {entities}: {Entities}
        {entity}: {Entity}
        create_{entity}: Create {entity}
        edit_{entity}: Edit {entity}
        show_{entity}: {Entity} details
    form:
        {entity}:
            name: Name
            description: Description
            status: Status
            status.active: Active
            status.inactive: Inactive
    flash:
        {entity}:
            created: {Entity} has been created successfully.
            updated: {Entity} has been updated successfully.
            deleted: {Entity} has been deleted successfully.
```

## Security Patterns

### Admin Voter
```php
namespace App\{Context}Context\UI\Web\Admin\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class {Entity}Voter extends Voter
{
    private const VIEW = 'app.{entity}.view';
    private const CREATE = 'app.{entity}.create';
    private const UPDATE = 'app.{entity}.update';
    private const DELETE = 'app.{entity}.delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, 'app.{entity}.');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        return match($attribute) {
            self::VIEW => true, // All authenticated users can view
            self::CREATE, self::UPDATE => $user->hasRole('ROLE_ADMIN'),
            self::DELETE => $user->hasRole('ROLE_SUPER_ADMIN'),
            default => false,
        };
    }
}
```