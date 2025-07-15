# US-016: Upload and Manage Media Files

## Business Context

### From PRD
Rich media content enhances article engagement and readability. A robust media management system enables authors to easily incorporate images, documents, and other files into their content.

### Business Value
- Enriches content quality
- Improves reader engagement
- Streamlines media workflow
- Ensures consistent media handling

## User Story

**As an** author  
**I want** to upload and manage media files  
**So that** I can enrich my articles with visual content

## Functional Requirements

### Main Flow
1. Author clicks media button in editor
2. Media library modal opens
3. Author drags files to upload area
4. System validates and optimizes files
5. Author adds alt text for accessibility
6. Author inserts media into article
7. Media displays in article

### Alternative Flows
- Upload multiple files at once
- Search existing media library
- Replace existing media
- Delete unused media

### Business Rules
- Drag & drop for image upload
- Supported formats: JPG, PNG, GIF, PDF, DOC
- Maximum 10MB per file
- Media library with search
- Mandatory alt text for images
- Automatic image optimization

## Technical Implementation

### From Technical Plan
Media handling with optimization pipeline, CDN integration, and library management.

### Architecture Components
- **Domain**: 
  - `MediaFile` aggregate
  - `AltText` value object
  - File validation rules
  - `MediaUploaded` event
- **Application**: 
  - `UploadMedia\Gateway`
  - `OptimizeImage\Service`
  - Media library queries
- **Infrastructure**: 
  - File storage adapter
  - Image optimization tools
  - CDN integration
  - Virus scanning
- **UI**: 
  - Drag-drop upload zone
  - Media library browser
  - Image editor basic tools

### Database Changes
- `blog_media` table:
  - id, filename, original_name
  - mime_type, size, dimensions
  - alt_text, caption
  - uploaded_by, uploaded_at
- Media usage tracking

## Acceptance Criteria

### Functional Criteria
- [ ] Given drag-drop, when files dropped, then upload starts
- [ ] Given upload, when complete, then see optimized preview
- [ ] Given image, when inserted, then alt text required
- [ ] Given library, when searching, then find by name/alt text
- [ ] Given large image, when uploaded, then automatically optimized

### Non-Functional Criteria
- [ ] Performance: Upload feedback < 100ms
- [ ] Optimization: Images < 200KB
- [ ] Security: Virus scan all files
- [ ] Accessibility: Alt text enforced

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Upload and manage media
  As an author
  I want to manage media files
  So that I can enrich articles

  Background:
    Given I am editing an article

  Scenario: Drag and drop upload
    When I drag "photo.jpg" to upload area
    Then I should see upload progress
    And file should be validated:
      | Type: JPG |
      | Size: Under 10MB |
      | Dimensions: Valid |
    When upload completes
    Then I should see optimized preview
    And be prompted for alt text

  Scenario: Bulk upload
    When I select multiple files:
      | photo1.jpg |
      | photo2.png |
      | document.pdf |
    Then all should upload in parallel
    And each should show progress
    And succeed/fail independently

  Scenario: Format validation
    When I try to upload "script.exe"
    Then I should see "File type not supported"
    When I upload "huge-photo.jpg" (15MB)
    Then I should see "File too large (max 10MB)"

  Scenario: Image optimization
    Given I upload "large-photo.jpg" (5MB, 4000x3000)
    When optimization completes
    Then image should be:
      | Size: < 200KB |
      | Dimensions: 1920x1440 max |
      | Format: Optimized JPG |
    And original should be preserved

  Scenario: Media library search
    Given media library contains:
      | team-photo.jpg | "Company team" |
      | logo.png | "Company logo" |
      | report.pdf | "Annual report" |
    When I search "company"
    Then I should see:
      | team-photo.jpg |
      | logo.png |

  Scenario: Alt text requirement
    When I insert image without alt text
    Then insertion should be blocked
    And I should see "Alt text required for accessibility"
    When I add "Team celebrating launch"
    Then image should insert successfully
```

### Unit Test Coverage
- [ ] File validation rules
- [ ] Image optimization logic
- [ ] Media search algorithm
- [ ] Storage adapter tests
- [ ] Alt text validation

## Dependencies

### Depends On
- File storage system
- Image processing libraries
- CDN configuration
- Security scanning

### Blocks
- Featured images
- Media analytics
- Gallery features

## Implementation Notes

### Risks
- Large file handling performance
- Storage costs growth
- Image quality vs size balance
- Security vulnerabilities

### Decisions
- Client-side preview generation
- Server-side optimization
- Progressive enhancement approach
- Lazy loading for performance

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Security scan configured
- [ ] Performance benchmarked

## References

- PRD: @docs/contexts/blog/prd.md#us-016-upload-and-manage-media-files
- Technical Plan: @docs/contexts/blog/technical-plan.md#media-management
- API Documentation: POST /api/media/upload