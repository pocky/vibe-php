<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Form;

use App\BlogContext\UI\Web\Admin\Resource\ArticleResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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
