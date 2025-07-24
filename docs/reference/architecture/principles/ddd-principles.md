# Domain-Driven Design Principles

## Overview

This document outlines the core principles of Domain-Driven Design (DDD) as applied in this project.

## Core Concepts

### 1. Bounded Contexts
- Clear boundaries between different domains
- Each context has its own language and models
- Minimal coupling between contexts

### 2. Ubiquitous Language
- Consistent terminology across code and documentation
- Domain experts and developers use the same language
- Code reflects business concepts directly

### 3. Aggregates
- Consistency boundaries for related entities
- One aggregate root per aggregate
- External references only by ID

### 4. Value Objects
- Immutable objects defined by their attributes
- No identity concept
- Business logic embedded in the object

### 5. Domain Events
- Capture business state changes
- Enable loose coupling between contexts
- Foundation for event sourcing if needed

## Implementation Guidelines

### Domain Layer
- Pure PHP with no framework dependencies
- Business logic only
- Strong typing throughout

### Application Layer
- Use cases and orchestration
- Gateway pattern for entry points
- CQRS for read/write separation

### Infrastructure Layer
- Framework-specific implementations
- External service adapters
- Persistence mechanisms

## References
- See @docs/architecture/patterns/ for specific pattern implementations
- See @docs/agent/instructions/architecture.md for detailed guidelines