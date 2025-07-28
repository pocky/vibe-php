@blog @api @tag
Feature: Tag management via REST API
    In order to organize blog content
    As an API client
    I want to manage tags through REST endpoints

    Background:
        Given I have a valid API token

    Scenario: Creating a new tag
        When I send a POST request to "/api/blog/tags" with body:
            """
            {
                "name": "Technology",
                "slug": "technology"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON node "name" should be equal to "Technology"
        And the JSON node "slug" should be equal to "technology"
        And the JSON node "id" should exist
        And the JSON node "createdAt" should exist

    Scenario: Creating a tag without slug (auto-generation)
        When I send a POST request to "/api/blog/tags" with body:
            """
            {
                "name": "Cloud Computing"
            }
            """
        Then the response status code should be 201
        And the JSON node "name" should be equal to "Cloud Computing"
        And the JSON node "slug" should be equal to "cloud-computing"

    Scenario: Creating a tag with invalid data
        When I send a POST request to "/api/blog/tags" with body:
            """
            {
                "name": ""
            }
            """
        Then the response status code should be 422
        And the JSON node "violations[0].propertyPath" should be equal to "name"

    Scenario: Listing tags with pagination
        Given the following tags exist:
            | name        | slug        |
            | Technology  | technology  |
            | Science     | science     |
            | Travel      | travel      |
        When I send a GET request to "/api/blog/tags"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "hydra:totalItems" should be equal to 3
        And the JSON node "hydra:member" should have 3 elements

    Scenario: Getting a specific tag
        Given a tag exists with id "tag-123" and name "Technology"
        When I send a GET request to "/api/blog/tags/tag-123"
        Then the response status code should be 200
        And the JSON node "id" should be equal to "tag-123"
        And the JSON node "name" should be equal to "Technology"

    Scenario: Getting a non-existent tag
        When I send a GET request to "/api/blog/tags/non-existent"
        Then the response status code should be 404

    Scenario: Updating a tag
        Given a tag exists with id "tag-123" and name "Old Name"
        When I send a PUT request to "/api/blog/tags/tag-123" with body:
            """
            {
                "name": "New Name",
                "slug": "new-name"
            }
            """
        Then the response status code should be 200
        And the JSON node "name" should be equal to "New Name"
        And the JSON node "slug" should be equal to "new-name"
        And the JSON node "updatedAt" should exist

    Scenario: Updating a non-existent tag
        When I send a PUT request to "/api/blog/tags/non-existent" with body:
            """
            {
                "name": "New Name",
                "slug": "new-name"
            }
            """
        Then the response status code should be 404

    Scenario: Deleting a tag
        Given a tag exists with id "tag-123"
        When I send a DELETE request to "/api/blog/tags/tag-123"
        Then the response status code should be 204
        When I send a GET request to "/api/blog/tags/tag-123"
        Then the response status code should be 404

    Scenario: Deleting a non-existent tag
        When I send a DELETE request to "/api/blog/tags/non-existent"
        Then the response status code should be 404

    Scenario: Creating a duplicate tag
        Given a tag exists with name "Technology" and slug "technology"
        When I send a POST request to "/api/blog/tags" with body:
            """
            {
                "name": "Technology",
                "slug": "technology"
            }
            """
        Then the response status code should be 422
        And the response should contain "already exists"