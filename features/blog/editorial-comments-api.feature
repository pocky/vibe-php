Feature: Editorial Comments API
  As an editorial reviewer
  I want to add comments to articles through the REST API
  So that I can provide detailed feedback to authors

  Background:
    Given the database is empty
    And the following reviewers exist:
      | id                                   | name              |
      | 770e8400-e29b-41d4-a716-446655440001 | Editor Smith      |
      | 770e8400-e29b-41d4-a716-446655440002 | Senior Editor Doe |

  Scenario: Add general comment to article
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000                                          |
      | title       | Article for General Comment                                                    |
      | content     | This article will receive a general comment about its overall quality.        |
      | slug        | article-for-general-comment                                                    |
      | status      | pending_review                                                                 |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001                                           |
      | submittedAt | 2024-01-01T10:00:00+00:00                                                      |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/comments" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001",
        "comment": "This article is well-structured but needs more supporting examples in the third paragraph."
      }
      """
    Then the response should have status code 201
    And the response should contain JSON:
      """
      {
        "articleId": "550e8400-e29b-41d4-a716-446655440000",
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001",
        "comment": "This article is well-structured but needs more supporting examples in the third paragraph."
      }
      """
    And the response should contain a non-empty "id" property
    And the response should contain a non-empty "createdAt" property

  Scenario: Add inline comment with text selection
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000                                        |
      | title       | Article for Inline Comment                                                  |
      | content     | This article will receive an inline comment on a specific text selection.  |
      | slug        | article-for-inline-comment                                                  |
      | status      | pending_review                                                              |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001                                        |
      | submittedAt | 2024-01-01T10:00:00+00:00                                                   |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/comments" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440002",
        "comment": "Consider rephrasing this sentence for better clarity.",
        "selectedText": "specific text selection",
        "positionStart": 65,
        "positionEnd": 87
      }
      """
    Then the response should have status code 201
    And the response should contain JSON:
      """
      {
        "articleId": "550e8400-e29b-41d4-a716-446655440000",
        "reviewerId": "770e8400-e29b-41d4-a716-446655440002",
        "comment": "Consider rephrasing this sentence for better clarity.",
        "selectedText": "specific text selection",
        "positionStart": 65,
        "positionEnd": 87
      }
      """

  Scenario: List comments for an article
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000                 |
      | title       | Article with Comments                                |
      | content     | This article has multiple editorial comments.       |
      | slug        | article-with-comments                                |
      | status      | pending_review                                       |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001                 |
      | submittedAt | 2024-01-01T10:00:00+00:00                            |
    And the following editorial comments exist:
      | articleId                            | reviewerId                           | comment                                 | selectedText  | positionStart | positionEnd |
      | 550e8400-e29b-41d4-a716-446655440000 | 770e8400-e29b-41d4-a716-446655440001 | Overall structure is good              |               |               |             |
      | 550e8400-e29b-41d4-a716-446655440000 | 770e8400-e29b-41d4-a716-446655440002 | Consider expanding this section        | multiple      | 20            | 28          |
      | 550e8400-e29b-41d4-a716-446655440000 | 770e8400-e29b-41d4-a716-446655440001 | This phrase could be more precise      | editorial     | 29            | 38          |
    When I make a GET request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/comments"
    Then the response should have status code 200
    And the response should have header "content-type" with value "application/ld+json; charset=utf-8"
    And the collection should contain 3 items
    And each comment should have the correct article ID

  Scenario: Attempt to add comment to non-existent article
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440999/comments" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001",
        "comment": "This article doesn't exist"
      }
      """
    Then the response should have status code 404

  Scenario: Add comment with invalid reviewer ID
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000 |
      | title       | Article for Invalid Comment Test     |
      | content     | This article has invalid comment.    |
      | slug        | article-invalid-comment              |
      | status      | pending_review                       |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001 |
      | submittedAt | 2024-01-01T10:00:00+00:00            |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/comments" with JSON:
      """
      {
        "reviewerId": "invalid-uuid",
        "comment": "Testing invalid reviewer ID"
      }
      """
    Then the response should have status code 422
    And the response should contain JSON:
      """
      {
        "@type": "Error"
      }
      """

  Scenario: Add comment with missing required fields
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000 |
      | title       | Article for Missing Fields Test      |
      | content     | This article has missing fields.     |
      | slug        | article-missing-fields               |
      | status      | pending_review                       |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001 |
      | submittedAt | 2024-01-01T10:00:00+00:00            |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/comments" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001"
      }
      """
    Then the response should have status code 422
    And the response should contain JSON:
      """
      {
        "@type": "Error"
      }
      """

  Scenario: Add comment with invalid position range
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000                                |
      | title       | Article for Invalid Position Test                                   |
      | content     | This article will test invalid position ranges for inline comments.|
      | slug        | article-invalid-position                                            |
      | status      | pending_review                                                      |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001                                |
      | submittedAt | 2024-01-01T10:00:00+00:00                                           |
    When I make a POST request to "/api/articles/550e8400-e29b-41d4-a716-446655440000/comments" with JSON:
      """
      {
        "reviewerId": "770e8400-e29b-41d4-a716-446655440001",
        "comment": "Invalid position range",
        "selectedText": "some text",
        "positionStart": 100,
        "positionEnd": 50
      }
      """
    Then the response should have status code 422
    And the response should contain JSON:
      """
      {
        "@type": "Error"
      }
      """

  Scenario: Update an existing comment
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000 |
      | title       | Article for Comment Update           |
      | content     | This article has an updatable comment.|
      | slug        | article-comment-update               |
      | status      | pending_review                       |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001 |
      | submittedAt | 2024-01-01T10:00:00+00:00            |
    And the following editorial comments exist:
      | id                                   | articleId                            | reviewerId                           | comment                    |
      | 880e8400-e29b-41d4-a716-446655440001 | 550e8400-e29b-41d4-a716-446655440000 | 770e8400-e29b-41d4-a716-446655440001 | Original comment text      |
    When I make a PUT request to "/api/comments/880e8400-e29b-41d4-a716-446655440001" with JSON:
      """
      {
        "comment": "Updated comment text with more details"
      }
      """
    Then the response should have status code 200
    And the response should contain JSON:
      """
      {
        "id": "880e8400-e29b-41d4-a716-446655440001",
        "comment": "Updated comment text with more details"
      }
      """

  Scenario: Delete an editorial comment
    Given an article exists with the following data:
      | id          | 550e8400-e29b-41d4-a716-446655440000 |
      | title       | Article for Comment Deletion         |
      | content     | This article has a deletable comment.|
      | slug        | article-comment-deletion             |
      | status      | pending_review                       |
      | authorId    | 660e8400-e29b-41d4-a716-446655440001 |
      | submittedAt | 2024-01-01T10:00:00+00:00            |
    And the following editorial comments exist:
      | id                                   | articleId                            | reviewerId                           | comment                    |
      | 880e8400-e29b-41d4-a716-446655440001 | 550e8400-e29b-41d4-a716-446655440000 | 770e8400-e29b-41d4-a716-446655440001 | Comment to be deleted      |
    When I make a DELETE request to "/api/comments/880e8400-e29b-41d4-a716-446655440001"
    Then the response should have status code 204