# LexikJWTAuthenticationBundle Documentation Reference

## Overview
LexikJWTAuthenticationBundle provides JWT (JSON Web Token) authentication for Symfony applications with seamless integration into the Security component.

## Official Documentation
- **Main Documentation**: https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html

## Requirements
- Symfony 6.4+
- ext-openssl PHP extension
- **Highly recommended**: HTTPS in production

## Installation

### 1. Install Bundle
```bash
composer require "lexik/jwt-authentication-bundle"
```

### 2. Generate SSL Keys
```bash
php bin/console lexik:jwt:generate-keypair
```

### 3. Environment Configuration
```env
# .env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

## Configuration

### Security Configuration
```yaml
# config/packages/security.yaml
security:
    firewalls:
        login:
            pattern: ^/api/login
            stateless: true
            json_login:
                check_path: /api/login_check
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        api:
            pattern: ^/api
            stateless: true
            jwt: ~
```

### Bundle Configuration
```yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600 # Token lifetime in seconds
```

### Routing
```yaml
# config/routes.yaml
api_login_check:
    path: /api/login_check
```

## Token Workflow

### 1. Obtain Token
**Request:**
```http
POST /api/login_check
Content-Type: application/json

{
    "username": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### 2. Use Token
**Request with Token:**
```http
GET /api/protected-resource
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
```

## Token Structure

### JWT Payload
```json
{
    "iat": 1622547200,     // Issued at
    "exp": 1622550800,     // Expiration time
    "roles": ["ROLE_USER"], // User roles
    "username": "user@example.com"
}
```

## Advanced Features

### Custom Token Generation
```php
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class CustomTokenGenerator
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}
    
    public function createToken(UserInterface $user): string
    {
        return $this->jwtManager->create($user);
    }
}
```

### Token Validation
```php
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenValidator
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}
    
    public function validateToken(string $token): ?array
    {
        try {
            return $this->jwtManager->decode($token);
        } catch (JWTDecodeFailureException $e) {
            return null;
        }
    }
}
```

## Event System

### Available Events
- `lexik_jwt_authentication.on_authentication_success`
- `lexik_jwt_authentication.on_authentication_failure`
- `lexik_jwt_authentication.on_jwt_created`
- `lexik_jwt_authentication.on_jwt_decoded`
- `lexik_jwt_authentication.on_jwt_authenticated`

### Custom Event Listener Example
```php
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();
        
        // Add custom claims
        $payload['custom_claim'] = 'custom_value';
        
        $event->setData($payload);
    }
}
```

## Security Best Practices

1. **Use HTTPS in Production**
   - Prevents token interception
   - Required for secure authentication

2. **Secure Key Management**
   - Store keys outside web root
   - Use strong passphrases
   - Rotate keys regularly

3. **Token Expiration**
   - Use reasonable TTL (default: 1 hour)
   - Implement refresh token mechanism
   - Consider token blacklisting

4. **CORS Configuration**
   - Configure CORS for frontend applications
   - Restrict allowed origins in production

## Integration with DDD Architecture

### Domain Interface
```php
interface TokenGeneratorInterface
{
    public function generateToken(User $user): string;
    public function validateToken(string $token): ?TokenPayload;
}
```

### Infrastructure Adapter
```php
class LexikJwtTokenGenerator implements TokenGeneratorInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}
    
    public function generateToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }
}
```

This approach maintains clean separation between domain logic and JWT implementation details.