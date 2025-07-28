<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\<?php echo $context; ?>\UI\Api\Rest\Processor\Create<?php echo $entity; ?>Processor;
use App\<?php echo $context; ?>\UI\Api\Rest\Processor\Delete<?php echo $entity; ?>Processor;
use App\<?php echo $context; ?>\UI\Api\Rest\Processor\Update<?php echo $entity; ?>Processor;
use App\<?php echo $context; ?>\UI\Api\Rest\Provider\Get<?php echo $entity; ?>Provider;
use App\<?php echo $context; ?>\UI\Api\Rest\Provider\List<?php echo $entity_plural_pascal; ?>Provider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: '<?php echo $entity; ?>',
    operations: [
        new Get(
            uriTemplate: '/<?php echo $entity_plural_snake; ?>/{id}',
            provider: Get<?php echo $entity; ?>Provider::class,
        ),
        new GetCollection(
            uriTemplate: '/<?php echo $entity_plural_snake; ?>',
            provider: List<?php echo $entity_plural_pascal; ?>Provider::class,
        ),
        new Post(
            uriTemplate: '/<?php echo $entity_plural_snake; ?>',
            processor: Create<?php echo $entity; ?>Processor::class,
        ),
        new Put(
            uriTemplate: '/<?php echo $entity_plural_snake; ?>/{id}',
            provider: Get<?php echo $entity; ?>Provider::class,
            processor: Update<?php echo $entity; ?>Processor::class,
        ),
        new Delete(
            uriTemplate: '/<?php echo $entity_plural_snake; ?>/{id}',
            processor: Delete<?php echo $entity; ?>Processor::class,
        ),
    ],
)]
final class <?php echo $class_name . "\n"; ?>
{
    public ?string $id = null;
    
    // TODO: Add resource properties with validation
    // Example:
    // #[Assert\NotBlank(groups: ['create'])]
    // #[Assert\Length(min: 3, max: 200)]
    // public ?string $title = null;
    
    // #[Assert\NotBlank(groups: ['create'])]
    // public ?string $content = null;
    
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        ?string $id = null,
        // TODO: Add constructor parameters
        // Example:
        // ?string $title = null,
        // ?string $content = null,
        \DateTimeInterface|string|null $createdAt = null,
        \DateTimeInterface|string|null $updatedAt = null,
    ) {
        $this->id = $id;
        // TODO: Assign other properties
        // Example:
        // $this->title = $title;
        // $this->content = $content;

        // Handle DateTime conversion from strings
        if (is_string($createdAt)) {
            try {
                $this->createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                $this->createdAt = null;
            }
        } else {
            $this->createdAt = $createdAt instanceof \DateTimeImmutable
                ? $createdAt
                : ($createdAt instanceof \DateTime ? \DateTimeImmutable::createFromMutable($createdAt) : null);
        }

        if (is_string($updatedAt)) {
            try {
                $this->updatedAt = new \DateTimeImmutable($updatedAt);
            } catch (\Exception) {
                $this->updatedAt = null;
            }
        } else {
            $this->updatedAt = $updatedAt instanceof \DateTimeImmutable
                ? $updatedAt
                : ($updatedAt instanceof \DateTime ? \DateTimeImmutable::createFromMutable($updatedAt) : null);
        }
    }
}
