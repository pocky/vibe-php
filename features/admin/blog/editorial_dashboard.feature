@blog @admin @editorial
Feature: Editorial dashboard for content review
    In order to maintain content quality and brand consistency
    As an editor
    I want to review, comment on, and approve/reject articles submitted by content creators

    Background:
        Given I am logged in as an editor
        And the following articles exist in different states:
            | title                    | author          | status    | submittedAt         |
            | Great Content Article    | John Writer     | pending   | 2025-01-15 10:00:00 |
            | Needs Improvement Post   | Jane Blogger    | pending   | 2025-01-15 09:00:00 |
            | Previously Approved      | Mike Author     | approved  | 2025-01-14 14:00:00 |
            | Previously Rejected      | Sarah Creator   | rejected  | 2025-01-14 13:00:00 |
            | Draft in Progress        | Tom Drafter     | draft     | 2025-01-15 11:00:00 |

    @ui @dashboard
    Scenario: Editor accesses editorial dashboard
        When I go to the editorial dashboard
        Then I should see "Editorial Dashboard" as the page title
        And I should see "Articles Pending Review" section
        And I should see "2" articles awaiting review
        And I should not see articles with status "draft"

    @ui @review-queue
    Scenario: View pending review queue with article details
        When I access the editorial dashboard
        Then I should see articles pending review:
            | title                    | author       | submitted           |
            | Great Content Article    | John Writer  | 2025-01-15 10:00:00 |
            | Needs Improvement Post   | Jane Blogger | 2025-01-15 09:00:00 |
        And the articles should be sorted by submission date (newest first)
        And each article should display:
            | author name |
            | submission time |
            | word count |
            | preview text |

    @ui @review-article
    Scenario: Review and approve an article
        Given I am on the editorial dashboard
        When I select "Great Content Article" for review
        Then I should see the full article content
        And I should see the author information
        When I add an editorial comment "Excellent work on the introduction"
        And I click "Approve Article"
        Then I should see "Article approved successfully"
        And "Great Content Article" should no longer appear in pending queue
        And the author "John Writer" should be notified of the approval

    @ui @review-article @rejection
    Scenario: Review and reject an article with feedback
        Given I am on the editorial dashboard
        When I select "Needs Improvement Post" for review
        And I add editorial comments:
            | section      | comment                                |
            | Introduction | Needs a stronger hook                  |
            | Body         | Please add supporting data and sources |
            | Conclusion   | Too abrupt, expand on key points       |
        And I provide rejection reason "Article needs significant revision - see inline comments"
        And I click "Reject Article"
        Then I should see "Article rejected with feedback sent to author"
        And "Needs Improvement Post" should move to "Rejected" status
        And the author "Jane Blogger" should receive the feedback

    @ui @filtering
    Scenario: Filter articles by review status
        When I go to the editorial dashboard
        And I filter by status "All"
        Then I should see 4 articles total
        When I filter by status "Pending"
        Then I should see 2 articles
        When I filter by status "Approved"
        Then I should see 1 article
        When I filter by status "Rejected"
        Then I should see 1 article

    @ui @search
    Scenario: Search for specific articles in review queue
        When I go to the editorial dashboard
        And I search for "Content"
        Then I should see "Great Content Article" in the results
        And I should not see "Needs Improvement Post"
        When I clear the search
        Then I should see all pending articles again

    @ui @bulk-actions
    Scenario: Perform bulk actions on multiple articles
        When I go to the editorial dashboard
        And I select multiple articles:
            | Great Content Article |
            | Needs Improvement Post |
        And I choose bulk action "Assign to reviewer"
        And I select reviewer "Senior Editor"
        Then both articles should be assigned to "Senior Editor"
        And I should see "2 articles assigned successfully"

    @ui @statistics
    Scenario: View editorial statistics
        When I go to the editorial dashboard
        Then I should see editorial statistics:
            | metric                  | value |
            | Articles pending review | 2     |
            | Approved today         | 0     |
            | Rejected today         | 0     |
            | Average review time    | N/A   |
        And I should see a chart showing review trends

    @ui @quick-actions
    Scenario: Use quick actions from the dashboard
        When I go to the editorial dashboard
        And I hover over "Great Content Article"
        Then I should see quick action buttons:
            | Quick Approve |
            | Quick Reject  |
            | Preview       |
            | Assign        |
        When I click "Preview"
        Then I should see a modal with the article preview
        And I should be able to close the preview and return to the dashboard