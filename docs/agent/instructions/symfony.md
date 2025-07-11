# Symfony Framework Best Practices

## Official Documentation References
- **Main Documentation**: https://symfony.com/doc/current/index.html
- **API Reference**: https://api.symfony.com/7.3/
- **Best Practices**: https://symfony.com/doc/current/best_practices.html
- **Security**: https://symfony.com/doc/current/security.html
- **Performance**: https://symfony.com/doc/current/performance.html

## Symfony Configuration

### Directory Structure
```
config/               # Symfony configuration
├── packages/         # Bundle configurations  
├── routes/           # Routing definitions
└── services.php      # Service container

public/               # Web root with index.php
templates/            # Twig templates
```

### Core Components
- Use Symfony 7.3 with MicroKernelTrait
- Configure services, routes, and bundles in the `config/` directory
- Web root is in `public/` with `index.php` as entry point

## Dependency Injection

- Use Symfony's dependency injection with constructor injection
- Take advantage of autowiring and autoconfiguration
- Manually configure services in `config/services.php` only for specific cases
- Services should follow single responsibility principle

## Configuration Management

- Use `.env` files and environment variables for sensitive configurations
- Never store secrets in code
- Environment-specific configurations in `config/packages/[env]/`
- Use Symfony's parameter system for application configuration

## Development Tools

- **Symfony Console**: Access via `bin/console`
- **Web Profiler**: Available in dev/test environments for debugging
- **Debug Toolbar**: Automatically enabled in dev environment

## Common Symfony Commands

```bash
# Cache management
bin/console cache:clear              # Clear cache
bin/console cache:warmup             # Warm up cache

# Debug commands
bin/console debug:router             # List all routes
bin/console debug:container          # List services
bin/console debug:config             # Show bundle configuration

# Database (when Doctrine is installed)
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
bin/console doctrine:schema:update --force

# Assets
bin/console assets:install           # Install bundle assets
```

## Bundle Management

- Register bundles in `config/bundles.php`
- Bundle configuration in `config/packages/`
- Use Symfony Flex for automatic bundle configuration

## Routing Best Practices

- Define routes using attributes/annotations on controllers
- Group routes by context/module
- Use route prefixes for API versioning
- Keep route names consistent and descriptive

## Security Considerations

- Always validate and sanitize user input
- Use Symfony's CSRF protection
- Configure security firewall in `config/packages/security.php`
- Use voters for complex authorization logic

## Performance Optimization

- Enable OPcache in production
- Use Symfony cache pools for data caching
- Configure doctrine query cache
- Minimize service definitions (use autowiring)

## Environment Management

- **dev**: Development with debug tools
- **test**: Testing environment
- **prod**: Production with optimizations
- Set `APP_ENV` environment variable appropriately

## Testing with Symfony

- Use Symfony test client for functional tests
- Test services with KernelTestCase
- Use fixtures for test data
- Configure test environment in `.env.test`
