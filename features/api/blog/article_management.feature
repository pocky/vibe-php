@blog @api @article-management
Feature: Article management through API
    In order to create and manage blog content programmatically
    As a content creator using the API
    I want to perform CRUD operations on articles

    Background:
        Given I am authenticated as a content creator
        And the following articles exist:
            | id                                   | title                  | content                                                        | slug                   | status    | author        | createdAt           |
            | 550e8400-e29b-41d4-a716-446655440010 | My Draft Article      | This is my draft article that I'm still working on.          | my-draft-article      | draft     | John Writer   | 2025-01-01 10:00:00 |
            | 550e8400-e29b-41d4-a716-446655440011 | Published Tutorial    | This tutorial explains how to use our blogging platform.      | published-tutorial    | published | Jane Blogger  | 2025-01-01 11:00:00 |
            | 550e8400-e29b-41d4-a716-446655440012 | Article Under Review  | This article is currently being reviewed by editors.          | article-under-review  | pending   | Mike Author   | 2025-01-02 09:00:00 |

    @api @create @content-creation
    Scenario: Content creator creates a new article draft
        When I create a new article with:
            """
            {
                "title": "Getting Started with Our API",
                "content": "This comprehensive guide will help you understand how to use our blogging API effectively. We'll cover authentication, endpoints, and best practices.",
                "slug": "getting-started-api"
            }
            """
        Then the article should be created successfully with status code 201
        And the response should include:
            | field   | value                      |
            | title   | Getting Started with Our API |
            | status  | draft                      |
            | slug    | getting-started-api        |
        And I should receive the article ID for future operations

    @api @create @validation
    Scenario: Article creation fails with invalid data
        When I try to create an article with:
            """
            {
                "title": "AB",
                "content": "Too short",
                "slug": "invalid slug!"
            }
            """
        Then the request should fail with status code 422
        And I should receive validation errors:
            | field   | error                                                    |
            | title   | Title must be between 3 and 200 characters             |
            | content | Content must be at least 10 characters                  |
            | slug    | Slug can only contain lowercase letters, numbers and hyphens |

    @api @read @content-discovery
    Scenario: Retrieve my draft article
        When I request the article "550e8400-e29b-41d4-a716-446655440010"
        Then I should receive the article with status code 200
        And the article should contain:
            | field   | value              |
            | title   | My Draft Article   |
            | status  | draft              |
            | author  | John Writer        |

    @api @read @authorization
    Scenario: Cannot retrieve another author's draft
        Given I am authenticated as "Mike Author"
        When I request the article "550e8400-e29b-41d4-a716-446655440010"
        Then the request should fail with status code 403
        And I should receive error "You don't have permission to access this draft"

    @api @list @content-discovery
    Scenario: List my articles with pagination
        Given I have 25 articles in various states
        When I request my articles with:
            | page  | 1  |
            | limit | 20 |
        Then I should receive 20 articles
        And the response should include pagination metadata:
            | field       | value |
            | currentPage | 1     |
            | totalPages  | 2     |
            | totalItems  | 25    |

    @api @list @filtering
    Scenario: Filter articles by status
        When I request articles filtered by:
            | status | draft |
        Then I should only receive articles with status "draft"
        And the results should include "My Draft Article"
        And the results should not include "Published Tutorial"

    @api @update @content-editing
    Scenario: Update my draft article
        When I update article "550e8400-e29b-41d4-a716-446655440010" with:
            """
            {
                "title": "My Updated Draft Article",
                "content": "This is the updated content of my draft article with much more detail and information than before."
            }
            """
        Then the article should be updated successfully with status code 200
        And the response should show:
            | field   | value                    |
            | title   | My Updated Draft Article |
            | status  | draft                    |

    @api @update @workflow
    Scenario: Submit article for review
        When I update article "550e8400-e29b-41d4-a716-446655440010" with:
            """
            {
                "status": "pending"
            }
            """
        Then the article should be updated successfully
        And the article status should be "pending"
        And an editor should be notified of the submission

    @api @delete @content-management
    Scenario: Delete my draft article
        When I delete article "550e8400-e29b-41d4-a716-446655440010"
        Then the article should be deleted successfully with status code 204
        When I request the article "550e8400-e29b-41d4-a716-446655440010"
        Then the request should fail with status code 404

    @api @delete @business-rules
    Scenario: Cannot delete published articles
        When I try to delete article "550e8400-e29b-41d4-a716-446655440011"
        Then the request should fail with status code 403
        And I should receive error "Published articles cannot be deleted"

    @api @search @content-discovery
    Scenario: Search articles by keyword
        When I search for articles containing "tutorial"
        Then the results should include "Published Tutorial"
        And the results should be sorted by relevance
        And each result should highlight the matching terms

    @api @bulk @efficiency
    Scenario: Bulk update article status
        Given I have multiple draft articles
        When I bulk update articles with IDs:
            | 550e8400-e29b-41d4-a716-446655440010 |
            | 550e8400-e29b-41d4-a716-446655440013 |
        And set their status to "pending"
        Then all articles should be updated successfully
        And I should receive a summary of the operation

    @api @export @content-management
    Scenario: Export articles in different formats
        When I request to export my articles in "markdown" format
        Then I should receive a downloadable file
        And the file should contain all my articles in markdown format
        And the export should include metadata for each article