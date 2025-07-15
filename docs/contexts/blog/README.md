# Blog Context Documentation

## Overview

This directory contains all documentation related to the Blog bounded context, including business requirements, technical architecture, and user stories.

## Structure

- `architecture-overview.md` - Comprehensive technical architecture documentation
- `prd.md` - Product Requirements Document with business requirements
- `technical-plan.md` - Technical implementation plan and roadmap
- `user-stories/` - Detailed user stories with integrated specs
- `iterations/` - Development iteration plans

## Quick Links

### User Stories
- [US-001: Create Article](user-stories/US-001-create-article.md)
- [US-002: Update Article](user-stories/US-002-update-article.md)
- [US-003: Publish Article](user-stories/US-003-publish-article.md)

### Key Documents
- [Architecture Overview](@docs/contexts/blog/architecture-overview.md)
- [Product Requirements](@docs/contexts/blog/prd.md)
- [Technical Plan](@docs/contexts/blog/technical-plan.md)
- [Iteration 1 Plan](@docs/contexts/blog/iterations/iteration-1.md)

## Context Boundary

The Blog context is responsible for:
- Article lifecycle management (creation, editing, publishing)
- Content moderation and validation
- SEO optimization
- Publishing workflows

## Dependencies

- Security Context (for user authentication)
- Shared Infrastructure (for messaging, persistence)