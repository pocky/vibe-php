---
name: report
description: Generate project documentation, ADRs, and retrospective reports
args:
  - name: type
    description: Type of report (adr, retrospective, status, documentation)
    required: false
    default: status
---

I'll help you create project documentation and reports. Let me analyze the current state and generate the appropriate documentation.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "📊 Analyze project state - Review recent changes and decisions",
    "status": "in_progress",
    "priority": "high"
  },
  {
    "id": "2",
    "content": "📝 Document decisions - Create/update ADRs if needed",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3",
    "content": "📚 Update documentation - Ensure docs reflect current state",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "4",
    "content": "🔍 Generate report - Create requested report type",
    "status": "pending",
    "priority": "high"
  }
]</parameter>
</invoke>
</function_calls>

## Report Type: {{type}}

{{#if (eq type "adr")}}
### Creating Architecture Decision Record

I'll create an ADR following the standard format. Please provide:

1. **What architectural decision was made?**
2. **What problem does it solve?**
3. **What alternatives were considered?**
4. **What are the consequences?**

I'll create the ADR in `docs/adr/` with proper numbering and formatting.

{{else if (eq type "retrospective")}}
### Conducting Retrospective

Let's review the recent work and capture lessons learned:

1. **What went well?** (Successes to repeat)
2. **What could be improved?** (Areas for enhancement)
3. **What did we learn?** (Key insights)
4. **Action items?** (Concrete next steps)

{{else if (eq type "documentation")}}
### Updating Project Documentation

I'll review and update:

1. **README.md** - Current project state and setup
2. **Architecture docs** - System design updates
3. **API documentation** - Endpoint changes
4. **Development guides** - New workflows or patterns
5. **CLAUDE.md** - AI agent instructions

{{else}}
### Project Status Report

Let me analyze the current project state and provide a comprehensive status report.

<function_calls>
<invoke name="Bash">
<parameter name="command">git log --oneline --since="1 week ago" --pretty=format:"%h %s" | head -20</parameter>
<parameter name="description">Get recent commits for status report</parameter>
</invoke>
</function_calls>

I'll generate a status report including:
- Recent changes and commits
- Current technical decisions
- Documentation status
- Identified risks or issues
- Recommended next steps
{{/if}}

### Standard Report Sections

Regardless of type, I'll ensure:

1. **Context** - Current project state and recent activities
2. **Decisions** - Technical choices and rationale
3. **Documentation** - Updates needed or completed
4. **Metrics** - Progress indicators if applicable
5. **Next Steps** - Clear action items

### Documentation Structure

```
docs/
├── adr/                    # Architecture Decision Records
│   ├── 001-ddd-structure.md
│   └── 002-[next-decision].md
├── architecture/           # System design docs
├── api/                    # API documentation
└── guides/                 # Development guides
```

### What would you like me to focus on?

Please provide:
- Specific decisions or changes to document
- Time period to cover (last sprint, week, etc.)
- Any particular concerns or areas of focus
- Stakeholders who need this report

I'll create comprehensive documentation that captures the current state and provides valuable insights for the team.