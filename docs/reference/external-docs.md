# External Documentation References

This file contains links to important external documentation that Claude should be aware of when working on this project.

## Core Tools Documentation

### Claude Code
- **Official Documentation**: https://docs.anthropic.com/en/docs/claude-code
- **Quickstart**: https://docs.anthropic.com/en/docs/claude-code/quickstart
- **Common Workflows**: https://docs.anthropic.com/en/docs/claude-code/common-workflows
- **Memory Management**: https://docs.anthropic.com/en/docs/claude-code/memory
- **IDE Integrations**: https://docs.anthropic.com/en/docs/claude-code/ide-integrations
- **MCP (Model Context Protocol)**: https://docs.anthropic.com/en/docs/claude-code/mcp
- **CLI Reference**: https://docs.anthropic.com/en/docs/claude-code/cli-reference
- **Interactive Mode**: https://docs.anthropic.com/en/docs/claude-code/interactive-mode
- **Slash Commands**: https://docs.anthropic.com/en/docs/claude-code/slash-commands
- **Settings**: https://docs.anthropic.com/en/docs/claude-code/settings

### Docker
- **Official Documentation**: https://docs.docker.com/
- **Dockerfile Reference**: https://docs.docker.com/engine/reference/builder/
- **Compose File Reference**: https://docs.docker.com/compose/compose-file/
- **Best Practices**: https://docs.docker.com/develop/dev-best-practices/
- **Multi-stage Builds**: https://docs.docker.com/build/building/multi-stage/
- **PHP Images**: https://hub.docker.com/_/php

### Composer
- **Official Documentation**: https://getcomposer.org/doc/
- **Command Reference**: https://getcomposer.org/doc/03-cli.md
- **composer.json Schema**: https://getcomposer.org/doc/04-schema.md
- **Autoloading**: https://getcomposer.org/doc/04-schema.md#autoload
- **Scripts**: https://getcomposer.org/doc/articles/scripts.md
- **Versions & Constraints**: https://getcomposer.org/doc/articles/versions.md

### GitHub CLI
- **Official Documentation**: https://cli.github.com/
- **Manual**: https://cli.github.com/manual/
- **Command Reference**: https://cli.github.com/manual/gh
- **PR Management**: https://cli.github.com/manual/gh_pr
- **Issue Management**: https://cli.github.com/manual/gh_issue
- **Workflow Commands**: https://cli.github.com/manual/gh_workflow
- **API Access**: https://cli.github.com/manual/gh_api

## Framework Documentation

### Symfony 7.3
- **Official Docs**: https://symfony.com/doc/7.3/index.html
- **Components**: https://symfony.com/doc/current/components/index.html
- **Bundles**: https://symfony.com/doc/current/bundles.html
- **Cookbook**: https://symfony.com/doc/current/cookbook/index.html

### PHP
- **PHP Manual**: https://www.php.net/manual/en/
- **PHP 8.4 Features**: https://www.php.net/releases/8.4/en.php
- **PHP 8.3 Features**: https://www.php.net/releases/8.3/en.php
- **PSR Standards**: https://www.php-fig.org/psr/
- **PHP RFCs**: https://wiki.php.net/rfc

## Common Tasks References

### Sylius Stack
- **Documentation**: https://stack.sylius.com/

### Symfony Specific
- **Forms**: https://symfony.com/doc/current/forms.html
- **Validation**: https://symfony.com/doc/current/validation.html
- **Doctrine ORM**: https://symfony.com/doc/current/doctrine.html
- **Messenger**: https://symfony.com/doc/current/messenger.html
- **Security**: https://symfony.com/doc/current/security.html

### Testing
- **PHPUnit**: https://phpunit.de/documentation.html
- **Symfony Testing**: https://symfony.com/doc/current/testing.html

### Code Quality
- **PHPStan**: https://phpstan.org/user-guide/getting-started
- **PHP CS Fixer**: https://cs.symfony.com/

## How to Use These References

When you need specific information:
1. Ask: "Check Symfony docs for form validation"
2. Reference: "According to https://symfony.com/doc/current/validation.html"
3. Context: "I need to implement feature X, check the Symfony docs"

## Quick Reference Patterns

### For Claude Code Features
```
"How do I use [feature] in Claude Code?"
→ Check: https://docs.anthropic.com/en/docs/claude-code/[feature]
```

### For Docker
```
"What's the best practice for [task] in Docker?"
→ Check: https://docs.docker.com/develop/dev-best-practices/
```

### For Composer
```
"How do I configure [feature] in composer.json?"
→ Check: https://getcomposer.org/doc/04-schema.md#[feature]
```

### For Symfony Features
```
"How do I [task] in Symfony?"
→ Check: https://symfony.com/doc/current/[feature].html
```

### For PHP Features
```
"What's the syntax for [feature] in PHP 8.4?"
→ Check: https://www.php.net/manual/en/[feature]
```

## Important Documentation Pages

### Must-Know for This Project

#### Claude Code Essentials
1. **CLAUDE.md Files**: https://docs.anthropic.com/en/docs/claude-code/memory#claude-md-files
2. **Custom Commands**: https://docs.anthropic.com/en/docs/claude-code/slash-commands#custom-commands
3. **Working with Git**: https://docs.anthropic.com/en/docs/claude-code/common-workflows#git-workflows
4. **IDE Integration**: https://docs.anthropic.com/en/docs/claude-code/ide-integrations

#### Docker Essentials
1. **Docker Compose**: https://docs.docker.com/compose/
2. **PHP Docker Images**: https://hub.docker.com/_/php
3. **Volume Management**: https://docs.docker.com/storage/volumes/
4. **Networking**: https://docs.docker.com/network/

#### Composer Essentials
1. **Autoloading**: https://getcomposer.org/doc/01-basic-usage.md#autoloading
2. **Platform Requirements**: https://getcomposer.org/doc/06-config.md#platform
3. **Scripts**: https://getcomposer.org/doc/articles/scripts.md
4. **Versions**: https://getcomposer.org/doc/articles/versions.md

#### Symfony Essentials
1. **DDD in Symfony**: https://symfony.com/doc/current/doctrine/repository.html
2. **Service Container**: https://symfony.com/doc/current/service_container.html
3. **Event System**: https://symfony.com/doc/current/event_dispatcher.html
4. **Console Commands**: https://symfony.com/doc/current/console.html

## API Documentation

### When Implementing New Features
- Always check: https://api.symfony.com/7.3/index.html
- Search for the class/interface you're working with
- Review available methods and their signatures

## Community Resources
- **SymfonyCasts**: https://symfonycasts.com/
- **Symfony Slack**: https://symfony.com/slack
- **Stack Overflow**: https://stackoverflow.com/questions/tagged/symfony

## Note for Claude
When I reference external documentation, you can:
1. Acknowledge the reference
2. Apply the patterns from the documentation
3. Adapt examples to our project structure
4. Follow the conventions shown in the docs
