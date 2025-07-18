# Admin Resource Template

## Resource Class

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Resource;

use App\[Context]Context\UI\Web\Admin\Form\[Resource]Type;
use App\[Context]Context\UI\Web\Admin\Grid\[Resource]Grid;
use App\[Context]Context\UI\Web\Admin\Processor\Create[Resource]Processor;
use App\[Context]Context\UI\Web\Admin\Processor\Delete[Resource]Processor;
use App\[Context]Context\UI\Web\Admin\Processor\Update[Resource]Processor;
use App\[Context]Context\UI\Web\Admin\Provider\[Resource]ItemProvider;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Show;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;

#[AsResource(
    alias: 'app.[resource]',
    section: 'admin',
    formType: [Resource]Type::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(
    grid: [Resource]Grid::class,
)]
#[Create(
    processor: Create[Resource]Processor::class,
    redirectToRoute: 'app_admin_[resource]_index',
)]
#[Show(
    provider: [Resource]ItemProvider::class,
)]
#[Update(
    provider: [Resource]ItemProvider::class,
    processor: Update[Resource]Processor::class,
    redirectToRoute: 'app_admin_[resource]_index',
)]
#[Delete(
    provider: [Resource]ItemProvider::class,
    processor: Delete[Resource]Processor::class,
)]
final class [Resource]Resource implements ResourceInterface
{
    public function __construct(
        public string|null $id = null,
        public string|null $name = null,
        public string|null $description = null,
        public string|null $slug = null,
        public string|null $status = null,
        public \DateTimeInterface|null $createdAt = null,
        public \DateTimeInterface|null $updatedAt = null,
    ) {
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
```

## Grid Configuration

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Grid;

use App\[Context]Context\UI\Web\Admin\Provider\[Resource]GridProvider;
use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\ShowAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;

final class [Resource]Grid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider([Resource]GridProvider::class)
            ->setLimits([10, 20, 50])
            ->addField(
                StringField::create('name')
                    ->setLabel('app.ui.name')
                    ->setSortable(true)
            )
            ->addField(
                StringField::create('status')
                    ->setLabel('app.ui.status')
                    ->setSortable(true)
            )
            ->addField(
                DateTimeField::create('createdAt')
                    ->setLabel('app.ui.created_at')
                    ->setSortable(true)
            )
            ->addActionGroup(
                MainActionGroup::create(
                    CreateAction::create()
                        ->setLabel('app.ui.create_[resource]')
                        ->setIcon('tabler:plus')
                )
            )
            ->addActionGroup(
                ItemActionGroup::create(
                    ShowAction::create()
                        ->setIcon('tabler:eye'),
                    UpdateAction::create()
                        ->setIcon('tabler:pencil'),
                    DeleteAction::create()
                        ->setIcon('tabler:trash')
                )
            );
    }

    public function getResourceClass(): string
    {
        return [Resource]Resource::class;
    }
}
```

## Form Type

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Form;

use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class [Resource]Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'app.ui.name',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'app.[resource].name.not_blank'),
                    new Assert\Length(
                        min: 2,
                        max: 100,
                        minMessage: 'app.[resource].name.min_length',
                        maxMessage: 'app.[resource].name.max_length',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'app.ui.enter_name',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'app.ui.slug',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'app.[resource].slug.not_blank'),
                    new Assert\Regex(
                        pattern: '/^[a-z0-9\-]+$/',
                        message: 'app.[resource].slug.invalid_format',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'app.ui.enter_slug',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'app.ui.description',
                'required' => false,
                'constraints' => [
                    new Assert\Length(
                        max: 500,
                        maxMessage: 'app.[resource].description.max_length',
                    ),
                ],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'app.ui.enter_description',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'app.ui.status',
                'required' => true,
                'choices' => [
                    'app.ui.active' => 'active',
                    'app.ui.inactive' => 'inactive',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'app.[resource].status.not_blank'),
                    new Assert\Choice(
                        choices: ['active', 'inactive'],
                        message: 'app.[resource].status.invalid_choice',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => [Resource]Resource::class,
            'translation_domain' => 'messages',
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'app_admin_[resource]';
    }
}
```

## Translations Template

```yaml
# translations/messages.en.yaml
app:
    ui:
        [resources]: '[Resources]'
        [resource]: '[Resource]'
        create_[resource]: 'Create [Resource]'
        edit_[resource]: 'Edit [Resource]'
        delete_[resource]: 'Delete [Resource]'
        name: 'Name'
        description: 'Description'
        slug: 'Slug'
        status: 'Status'
        active: 'Active'
        inactive: 'Inactive'
        created_at: 'Created At'
        updated_at: 'Updated At'
        enter_name: 'Enter name'
        enter_slug: 'Enter slug (e.g., my-[resource])'
        enter_description: 'Enter description (optional)'
        actions: 'Actions'
    
    [resource]:
        name:
            not_blank: 'Name cannot be empty.'
            min_length: 'Name must be at least {{ limit }} characters long.'
            max_length: 'Name cannot be longer than {{ limit }} characters.'
        slug:
            not_blank: 'Slug cannot be empty.'
            invalid_format: 'Slug can only contain lowercase letters, numbers, and hyphens.'
        description:
            max_length: 'Description cannot be longer than {{ limit }} characters.'
        status:
            not_blank: 'Status must be selected.'
            invalid_choice: 'Invalid status selected.'
```