# US-007: Manage User Permissions

## Business Context

### From PRD
Role-based access control is critical for maintaining content security and proper workflow. This feature enables administrators to manage user roles and permissions within the blog system.

### Business Value
- Ensures content security and proper workflow
- Enables delegation of responsibilities
- Protects sensitive operations
- Maintains audit trail for compliance

## User Story

**As a** blog administrator  
**I want** to manage user roles and permissions  
**So that** I can control who can create and edit content

## Functional Requirements

### Main Flow
1. Administrator accesses user management interface
2. Administrator views list of users with current roles
3. Administrator assigns role to user (Admin, Editor, Author, Guest)
4. System updates permissions immediately
5. User's access reflects new permissions
6. Change is logged in audit trail

### Alternative Flows
- Bulk role assignment for multiple users
- Temporary permission elevation
- Role delegation by editors
- Permission inheritance from groups

### Business Rules
- Can assign roles: Admin, Editor, Author, Contributor
- Role-based access to features and content
- Can modify permissions for existing users
- Audit log of permission changes
- Bulk user management capabilities
- Only admins can manage permissions

## Technical Implementation

### From Technical Plan
Permissions use policy-based authorization with caching for performance.

### Architecture Components
- **Domain**: 
  - `BlogRole` enum (Admin, Editor, Author, Guest)
  - `Permission` value objects
  - `RoleAssigned`, `PermissionChanged` events
- **Application**: 
  - `AssignRole\Gateway`
  - `CheckPermission\Query`
  - Bulk operations handler
- **Infrastructure**: 
  - Role-based voters
  - Permission cache with TTL
  - Audit log storage
- **UI**: 
  - User management dashboard
  - Role assignment interface

### Database Changes
- `blog_user_roles` table:
  - user_email (identifier)
  - role
  - assigned_by
  - assigned_at
- `blog_audit_log` table for changes

## Acceptance Criteria

### Functional Criteria
- [ ] Given user list, when viewing, then see all users with current roles
- [ ] Given selecting user, when assigning role, then permissions update immediately
- [ ] Given role change, when completed, then audit log entry created
- [ ] Given bulk selection, when applying role, then all users updated
- [ ] Given permission check, when accessing feature, then respect role limits

### Non-Functional Criteria
- [ ] Performance: Permission checks < 50ms
- [ ] Security: Audit trail tamper-proof
- [ ] Scalability: Handle 1000+ users
- [ ] UX: Clear role descriptions

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Manage user permissions
  As a blog administrator
  I want to manage user roles
  So that I can control access

  Background:
    Given I am logged in as an administrator
    And these users exist:
      | email           | current_role |
      | sarah@blog.com  | Author       |
      | mark@blog.com   | Author       |
      | alex@blog.com   | Guest        |

  Scenario: Assign editor role
    When I navigate to user management
    And I select user "sarah@blog.com"
    And I assign role "Editor"
    Then Sarah should have Editor permissions
    And audit log should show:
      | sarah@blog.com | Author -> Editor | [timestamp] | admin@blog.com |

  Scenario: Bulk role assignment
    When I select users:
      | mark@blog.com |
      | alex@blog.com |
    And I choose "Bulk Actions" > "Assign Role"
    And I select "Author" role
    Then both users should have Author role
    And audit log should have 2 entries

  Scenario: Role-based access control
    Given Sarah has "Editor" role
    When Sarah logs in
    Then she should see "Editorial Dashboard"
    And she should access review queue
    But she should not see "User Management"

  Scenario: Permission inheritance
    Given role hierarchy:
      | Admin  | All permissions        |
      | Editor | Review + Author perms  |
      | Author | Create + Edit own      |
      | Guest  | Create only            |
    When user with Editor role accesses system
    Then they should have all Author permissions
    Plus editorial permissions

  Scenario: Audit trail
    When I view audit log
    Then I should see all permission changes:
      | User    | Change         | Date       | Changed By |
      | sarah   | Author->Editor | 2024-01-10 | admin      |
    And entries should be immutable
```

### Unit Test Coverage
- [ ] Role assignment logic
- [ ] Permission inheritance
- [ ] Audit log integrity
- [ ] Bulk operations
- [ ] Cache invalidation

## Dependencies

### Depends On
- Blog user identification system
- Authentication infrastructure

### Blocks
- All role-dependent features
- Editorial workflow
- Content access control

## Implementation Notes

### Risks
- Permission cache inconsistency
- Complex permission inheritance
- Audit log storage growth

### Decisions
- Email as user identifier within blog context
- Simple role hierarchy (no custom permissions)
- Immutable audit log with retention policy
- 5-minute permission cache TTL

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance criteria met
- [ ] Security review completed

## References

- PRD: @docs/contexts/blog/prd.md#us-007-manage-user-permissions
- Technical Plan: @docs/contexts/blog/technical-plan.md#authorization
- API Documentation: POST /api/users/{email}/role