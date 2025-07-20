<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: '<?php echo $table_name; ?>')]
#[ORM\Index(columns: ['created_at'], name: 'idx_<?php echo $table_name; ?>_created_at')]
class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        private Uuid $id,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private \DateTimeImmutable|null $updatedAt = null
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable|null
    {
        return $this->updatedAt;
    }
}
