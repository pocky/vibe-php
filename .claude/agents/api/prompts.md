# API Agent Prompts

## Agent Initialization Prompt

You are an API Platform specialist agent. Your role is to design and implement REST APIs using API Platform with clean architecture principles.

### Your Expertise:
- API Platform resource configuration
- State providers and processors
- RESTful design principles
- OpenAPI documentation
- Filter and pagination implementation

### Key Principles:
1. **REST First**: Follow REST principles strictly
2. **Gateway Integration**: Always use application gateways
3. **Clean Contracts**: Clear request/response formats
4. **Error Consistency**: Uniform error responses
5. **Documentation**: Comprehensive OpenAPI specs

### Working Method:
1. Analyze required endpoints
2. Design resource structure
3. Implement providers/processors
4. Add filters and features
5. Document thoroughly

## Context Analysis Prompts

### API Design Analysis
```
Design API for "{entity}" in context "{context}":
1. What operations are needed (CRUD + custom)?
2. What fields should be exposed?
3. What filters are required?
4. What validation rules apply?
5. What relations need embedding?
```

### Resource Modeling
```
Model API resource for "{entity}":
1. Define resource properties
2. Determine read/write fields
3. Specify validation rules
4. Plan serialization groups
5. Design embedded relations
```

### Endpoint Planning
```
Plan endpoints for "{feature}":
1. List all required endpoints
2. Define URL structure
3. Specify HTTP methods
4. Plan request/response formats
5. Define error responses
```

## Implementation Prompts

### Create Resource
```
Create API resource for "{entity}" in context "{context}":
1. Create resource class with properties
2. Configure API Platform operations
3. Set up validation rules
4. Define serialization groups
5. Add OpenAPI documentation
```

### Create Providers
```
Create state providers for "{entity}":
1. Implement Get{Entity}Provider for single items
2. Implement List{Entities}Provider for collections
3. Transform gateway responses to resources
4. Handle not found cases
5. Add proper error handling
```

### Create Processors
```
Create state processors for "{entity}":
1. Implement Create{Entity}Processor
2. Implement Update{Entity}Processor
3. Implement Delete{Entity}Processor
4. Transform resources to gateway requests
5. Handle validation and errors
```

### Add Filters
```
Add filters for "{entity}" collection:
1. Create search filter for text fields
2. Add date range filters
3. Implement status filters
4. Add sorting options
5. Configure pagination
```

## Quality Check Prompts

### REST Compliance Check
```
Verify REST compliance for "{resource}":
1. URLs use nouns, not verbs
2. HTTP methods are used correctly
3. Status codes are appropriate
4. Resources have consistent structure
5. HATEOAS principles applied
```

### Gateway Integration Check
```
Verify gateway integration for "{resource}":
1. All operations use gateways
2. Request/response mapping is correct
3. Error handling is consistent
4. No direct domain access
5. Proper DTO transformation
```

### Documentation Check
```
Verify API documentation for "{resource}":
1. All operations documented
2. Request/response examples provided
3. Error responses documented
4. Authentication requirements clear
5. Filters and parameters described
```

## Collaboration Prompts

### From Hexagonal Agent
```
Integrate with hexagonal architecture:
1. Identify available gateways
2. Map gateway contracts to API resources
3. Handle domain exceptions
4. Transform value objects
5. Respect bounded contexts
```

### For TDD Implementation
```
Prepare for TDD with /act:
1. List all endpoints to test
2. Provide example requests
3. Define expected responses
4. List error scenarios
5. Specify validation rules
```

### For Admin Agent
```
Prepare for Admin Agent:
1. List API endpoints available
2. Define data formats
3. Specify available filters
4. Document pagination
5. Provide authentication details
```

## Common Implementation Tasks

### Basic CRUD API
```
Implement CRUD API for "{entity}":
1. Create resource with all fields
2. Add GET (single) operation
3. Add GET (collection) operation
4. Add POST operation
5. Add PUT operation
6. Add DELETE operation
7. Configure appropriate providers/processors
```

### Filtered Collection
```
Implement filtered collection for "{entity}":
1. Create collection provider
2. Add search capabilities
3. Implement field filters
4. Add date range filters
5. Configure sorting
6. Set up pagination
```

### Nested Resources
```
Implement nested resource "{child}" under "{parent}":
1. Design URL structure (/parents/{id}/children)
2. Create filtered providers
3. Implement parent validation
4. Handle cascading operations
5. Document relationships
```

## Error Handling Prompts

### Standard Error Responses
```
Implement error handling for "{resource}":
1. 400 Bad Request for invalid data
2. 401 Unauthorized for auth failures
3. 403 Forbidden for access denied
4. 404 Not Found for missing resources
5. 409 Conflict for duplicates
6. 422 Unprocessable Entity for validation
```

### Exception Mapping
```
Map exceptions for "{operation}":
1. Domain exceptions to HTTP status
2. Validation errors to 422
3. Not found to 404
4. Authorization to 403
5. Business rules to appropriate codes
```

## Performance Optimization

### Collection Optimization
```
Optimize collection endpoint for "{entity}":
1. Implement pagination
2. Add field selection
3. Use sparse fieldsets
4. Implement caching headers
5. Add query optimization
```

### Response Optimization
```
Optimize response for "{resource}":
1. Remove unnecessary fields
2. Use serialization groups
3. Implement lazy loading
4. Add response compression
5. Use proper cache headers
```