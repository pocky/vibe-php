<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Application\Specification;

use App\Shared\Application\Specification\Specification;
use PHPUnit\Framework\TestCase;

final class SpecificationTest extends TestCase
{
    public function testSpecificationIsInterface(): void
    {
        $reflection = new \ReflectionClass(Specification::class);

        $this->assertTrue($reflection->isInterface());
    }

    public function testSpecificationHasIsSatisfiedByMethod(): void
    {
        $reflection = new \ReflectionClass(Specification::class);

        $this->assertTrue($reflection->hasMethod('isSatisfiedBy'));

        $method = $reflection->getMethod('isSatisfiedBy');
        $this->assertTrue($method->isPublic());
        $this->assertFalse($method->isStatic());
    }

    public function testIsSatisfiedByMethodSignature(): void
    {
        $reflection = new \ReflectionClass(Specification::class);
        $method = $reflection->getMethod('isSatisfiedBy');

        // Vérifier les paramètres
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);

        $candidateParam = $parameters[0];
        $this->assertSame('candidate', $candidateParam->getName());
        $this->assertTrue($candidateParam->hasType());
        $this->assertSame('object', $candidateParam->getType()->getName());

        // Vérifier le type de retour
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('bool', $method->getReturnType()->getName());
    }

    public function testConcreteSpecificationImplementation(): void
    {
        $specification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return $candidate instanceof \stdClass;
            }
        };

        $this->assertInstanceOf(Specification::class, $specification);

        $stdObject = new \stdClass();
        $arrayObject = new \ArrayObject();

        $this->assertTrue($specification->isSatisfiedBy($stdObject));
        $this->assertFalse($specification->isSatisfiedBy($arrayObject));
    }

    public function testSpecificationWithComplexLogic(): void
    {
        $specification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                // Exemple : objet doit avoir une propriété 'status' avec valeur 'active'
                return property_exists($candidate, 'status') && 'active' === $candidate->status;
            }
        };

        $activeObject = new \stdClass();
        $activeObject->status = 'active';

        $inactiveObject = new \stdClass();
        $inactiveObject->status = 'inactive';

        $objectWithoutStatus = new \stdClass();

        $this->assertTrue($specification->isSatisfiedBy($activeObject));
        $this->assertFalse($specification->isSatisfiedBy($inactiveObject));
        $this->assertFalse($specification->isSatisfiedBy($objectWithoutStatus));
    }

    public function testMultipleSpecificationImplementations(): void
    {
        $typeSpecification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return $candidate instanceof \DateTimeInterface;
            }
        };

        $valueSpecification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                if (!$candidate instanceof \DateTimeInterface) {
                    return false;
                }

                return '2024' === $candidate->format('Y');
            }
        };

        $date2024 = new \DateTimeImmutable('2024-01-01');
        $date2023 = new \DateTimeImmutable('2023-01-01');
        $stdObject = new \stdClass();

        // Test type specification
        $this->assertTrue($typeSpecification->isSatisfiedBy($date2024));
        $this->assertTrue($typeSpecification->isSatisfiedBy($date2023));
        $this->assertFalse($typeSpecification->isSatisfiedBy($stdObject));

        // Test value specification
        $this->assertTrue($valueSpecification->isSatisfiedBy($date2024));
        $this->assertFalse($valueSpecification->isSatisfiedBy($date2023));
        $this->assertFalse($valueSpecification->isSatisfiedBy($stdObject));
    }

    public function testSpecificationWithDomainObject(): void
    {
        // Création d'un objet domain simulé
        $user = new class {
            public function __construct(
                public string $email = 'test@example.com',
                public int $age = 25,
                public bool $isActive = true,
            ) {
            }
        };

        $emailSpecification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return property_exists($candidate, 'email')
                    && false !== filter_var($candidate->email, FILTER_VALIDATE_EMAIL);
            }
        };

        $adultSpecification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return property_exists($candidate, 'age') && 18 <= $candidate->age;
            }
        };

        $activeSpecification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return property_exists($candidate, 'isActive') && true === $candidate->isActive;
            }
        };

        $validUser = new $user('valid@example.com', 25, true);
        $invalidEmailUser = new $user('invalid-email', 25, true);
        $minorUser = new $user('minor@example.com', 17, true);
        $inactiveUser = new $user('inactive@example.com', 25, false);

        // Test email specification
        $this->assertTrue($emailSpecification->isSatisfiedBy($validUser));
        $this->assertFalse($emailSpecification->isSatisfiedBy($invalidEmailUser));

        // Test adult specification
        $this->assertTrue($adultSpecification->isSatisfiedBy($validUser));
        $this->assertFalse($adultSpecification->isSatisfiedBy($minorUser));

        // Test active specification
        $this->assertTrue($activeSpecification->isSatisfiedBy($validUser));
        $this->assertFalse($activeSpecification->isSatisfiedBy($inactiveUser));
    }

    public function testSpecificationComposition(): void
    {
        // Simule une composition AND de spécifications
        $compositeSpecification = new readonly class implements Specification {
            private array $specifications;

            public function __construct(
                Specification ...$specifications
            ) {
                $this->specifications = $specifications;
            }

            public function isSatisfiedBy(object $candidate): bool
            {
                foreach ($this->specifications as $specification) {
                    if (!$specification->isSatisfiedBy($candidate)) {
                        return false;
                    }
                }

                return true;
            }
        };

        $typeSpec = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return $candidate instanceof \stdClass;
            }
        };

        $propertySpec = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return property_exists($candidate, 'name');
            }
        };

        $composite = new $compositeSpecification($typeSpec, $propertySpec);

        $validObject = new \stdClass();
        $validObject->name = 'test';

        $invalidTypeObject = new \ArrayObject();
        $invalidTypeObject->name = 'test';

        $invalidPropertyObject = new \stdClass();

        $this->assertTrue($composite->isSatisfiedBy($validObject));
        $this->assertFalse($composite->isSatisfiedBy($invalidTypeObject));
        $this->assertFalse($composite->isSatisfiedBy($invalidPropertyObject));
    }

    public function testSpecificationWithExceptionHandling(): void
    {
        $safeSpecification = new class implements Specification {
            public function isSatisfiedBy(object $candidate): bool
            {
                try {
                    // Simule une opération qui pourrait lever une exception
                    if (!property_exists($candidate, 'data')) {
                        return false;
                    }

                    // Simule l'accès à une propriété qui pourrait ne pas exister
                    return !empty($candidate->data['required_field']);
                } catch (\Throwable) {
                    return false;
                }
            }
        };

        $validObject = new \stdClass();
        $validObject->data = [
            'required_field' => 'value',
        ];

        $invalidObject = new \stdClass();
        $invalidObject->data = [];

        $objectWithoutData = new \stdClass();

        $this->assertTrue($safeSpecification->isSatisfiedBy($validObject));
        $this->assertFalse($safeSpecification->isSatisfiedBy($invalidObject));
        $this->assertFalse($safeSpecification->isSatisfiedBy($objectWithoutData));
    }
}
