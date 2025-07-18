---
description: Create Symfony form types for Sylius Admin UI
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), TodoWrite
---

# Admin Form Type Creation

Create Symfony form types for use with Sylius Admin UI resources.

## Usage
`/admin:form [context] [form-name]`

Example: `/admin:form Blog CategoryType`

## Process

1. **Create Form Type Class**
   ```php
   namespace App\[Context]Context\UI\Web\Admin\Form;
   
   use Symfony\Component\Form\AbstractType;
   use Symfony\Component\Form\FormBuilderInterface;
   use Symfony\Component\OptionsResolver\OptionsResolver;
   
   final class [FormName] extends AbstractType
   {
       public function buildForm(FormBuilderInterface $builder, array $options): void
       {
           // Form fields
       }
       
       public function configureOptions(OptionsResolver $resolver): void
       {
           // Form options
       }
       
       public function getBlockPrefix(): string
       {
           return 'app_admin_[resource]';
       }
   }
   ```

2. **Add Form Fields**
   ```php
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
   ```

3. **Field Types**
   - **TextType**: Single line text
   - **TextareaType**: Multi-line text
   - **EmailType**: Email input with validation
   - **UrlType**: URL input with validation
   - **ChoiceType**: Dropdown/radio/checkboxes
   - **CheckboxType**: Single checkbox
   - **DateType**: Date picker
   - **DateTimeType**: Date and time picker
   - **NumberType**: Numeric input
   - **MoneyType**: Currency input
   - **FileType**: File upload
   - **EntityType**: Related entity selection

4. **Common Field Options**
   ```php
   ->add('status', ChoiceType::class, [
       'label' => 'app.ui.status',
       'required' => true,
       'choices' => [
           'app.ui.active' => 'active',
           'app.ui.inactive' => 'inactive',
           'app.ui.archived' => 'archived',
       ],
       'placeholder' => 'app.ui.choose_status',
       'constraints' => [
           new Assert\NotBlank(),
           new Assert\Choice(['active', 'inactive', 'archived']),
       ],
   ])
   ```

5. **Validation Constraints**
   - `Assert\NotBlank`: Field required
   - `Assert\Length`: String length limits
   - `Assert\Email`: Valid email format
   - `Assert\Url`: Valid URL format
   - `Assert\Choice`: Limited to specific values
   - `Assert\Regex`: Pattern matching
   - `Assert\Range`: Numeric range
   - `Assert\GreaterThan`: Minimum value
   - `Assert\LessThan`: Maximum value
   - `Assert\Date`: Valid date
   - `Assert\DateTime`: Valid datetime

6. **Hierarchical Forms**
   ```php
   ->add('parent', EntityType::class, [
       'label' => 'app.ui.parent_category',
       'class' => Category::class,
       'choice_label' => 'name',
       'placeholder' => 'app.ui.no_parent',
       'required' => false,
       'query_builder' => function (EntityRepository $er) {
           return $er->createQueryBuilder('c')
               ->where('c.level < :maxLevel')
               ->setParameter('maxLevel', 2)
               ->orderBy('c.name', 'ASC');
       },
   ])
   ```

7. **Dynamic Forms**
   ```php
   public function buildForm(FormBuilderInterface $builder, array $options): void
   {
       $builder->addEventListener(
           FormEvents::PRE_SET_DATA,
           function (FormEvent $event) {
               $data = $event->getData();
               $form = $event->getForm();
               
               if ($data && $data->hasChildren()) {
                   $form->add('moveChildren', CheckboxType::class, [
                       'label' => 'app.ui.move_children_to_parent',
                       'required' => false,
                   ]);
               }
           }
       );
   }
   ```

8. **Form Options**
   ```php
   public function configureOptions(OptionsResolver $resolver): void
   {
       $resolver->setDefaults([
           'data_class' => [Resource]Resource::class,
           'translation_domain' => 'messages',
           'validation_groups' => ['Default', 'admin'],
       ]);
   }
   ```

## Advanced Features

### Custom Form Types
```php
->add('description', RichTextType::class, [
    'label' => 'app.ui.description',
    'config' => [
        'toolbar' => 'basic',
        'height' => 200,
    ],
])
```

### Collection Forms
```php
->add('tags', CollectionType::class, [
    'entry_type' => TagType::class,
    'allow_add' => true,
    'allow_delete' => true,
    'by_reference' => false,
])
```

### Conditional Fields
```php
->add('publishedAt', DateTimeType::class, [
    'label' => 'app.ui.published_at',
    'required' => false,
    'widget' => 'single_text',
    'attr' => [
        'class' => 'js-published-at',
        'data-show-when' => 'status:published',
    ],
])
```

## Best Practices
- Always use translation keys for labels
- Add meaningful validation messages
- Use placeholders for better UX
- Group related fields together
- Add help text for complex fields
- Use appropriate HTML5 input types

## Next Steps
1. Register form with resource
2. Add all translation keys
3. Test form validation
4. Style custom fields if needed