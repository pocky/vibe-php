# 🧪 Vibe PHP - Claude AI Test Project

⚠️ **ATTENTION: This is an experimental test project for Claude AI and PHP development. Use with EXTREME CAUTION or only as a source of inspiration.**

## ⚡ Warning

This project is:
- **Experimental**: Created to test Claude AI's capabilities with PHP development
- **Not Production-Ready**: Should NOT be used in production environments
- **Educational Only**: Best used as a learning resource or inspiration
- **Potentially Unstable**: May contain experimental patterns and untested code

## 🎯 Purpose

This repository serves as a testing ground for:
- Claude AI's ability to generate PHP code following DDD principles
- AI-assisted documentation and workflow optimization
- Experimental architectural patterns and implementations
- Testing AI agent collaboration workflows

## 🏗️ Architecture Overview

The project demonstrates:
- **Domain-Driven Design (DDD)** with bounded contexts
- **Hexagonal Architecture** with clear separation of concerns
- **CQRS Pattern** for command/query separation
- **Gateway Pattern** for application entry points
- **PHP 8.4** features and modern practices
- **Symfony 7.3** framework integration

## 📚 Documentation

Extensive documentation has been created to guide AI agents:
- `CLAUDE.md` - Main instructions for Claude AI
- `docs/AGENT_INDEX.md` - Central reference for all patterns and commands
- `docs/agent/` - AI agent-specific instructions and workflows
- `docs/reference/` - Pattern documentation and guidelines
- `.claude/commands/` - Custom Claude commands for development workflow
- `docs/reference/development/tools/makers/` - **DDD Makers documentation**:
  - `ddd-makers-guide.md` - Comprehensive guide for all DDD Makers
  - `quick-reference.md` - Quick reference for common commands
- `.claude/agents/maker-expert.md` - Expert agent for code generation using makers

## 🚀 If You Must Use This Project

### Prerequisites

#### Required System Dependencies
- **PHP 8.4+** - Latest PHP version with property hooks support
- **Docker & Docker Compose** - For containerized development environment
- **Composer 2.x** - PHP dependency management
- **Git** - Version control system

#### Optional AI Agent Tools
If using Claude AI agent features:
- **Claude CLI** - Install with: `npm install -g @anthropic-ai/claude-code`
- **Google Gemini CLI** - For large-scale code analysis ([installation guide](https://github.com/google/generative-ai-cli))
- **jq** - JSON processor for Claude-Gemini bridge
  - macOS: `brew install jq`
  - Ubuntu/Debian: `sudo apt-get install jq`
  - Other Linux: `sudo yum install jq` or equivalent
- **Node.js 18+** - Required for Claude CLI
- **Bash 4.0+** - For bridge scripts (macOS users: `brew install bash`)

#### Knowledge Requirements
- Basic understanding of Domain-Driven Design (DDD)
- Familiarity with Hexagonal Architecture
- Understanding of CQRS pattern (helpful but not required)

### Setup
```bash
# Clone the repository
git clone https://github.com/your-username/vibe-php.git
cd vibe-php

# Start Docker environment
docker compose up -d

# Install dependencies
docker compose exec app composer install

# Run quality checks
docker compose exec app composer qa
```

## ⚠️ Important Disclaimers

1. **No Warranty**: This code comes with NO WARRANTY of any kind
2. **No Support**: This is an experimental project with no official support
3. **Security**: Has NOT been audited for security vulnerabilities
4. **Performance**: Not optimized for production workloads
5. **Stability**: May contain breaking changes at any time

## 💡 Recommended Usage

### ✅ Good Uses:
- **Learning Resource**: Study the patterns and architecture
- **Inspiration**: Extract ideas for your own projects
- **AI Experimentation**: Test Claude AI workflows
- **Pattern Reference**: See DDD/CQRS/Hexagonal implementations

### ❌ Bad Uses:
- **Production Deployment**: Do NOT use in production
- **Copy-Paste Coding**: Understand before using any code
- **Business Critical**: Not suitable for important projects
- **As-Is Implementation**: Always adapt to your needs

## 🤖 AI Agent Features

This project includes optimized documentation for AI agents:
- Visual navigation with Mermaid diagrams
- Structured command system for development workflow
- Comprehensive pattern documentation
- Testing guidelines and best practices

### 🛠️ DDD Code Generation Tools

The project includes powerful Symfony Maker commands for DDD code generation, orchestrated by the **maker-expert agent**:

**Infrastructure Layer:**
- `make:infrastructure:entity` - Create Doctrine entities with repositories

**Domain Layer:**
- `make:domain:aggregate` - Create domain aggregates with events
- `make:domain:value-object` - Create value objects with validation
- `make:domain:id-generator` - Create ID generators for entities

**Application Layer:**
- `make:application:gateway` - Create gateways with middleware
- `make:application:command` - Create CQRS commands
- `make:application:query` - Create CQRS queries

**UI Layer:**
- `make:admin:resource` - Create Sylius Admin UI resources
- `make:api:resource` - Create API Platform resources

The **maker-expert agent** coordinates these makers in the correct order, ensuring proper dependencies and consistency. See `docs/reference/development/tools/makers/ddd-makers-guide.md` for complete documentation.

## 📝 License

Licensed under the European Union Public Licence v1.2 (EUPL-1.2). See [LICENSE](LICENSE) file.

## 🙏 Acknowledgments

This project was created as an experiment in AI-assisted development using:
- [Claude AI](https://claude.ai) by Anthropic
- [Symfony Framework](https://symfony.com)
- Modern PHP practices and patterns

---

**Remember**: This is a TEST PROJECT. Use it for learning and inspiration, but always write and review your own production code carefully.

🤖 Generated with [Claude Code](https://claude.ai/code)