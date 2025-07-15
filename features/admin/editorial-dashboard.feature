Feature: Editorial Dashboard for Article Review
  In order to manage article submissions and reviews
  As an editorial reviewer
  I want to access a dedicated dashboard to review articles through the admin interface

  Background:
    Given I am on the admin dashboard

  Scenario: View editorial dashboard
    When I go to "/admin/editorial"
    Then I should see "Editorial Dashboard" in the title
    And I should see the pending articles section
    And I should see the articles awaiting review grid
    And the grid should have columns:
      | Column          |
      | Title           |
      | Author          |
      | Submitted At    |
      | Status          |
      | Actions         |

  Scenario: Empty pending review list
    Given there are no articles pending review
    When I go to "/admin/editorial"
    Then I should see "Editorial Dashboard" in the title
    And I should see "No articles pending review" message

  Scenario: View articles pending review
    Given there are articles pending review:
      | title                    | author     | submitted_at         | status         |
      | Article Needs Review     | John Doe   | 2024-01-15 09:30:00  | pending_review |
      | Another Review Required  | Jane Smith | 2024-01-16 14:15:00  | pending_review |
      | Third Article Submitted  | Bob Wilson | 2024-01-17 11:45:00  | pending_review |
    When I go to "/admin/editorial"
    Then I should see "Editorial Dashboard" in the title
    And I should see "Article Needs Review" in the grid
    And I should see "Another Review Required" in the grid
    And I should see "Third Article Submitted" in the grid
    And I should see "Approve" action for each article
    And I should see "Reject" action for each article

  Scenario: View article details for review
    Given there are articles pending review:
      | title                | author   | submitted_at         | status         | content                                    |
      | Article for Review   | John Doe | 2024-01-15 10:00:00  | pending_review | This is a detailed article for review... |
    When I go to "/admin/editorial"
    And I click "Review" button for "Article for Review"
    Then I should see "Review Article" in the title
    And I should see "Article for Review" as the article title
    And I should see "John Doe" as the author
    And I should see "This is a detailed article for review..." in the content
    And I should see "Approve Article" button
    And I should see "Reject Article" button
    And I should see "Editorial Comments" section

  Scenario: Approve article with reason
    Given there are articles pending review:
      | title                | author   | submitted_at         | status         |
      | Article to Approve   | John Doe | 2024-01-15 10:00:00  | pending_review |
    When I go to "/admin/editorial"
    And I click "Review" button for "Article to Approve"
    And I fill in "Approval Reason" with "Well-written article, ready for publication"
    And I click "Approve Article" button
    Then I should see "Article approved successfully" message
    And I should be redirected to the editorial dashboard
    And I should not see "Article to Approve" in the pending grid

  Scenario: Approve article without reason
    Given there are articles pending review:
      | title                      | author   | submitted_at         | status         |
      | Article to Approve Simple  | John Doe | 2024-01-15 10:00:00  | pending_review |
    When I go to "/admin/editorial"
    And I click "Review" button for "Article to Approve Simple"
    And I click "Approve Article" button
    Then I should see "Article approved successfully" message
    And I should be redirected to the editorial dashboard

  Scenario: Reject article with required reason
    Given there are articles pending review:
      | title               | author   | submitted_at         | status         |
      | Article to Reject   | John Doe | 2024-01-15 10:00:00  | pending_review |
    When I go to "/admin/editorial"
    And I click "Review" button for "Article to Reject"
    And I fill in "Rejection Reason" with "Article needs significant improvements in structure and content"
    And I click "Reject Article" button
    Then I should see "Article rejected successfully" message
    And I should be redirected to the editorial dashboard
    And I should not see "Article to Reject" in the pending grid

  Scenario: Reject article without reason shows validation error
    Given there are articles pending review:
      | title                    | author   | submitted_at         | status         |
      | Article Missing Reason   | John Doe | 2024-01-15 10:00:00  | pending_review |
    When I go to "/admin/editorial"
    And I click "Review" button for "Article Missing Reason"
    And I click "Reject Article" button
    Then I should see "Rejection reason is required" validation error
    And I should still see "Review Article" in the title

  Scenario: Add editorial comment to article
    Given there are articles pending review:
      | title                     | author   | submitted_at         | status         |
      | Article for Comment       | John Doe | 2024-01-15 10:00:00  | pending_review |
    When I go to "/admin/editorial"
    And I click "Review" button for "Article for Comment"
    And I scroll to "Editorial Comments" section
    And I fill in "Add Comment" with "Please revise the introduction to provide more context"
    And I click "Add Comment" button
    Then I should see "Comment added successfully" message
    And I should see "Please revise the introduction to provide more context" in the comments list

  Scenario: Add inline editorial comment with text selection
    Given there are articles pending review:
      | title                      | author   | submitted_at         | status         | content                                          |
      | Article for Inline Comment | John Doe | 2024-01-15 10:00:00  | pending_review | This is a paragraph that needs specific feedback. |
    When I go to "/admin/editorial"
    And I click "Review" button for "Article for Inline Comment"
    And I select the text "needs specific feedback"
    And I add inline comment "Consider rephrasing this section for clarity"
    Then I should see "Inline comment added successfully" message
    And I should see the inline comment highlighted in the text
    And I should see "Consider rephrasing this section for clarity" in the comments list

  Scenario: View review history and statistics
    Given there are reviewed articles:
      | title            | reviewer      | reviewed_at          | decision | reason                      |
      | Approved Article | Editor Smith  | 2024-01-10 15:30:00  | approved | Excellent content           |
      | Rejected Article | Senior Editor | 2024-01-12 09:15:00  | rejected | Needs significant revision  |
    When I go to "/admin/editorial/history"
    Then I should see "Review History" in the title
    And I should see "Approved Article" in the history grid
    And I should see "Rejected Article" in the history grid
    And I should see review statistics:
      | Metric              | Value |
      | Total Reviews       | 2     |
      | Approved Articles   | 1     |
      | Rejected Articles   | 1     |

  Scenario: Filter articles by review status
    Given there are articles with different review statuses:
      | title               | status         | submitted_at         |
      | Pending Article 1   | pending_review | 2024-01-15 10:00:00  |
      | Pending Article 2   | pending_review | 2024-01-16 11:00:00  |
      | Approved Article    | approved       | 2024-01-14 09:00:00  |
      | Rejected Article    | rejected       | 2024-01-13 14:00:00  |
    When I go to "/admin/editorial"
    And I select "Pending Review" from the status filter
    Then I should see "Pending Article 1" in the grid
    And I should see "Pending Article 2" in the grid
    And I should not see "Approved Article" in the grid
    And I should not see "Rejected Article" in the grid

  Scenario: Search articles by title or author
    Given there are articles pending review:
      | title                    | author     | submitted_at         | status         |
      | Technical Documentation  | John Tech  | 2024-01-15 10:00:00  | pending_review |
      | Marketing Article        | Jane Market| 2024-01-16 11:00:00  | pending_review |
      | Development Guide        | John Dev   | 2024-01-17 12:00:00  | pending_review |
    When I go to "/admin/editorial"
    And I fill in the search field with "John"
    And I click "Search" button
    Then I should see "Technical Documentation" in the grid
    And I should see "Development Guide" in the grid
    And I should not see "Marketing Article" in the grid

  Scenario: Bulk approve multiple articles
    Given there are articles pending review:
      | title               | author   | submitted_at         | status         |
      | Article to Bulk 1   | John Doe | 2024-01-15 10:00:00  | pending_review |
      | Article to Bulk 2   | Jane Doe | 2024-01-16 11:00:00  | pending_review |
      | Article to Bulk 3   | Bob Smith| 2024-01-17 12:00:00  | pending_review |
    When I go to "/admin/editorial"
    And I select "Article to Bulk 1" checkbox
    And I select "Article to Bulk 2" checkbox
    And I click "Bulk Actions" dropdown
    And I select "Approve Selected" from bulk actions
    And I click "Apply" button
    Then I should see "2 articles approved successfully" message
    And I should not see "Article to Bulk 1" in the pending grid
    And I should not see "Article to Bulk 2" in the pending grid
    And I should see "Article to Bulk 3" in the pending grid