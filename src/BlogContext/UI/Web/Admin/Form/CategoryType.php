<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Form;

use App\BlogContext\UI\Web\Admin\Resource\CategoryResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Category name cannot be blank'),
                    new Assert\Length(
                        min: 2,
                        max: 100,
                        minMessage: 'Category name must be at least {{ limit }} characters',
                        maxMessage: 'Category name cannot exceed {{ limit }} characters',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'Enter category name',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Category slug cannot be blank'),
                    new Assert\Regex(
                        pattern: '/^[a-z0-9-]+$/',
                        message: 'Slug must contain only lowercase letters, numbers and hyphens',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'category-slug',
                ],
            ])
            ->add('parentId', ChoiceType::class, [
                'label' => 'Parent Category',
                'required' => false,
                'placeholder' => '-- No Parent (Root Category) --',
                'choices' => [], // Will be populated dynamically
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Optional category description',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryResource::class,
            'translation_domain' => 'messages',
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'app_admin_category';
    }
}
