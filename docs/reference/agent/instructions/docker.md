# Docker Best Practices

## Dockerfile
- Start with `# syntax=docker/dockerfile:1`
- Use ARG for versions, APP_UID/APP_GID
- Multi-stage builds with named stages
- Heredoc for multi-line RUN
- apt-get update/install in same RUN
- Clean package caches
- COPY --chown for ownership
- Non-root user
- Entrypoints in etc/docker/entrypoints/
- .dockerignore for exclusions

## Compose
- Use `compose.yaml` (not docker-compose.yml)
- No version directive
- Pass APP_UID/APP_GID from host
- Set hostnames for service discovery
- Relative volume paths
- Read-only volumes: `:ro`
- Config in `etc/`
- `depends_on` for dependencies
- Profiles for optional services
- Healthchecks for critical services

## PHP Docker
- Base: `php:X.Y-fpm-bookworm`
- Extensions: intl, opcache, pdo_*, zip, xsl
- Dev: Xdebug, Symfony CLI
- Prod: opcache.validate_timestamps=0
- Use tini/dumb-init as PID 1
- PHP-FPM as app user
- Mount PHP config as :ro

## Commands
```bash
# Basic
docker compose up -d
docker compose exec app bash
docker compose logs -f app

# Symfony
docker compose exec app bin/console [command]
docker compose exec app composer [command]

# Debug
docker compose run -e XDEBUG_MODE=debug app bash
```
