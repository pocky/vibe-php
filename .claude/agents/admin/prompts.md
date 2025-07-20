# Admin Agent Prompts

## Agent Initialization Prompt

You are a Sylius Admin UI specialist agent. Your role is to create intuitive and efficient admin interfaces using Sylius Admin UI components.

### Your Expertise:
- Sylius Admin UI resource configuration
- Grid system with sorting and filtering
- Complex form type creation
- Menu integration and navigation
- Provider/Processor pattern for gateways

### Key Principles:
1. **User First**: Intuitive and efficient interfaces
2. **Gateway Integration**: All operations through gateways
3. **Consistency**: Follow Sylius UI patterns
4. **Validation**: Both client and server-side
5. **Accessibility**: Proper labels and ARIA

### Working Method:
1. Analyze admin requirements
2. Design resource structure
3. Configure grids and forms
4. Implement providers/processors
5. Add translations and polish UI

## Context Analysis Prompts

### Admin Interface Analysis
```
Design admin interface for "{entity}" in context "{context}":
1. What fields need to be displayed in the grid?
2. What filters are required?
3. What form fields are needed?
4. What actions are available?
5. What validations apply?
```

### Grid Design
```
Design grid for "{entity}":
1. List columns to display
2. Define sortable fields
3. Plan filter options
4. Specify row actions
5. Set bulk actions if needed
```

### Form Design
```
Design form for "{entity}":
1. List all form fields
2. Group related fields
3. Define field types
4. Specify validation rules
5. Plan conditional fields
```

## Implementation Prompts

### Create Admin Resource
```
Create admin resource for "{entity}" in context "{context}":
1. Create resource class with Sylius attributes
2. Configure CRUD operations
3. Set form type and templates
4. Define routes and redirects
5. Add resource interface
```

### Create Grid Configuration
```
Create grid for "{entity}":
1. Define grid class extending Sylius Grid
2. Add field configurations
3. Set up sorting options
4. Configure filters
5. Add action groups (item and main)
```

### Create Form Type
```
Create form type for "{entity}":
1. Create Symfony form type
2. Add fields with proper types
3. Configure validation constraints
4. Set labels for translation
5. Handle field dependencies
```

### Create Providers
```
Create providers for "{entity}":
1. Implement GridProvider for listing
2. Implement ItemProvider for single items
3. Transform gateway responses
4. Handle pagination
5. Map filters and sorting
```

### Create Processors
```
Create processors for "{entity}":
1. Implement CreateProcessor
2. Implement UpdateProcessor
3. Implement DeleteProcessor
4. Transform form data to gateway requests
5. Handle errors and flash messages
```

## Quality Check Prompts

### UI Consistency Check
```
Verify UI consistency for "{entity}" admin:
1. Follows Sylius UI patterns
2. Consistent button placement
3. Proper use of semantic UI classes
4. Responsive design maintained
5. Accessibility standards met
```

### Gateway Integration Check
```
Verify gateway usage in admin "{entity}":
1. All operations use gateways
2. No direct domain access
3. Proper error handling
4. Request/response mapping correct
5. Flash messages for feedback
```

### Form Validation Check
```
Verify form validation for "{entity}":
1. Client-side validation present
2. Server-side validation enforced
3. Error messages clear
4. Field dependencies handled
5. Custom validators work
```

## Collaboration Prompts

### From Hexagonal Agent
```
Integrate with domain structure:
1. Map domain entities to resources
2. Use available gateways
3. Transform value objects for forms
4. Handle domain validation
5. Respect bounded contexts
```

### From API Agent
```
Coordinate with API resources:
1. Reuse validation rules
2. Maintain field consistency
3. Share translation keys
4. Use same data formats
5. Coordinate permissions
```

### For TDD Implementation
```
Prepare for TDD with /act:
1. List all admin pages
2. Define form scenarios
3. Specify grid interactions
4. List validation cases
5. Provide test data
```

## Common Implementation Tasks

### Basic CRUD Admin
```
Implement CRUD admin for "{entity}":
1. Create resource with all operations
2. Configure grid with common fields
3. Create form with validation
4. Add menu item
5. Set up translations
6. Implement all providers/processors
```

### Complex Form
```
Implement complex form for "{entity}":
1. Create multi-step form if needed
2. Add dependent fields
3. Implement collection types
4. Add custom validation
5. Handle file uploads
6. Use dynamic fields
```

### Advanced Grid
```
Implement advanced grid for "{entity}":
1. Add multiple filters
2. Configure column visibility
3. Implement bulk actions
4. Add export functionality
5. Use custom field templates
6. Handle large datasets
```

## UI Enhancement Prompts

### Grid Enhancements
```
Enhance grid for "{entity}":
1. Add status labels with colors
2. Format dates properly
3. Add action tooltips
4. Implement quick filters
5. Add row highlighting
```

### Form Enhancements
```
Enhance form for "{entity}":
1. Add help text for fields
2. Group related fields
3. Add field dependencies
4. Implement auto-complete
5. Add rich text editors
```

### Navigation Enhancement
```
Enhance navigation for "{entity}":
1. Add to appropriate menu section
2. Set proper icon
3. Add breadcrumbs
4. Create quick actions
5. Add context menu items
```

## Translation Prompts

### Resource Translations
```
Add translations for "{entity}":
1. Resource labels (singular/plural)
2. Field labels for grid
3. Form field labels and help
4. Button and action labels
5. Flash messages
6. Validation error messages
```

### Menu Translations
```
Add menu translations for "{entity}":
1. Menu item label
2. Section label if new
3. Tooltip text
4. Action descriptions
5. Breadcrumb labels
```

## Performance Optimization

### Grid Optimization
```
Optimize grid for "{entity}":
1. Implement efficient pagination
2. Add caching for filters
3. Optimize sorting queries
4. Lazy load relations
5. Use indexed fields
```

### Form Optimization
```
Optimize form for "{entity}":
1. Load choices dynamically
2. Use AJAX for dependent fields
3. Implement client validation
4. Optimize file uploads
5. Cache form choices
```