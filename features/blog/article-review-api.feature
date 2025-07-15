Feature: Article Review API
  As an editorial reviewer
  I want to review submitted articles through the REST API
  So that I can approve or reject articles for publication

  Background:
    Given the database is empty
    And the following reviewers exist:
      | id                                   | name              |
      | 770e8400-e29b-41d4-a716-446655440001 | Editor Smith      |
      | 770e8400-e29b-41d4-a716-446655440002 | Senior Editor Doe |

  Scenario: Submit article for review
    Given an article exists with the following data:
      | id        | 550e8400-e29b-41d4-a716-446655440000                                             |
      | title     | Article Awaiting Review                                                           |
      | content   | This is a well-written article that is ready for editorial review and approval. |
      | slug      | article-awaiting-review                                                          |
      | status    | draft                                                                            |
      | authorId  | 660e8400-e29b-41d4-a716-446655440001                                             |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/submit-for-review" with JSON:
      """
      {
        "authorId": "660e8400-e29b-41d4-a716-446655440001"
      }
      """
    Then the response should have status code 201
    And the response should contain JSON:
      """
      {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "status": "pending_review"
      }
      """
    And the article status in database should be "pending_review"

  Scenario: Attempt to submit already published article for review
    Given an article exists with the following data:
      | id        | 550e8400-e29b-41d4-a716-446655440000 |
      | title     | Published Article                    |
      | content   | This article is already published.   |
      | slug      | published-article                    |
      | status    | published                            |
      | authorId  | 660e8400-e29b-41d4-a716-446655440001 |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/submit-for-review" with JSON:
      """
      {
        "authorId": "660e8400-e29b-41d4-a716-446655440001"
      }
      """
    Then the response should have status code 422
    And the response should contain JSON:
      """
      {
        "@type": "Error"
      }
      """

  Scenario: Approve article for publication
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000                                             |
      | title       | Article Ready for Approval                                                       |
      | content     | This article has been submitted for review and is ready for approval decision.  |
      | slug        | article-ready-for-approval                                                       |
      | status      | pending_review                                                                   |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001                                             |
      | submittedAt | 2024-01-01T10:00:00+00:00                                                        |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/approve" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001",
        "reason": "Excellent article, ready for publication"
      }
      """
    Then the response should have status code 201
    And the response should contain JSON:
      """
      {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "status": "approved"
      }
      """
    And the article status in database should be "approved"

  Scenario: Approve article without reason
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000                           |
      | title       | Simple Approval                                                |
      | content     | This article will be approved without a specific reason.      |
      | slug        | simple-approval                                                |
      | status      | pending_review                                                 |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001                           |
      | submittedAt | 2024-01-01T10:00:00+00:00                                      |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/approve" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001"
      }
      """
    Then the response should have status code 201
    And the response should contain JSON:
      """
      {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "status": "approved"
      }
      """

  Scenario: Reject article with reason
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000                                        |
      | title       | Article Needs Work                                                           |
      | content     | This article needs significant improvements before it can be published.     |
      | slug        | article-needs-work                                                           |
      | status      | pending_review                                                               |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001                                         |
      | submittedAt | 2024-01-01T10:00:00+00:00                                                    |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/reject" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440002",
        "reason": "The article lacks sufficient detail and needs more supporting examples."
      }
      """
    Then the response should have status code 201
    And the response should contain JSON:
      """
      {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "status": "rejected"
      }
      """
    And the article status in database should be "rejected"

  Scenario: Attempt to approve draft article (not submitted for review)
    Given an article exists with the following data:
      | id       | 550e8400-e29b-41d4-a716-446655440000 |
      | title    | Draft Article                        |
      | content  | This is just a draft article.        |
      | slug     | draft-article                        |
      | status   | draft                                |
      | authorId | 660e8400-e29b-41d4-a716-446655440001 |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/approve" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001",
        "reason": "Looks good to me"
      }
      """
    Then the response should have status code 422
    And the response should contain JSON:
      """
      {
        "@type": "Error"
      }
      """

  Scenario: Attempt to reject article without reason
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000 |
      | title       | Article for Rejection Test           |
      | content     | This article will be rejected.       |
      | slug        | article-for-rejection-test           |
      | status      | pending_review                       |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001 |
      | submittedAt | 2024-01-01T10:00:00+00:00            |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/reject" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440002"
      }
      """
    Then the response should have status code 422
    And the response should contain JSON:
      """
      {
        "@type": "Error"
      }
      """

  Scenario: List articles pending review
    Given the following articles exist for review:
      | id                                   | title                    | status         | submittedAt              | authorId                             |
      | 550e8400-e29b-41d4-a716-446655440001 | First Pending Article    | pending_review | 2024-01-01T09:00:00+00:00 | 660e8400-e29b-41d4-a716-446655440001 |
      | 550e8400-e29b-41d4-a716-446655440002 | Second Pending Article   | pending_review | 2024-01-01T10:00:00+00:00 | 660e8400-e29b-41d4-a716-446655440002 |
      | 550e8400-e29b-41d4-a716-446655440003 | Third Pending Article    | pending_review | 2024-01-01T11:00:00+00:00 | 660e8400-e29b-41d4-a716-446655440001 |
      | 550e8400-e29b-41d4-a716-446655440004 | Published Article        | published      | 2024-01-01T08:00:00+00:00 | 660e8400-e29b-41d4-a716-446655440002 |
      | 550e8400-e29b-41d4-a716-446655440005 | Draft Article            | draft          |                          | 660e8400-e29b-41d4-a716-446655440001 |
    When I make a GET request to "/api/articles?status=pending_review"
    Then the response should have status code 200
    And the response should have header "content-type" with value "application/ld+json; charset=utf-8"
    And the collection should contain 3 items
    And the response should contain articles with status "pending_review"

  Scenario: Attempt to review non-existent article
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440999/approve" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001",
        "reason": "This article doesn't exist"
      }
      """
    Then the response should have status code 404

  Scenario: Review article with invalid reviewer ID
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000 |
      | title       | Article for Invalid Reviewer Test    |
      | content     | This article has invalid reviewer.   |
      | slug        | article-invalid-reviewer             |
      | status      | pending_review                       |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001 |
      | submittedAt | 2024-01-01T10:00:00+00:00            |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/approve" with JSON:
      """
      {
        "reviewerId": "invalid-uuid",
        "reason": "Testing invalid reviewer"
      }
      """
    Then the response should have status code 422
    And the response should contain JSON:
      """
      {
        "@type": "Error"
      }
      """