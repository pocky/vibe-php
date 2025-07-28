---
description: Setup or update Docker configuration for a new service or feature
allowed-tools: Read, Write, Edit, MultiEdit, Bash, Grep, TodoWrite
argument-hint: <service-name> [options] - Service to configure with optional flags
---

Setup Docker configuration for: $ARGUMENTS

Using the docker-expert agent to:

1. **Service Configuration**:
   - Create Dockerfile with proper multi-stage build
   - Configure Docker Compose service
   - Setup health checks and dependencies
   - Implement proper signal handling

2. **Environment Setup**:
   - Development configuration with debugging tools
   - Test environment with isolated services
   - Production-ready configuration

3. **Integration**:
   - Connect to existing services
   - Configure volumes and networks
   - Setup environment variables
   - Document configuration

The expert will ensure:
- ✅ Production-grade security (non-root, minimal attack surface)
- ✅ Optimized builds (caching, layer efficiency)
- ✅ Proper signal handling (graceful shutdown)
- ✅ Environment-specific configurations
- ✅ Consistent with project standards

All configurations follow the established patterns in this codebase.