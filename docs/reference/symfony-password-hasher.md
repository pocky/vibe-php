# Symfony Password Hasher Documentation Reference

## Overview
Symfony's password hashing system provides secure password management through the `PasswordHasherInterface` with support for multiple algorithms and automatic migrations.

## Official Documentation
- **Main Documentation**: https://symfony.com/doc/current/security/passwords.html

## Key Components

### PasswordHasherInterface
- Central interface for password hashing operations
- Handles cryptographic salt automatically
- Supports different hashing algorithms per user type

### Supported Algorithms

1. **"auto" Hasher** (Recommended)
   - Automatically selects the most secure available algorithm
   - Future-proof approach
   - Handles algorithm upgrades seamlessly

2. **Bcrypt**
   - Produces 60-character hashes
   - Configurable cost parameter
   - Well-established and secure

3. **Sodium (Argon2)**
   - Uses Argon2 key derivation function
   - Generates 96-character hashes
   - Modern and highly secure

4. **PBKDF2** (Legacy)
   - Not recommended for new applications
   - Kept for backward compatibility

## Configuration

### Basic Configuration
```yaml
security:
    password_hashers:
        App\Entity\User: 'auto'
```

### Advanced Configuration
```yaml
security:
    password_hashers:
        App\Entity\User:
            algorithm: 'auto'
            migrate_from:
                - 'bcrypt'
                - 'argon2i'
```

## Usage

### Password Hashing
```php
$hashedPassword = $passwordHasher->hashPassword(
    $user,
    $plaintextPassword
);
```

### Password Verification
```php
$isPasswordValid = $passwordHasher->isPasswordValid(
    $user, 
    $plaintextPassword
);
```

### Password Upgrading
Implement `PasswordUpgraderInterface` for automatic password migrations:
```php
public function upgradePassword(UserInterface $user, string $newHashedPassword): void
{
    $user->setPassword($newHashedPassword);
    $this->entityManager->flush();
}
```

## Best Practices

1. **Use "auto" Algorithm**
   - Future-proof your application
   - Automatic security improvements
   - Seamless algorithm transitions

2. **Implement Password Upgrading**
   - Gradual migration to newer algorithms
   - No user disruption
   - Enhanced security over time

3. **Validate Password Length**
   - Maximum 4096 characters
   - Prevent DoS attacks
   - Maintain performance

4. **Security Considerations**
   - Never store plaintext passwords
   - Use secure password policies
   - Implement rate limiting for login attempts

## Integration with DDD

### Domain Interface
```php
interface PasswordHasherInterface
{
    public function hashPassword(string $plainPassword): string;
    public function isPasswordValid(string $hashedPassword, string $plainPassword): bool;
}
```

### Infrastructure Implementation
```php
class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserInterface $user
    ) {}
}
```

This approach maintains clean architecture while leveraging Symfony's secure password hashing capabilities.