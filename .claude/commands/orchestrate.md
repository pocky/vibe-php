---
description: Orchestrate specialized agents for feature implementation with smart selection
allowed-tools: Task, Read, Bash, TodoWrite
args:
  - name: feature-name
    description: Feature name (matches user story filename)
    required: true
  - name: context
    description: Business context (blog, security, payment, etc.)
    required: true
  - name: agents
    description: Agents to use (auto-selected based on user story)
    required: false
    default: auto
  - name: pattern
    description: Execution pattern (collaborative recommended)
    required: false
    default: collaborative
---

# Orchestrating {{feature-name}} in {{context}}

## 🧠 Smart Agent Selection

I analyze user stories to select only required agents:
- **UI: None** → Skip API/Admin agents
- **Foundation stories** → Domain modeling only
- **Feature stories** → Include UI agents as needed

## Pre-Orchestration Check

[Ensure Docker is running:
Bash: .claude/scripts/ensure-docker.sh
Description: Verify Docker environment]

[Create orchestration tasks:
TodoWrite:
- 🎯 Analyze {{feature-name}} requirements (orch-1, in_progress, high)
- 🏗️ Phase 2.1: Domain modeling (orch-2, pending, high)
- 🔨 Phase 2.2: Code generation (orch-3, pending, high)
- 🧪 Phase 2.3: TDD implementation (orch-4, pending, high)
- 🌐 Phase 2.4: UI implementation (orch-5, pending, medium)
- ✅ Phase 3: Quality assurance (orch-6, pending, high)]

## Phase 1: Requirements Analysis

[Read user story: docs/contexts/{{context}}/requirements/user-stories/{{feature-name}}.md]

Based on the user story:
- **Components needed**: {{detected-components}}
- **Selected agents**: {{smart-agent-selection}}

## Phase 2: Agent Execution

### 2.1 Domain Modeling

[Launch domain expert:
Task: Design domain model for {{feature-name}}
Prompt: "As domain-expert, create the complete domain model for {{feature-name}} in {{context}}:
1. Analyze requirements thoroughly
2. Design all value objects, aggregates, services, events
3. Define repository interfaces and gateways
4. Focus on business rules and ubiquitous language
5. Prepare comprehensive design for scaffolding"]

### 2.2 Code Generation

[Launch maker expert:
Task: Generate DDD structure using makers
Prompt: "As maker-expert, generate the complete structure for {{feature-name}}:
1. Review domain model from Phase 2.1
2. Generate value objects, aggregates, infrastructure
3. Create commands, queries, gateways, resources
4. Run QA after generation
5. Document generated components in tasks.md"]

### 2.3 TDD Implementation

[Launch TDD expert:
Task: Implement ALL layers using TDD
Prompt: "As tdd-expert, complete ALL implementations for {{feature-name}}:

CRITICAL Requirements:
1. Domain Layer: All business logic with tests
2. Infrastructure Layer (MANDATORY):
   - Doctrine entities at Infrastructure/Persistence/Doctrine/Entity/
   - Repository implementations
   - ID generators at Infrastructure/Identity/
   - Database migrations
3. Application Layer: All gateways and handlers

Follow Red-Green-Refactor for EVERY component.
Task is NOT complete without ALL layers implemented."]

### 2.4 UI Implementation (if needed)

{{#if needs-ui}}
**Parallel Execution with Git Worktrees**

[Setup worktrees:
Bash: .claude/scripts/parallel-agents.sh setup {{feature-name}} --context {{context}}
Description: Create parallel workspaces]

**Open 2 new terminals:**

Terminal 1 - API:
```bash
cd ../vibe-php-api
claude "Use api-platform-expert to create REST API for {{feature-name}}"
```

Terminal 2 - Admin:
```bash
cd ../vibe-php-admin
claude "Use admin-ui-expert to create admin interface for {{feature-name}}"
```

[Track parallel work:
TodoWrite:
- 🌐 API: Working in parallel (parallel-api, in_progress, high)
- 🖥️ Admin: Working in parallel (parallel-admin, in_progress, high)]

Wait for both agents to complete, then:
[Sync worktrees:
Bash: .claude/scripts/parallel-agents.sh sync
Description: Check parallel agent status]
{{/if}}

## Phase 3: Verification & QA

[Verify infrastructure:
- Check Doctrine entities exist
- Verify repositories implemented
- Confirm migrations created]

[Run quality assurance:
/qa fix all]

## Completion

✅ Orchestration complete for {{feature-name}}!

**Next steps:**
1. Review implementation
2. Create PR if satisfied
3. Run `/qa` for final checks

**Monitoring:**
- Check agent outputs as they complete
- Use `.claude/scripts/parallel-agents.sh status` for UI agents
- Review quality metrics from final QA