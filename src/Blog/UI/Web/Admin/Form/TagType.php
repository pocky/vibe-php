<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Form;

use App\Blog\UI\Web\Admin\Resource\TagResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('name', TextType::class, [
                'label' => 'app.ui.name',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'app.tag.name.not_blank'),
                    new Assert\Length(
                        min: 1,
                        max: 50,
                        minMessage: 'app.tag.name.min_length',
                        maxMessage: 'app.tag.name.max_length',
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s\-_]+$/',
                        message: 'app.tag.name.invalid_characters',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'app.ui.enter_tag_name',
                    'autofocus' => true,
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'app.ui.slug',
                'required' => false,
                'constraints' => [
                    new Assert\Length(
                        max: 100,
                        maxMessage: 'app.tag.slug.max_length',
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-z0-9\-]+$/',
                        message: 'app.tag.slug.invalid_format',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'app.ui.leave_empty_to_generate',
                ],
                'help' => 'app.ui.slug_help',
            ]);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'data_class' => TagResource::class,
            'translation_domain' => 'messages',
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'app_admin_tag';
    }
}
