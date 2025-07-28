---
description: Setup Docker service with production-ready config (Dockerfile, compose, entrypoint)
allowed-tools: Task
argument-hint: <service-name> [--type=php|node|python] [--env=dev|prod]
---

# Docker Setup: $ARGUMENTS

[Task: Use @agent-docker-expert to create production-ready Docker configuration for: $ARGUMENTS

Generate complete service setup:
- Multi-stage Dockerfile (base→dev→test→prod)
- compose.yaml service with healthcheck
- Entrypoint with signal handling
- Environment-specific configs
- Security hardening (non-root, minimal surface)]
