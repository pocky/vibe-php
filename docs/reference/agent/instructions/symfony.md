# Symfony Best Practices

**Symfony 7.3** - See official docs for details.

## Configuration
```
config/packages/    # Bundle config
config/routes/      # Routing
config/services.php # DI container
public/index.php    # Entry point
```

## DI
- Constructor injection + autowiring
- Manual config only when needed
- Single responsibility

## Config
- `.env` for secrets
- `config/packages/[env]/` for env-specific
- Parameter system for app config

## Tools
- `bin/console` - CLI
- Web Profiler (dev/test)
- Debug Toolbar (dev)

## Commands
```bash
# Cache
bin/console cache:clear
bin/console cache:warmup

# Debug
bin/console debug:router
bin/console debug:container

# Doctrine
bin/console doctrine:migrations:migrate

# Assets
bin/console assets:install
```

## Bundles
- Register: `config/bundles.php`
- Configure: `config/packages/`
- Flex for auto-config

## Routing
- Use attributes on controllers
- Group by context
- Prefix for API versions
- Consistent naming

## Security
- Validate/sanitize input
- CSRF protection
- Firewall: `config/packages/security.php`
- Voters for complex auth

## Performance
- OPcache (prod)
- Cache pools
- Query cache
- Autowiring > manual services

## Environments
- **dev**: Debug tools
- **test**: Testing
- **prod**: Optimized
- Set via `APP_ENV`

## Testing
- Test client for functional
- KernelTestCase for services
- Fixtures for data
- Config: `.env.test`
