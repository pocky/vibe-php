<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class <?php echo $class_name; ?> implements GatewayResponse
{
    public function __construct(
        // Add your response properties here
        // Example:
        // public string $id,
        // public string $status,
    ) {
    }

    public function data(): array
    {
        return [
            // Return array representation
            // Example:
            // 'id' => $this->id,
            // 'status' => $this->status,
        ];
    }
}