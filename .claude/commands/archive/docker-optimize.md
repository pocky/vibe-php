---
description: Optimize Docker configuration for build speed and image size
allowed-tools: Read, Write, Edit, MultiEdit, Bash, TodoWrite
argument-hint: [focus] - Specify 'build', 'size', 'security', or 'performance'
---

Optimize Docker configuration using the docker-expert agent with focus on: $ARGUMENTS

The expert will:

1. **Analyze Current State**:
   - Measure baseline metrics
   - Identify optimization opportunities
   - Prioritize improvements by impact

2. **Apply Optimizations**:
   - Implement build cache strategies
   - Reduce layer count and sizes
   - Apply security hardening
   - Configure performance tuning

3. **Validate Results**:
   - Compare before/after metrics
   - Run security scans
   - Verify functionality

Specific optimization areas:
- **build**: Cache optimization, parallel builds, layer efficiency
- **size**: Multi-stage refinement, dependency cleanup, base image selection
- **security**: Non-root enforcement, minimal attack surface, vulnerability fixes
- **performance**: OPcache tuning, PHP-FPM optimization, resource limits

All changes will maintain the project's production-grade Docker standards.