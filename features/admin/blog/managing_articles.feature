@blog @admin @managing_articles
Feature: Managing articles in admin
    In order to manage blog content effectively
    As a content creator or editor
    I want to create, browse, update and manage articles through the admin interface

    Background:
        Given the following articles exist
            | title              | status    | slug               | createdAt           |
            | Base Draft Article | draft     | base-draft-article | 2025-01-01 10:00:00 |
            | Base Published     | published | base-published     | 2025-01-01 11:00:00 |

    @ui @browsing
    Scenario: Content creator browsing their articles
        When I want to browse articles
        Then I should see a title
        And I should see a grid
        And the grid should have columns
            | Column  |
            | Title   |
            | Status  |
            | Created |
        And I should see "Base Draft Article" in the grid
        And I should see "Base Published" in the grid
        And I should not see pagination

    @ui @creating @content-creation
    Scenario: Content creator creates a new article
        When I want to create a new article
        And I fill in the article form with:
            | title   | My New Blog Post |
            | content | This is the content of my new blog post about interesting topics. |
            | slug    | my-new-blog-post |
        And I save it
        Then I should be notified that the article has been successfully created
        And the article "My New Blog Post" should appear in the list with status "draft"

    @ui @creating @validation
    Scenario: Article creation with validation errors
        When I want to create a new article
        And I fill in the article form with:
            | title   | AB |
            | content | Too short |
            | slug    | invalid slug! |
        And I try to save it
        Then I should be notified that:
            | Title must be between 3 and 200 characters |
            | Content must be at least 10 characters |
            | Slug can only contain lowercase letters, numbers and hyphens |

    @ui @updating @content-creation
    Scenario: Content creator updates their draft article
        Given I have a draft article "Work in Progress"
        When I edit the article "Work in Progress"
        And I update the content to "This is my updated content with more details and information."
        And I save the changes
        Then I should be notified that the article has been successfully updated
        And the article should still have status "draft"

    @ui @filtering
    Scenario: Editor filters articles by status
        Given there are articles with different statuses:
            | title                  | status      |
            | Draft Article 1        | draft       |
            | Published Article 1    | published   |
            | Draft Article 2        | draft       |
            | Under Review Article   | pending     |
        When I filter articles by status "draft"
        Then I should only see articles with status "draft"
        And I should see 2 articles in the grid

    Scenario: Pagination with default limit
        Given there are 13 additional articles
        When I go to "/admin/articles"
        Then I should see the articles grid
        And the current URL should contain "page=1" or no page parameter
        And I should see 10 articles in the grid

    Scenario: Change items per page limit
        Given there are 23 additional articles
        When I go to "/admin/articles"
        And I change the limit to "20"
        Then I should see the articles grid
        And the current URL should contain "limit=20"

    Scenario: Test all available limits
        Given there are 53 additional articles
        When I go to "/admin/articles"
        Then I should see limit options "10, 20, 50"
        When I change the limit to "50"
        Then the current URL should contain "limit=50"

    @ui @admin
    Scenario: Navigate to page 2
        Given there are 13 additional articles
        When I go to "/admin/articles?page=2"
        Then I should see the articles grid
        And the current URL should contain "page=2"

    @ui @admin
    Scenario: Pagination preserves current page when changing limit
        Given there are 28 additional articles
        When I go to "/admin/articles?page=2"
        And I change the limit to "20"
        Then the current URL should contain "limit=20"
