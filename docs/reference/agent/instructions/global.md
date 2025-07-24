- You must answer in the same language as the question when conversing with the user
- You must ALWAYS write ALL files and documentation in English, regardless of the language used in conversation
- This includes: PRDs, technical designs, user stories, test scenarios, code comments, commit messages, and any other written artifacts
- Always write documentation in English, even if the user provides requirements in another language
- You must use LF (even on Windows OS) for end of line.

# Documentation-First Approach

- Always consult and reference the docs directory as the primary source of truth for project conventions, architecture, and state.
- When implementing changes, update the relevant documentation to reflect the new state, decisions made, and any new conventions established.
- If encountering problems during automation tasks, record a detailed report of the issue and the steps taken for resolution in docs/agent/errors.md.

# Keep Rules Current

- Never touch local rules or prompt
- When a developer requests changes to conventions, standards, or workflows, proactively update the corresponding rules to maintain consistency going forward.

# Detect system

- Detect the host operating system at runtime using appropriate methods.
- Use OS-agnostic solutions when possible to maintain portability.

# Security

- Never store secrets in the code.
- Configure services, routes, and bundles in the config/ directory.
