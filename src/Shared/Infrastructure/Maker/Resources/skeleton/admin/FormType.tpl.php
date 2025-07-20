<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\UI\Web\Admin\Resource\<?php echo $entity; ?>Resource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class <?php echo $class_name; ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // TODO: Add your form fields here
            // Example:
            // ->add('name', TextType::class, [
            //     'label' => 'app.ui.name',
            //     'required' => true,
            //     'constraints' => [
            //         new Assert\NotBlank(message: 'app.<?php echo $entity_snake; ?>.name.not_blank'),
            //         new Assert\Length(
            //             min: 3,
            //             max: 100,
            //             minMessage: 'app.<?php echo $entity_snake; ?>.name.min_length',
            //             maxMessage: 'app.<?php echo $entity_snake; ?>.name.max_length',
            //         ),
            //     ],
            //     'attr' => [
            //         'placeholder' => 'app.ui.enter_name',
            //     ],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => <?php echo $entity; ?>Resource::class,
            'translation_domain' => 'messages',
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'app_admin_<?php echo $entity_snake; ?>';
    }
}
