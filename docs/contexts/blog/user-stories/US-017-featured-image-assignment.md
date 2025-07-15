# US-017: Featured Image Assignment

## Business Context

### From PRD
Featured images are crucial for visual appeal and social media sharing. They serve as the primary visual representation of articles across the platform and external shares.

### Business Value
- Improves visual engagement
- Enhances social media presence
- Increases click-through rates
- Provides consistent article presentation

## User Story

**As an** author  
**I want** to assign a main image to my article  
**So that** I can improve visual attractiveness

## Functional Requirements

### Main Flow
1. Author edits article
2. Author clicks "Set Featured Image"
3. Media library opens
4. Author selects image
5. Preview shows image placement
6. Author confirms selection
7. Image saved as featured

### Alternative Flows
- Upload new image as featured
- Remove featured image
- Replace existing featured image
- Auto-suggest from article images

### Business Rules
- Image selection from media library
- Main image preview
- Automatic resizing
- Image used in social media
- Optional image (can be empty)
- One featured image per article

## Technical Implementation

### From Technical Plan
Featured image system with multiple size generation and social media optimization.

### Architecture Components
- **Domain**: 
  - `FeaturedImage` value object
  - Image requirements validation
  - Social media formats
- **Application**: 
  - `SetFeaturedImage\Command`
  - Image variant generation
  - OpenGraph meta service
- **Infrastructure**: 
  - Multi-size image generation
  - CDN variant storage
  - Social preview cache
- **UI**: 
  - Featured image selector
  - Preview in context
  - Social preview tool

### Database Changes
- Add `featured_image_id` to articles
- Image variant tracking
- Social media metadata

## Acceptance Criteria

### Functional Criteria
- [ ] Given article edit, when clicking featured image, then media library opens
- [ ] Given image selection, when chosen, then preview displays
- [ ] Given save, when completed, then image set as featured
- [ ] Given social share, when generated, then uses featured image
- [ ] Given no selection, when saved, then article has no featured image

### Non-Functional Criteria
- [ ] Performance: Image load < 2 seconds
- [ ] Quality: Multiple sizes generated
- [ ] SEO: Proper meta tags added
- [ ] Social: Open Graph compliance

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Featured image assignment
  As an author
  I want to set featured images
  So that articles are visually appealing

  Background:
    Given I am editing an article
    And media library has images

  Scenario: Set featured image
    When I click "Set Featured Image"
    Then media library should open
    When I select "hero-image.jpg"
    Then I should see preview:
      | Article card preview |
      | Social media preview |
      | Mobile view preview |
    When I click "Set as Featured"
    Then image should be saved
    And appear in article header

  Scenario: Replace featured image
    Given article has featured image "old-image.jpg"
    When I click "Change Featured Image"
    And I select "new-image.jpg"
    Then preview should update
    When I save
    Then new image should replace old

  Scenario: Auto-sizing
    Given I select image 4000x3000px
    When set as featured
    Then system should generate:
      | Thumbnail: 150x150 |
      | Card: 400x300 |
      | Hero: 1200x630 |
      | Full: 1920x1080 |
    And serve appropriate size

  Scenario: Social media preview
    Given article has featured image
    When I view "Social Preview"
    Then I should see:
      | Facebook card |
      | Twitter card |
      | LinkedIn preview |
    With proper dimensions

  Scenario: Remove featured image
    Given article has featured image
    When I click "Remove Featured Image"
    And I confirm removal
    Then featured image should be unset
    And article shows without image

  Scenario: Suggested images
    Given article contains 3 inline images
    When I click "Set Featured Image"
    Then I should see section "From this article"
    With the 3 images as options
```

### Unit Test Coverage
- [ ] Image sizing logic
- [ ] Variant generation
- [ ] Meta tag generation
- [ ] Preview rendering
- [ ] Social format compliance

## Dependencies

### Depends On
- US-016: Media upload system
- Image processing service
- CDN infrastructure

### Blocks
- Social sharing features
- Article cards/previews
- Gallery layouts

## Implementation Notes

### Risks
- Image quality degradation
- Processing time for variants
- Storage cost for variants
- CDN propagation delays

### Decisions
- Generate variants on upload
- Lazy loading for previews
- WebP format with fallbacks
- Fixed social media dimensions

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Social previews validated
- [ ] Performance optimized

## References

- PRD: @docs/contexts/blog/prd.md#us-017-featured-image-assignment
- Technical Plan: @docs/contexts/blog/technical-plan.md#featured-images
- API Documentation: PUT /api/articles/{id}/featured-image