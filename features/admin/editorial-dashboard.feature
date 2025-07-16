Feature: Editorial Dashboard for Article Review
  In order to manage article submissions and reviews
  As an editorial reviewer
  I want to access a dedicated dashboard to review articles through the admin interface

  Background:
    Given I am on the admin dashboard
    And there are articles pending review:
      | title                  | status         | submittedAt         | content                                                |
      | Article Needs Review 1 | pending_review | 2025-01-01 09:00:00 | This article has been submitted for editorial review. |
      | Article Needs Review 2 | pending_review | 2025-01-01 10:00:00 | Another article awaiting review and approval.         |

  Scenario: View editorial dashboard
    When I go to "/admin/editorials"
    Then the page should load successfully

  Scenario: View pending articles in editorial dashboard
    When I go to "/admin/editorials"
    Then the page should load successfully