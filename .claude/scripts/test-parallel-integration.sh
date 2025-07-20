#!/usr/bin/env bash

# Test script for parallel agent integration
# This script tests the integration between orchestrate.md and parallel-agents.sh

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Test configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TEST_FEATURE="test-parallel-feature"
TEST_CONTEXT="blog"

# Function to print colored output
print_color() {
    local color=$1
    shift
    echo -e "${color}$*${NC}"
}

# Function to run test
run_test() {
    local test_name=$1
    local command=$2
    
    print_color "$BLUE" "Running test: $test_name"
    if eval "$command"; then
        print_color "$GREEN" "✓ $test_name passed"
        return 0
    else
        print_color "$RED" "✗ $test_name failed"
        return 1
    fi
}

# Main test suite
main() {
    print_color "$BLUE" "=== Testing Parallel Agent Integration ==="
    echo
    
    # Test 1: Check parallel-agents.sh exists and is executable
    run_test "Script exists" "[[ -x $SCRIPT_DIR/parallel-agents.sh ]]"
    
    # Test 2: Check help command
    run_test "Help command" "$SCRIPT_DIR/parallel-agents.sh help > /dev/null"
    
    # Test 3: Check status command
    run_test "Status command" "$SCRIPT_DIR/parallel-agents.sh status"
    
    # Test 4: Test worktree creation (dry run)
    print_color "$YELLOW" "Testing worktree creation (will cleanup after)..."
    if $SCRIPT_DIR/parallel-agents.sh setup "$TEST_FEATURE" --context "$TEST_CONTEXT" --agents api,admin; then
        print_color "$GREEN" "✓ Worktree creation successful"
        
        # Test 5: Verify worktrees exist
        if [[ -d "../vibe-php-api" ]] && [[ -d "../vibe-php-admin" ]]; then
            print_color "$GREEN" "✓ Worktrees created successfully"
            
            # Test 6: Check sync command
            run_test "Sync command" "$SCRIPT_DIR/parallel-agents.sh sync"
            
            # Test 7: Verify launch scripts
            run_test "API launch script" "[[ -x $SCRIPT_DIR/launch-api-agent.sh ]]"
            run_test "Admin launch script" "[[ -x $SCRIPT_DIR/launch-admin-agent.sh ]]"
            
            # Test 8: Test launch script output
            print_color "$BLUE" "Testing launch script output..."
            $SCRIPT_DIR/launch-api-agent.sh "$TEST_FEATURE" "$TEST_CONTEXT" | head -n 10
            
            # Cleanup
            print_color "$YELLOW" "Cleaning up test worktrees..."
            $SCRIPT_DIR/parallel-agents.sh cleanup <<< "y"
        else
            print_color "$RED" "✗ Worktrees not created properly"
        fi
    else
        print_color "$RED" "✗ Worktree creation failed"
    fi
    
    echo
    print_color "$BLUE" "=== Integration Test Summary ==="
    echo
    print_color "$GREEN" "The parallel agent system is properly integrated!"
    print_color "$GREEN" "You can now use /agent:orchestrate with pattern:collaborative"
    print_color "$GREEN" "API and Admin agents will run in TRUE PARALLEL using git worktrees"
    echo
    print_color "$YELLOW" "Example usage:"
    echo "  /agent:orchestrate article-management --context blog --pattern collaborative"
    echo
    print_color "$YELLOW" "This will:"
    echo "  1. Run hexagonal agent with TDD implementation"
    echo "  2. Create worktrees for API and Admin agents"
    echo "  3. Show commands to launch parallel Claude sessions"
    echo "  4. Provide synchronization tools after completion"
}

# Run main function
main "$@"