Feature: Auto-save article draft
  In order to prevent content loss
  As a content creator
  I want to auto-save my work as draft

  Background:
    Given the following articles exist:
      | id                                   | title             | content                       | slug              | status    |
      | 550e8400-e29b-41d4-a716-446655440001 | Draft Article     | This is a draft article       | draft-article     | draft     |
      | 550e8400-e29b-41d4-a716-446655440002 | Published Article | This is a published article   | published-article | published |

  Scenario: Successfully auto-save a draft article
    When I send a "PUT" request to "/api/articles/550e8400-e29b-41d4-a716-446655440001/auto-save" with body:
    """
    {
      "title": "Updated Draft Title",
      "content": "This is the updated content from auto-save",
      "slug": "draft-article"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "id" should be equal to "550e8400-e29b-41d4-a716-446655440001"
    And the JSON node "title" should be equal to "Updated Draft Title"
    And the JSON node "content" should be equal to "This is the updated content from auto-save"
    And the JSON node "status" should be equal to "draft"

  Scenario: Cannot auto-save a published article
    When I send a "PUT" request to "/api/articles/550e8400-e29b-41d4-a716-446655440002/auto-save" with body:
    """
    {
      "title": "Trying to Update Published",
      "content": "This should not work",
      "slug": "published-article"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "detail" should contain "Cannot auto-save published article"

  Scenario: Auto-save with invalid data
    When I send a "PUT" request to "/api/articles/550e8400-e29b-41d4-a716-446655440001/auto-save" with body:
    """
    {
      "title": "AB",
      "content": "Too short",
      "slug": "draft-article"
    }
    """
    Then the response status code should be 422
    And the response should be in JSON
    And the JSON node "violations" should exist
    And the JSON node "violations[0].propertyPath" should be equal to "title"
    And the JSON node "violations[0].message" should contain "at least"

  Scenario: Auto-save non-existent article
    When I send a "PUT" request to "/api/articles/550e8400-e29b-41d4-a716-446655440999/auto-save" with body:
    """
    {
      "title": "Non-existent Article",
      "content": "This article does not exist",
      "slug": "non-existent"
    }
    """
    Then the response status code should be 404
    And the response should be in JSON
    And the JSON node "detail" should contain "Not Found"

  Scenario: Auto-save preserves slug when not provided
    When I send a "PUT" request to "/api/articles/550e8400-e29b-41d4-a716-446655440001/auto-save" with body:
    """
    {
      "title": "Updated Title Only",
      "content": "Updated content without changing slug",
      "slug": "draft-article"
    }
    """
    Then the response status code should be 200
    And the JSON node "slug" should be equal to "draft-article"

  Scenario: Auto-save with minimum valid content
    When I send a "PUT" request to "/api/articles/550e8400-e29b-41d4-a716-446655440001/auto-save" with body:
    """
    {
      "title": "Min",
      "content": "1234567890",
      "slug": "draft-article"
    }
    """
    Then the response status code should be 200
    And the JSON node "title" should be equal to "Min"
    And the JSON node "content" should be equal to "1234567890"