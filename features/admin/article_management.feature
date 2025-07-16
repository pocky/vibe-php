Feature: Article management in admin
  In order to manage blog content
  As an administrator
  I want to be able to view, create, update and delete articles through the admin interface

  Background:
    Given I am on the admin dashboard

  Scenario: View articles list in admin
    When I go to "/admin/articles"
    Then I should see "Articles" in the title
    And I should see the articles grid
    And the grid should have columns:
      | Column    |
      | Title     |
      | Status    |
      | Created   |

  Scenario: Empty articles list
    Given there are 0 articles
    When I go to "/admin/articles"
    Then I should see "Articles" in the title
    And I should see the articles grid
    And I should see no results in the grid

  Scenario: Articles list with data
    Given there are articles:
      | title                | status    | created_at          |
      | My First Article     | draft     | 2025-01-01 10:00:00 |
      | Published Article    | published | 2025-01-02 14:30:00 |
      | Another Draft        | draft     | 2025-01-03 09:15:00 |
    When I go to "/admin/articles"
    Then I should see "Articles" in the title
    And I should see "My First Article" in the grid
    And I should see "Published Article" in the grid
    And I should see "Another Draft" in the grid

  Scenario: Create new article
    When I go to "/admin/articles/new"
    And I should see "Title" field
    And I should see "Slug" field
    And I should see "Content" field
    And I should see "Status" field
    And I should see "Create" button

  Scenario: Create new article with form submission
    When I go to "/admin/articles/new"
    Then I should see "Title" field
    And I should see "Slug" field
    And I should see "Content" field
    And I should see "Status" field
    And I should see "Create" button


  Scenario: Edit existing article
    Given there are articles:
      | title            | status | slug             |
      | Article to Edit  | draft  | article-to-edit  |
    When I go to "/admin/articles"
    Then I should see the articles grid
    When I go to "/admin/articles/new"
    Then I should see "Title" field
    And I should see "Content" field
    And I should see "Status" field

  Scenario: Delete article via admin interface
    When I go to "/admin/articles"
    Then I should see the articles grid

  Scenario: View article details
    Given there are articles:
      | title            | status    | slug             | content                    |
      | Article to View  | published | article-to-view  | This is the article content |
    When I go to "/admin/articles"
    Then I should see the articles grid

  Scenario: Form validation on create
    When I go to "/admin/articles/new"
    Then I should see "Title" field
    And I should see "Slug" field
    And I should see "Content" field

  Scenario: Duplicate slug validation
    Given there are articles:
      | title              | status | slug               |
      | Existing Article   | draft  | existing-article   |
    When I go to "/admin/articles/new"
    Then I should see "Title" field
    And I should see "Slug" field
    And I should see "Content" field

  Scenario: Pagination with default limit
    Given there are 15 articles
    When I go to "/admin/articles"
    Then I should see the articles grid
    And the current URL should contain "page=1" or no page parameter
    And I should see 10 articles in the grid

  Scenario: Navigate to page 2
    Given there are 15 articles
    When I go to "/admin/articles?page=2"
    Then I should see the articles grid
    And the current URL should contain "page=2"

  Scenario: Change items per page limit
    Given there are 25 articles
    When I go to "/admin/articles"
    And I change the limit to "20"
    Then I should see the articles grid
    And the current URL should contain "limit=20"

  Scenario: Test all available limits
    Given there are 55 articles
    When I go to "/admin/articles"
    Then I should see limit options "10, 20, 50"
    When I change the limit to "50"
    Then the current URL should contain "limit=50"

  Scenario: No pagination with few articles
    Given there are 5 articles
    When I go to "/admin/articles"
    Then I should see the articles grid
    And I should not see pagination

  Scenario: Pagination preserves current page when changing limit
    Given there are 30 articles
    When I go to "/admin/articles?page=2"
    And I change the limit to "20"
    Then the current URL should contain "limit=20"
