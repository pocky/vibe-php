# Docker Best Practices

## General Docker Best Practices

1. Always start Dockerfiles with syntax directive (e.g. # syntax=docker/dockerfile:1).
2. Define versions using ARG instructions for better maintainability.
3. Use ARG for user/group IDs (APP_UID, APP_GID) to match host permissions.
4. Implement multi-stage builds with clearly named stages.
5. Include OpenContainers labels in the base image (title, description, authors, source).
6. Create a base image with common dependencies, then specific images for development and production.
7. Use heredoc syntax (<<EOF) for multi-line RUN instructions to improve readability.
8. Use complete apt-get options for better control and visibility.
9. Always run apt-get update, upgrade, and install in the same RUN block.
10. Create a dedicated application user/group with proper UID/GID mapping to host.
11. Pin dependency versions when possible for build reproducibility.
12. Always clean up package manager caches after installation.
13. Use COPY with --chown flag to set proper ownership for files.
14. Use proper file ownership (chown) for application directories and files.
15. Run containers as non-root users in production and development.
16. Expose only necessary ports.
17. Store entrypoint scripts in etc/docker/entrypoints directory without file extensions.
18. Copy all entrypoint scripts to /usr/local/bin/ in the container.
19. Use SHELL instruction to define default shell for RUN commands.
20. Set STOPSIGNAL to handle container termination properly.
21. Maintain an up-to-date .dockerignore to exclude unnecessary files.
22. Configure application through environment variables.
23. Always use compose.yaml (not docker-compose.yml) for Docker Compose configuration.
24. Pass APP_UID and APP_GID from host environment to Docker build process in compose.yaml.

## Docker Compose Best Practices

1. Never include a version directive in compose.yaml files (it's obsolete in recent Docker Compose versions).
2. Use compose.yaml (not docker-compose.yml) for Docker Compose configuration.
3. Pass APP_UID and APP_GID from host environment to Docker build process.
4. Set specific hostname for services to allow reliable service discovery.
5. Configure service-specific entrypoints using the entrypoint directive.
6. Use relative paths for volumes when possible.
7. Use read-only volumes for configuration files with :ro suffix.
8. Store all configuration files in etc/ directory, maintaining subdirectory structure.
9. Use environment variables for runtime configuration.
10. Define dependencies between services using depends_on when appropriate.
11. Use profiles for services that shouldn't start by default.
12. Leverage environment files with env_file for complex configurations.
13. Use healthchecks for critical services.

## PHP Specific Docker Practices

1. Use official PHP-FPM images (php:X.Y-fpm-bookworm) as base for better Debian compatibility.
2. Install PHP extensions using the install-php-extensions helper script for easier management.
3. Essential PHP extensions for Symfony:
   - `intl` - Internationalization support
   - `opcache` - Performance optimization
   - `pdo_mysql` or `pdo_pgsql` - Database connectivity
   - `zip` - Composer package handling
   - `xsl` - XML transformations
4. Development-only tools:
   - Install Xdebug only in development stage
   - Install Symfony CLI only in development stage
   - Configure Xdebug via environment variables (XDEBUG_MODE, XDEBUG_CLIENT_HOST)
5. Production optimizations:
   - Enable and configure OPcache with production settings
   - Set `opcache.validate_timestamps=0` in production
   - Copy production php.ini settings
6. Use dumb-init or tini as PID 1 process for proper signal handling.
7. Configure PHP-FPM to run as the application user.
8. Mount custom PHP configurations as read-only volumes in compose.yaml.

## Common Commands Reference

```bash
# Development environment
docker compose up -d                   # Start services
docker compose exec app bash           # Enter container
docker compose logs -f app             # View logs
docker compose down                    # Stop services

# Running Symfony commands
docker compose exec app bin/console cache:clear
docker compose exec app composer install
docker compose exec app bin/console list

# Debugging with Xdebug
docker compose run -e XDEBUG_MODE=debug app bash
```
