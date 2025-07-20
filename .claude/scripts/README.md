# Claude Scripts

This directory contains utility scripts used by Claude commands for various automation tasks.

## Docker Management Scripts

### ensure-docker.sh

**Purpose**: Ensures Docker is running and services are up before starting agent orchestration.

**Features**:
- Detects operating system (macOS, Linux, WSL2)
- Attempts to start Docker automatically based on OS
- Waits for Docker daemon to be ready
- Checks and starts Docker Compose services
- Provides clear status messages

**Usage**:
```bash
.claude/scripts/ensure-docker.sh
```

**Exit Codes**:
- `0`: Success - Docker and services are running
- `1`: Failure - Docker could not be started or services failed

### docker-recovery.sh

**Purpose**: Diagnostic and recovery tool for Docker issues during development.

**Features**:
- Comprehensive Docker status diagnostics
- Checks for port conflicts
- Monitors disk space
- Attempts automatic recovery
- Provides manual recovery instructions

**Usage**:
```bash
.claude/scripts/docker-recovery.sh
```

**Interactive**: The script will ask if you want to attempt automatic recovery.

## Integration with Commands

These scripts are automatically used by:
- `/agent:orchestrate` - Runs `ensure-docker.sh` before starting orchestration
- Manual recovery - Users can run `docker-recovery.sh` if issues occur

## Adding New Scripts

When adding new scripts:
1. Place them in this directory
2. Make them executable: `chmod +x script-name.sh`
3. Update this README
4. Add error handling and colored output for consistency
5. Use exit codes appropriately (0 for success, 1 for failure)

## Script Standards

All scripts should follow these standards:
- Use bash shebang: `#!/bin/bash`
- Set error handling: `set -e`
- Use colored output for better readability
- Provide clear error messages
- Handle different operating systems when relevant
- Document purpose and usage in script comments