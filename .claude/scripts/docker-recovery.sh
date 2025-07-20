#!/bin/bash

# docker-recovery.sh - Recovery script for Docker issues during orchestration
# This script helps recover from Docker failures and provides diagnostics

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to check Docker status
check_docker_status() {
    print_status $BLUE "=== Docker Status Check ==="
    
    if docker version >/dev/null 2>&1; then
        print_status $GREEN "✅ Docker client is working"
        docker version --format 'Client: {{.Client.Version}}'
    else
        print_status $RED "❌ Docker client is not accessible"
    fi
    
    if docker info >/dev/null 2>&1; then
        print_status $GREEN "✅ Docker daemon is running"
        docker info --format 'Server Version: {{.ServerVersion}}'
    else
        print_status $RED "❌ Docker daemon is not running"
    fi
}

# Function to check Docker Compose
check_compose_status() {
    print_status $BLUE "\n=== Docker Compose Status ==="
    
    if command -v docker-compose &> /dev/null; then
        print_status $GREEN "✅ docker-compose command found"
    fi
    
    if docker compose version &> /dev/null; then
        print_status $GREEN "✅ docker compose plugin found"
        docker compose version
    else
        print_status $RED "❌ docker compose plugin not found"
    fi
}

# Function to check services
check_services() {
    print_status $BLUE "\n=== Service Status ==="
    
    if [ -f "docker-compose.yml" ] || [ -f "compose.yaml" ]; then
        print_status $GREEN "✅ Docker Compose file found"
        
        if docker compose ps 2>/dev/null; then
            # Check for unhealthy services
            if docker compose ps | grep -E "Exit|Error|Restarting"; then
                print_status $YELLOW "⚠️  Some services are in an error state"
            fi
        else
            print_status $RED "❌ Cannot list services"
        fi
    else
        print_status $RED "❌ No docker-compose.yml or compose.yaml found"
    fi
}

# Function to check disk space
check_disk_space() {
    print_status $BLUE "\n=== Disk Space Check ==="
    
    # Docker root directory
    if docker info --format '{{.DockerRootDir}}' 2>/dev/null; then
        local docker_root=$(docker info --format '{{.DockerRootDir}}')
        print_status $YELLOW "Docker root: $docker_root"
        df -h "$docker_root" 2>/dev/null || df -h /var/lib/docker
    fi
    
    # Check if disk is full
    local disk_usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    if [ "$disk_usage" -gt 90 ]; then
        print_status $RED "⚠️  Disk usage is above 90% - this may cause Docker issues"
    fi
}

# Function to check port conflicts
check_port_conflicts() {
    print_status $BLUE "\n=== Port Conflicts Check ==="
    
    local ports=("80" "8000" "5432" "59990")
    for port in "${ports[@]}"; do
        if lsof -i :$port >/dev/null 2>&1 || netstat -tuln 2>/dev/null | grep -q ":$port "; then
            print_status $YELLOW "Port $port is in use"
        else
            print_status $GREEN "Port $port is available"
        fi
    done
}

# Function to attempt recovery
attempt_recovery() {
    print_status $BLUE "\n=== Attempting Recovery ==="
    
    # Try to restart Docker
    print_status $YELLOW "1. Attempting to restart Docker..."
    if .claude/scripts/ensure-docker.sh; then
        print_status $GREEN "✅ Docker recovery successful!"
        return 0
    fi
    
    # Clean up and retry
    print_status $YELLOW "\n2. Cleaning up Docker resources..."
    
    # Remove stopped containers
    if docker container prune -f 2>/dev/null; then
        print_status $GREEN "✅ Cleaned up stopped containers"
    fi
    
    # Clean up unused images
    if docker image prune -f 2>/dev/null; then
        print_status $GREEN "✅ Cleaned up unused images"
    fi
    
    # Try to stop all services first
    print_status $YELLOW "\n3. Stopping all services..."
    docker compose down 2>/dev/null || true
    
    # Try to start again
    print_status $YELLOW "\n4. Starting services again..."
    if docker compose up -d 2>/dev/null; then
        print_status $GREEN "✅ Services restarted successfully!"
        docker compose ps
        return 0
    else
        print_status $RED "❌ Failed to restart services"
        return 1
    fi
}

# Function to show manual recovery steps
show_manual_recovery() {
    print_status $BLUE "\n=== Manual Recovery Steps ==="
    
    print_status $YELLOW "If automatic recovery failed, try these steps:"
    echo
    echo "1. Restart Docker Desktop/Service:"
    echo "   - macOS: Quit and restart Docker Desktop"
    echo "   - Linux: sudo systemctl restart docker"
    echo "   - Windows: Restart Docker Desktop from system tray"
    echo
    echo "2. Clean up Docker resources:"
    echo "   docker system prune -a --volumes"
    echo
    echo "3. Reset Docker to factory defaults:"
    echo "   - Docker Desktop: Preferences → Reset → Reset to factory defaults"
    echo
    echo "4. Check for conflicting processes:"
    echo "   lsof -i :80"
    echo "   lsof -i :5432"
    echo
    echo "5. Rebuild containers:"
    echo "   docker compose down -v"
    echo "   docker compose build --no-cache"
    echo "   docker compose up -d"
}

# Main execution
main() {
    print_status $YELLOW "🔧 Docker Recovery Tool\n"
    
    # Run diagnostics
    check_docker_status
    check_compose_status
    check_services
    check_disk_space
    check_port_conflicts
    
    # Ask if user wants to attempt recovery
    echo
    read -p "Do you want to attempt automatic recovery? (y/n): " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if attempt_recovery; then
            print_status $GREEN "\n🎉 Recovery successful! You can now continue with orchestration."
        else
            print_status $RED "\n❌ Automatic recovery failed."
            show_manual_recovery
        fi
    else
        show_manual_recovery
    fi
}

# Run main function
main "$@"