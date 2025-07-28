---
description: Comprehensive Docker audit for security, performance, and best practices compliance
allowed-tools: Read, Grep, Bash, TodoWrite
argument-hint: [target] - Specify 'dockerfile', 'compose', or leave empty for full audit
---

Execute a comprehensive Docker audit using the docker-expert agent to analyze:

1. **Dockerfile Analysis**:
   - Security vulnerabilities and anti-patterns
   - Build optimization opportunities
   - Layer efficiency and caching
   - Best practices compliance

2. **Docker Compose Review**:
   - Service configuration
   - Volume and network setup
   - Environment management
   - Healthcheck implementation

3. **Performance Metrics**:
   - Image sizes
   - Build times
   - Runtime efficiency

Target: $ARGUMENTS (or full audit if not specified)

Generate a detailed report with:
- Current issues ranked by severity
- Specific recommendations for improvement
- Code examples for fixes
- Performance impact estimates

Focus on maintaining the project's exceptional Docker standards while identifying areas for enhancement.