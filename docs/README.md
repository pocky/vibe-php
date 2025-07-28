# Vibe PHP Project Documentation

This directory contains all project documentation, organized to guide both human developers and AI agents.

## 🗺️ Documentation Structure

```mermaid
graph TD
    docs[📁 docs/] --> reference[🏗️ Standard Reference]
    docs --> contexts[📦 Business Contexts]
    docs --> agent[🤖 Agent Instructions]
    
    reference --> ref_arch[📐 Core Architecture]
    reference --> ref_dev[💻 Development Guides]
    reference --> arch_ref[📚 Integrations]
    
    contexts --> blog[📝 Blog]
    contexts --> security[🔐 Security]
    
    agent --> instructions[📋 Instructions]
    agent --> methodologies[📚 Methodologies]
    
    style docs fill:#e1f5fe
    style reference fill:#e8f5e8
    style contexts fill:#f3e5f5
    style agent fill:#fce4ec
```

```
docs/
├── reference/         # Standard architecture, patterns, and workflows for ALL projects.
│   ├── CLAUDE.md      # Entry point for the standard reference.
│   ├── architecture/  # Core architectural patterns (DDD, Hexagonal).
│   ├── development/   # Standard development guides (TDD, Git, Testing).
│   └── reference/     # Standard external documentation links.
├── contexts/          # Business-specific documentation for THIS project.
│   ├── blog/
│   └── security/
└── agent/             # Global instructions for AI agents.
```

## 🚀 Unified Spec-Driven Methodology

We use a unified approach that combines business vision with technical rigor.

```mermaid
graph LR
    subgraph "📋 Requirements Phase"
        PRD["📋 /spec:prd<br/>Business Vision +<br/>EARS Requirements"]
        REQ["/spec:requirements<br/>Detailed Requirements"]
        PRD --> REQ
    end
    
    subgraph "🏗️ Design Phase"
        PLAN["🏗️ /spec:plan<br/>Architecture +<br/>Technical Design"]
        DESIGN["/spec:design<br/>Domain Design<br/>(Optional)"]
        REQ --> PLAN
        PLAN -.-> DESIGN
    end
    
    subgraph "⚡ Implementation Phase"
        ORCH["⚡ /orchestrate<br/>Coordinate Implementation<br/>with Expert Agents"]
        QA["✅ /qa<br/>Quality Checks"]
        PLAN --> ORCH
        ORCH --> QA
    end
    
    style PRD fill:#fff3e0
    style PLAN fill:#e1f5fe
    style ACT fill:#e8f5e9
    style QA fill:#ffebee
```

### Quick Command Reference
| Command | Purpose | Approval Gate |
|---|---|---|
| `/spec:prd [context] [feature]` | Create PRD with business vision & EARS requirements | ✅ Required |
| `/spec:requirements` | Create detailed requirements and acceptance criteria | ✅ Required |
| `/spec:plan [context]` | Create technical architecture & design | ✅ Required |
| `/orchestrate` | Coordinate implementation with expert agents | ✅ Required |
| `/qa` | Run comprehensive quality checks | ✅ Final |


## 📍 Quick Navigation

### 🤖 I'm an AI agent
1.  **Start Here**: Read the root `CLAUDE.md` file for project-specific instructions.
2.  **Architecture & Patterns**: `@docs/reference/CLAUDE.md` is the entry point for all standard patterns and workflows.
3.  **Business Context**: `@docs/contexts/[context-name]/` contains the requirements for the specific feature you are working on.
4.  **Global Instructions**: `@docs/agent/instructions/` contains your general operating instructions.
5.  **Error Log**: Check `@docs/agent/errors.md` before attempting complex tasks.

### 🧑‍💻 I'm a Human Developer
1.  **Project Overview**: Start with this `README.md`.
2.  **To implement a feature**:
    -   Understand requirements in `@docs/contexts/[context-name]/`.
    -   Follow patterns from `@docs/reference/architecture/patterns/`.
    -   Use workflows from `@docs/reference/development/workflows/`.
3.  **To understand architecture**:
    -   Principles: `@docs/reference/architecture/principles/`
    -   Patterns: `@docs/reference/architecture/patterns/`
    -   Standards: `@docs/reference/architecture/standards/`

---
*This document is the single source of truth for navigating the project's documentation.*
