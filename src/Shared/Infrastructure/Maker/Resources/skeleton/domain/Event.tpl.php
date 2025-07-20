<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;

final readonly class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        private <?php echo $entity; ?>Id $<?php echo $entity_snake; ?>Id,
        // TODO: Add other event data
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public function <?php echo $entity_snake; ?>Id(): <?php echo $entity; ?>Id
    {
        return $this-><?php echo $entity_snake; ?>Id;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function eventType(): string
    {
        return '<?php echo str_replace('Context', '', $context); ?>.<?php echo $entity; ?>.<?php echo $event_action; ?>';
    }

    public function aggregateId(): string
    {
        return $this-><?php echo $entity_snake; ?>Id->getValue();
    }

    public function toArray(): array
    {
        return [
            '<?php echo $entity_snake; ?>Id' => $this-><?php echo $entity_snake; ?>Id->getValue(),
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
