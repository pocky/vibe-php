# Practical Examples: Specification Pattern

This document presents concrete examples of using the Specification Pattern in different business contexts.

## Complete Example: Order Management System

### 1. Domain Structure

```
src/OrderContext/
├── Domain/
│   ├── Order.php
│   ├── OrderStatus.php
│   ├── ValueObject/
│   │   ├── OrderId.php
│   │   ├── Amount.php
│   │   └── CustomerId.php
│   └── Specification/
│       ├── CanBeCancelledSpecification.php
│       ├── CanBeRefundedSpecification.php
│       ├── IsEligibleForDiscountSpecification.php
│       └── RequiresManagerApprovalSpecification.php
└── Application/
    ├── UseCase/
    │   ├── CancelOrderUseCase.php
    │   └── ApplyDiscountUseCase.php
    └── Service/
        └── OrderValidationService.php
```

### 2. Order Entity

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain;

use App\OrderContext\Domain\ValueObject\{OrderId, Amount, CustomerId};

final readonly class Order
{
    public function __construct(
        private OrderId $id,
        private CustomerId $customerId,
        private Amount $amount,
        private OrderStatus $status,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $shippedAt = null,
        private ?\DateTimeImmutable $deliveredAt = null,
        private bool $isPriority = false,
    ) {}

    public static function create(
        CustomerId $customerId,
        Amount $amount,
        bool $isPriority = false,
    ): self {
        return new self(
            id: OrderId::generate(),
            customerId: $customerId,
            amount: $amount,
            status: OrderStatus::PENDING,
            createdAt: new \DateTimeImmutable(),
            isPriority: $isPriority,
        );
    }

    public function ship(): self
    {
        if ($this->status !== OrderStatus::PENDING) {
            throw new \DomainException('Only pending orders can be shipped');
        }

        return new self(
            id: $this->id,
            customerId: $this->customerId,
            amount: $this->amount,
            status: OrderStatus::SHIPPED,
            createdAt: $this->createdAt,
            shippedAt: new \DateTimeImmutable(),
            deliveredAt: $this->deliveredAt,
            isPriority: $this->isPriority,
        );
    }

    public function deliver(): self
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            throw new \DomainException('Only shipped orders can be delivered');
        }

        return new self(
            id: $this->id,
            customerId: $this->customerId,
            amount: $this->amount,
            status: OrderStatus::DELIVERED,
            createdAt: $this->createdAt,
            shippedAt: $this->shippedAt,
            deliveredAt: new \DateTimeImmutable(),
            isPriority: $this->isPriority,
        );
    }

    public function cancel(): self
    {
        return new self(
            id: $this->id,
            customerId: $this->customerId,
            amount: $this->amount,
            status: OrderStatus::CANCELLED,
            createdAt: $this->createdAt,
            shippedAt: $this->shippedAt,
            deliveredAt: $this->deliveredAt,
            isPriority: $this->isPriority,
        );
    }

    // Getters
    public function id(): OrderId { return $this->id; }
    public function customerId(): CustomerId { return $this->customerId; }
    public function amount(): Amount { return $this->amount; }
    public function status(): OrderStatus { return $this->status; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function shippedAt(): ?\DateTimeImmutable { return $this->shippedAt; }
    public function deliveredAt(): ?\DateTimeImmutable { return $this->deliveredAt; }
    public function isPriority(): bool { return $this->isPriority; }

    public function daysSinceCreation(): int
    {
        return $this->createdAt->diff(new \DateTimeImmutable())->days;
    }

    public function isDeliveredWithinDays(int $days): bool
    {
        if ($this->deliveredAt === null) {
            return false;
        }

        return $this->createdAt->diff($this->deliveredAt)->days <= $days;
    }
}
```

### 3. OrderStatus Enum

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => in_array($newStatus, [self::DELIVERED, self::CANCELLED]),
            self::DELIVERED => $newStatus === self::REFUNDED,
            self::CANCELLED => false,
            self::REFUNDED => false,
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::SHIPPED]);
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED, self::REFUNDED]);
    }
}
```

### 4. Business Specifications

#### CanBeCancelledSpecification

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain\Specification;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\{Order, OrderStatus};

final readonly class CanBeCancelledSpecification implements Specification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Order) {
            return false;
        }

        // Règles métier : une commande peut être annulée si :
        // 1. Elle n'est pas encore livrée
        // 2. Elle n'est pas déjà annulée ou remboursée
        return !in_array($candidate->status(), [
            OrderStatus::DELIVERED,
            OrderStatus::CANCELLED,
            OrderStatus::REFUNDED,
        ]);
    }
}
```

#### CanBeRefundedSpecification

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain\Specification;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\{Order, OrderStatus};

final readonly class CanBeRefundedSpecification implements Specification
{
    public function __construct(
        private int $refundPeriodInDays = 30,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Order) {
            return false;
        }

        // Règles métier : une commande peut être remboursée si :
        // 1. Elle est livrée
        // 2. Elle a été livrée dans les X jours
        // 3. Elle n'est pas déjà remboursée
        
        if ($candidate->status() !== OrderStatus::DELIVERED) {
            return false;
        }

        if ($candidate->daysSinceCreation() > $this->refundPeriodInDays) {
            return false;
        }

        return true;
    }
}
```

#### IsEligibleForDiscountSpecification

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain\Specification;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\Order;

final readonly class IsEligibleForDiscountSpecification implements Specification
{
    public function __construct(
        private float $minimumAmount = 100.0,
        private int $minimumDaysSinceCreation = 7,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Order) {
            return false;
        }

        // Règles métier : une commande est éligible à une remise si :
        // 1. Le montant est supérieur au minimum
        // 2. Elle est en attente depuis X jours
        // 3. Elle n'est pas prioritaire (déjà traitée avec soin)
        
        return $candidate->amount()->value() >= $this->minimumAmount
            && $candidate->daysSinceCreation() >= $this->minimumDaysSinceCreation
            && !$candidate->isPriority();
    }
}
```

#### RequiresManagerApprovalSpecification

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain\Specification;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\Order;

final readonly class RequiresManagerApprovalSpecification implements Specification
{
    public function __construct(
        private float $highValueThreshold = 1000.0,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Order) {
            return false;
        }

        // Règles métier : une commande nécessite l'approbation du manager si :
        // 1. Le montant dépasse le seuil de valeur élevée
        // 2. OU c'est une commande prioritaire (traitement spécial)
        
        return $candidate->amount()->value() > $this->highValueThreshold
            || $candidate->isPriority();
    }
}
```

### 5. Composite Specifications for Complex Rules

#### IsEligibleForExpressShippingSpecification

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain\Specification;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\{Order, OrderStatus};

final readonly class IsEligibleForExpressShippingSpecification implements Specification
{
    public function __construct(
        private float $minimumAmount = 50.0,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Order) {
            return false;
        }

        // Règles métier : une commande est éligible à l'expédition express si :
        // 1. Elle est en attente (pas encore expédiée)
        // 2. Le montant dépasse le minimum
        // 3. Elle a été créée aujourd'hui (urgence)
        
        $isPending = $candidate->status() === OrderStatus::PENDING;
        $meetsAmountThreshold = $candidate->amount()->value() >= $this->minimumAmount;
        $isToday = $candidate->createdAt()->format('Y-m-d') === (new \DateTimeImmutable())->format('Y-m-d');
        
        return $isPending && $meetsAmountThreshold && $isToday;
    }
}
```

#### IsProblematicOrderSpecification

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Domain\Specification;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\{Order, OrderStatus};

final readonly class IsProblematicOrderSpecification implements Specification
{
    public function __construct(
        private int $maxShippingDays = 5,
        private int $maxPendingDays = 3,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Order) {
            return false;
        }

        // Règles métier : une commande est problématique si :
        // 1. Elle est en attente depuis trop longtemps
        // 2. OU elle est expédiée depuis trop longtemps sans livraison
        
        if ($candidate->status() === OrderStatus::PENDING) {
            return $candidate->daysSinceCreation() > $this->maxPendingDays;
        }

        if ($candidate->status() === OrderStatus::SHIPPED && $candidate->shippedAt() !== null) {
            $daysSinceShipped = $candidate->shippedAt()->diff(new \DateTimeImmutable())->days;
            return $daysSinceShipped > $this->maxShippingDays;
        }

        return false;
    }
}
```

### 6. Use Cases using Specifications

#### CancelOrderUseCase

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Application\UseCase;

use App\OrderContext\Domain\Specification\CanBeCancelledSpecification;
use App\OrderContext\Domain\{Order, OrderRepository};
use App\OrderContext\Domain\ValueObject\OrderId;
use App\OrderContext\Domain\Exception\OrderCannotBeCancelledException;

final readonly class CancelOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CanBeCancelledSpecification $canBeCancelledSpec,
    ) {}

    public function execute(OrderId $orderId): Order
    {
        $order = $this->orderRepository->findById($orderId);
        
        if ($order === null) {
            throw new \DomainException('Order not found');
        }

        if (!$this->canBeCancelledSpec->isSatisfiedBy($order)) {
            throw new OrderCannotBeCancelledException(
                sprintf('Order %s cannot be cancelled', $orderId->value())
            );
        }

        $cancelledOrder = $order->cancel();
        $this->orderRepository->save($cancelledOrder);

        return $cancelledOrder;
    }
}
```

#### ApplyDiscountUseCase

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Application\UseCase;

use App\OrderContext\Domain\Specification\IsEligibleForDiscountSpecification;
use App\OrderContext\Domain\{Order, OrderRepository};
use App\OrderContext\Domain\ValueObject\{OrderId, Amount};

final readonly class ApplyDiscountUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private IsEligibleForDiscountSpecification $discountEligibilitySpec,
    ) {}

    public function execute(OrderId $orderId, float $discountPercentage): Order
    {
        $order = $this->orderRepository->findById($orderId);
        
        if ($order === null) {
            throw new \DomainException('Order not found');
        }

        if (!$this->discountEligibilitySpec->isSatisfiedBy($order)) {
            throw new \DomainException('Order is not eligible for discount');
        }

        $discountAmount = $order->amount()->value() * ($discountPercentage / 100);
        $newAmount = Amount::fromFloat($order->amount()->value() - $discountAmount);

        // Créer une nouvelle version de la commande avec le montant réduit
        $discountedOrder = new Order(
            id: $order->id(),
            customerId: $order->customerId(),
            amount: $newAmount,
            status: $order->status(),
            createdAt: $order->createdAt(),
            shippedAt: $order->shippedAt(),
            deliveredAt: $order->deliveredAt(),
            isPriority: $order->isPriority(),
        );

        $this->orderRepository->save($discountedOrder);

        return $discountedOrder;
    }
}
```

### 7. Validation Service with Composition

#### OrderValidationService

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Application\Service;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\Specification\{
    CanBeCancelledSpecification,
    CanBeRefundedSpecification,
    IsEligibleForDiscountSpecification,
    RequiresManagerApprovalSpecification,
    IsEligibleForExpressShippingSpecification,
    IsProblematicOrderSpecification
};
use App\OrderContext\Domain\Order;

final readonly class OrderValidationService
{
    public function __construct(
        private CanBeCancelledSpecification $canBeCancelled,
        private CanBeRefundedSpecification $canBeRefunded,
        private IsEligibleForDiscountSpecification $eligibleForDiscount,
        private RequiresManagerApprovalSpecification $requiresManagerApproval,
        private IsEligibleForExpressShippingSpecification $eligibleForExpress,
        private IsProblematicOrderSpecification $isProblematic,
    ) {}

    public function getOrderCapabilities(Order $order): array
    {
        return [
            'can_be_cancelled' => $this->canBeCancelled->isSatisfiedBy($order),
            'can_be_refunded' => $this->canBeRefunded->isSatisfiedBy($order),
            'eligible_for_discount' => $this->eligibleForDiscount->isSatisfiedBy($order),
            'requires_manager_approval' => $this->requiresManagerApproval->isSatisfiedBy($order),
            'eligible_for_express_shipping' => $this->eligibleForExpress->isSatisfiedBy($order),
            'is_problematic' => $this->isProblematic->isSatisfiedBy($order),
        ];
    }

    public function canPerformAction(Order $order, string $action): bool
    {
        return match ($action) {
            'cancel' => $this->canBeCancelled->isSatisfiedBy($order),
            'refund' => $this->canBeRefunded->isSatisfiedBy($order),
            'apply_discount' => $this->eligibleForDiscount->isSatisfiedBy($order),
            'express_ship' => $this->eligibleForExpress->isSatisfiedBy($order),
            default => false,
        };
    }

    public function getValidationErrors(Order $order, string $action): array
    {
        $errors = [];

        switch ($action) {
            case 'cancel':
                if (!$this->canBeCancelled->isSatisfiedBy($order)) {
                    $errors[] = 'Order cannot be cancelled in its current status';
                }
                break;

            case 'refund':
                if (!$this->canBeRefunded->isSatisfiedBy($order)) {
                    $errors[] = 'Order is not eligible for refund (not delivered or refund period expired)';
                }
                break;

            case 'apply_discount':
                if (!$this->eligibleForDiscount->isSatisfiedBy($order)) {
                    $errors[] = 'Order does not meet discount criteria (amount, age, or priority status)';
                }
                break;

            default:
                $errors[] = 'Unknown action';
        }

        return $errors;
    }
}
```

### 8. Repository with Specifications

#### SpecificationBasedOrderRepository

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\Infrastructure\Repository;

use App\Shared\Application\Specification\Specification;
use App\OrderContext\Domain\{Order, OrderRepository};
use App\OrderContext\Domain\ValueObject\{OrderId, CustomerId};

final class SpecificationBasedOrderRepository implements OrderRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return Order[]
     */
    public function findSatisfying(Specification $specification): array
    {
        $allOrders = $this->findAll();
        
        return array_filter(
            $allOrders,
            fn(Order $order) => $specification->isSatisfiedBy($order)
        );
    }

    /**
     * @return Order[]
     */
    public function findProblematicOrders(): array
    {
        $problematicSpec = new IsProblematicOrderSpecification();
        return $this->findSatisfying($problematicSpec);
    }

    /**
     * @return Order[]
     */
    public function findOrdersRequiringApproval(): array
    {
        $approvalSpec = new RequiresManagerApprovalSpecification();
        return $this->findSatisfying($approvalSpec);
    }

    /**
     * @return Order[]
     */
    public function findEligibleForDiscount(CustomerId $customerId): array
    {
        $customerOrders = $this->findByCustomerId($customerId);
        $discountSpec = new IsEligibleForDiscountSpecification();
        
        return array_filter(
            $customerOrders,
            fn(Order $order) => $discountSpec->isSatisfiedBy($order)
        );
    }

    public function countSatisfying(Specification $specification): int
    {
        return count($this->findSatisfying($specification));
    }

    public function existsSatisfying(Specification $specification): bool
    {
        $allOrders = $this->findAll();
        
        foreach ($allOrders as $order) {
            if ($specification->isSatisfiedBy($order)) {
                return true;
            }
        }
        
        return false;
    }

    // Implémentation des méthodes de base du repository...
    public function findById(OrderId $id): ?Order { /* ... */ }
    public function findByCustomerId(CustomerId $customerId): array { /* ... */ }
    public function save(Order $order): void { /* ... */ }
    public function findAll(): array { /* ... */ }
}
```

### 9. Integration Tests with Specifications

#### OrderWorkflowTest

```php
<?php

declare(strict_types=1);

namespace App\Tests\OrderContext\Integration;

use App\OrderContext\Domain\{Order, OrderStatus};
use App\OrderContext\Domain\ValueObject\{CustomerId, Amount};
use App\OrderContext\Domain\Specification\{
    CanBeCancelledSpecification,
    CanBeRefundedSpecification,
    IsEligibleForDiscountSpecification
};
use PHPUnit\Framework\TestCase;

final class OrderWorkflowTest extends TestCase
{
    private CanBeCancelledSpecification $canBeCancelled;
    private CanBeRefundedSpecification $canBeRefunded;
    private IsEligibleForDiscountSpecification $eligibleForDiscount;

    protected function setUp(): void
    {
        $this->canBeCancelled = new CanBeCancelledSpecification();
        $this->canBeRefunded = new CanBeRefundedSpecification(refundPeriodInDays: 30);
        $this->eligibleForDiscount = new IsEligibleForDiscountSpecification(
            minimumAmount: 100.0,
            minimumDaysSinceCreation: 7
        );
    }

    public function testCompleteOrderWorkflow(): void
    {
        // Créer une commande
        $order = Order::create(
            customerId: CustomerId::generate(),
            amount: Amount::fromFloat(150.0),
            isPriority: false
        );

        // Une nouvelle commande peut être annulée
        $this->assertTrue($this->canBeCancelled->isSatisfiedBy($order));
        
        // Mais ne peut pas être remboursée (pas encore livrée)
        $this->assertFalse($this->canBeRefunded->isSatisfiedBy($order));
        
        // Et n'est pas encore éligible à une remise (pas assez ancienne)
        $this->assertFalse($this->eligibleForDiscount->isSatisfiedBy($order));

        // Expédier la commande
        $shippedOrder = $order->ship();
        
        // Une commande expédiée peut encore être annulée
        $this->assertTrue($this->canBeCancelled->isSatisfiedBy($shippedOrder));

        // Livrer la commande
        $deliveredOrder = $shippedOrder->deliver();
        
        // Une commande livrée ne peut plus être annulée
        $this->assertFalse($this->canBeCancelled->isSatisfiedBy($deliveredOrder));
        
        // Mais peut être remboursée (dans la période de remboursement)
        $this->assertTrue($this->canBeRefunded->isSatisfiedBy($deliveredOrder));
    }

    public function testDiscountEligibilityOverTime(): void
    {
        // Créer une commande avec un montant éligible
        $order = Order::create(
            customerId: CustomerId::generate(),
            amount: Amount::fromFloat(150.0),
            isPriority: false
        );

        // Nouvellement créée, pas encore éligible
        $this->assertFalse($this->eligibleForDiscount->isSatisfiedBy($order));

        // Simuler le passage du temps en modifiant la date de création
        $oldOrder = new Order(
            id: $order->id(),
            customerId: $order->customerId(),
            amount: $order->amount(),
            status: $order->status(),
            createdAt: new \DateTimeImmutable('-8 days'), // 8 jours dans le passé
            isPriority: $order->isPriority()
        );

        // Maintenant éligible pour une remise
        $this->assertTrue($this->eligibleForDiscount->isSatisfiedBy($oldOrder));
    }

    public function testPriorityOrderSpecialRules(): void
    {
        // Commande prioritaire
        $priorityOrder = Order::create(
            customerId: CustomerId::generate(),
            amount: Amount::fromFloat(150.0),
            isPriority: true
        );

        // Les commandes prioritaires ne sont pas éligibles aux remises
        // (même avec un montant suffisant et un âge suffisant)
        $oldPriorityOrder = new Order(
            id: $priorityOrder->id(),
            customerId: $priorityOrder->customerId(),
            amount: $priorityOrder->amount(),
            status: $priorityOrder->status(),
            createdAt: new \DateTimeImmutable('-8 days'),
            isPriority: true
        );

        $this->assertFalse($this->eligibleForDiscount->isSatisfiedBy($oldPriorityOrder));
    }
}
```

### 10. Configuration and usage in a Controller

#### OrderController

```php
<?php

declare(strict_types=1);

namespace App\OrderContext\UI\Controller;

use App\OrderContext\Application\Service\OrderValidationService;
use App\OrderContext\Application\UseCase\{CancelOrderUseCase, ApplyDiscountUseCase};
use App\OrderContext\Domain\ValueObject\OrderId;
use App\OrderContext\Infrastructure\Repository\SpecificationBasedOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;

final class OrderController extends AbstractController
{
    public function __construct(
        private SpecificationBasedOrderRepository $orderRepository,
        private OrderValidationService $validationService,
        private CancelOrderUseCase $cancelOrderUseCase,
        private ApplyDiscountUseCase $applyDiscountUseCase,
    ) {}

    #[Route('/api/orders/{id}/capabilities', methods: ['GET'])]
    public function getOrderCapabilities(string $id): JsonResponse
    {
        $orderId = OrderId::fromString($id);
        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        $capabilities = $this->validationService->getOrderCapabilities($order);

        return new JsonResponse([
            'order_id' => $id,
            'capabilities' => $capabilities,
        ]);
    }

    #[Route('/api/orders/{id}/cancel', methods: ['POST'])]
    public function cancelOrder(string $id): JsonResponse
    {
        try {
            $orderId = OrderId::fromString($id);
            $cancelledOrder = $this->cancelOrderUseCase->execute($orderId);

            return new JsonResponse([
                'message' => 'Order cancelled successfully',
                'order' => [
                    'id' => $cancelledOrder->id()->value(),
                    'status' => $cancelledOrder->status()->value,
                ],
            ]);
            
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/api/orders/{id}/discount', methods: ['POST'])]
    public function applyDiscount(string $id, Request $request): JsonResponse
    {
        try {
            $orderId = OrderId::fromString($id);
            $discountPercentage = (float) $request->request->get('discount_percentage', 0);

            if ($discountPercentage <= 0 || $discountPercentage > 50) {
                return new JsonResponse([
                    'error' => 'Invalid discount percentage (must be between 0 and 50)',
                ], 422);
            }

            $discountedOrder = $this->applyDiscountUseCase->execute($orderId, $discountPercentage);

            return new JsonResponse([
                'message' => 'Discount applied successfully',
                'order' => [
                    'id' => $discountedOrder->id()->value(),
                    'amount' => $discountedOrder->amount()->value(),
                    'discount_applied' => $discountPercentage . '%',
                ],
            ]);
            
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/api/orders/problematic', methods: ['GET'])]
    public function getProblematicOrders(): JsonResponse
    {
        $problematicOrders = $this->orderRepository->findProblematicOrders();

        $ordersData = array_map(function (Order $order) {
            return [
                'id' => $order->id()->value(),
                'customer_id' => $order->customerId()->value(),
                'amount' => $order->amount()->value(),
                'status' => $order->status()->value,
                'days_since_creation' => $order->daysSinceCreation(),
                'capabilities' => $this->validationService->getOrderCapabilities($order),
            ];
        }, $problematicOrders);

        return new JsonResponse([
            'problematic_orders' => $ordersData,
            'count' => count($ordersData),
        ]);
    }
}
```

## Benefits of this Approach

### 🎯 Business Clarity
- **Explicit rules**: Each specification encodes a clear business rule
- **Expressive naming**: `CanBeCancelledSpecification` vs scattered logic
- **Living documentation**: Code directly expresses the rules

### 🔧 Flexibility
- **Easy composition**: Rule combination with AND/OR/NOT
- **Reusability**: Same specification in different contexts
- **Configuration** : Paramètres adjustables (seuils, périodes)

### 🧪 Testability
- **Tests isolés** : Chaque règle testée indépendamment
- **Cas limites** : Tests précis des conditions limites
- **Couverture complète** : Toutes les combinaisons testables

### 🚀 Performance
- **Évaluation paresseuse** : Court-circuit dans les compositions
- **Cache possible** : Résultats cachables pour les objets immutables
- **Optimisation ciblée** : Profiling par spécification

This approach transforms complex business logic into simple, testable, and reusable components, facilitating system maintenance and evolution.