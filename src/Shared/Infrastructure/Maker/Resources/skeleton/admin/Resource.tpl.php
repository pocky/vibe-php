<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\UI\Web\Admin\Form\<?php echo $entity; ?>Type;
use App\<?php echo $context; ?>\UI\Web\Admin\Grid\<?php echo $entity; ?>Grid;
use App\<?php echo $context; ?>\UI\Web\Admin\Processor\Create<?php echo $entity; ?>Processor;
use App\<?php echo $context; ?>\UI\Web\Admin\Processor\Delete<?php echo $entity; ?>Processor;
use App\<?php echo $context; ?>\UI\Web\Admin\Processor\Update<?php echo $entity; ?>Processor;
use App\<?php echo $context; ?>\UI\Web\Admin\Provider\<?php echo $entity; ?>ItemProvider;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Show;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;

#[AsResource(
    alias: 'app.<?php echo $entity_snake; ?>',
    section: 'admin',
    formType: <?php echo $entity; ?>Type::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(
    grid: <?php echo $entity; ?>Grid::class,
)]
#[Create(
    processor: Create<?php echo $entity; ?>Processor::class,
    redirectToRoute: 'app_admin_<?php echo $entity_snake; ?>_index',
)]
#[Show(
    provider: <?php echo $entity; ?>ItemProvider::class,
)]
#[Update(
    provider: <?php echo $entity; ?>ItemProvider::class,
    processor: Update<?php echo $entity; ?>Processor::class,
    redirectToRoute: 'app_admin_<?php echo $entity_snake; ?>_index',
)]
#[Delete(
    provider: <?php echo $entity; ?>ItemProvider::class,
    processor: Delete<?php echo $entity; ?>Processor::class,
)]
final class <?php echo $class_name; ?> implements ResourceInterface
{
    public function __construct(
        public string|null $id = null,
        // TODO: Add resource properties
        // Example:
        // public string|null $name = null,
        // public string|null $description = null,
        public \DateTimeInterface|null $createdAt = null,
        public \DateTimeInterface|null $updatedAt = null,
    ) {
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
