---
description: Analyzes and optimizes Docker configs for specified target metric
allowed-tools: Task
argument-hint: build|size|security|performance
---

# Docker Optimization: $ARGUMENTS

[Task: Use @agent-docker-expert to optimize Docker configuration for: $ARGUMENTS

For '$ARGUMENTS' optimization:
- Analyze current Dockerfile and compose configs
- Apply specific optimizations:
  - build: Layer caching, BuildKit features, parallel stages
  - size: Multi-stage builds, minimal base images, layer squashing
  - security: Non-root users, distroless bases, secret handling
  - performance: Resource limits, health checks, startup optimization

Deliver:
1. **Current State Analysis** (metrics, issues)
2. **Optimization Plan** (specific changes)
3. **Implementation** (modified files)
4. **Impact Report** (before/after comparison)
5. **Best Practices** (maintainability tips)]
