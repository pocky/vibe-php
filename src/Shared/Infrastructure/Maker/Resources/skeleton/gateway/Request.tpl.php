<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class <?php echo $class_name; ?> implements GatewayRequest
{
    public function __construct(
        // Add your request properties here
        // Example:
        // public string $title,
        // public string $content,
    ) {
        // Add validation if needed
    }

    public static function fromData(array $data): self
    {
        return new self(
            // Map array data to constructor parameters
            // Example:
            // title: $data['title'] ?? '',
            // content: $data['content'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            // Return array representation
            // Example:
            // 'title' => $this->title,
            // 'content' => $this->content,
        ];
    }
}