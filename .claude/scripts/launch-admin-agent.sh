#!/usr/bin/env bash

# Launch Admin Agent in its worktree
# This script helps launch Claude with the correct Admin agent prompt

set -euo pipefail

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
PARENT_DIR="$(dirname "$PROJECT_ROOT")"
WORKTREE_PATH="${PARENT_DIR}/vibe-php-admin"

# Function to print colored output
print_color() {
    local color=$1
    shift
    echo -e "${color}$*${NC}"
}

# Check arguments
if [[ $# -lt 2 ]]; then
    print_color "$RED" "Usage: $0 <feature-name> <context>"
    print_color "$YELLOW" "Example: $0 article-management blog"
    exit 1
fi

FEATURE=$1
CONTEXT=$2

# Check if worktree exists
if [[ ! -d "$WORKTREE_PATH" ]]; then
    print_color "$RED" "Error: Admin worktree not found at $WORKTREE_PATH"
    print_color "$YELLOW" "Run './parallel-agents.sh setup $FEATURE --context $CONTEXT' first"
    exit 1
fi

# Build the Claude command
CLAUDE_PROMPT="/agent:admin 'Create admin interface for $FEATURE in $CONTEXT context'"

print_color "$BLUE" "🚀 Launching Admin Agent for $FEATURE"
echo
print_color "$GREEN" "Worktree: $WORKTREE_PATH"
print_color "$GREEN" "Prompt: $CLAUDE_PROMPT"
echo
print_color "$YELLOW" "📝 Notes:"
echo "- The Admin agent will create Sylius Admin UI interfaces"
echo "- It follows TDD with Behat for UI testing"
echo "- Creates grids, forms, menus, and actions"
echo "- Handles all admin UI concerns (responsive, accessible, intuitive)"
echo

# Change to worktree directory
cd "$WORKTREE_PATH" || exit 1

# Execute Claude command
print_color "$GREEN" "Executing Claude command..."
echo
claude "$CLAUDE_PROMPT"
