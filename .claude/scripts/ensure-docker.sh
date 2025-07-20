#!/bin/bash

# ensure-docker.sh - Ensure Docker is running and services are up
# Used by agent orchestration to handle Docker lifecycle

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to check if Docker is running
check_docker() {
    if docker info >/dev/null 2>&1; then
        return 0
    else
        return 1
    fi
}

# Function to start Docker based on OS
start_docker() {
    print_status $YELLOW "🐳 Docker is not running. Attempting to start..."
    
    # Detect OS and try to start Docker
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        if command -v open &> /dev/null; then
            print_status $YELLOW "Starting Docker Desktop on macOS..."
            open -a Docker
        else
            print_status $RED "Cannot start Docker Desktop automatically on macOS"
            return 1
        fi
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        # Linux
        if command -v systemctl &> /dev/null; then
            print_status $YELLOW "Starting Docker service using systemctl..."
            sudo systemctl start docker
        elif command -v service &> /dev/null; then
            print_status $YELLOW "Starting Docker service..."
            sudo service docker start
        else
            print_status $RED "Cannot determine how to start Docker on this Linux system"
            return 1
        fi
    elif grep -qi microsoft /proc/version 2>/dev/null; then
        # WSL2
        print_status $YELLOW "Running on WSL2 - checking if Docker Desktop is accessible..."
        # In WSL2, Docker Desktop should be started from Windows
        if ! check_docker; then
            print_status $RED "Please start Docker Desktop from Windows"
            print_status $RED "Once started, Docker will be available in WSL2"
            return 1
        fi
    else
        print_status $RED "Unsupported operating system: $OSTYPE"
        return 1
    fi
    
    # Wait for Docker to be ready
    print_status $YELLOW "Waiting for Docker to start..."
    local max_attempts=30
    for i in $(seq 1 $max_attempts); do
        if check_docker; then
            print_status $GREEN "✅ Docker is now running!"
            return 0
        fi
        
        if [ $i -eq $max_attempts ]; then
            print_status $RED "❌ Docker failed to start after ${max_attempts} seconds"
            return 1
        fi
        
        sleep 1
        echo -n "."
    done
    echo
}

# Function to check Docker Compose services
check_services() {
    if ! docker compose ps --format "table {{.Name}}\t{{.Status}}" 2>/dev/null | grep -q .; then
        return 1
    fi
    
    # Check if any services are in a bad state
    if docker compose ps --format "{{.Status}}" 2>/dev/null | grep -qE "Exit|Error|Restarting"; then
        return 1
    fi
    
    return 0
}

# Function to start Docker Compose services
start_services() {
    print_status $YELLOW "📦 Starting Docker Compose services..."
    
    if docker compose up -d; then
        print_status $GREEN "✅ Services started successfully"
    else
        print_status $RED "❌ Failed to start services"
        return 1
    fi
    
    # Wait for services to be healthy
    print_status $YELLOW "Waiting for services to be healthy..."
    local max_attempts=60
    for i in $(seq 1 $max_attempts); do
        if docker compose ps 2>/dev/null | grep -qE "healthy|Up"; then
            print_status $GREEN "✅ Services are ready!"
            docker compose ps
            return 0
        fi
        
        if [ $i -eq $max_attempts ]; then
            print_status $YELLOW "⚠️  Services may not be fully ready after ${max_attempts} seconds"
            docker compose ps
            return 0  # Continue anyway
        fi
        
        sleep 1
        echo -n "."
    done
    echo
}

# Main execution
main() {
    print_status $YELLOW "🔍 Checking Docker environment..."
    
    # Step 1: Check if Docker is running
    if check_docker; then
        print_status $GREEN "✅ Docker is running"
    else
        # Try to start Docker
        if ! start_docker; then
            print_status $RED "❌ Failed to start Docker automatically"
            print_status $RED "Please start Docker manually and run this command again:"
            print_status $YELLOW "  - macOS: Open Docker Desktop from Applications"
            print_status $YELLOW "  - Linux: sudo systemctl start docker"
            print_status $YELLOW "  - Windows/WSL2: Start Docker Desktop from Windows"
            exit 1
        fi
    fi
    
    # Step 2: Check if we're in the right directory
    if [ ! -f "docker-compose.yml" ] && [ ! -f "compose.yaml" ]; then
        print_status $RED "❌ No docker-compose.yml or compose.yaml found in current directory"
        print_status $RED "Please run this script from the project root"
        exit 1
    fi
    
    # Step 3: Check Docker Compose services
    print_status $YELLOW "🔍 Checking Docker Compose services..."
    if check_services; then
        print_status $GREEN "✅ Services are running"
        docker compose ps
    else
        # Try to start services
        if ! start_services; then
            print_status $RED "❌ Failed to start Docker Compose services"
            exit 1
        fi
    fi
    
    print_status $GREEN "🎉 Docker environment is ready for orchestration!"
}

# Run main function
main "$@"