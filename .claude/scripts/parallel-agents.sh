#!/usr/bin/env bash

# Parallel Agents Orchestration with Git Worktrees
# This script manages git worktrees for parallel Claude agent execution

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
PARENT_DIR="$(dirname "$PROJECT_ROOT")"

# Function to print colored output
print_color() {
    local color=$1
    shift
    echo -e "${color}$*${NC}"
}

# Function to show usage
usage() {
    cat << EOF
Usage: $0 <command> [options]

Commands:
    setup <feature> --context <context>    Create worktrees for parallel agents
    launch <feature> --context <context>   Launch all parallel agents automatically
    sync                                   Synchronize changes from worktrees
    status                                 Show status of all worktrees
    cleanup                                Remove all worktrees
    help                                   Show this help message

Options:
    --context <name>    Business context (e.g., blog, security, billing)
    --agents <list>     Comma-separated list of agents (default: api,admin)

Examples:
    $0 setup article-management --context blog
    $0 setup user-authentication --context security --agents api,admin,test
    $0 sync
    $0 cleanup

EOF
}

# Function to validate agent for worktree creation
validate_agent_for_worktree() {
    local agent=$1

    # Only API and Admin agents should use worktrees
    case $agent in
        api|admin)
            return 0
            ;;
        hexagonal|test)
            print_color "$YELLOW" "⚠ Agent '$agent' runs sequentially in orchestrator, no worktree needed"
            return 1
            ;;
        *)
            print_color "$RED" "❌ Unknown agent: $agent"
            return 1
            ;;
    esac
}

# Function to create a worktree
create_worktree() {
    local agent=$1
    local feature=$2
    local branch="feature/${feature}-${agent}"
    local worktree_path="${PARENT_DIR}/vibe-php-${agent}"

    # Validate agent type
    if ! validate_agent_for_worktree "$agent"; then
        return 1
    fi

    print_color "$BLUE" "Creating worktree for $agent agent..."

    # Check if worktree already exists
    if [[ -d "$worktree_path" ]]; then
        print_color "$YELLOW" "Worktree already exists at $worktree_path"
        return 0
    fi

    # Create feature branch if it doesn't exist
    if ! git rev-parse --verify "$branch" &>/dev/null; then
        git branch "$branch" HEAD
    fi

    # Create worktree
    git worktree add "$worktree_path" "$branch"

    print_color "$GREEN" "✓ Created worktree at $worktree_path on branch $branch"
}

# Function to setup worktrees for agents
setup_worktrees() {
    local feature=$1
    local context=$2
    local agents=$3

    print_color "$BLUE" "Setting up worktrees for feature: $feature in context: $context"
    echo

    # Split agents by comma
    IFS=',' read -ra AGENT_ARRAY <<< "$agents"

    # Filter out sequential agents and create worktrees only for parallel agents
    local parallel_agents=()
    local sequential_agents=()

    for agent in "${AGENT_ARRAY[@]}"; do
        case $agent in
            api|admin)
                parallel_agents+=("$agent")
                create_worktree "$agent" "$feature"
                ;;
            hexagonal|test)
                sequential_agents+=("$agent")
                print_color "$YELLOW" "ℹ️  Agent '$agent' runs sequentially in orchestrator (no worktree needed)"
                ;;
            *)
                print_color "$RED" "⚠️  Unknown agent: $agent"
                ;;
        esac
    done

    echo
    if [[ ${#parallel_agents[@]} -gt 0 ]]; then
        print_color "$GREEN" "✓ Worktrees created for parallel agents: ${parallel_agents[*]}"
    fi
    if [[ ${#sequential_agents[@]} -gt 0 ]]; then
        print_color "$YELLOW" "ℹ️  Sequential agents will run in orchestrator: ${sequential_agents[*]}"
    fi
    echo

    if [[ ${#parallel_agents[@]} -gt 0 ]]; then
        print_color "$YELLOW" "Next steps for parallel agents:"
        echo

        # Generate launch commands only for parallel agents
        for agent in "${parallel_agents[@]}"; do
            local worktree_path="${PARENT_DIR}/vibe-php-${agent}"
            echo "Terminal for $agent agent:"
            echo "  cd $worktree_path"

            case $agent in
                api)
                    echo "  claude \"/agent:api 'Create REST API for $feature in $context context'\""
                    ;;
                admin)
                    echo "  claude \"/agent:admin 'Create admin interface for $feature in $context context'\""
                    ;;
            esac
            echo
        done

        print_color "$BLUE" "Launch scripts available:"
        for agent in "${parallel_agents[@]}"; do
            if [[ -f "$SCRIPT_DIR/launch-${agent}-agent.sh" ]]; then
                echo "  $SCRIPT_DIR/launch-${agent}-agent.sh \"$feature\" \"$context\""
            fi
        done
        echo
        print_color "$GREEN" "Or launch all agents automatically with:"
        echo "  $0 launch $feature --context $context"
    fi

    if [[ ${#sequential_agents[@]} -gt 0 ]]; then
        echo
        print_color "$BLUE" "Sequential agents will be handled by:"
        echo "  /agent:orchestrate $feature --context $context --pattern collaborative"
    fi
}

# Function to synchronize changes from worktrees
sync_worktrees() {
    print_color "$BLUE" "Synchronizing changes from worktrees..."
    echo

    # Get list of worktrees
    local worktrees=$(git worktree list --porcelain | grep "^worktree" | cut -d' ' -f2)

    for worktree in $worktrees; do
        # Skip the main worktree
        if [[ "$worktree" == "$PROJECT_ROOT" ]]; then
            continue
        fi

        local agent=$(basename "$worktree" | sed 's/vibe-php-//')
        print_color "$YELLOW" "Checking $agent agent worktree..."

        cd "$worktree"

        # Check for uncommitted changes
        if [[ -n $(git status --porcelain) ]]; then
            print_color "$RED" "⚠ Uncommitted changes in $agent worktree:"
            git status --short
            echo
        fi

        # Get branch name
        local branch=$(git rev-parse --abbrev-ref HEAD)

        # Check for unpushed commits
        local unpushed=$(git log origin/"$branch".."$branch" --oneline 2>/dev/null | wc -l | tr -d ' ' || echo "0")
        if [[ $unpushed -gt 0 ]]; then
            print_color "$YELLOW" "📤 $unpushed unpushed commits in $agent worktree"
        fi

        cd - > /dev/null
    done

    echo
    print_color "$GREEN" "To merge changes back to main worktree:"
    echo "1. Commit and push changes in each worktree"
    echo "2. Create pull requests for each feature branch"
    echo "3. Or use: git merge <branch-name> in the main worktree"
}

# Function to show status of all worktrees
show_status() {
    print_color "$BLUE" "Worktree Status:"
    echo

    git worktree list

    echo
    print_color "$BLUE" "Branch Status:"
    echo

    # Show feature branches
    git branch -a | grep "feature/" | sed 's/remotes\/origin\///'  | sort | uniq
}

# Function to cleanup worktrees
cleanup_worktrees() {
    print_color "$YELLOW" "⚠ This will remove all worktrees except the main one."
    read -p "Are you sure? (y/N) " -n 1 -r
    echo

    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_color "$RED" "Cleanup cancelled."
        return 1
    fi

    print_color "$BLUE" "Cleaning up worktrees..."

    # Get list of worktrees with their branches
    local worktrees=$(git worktree list --porcelain | grep "^worktree" | cut -d' ' -f2)
    local branches_to_delete=()

    for worktree in $worktrees; do
        # Skip the main worktree
        if [[ "$worktree" == "$PROJECT_ROOT" ]]; then
            continue
        fi

        local agent=$(basename "$worktree" | sed 's/vibe-php-//')
        
        # Get the branch name for this worktree before removing it
        cd "$worktree" 2>/dev/null && local branch=$(git rev-parse --abbrev-ref HEAD)
        cd - > /dev/null
        
        if [[ -n "$branch" ]]; then
            branches_to_delete+=("$branch")
        fi
        
        print_color "$YELLOW" "Removing $agent worktree..."
        git worktree remove "$worktree" --force
    done

    # Prune worktree references
    git worktree prune

    # Delete the branches
    if [[ ${#branches_to_delete[@]} -gt 0 ]]; then
        print_color "$BLUE" "Deleting associated branches..."
        for branch in "${branches_to_delete[@]}"; do
            print_color "$YELLOW" "Deleting branch: $branch"
            git branch -D "$branch" 2>/dev/null || print_color "$RED" "Could not delete branch $branch"
        done
    fi

    print_color "$GREEN" "✓ All worktrees and branches cleaned up successfully!"
}

# Main script logic
main() {
    local command=${1:-help}

    case $command in
        setup)
            if [[ $# -lt 4 ]]; then
                print_color "$RED" "Error: Missing required arguments"
                usage
                exit 1
            fi

            local feature=$2
            local context=""
            local agents="api,admin"

            # Parse options
            shift 2
            while [[ $# -gt 0 ]]; do
                case $1 in
                    --context)
                        context=$2
                        shift 2
                        ;;
                    --agents)
                        agents=$2
                        shift 2
                        ;;
                    *)
                        print_color "$RED" "Unknown option: $1"
                        usage
                        exit 1
                        ;;
                esac
            done

            if [[ -z "$context" ]]; then
                print_color "$RED" "Error: --context is required"
                usage
                exit 1
            fi

            setup_worktrees "$feature" "$context" "$agents"
            ;;

        sync)
            sync_worktrees
            ;;

        status)
            show_status
            ;;

        cleanup)
            cleanup_worktrees
            ;;
            
        launch)
            if [[ $# -lt 4 ]]; then
                print_color "$RED" "Error: Missing required arguments"
                usage
                exit 1
            fi

            local feature=$2
            local context=""

            # Parse options
            shift 2
            while [[ $# -gt 0 ]]; do
                case $1 in
                    --context)
                        context=$2
                        shift 2
                        ;;
                    *)
                        print_color "$RED" "Unknown option: $1"
                        usage
                        exit 1
                        ;;
                esac
            done

            if [[ -z "$context" ]]; then
                print_color "$RED" "Error: --context is required"
                usage
                exit 1
            fi

            # Launch agents in parallel
            print_color "$BLUE" "🚀 Launching parallel agents for $feature in $context context"
            echo
            
            # Check if worktrees exist
            if [[ ! -d "${PARENT_DIR}/vibe-php-api" ]] || [[ ! -d "${PARENT_DIR}/vibe-php-admin" ]]; then
                print_color "$RED" "Error: Worktrees not found. Run 'setup' first."
                exit 1
            fi
            
            # Launch API agent in background
            print_color "$GREEN" "Launching API agent..."
            "$SCRIPT_DIR/launch-api-agent.sh" "$feature" "$context" &
            
            # Small delay to avoid terminal conflicts
            sleep 2
            
            # Launch Admin agent
            print_color "$GREEN" "Launching Admin agent..."
            "$SCRIPT_DIR/launch-admin-agent.sh" "$feature" "$context"
            ;;

        help)
            usage
            ;;

        *)
            print_color "$RED" "Unknown command: $command"
            usage
            exit 1
            ;;
    esac
}

# Run main function
main "$@"
