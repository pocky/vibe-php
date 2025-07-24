# Admin Behat Feature Template

## Feature File Template

```gherkin
@managing_[resources]
Feature: Managing [resources]
    In order to [business_goal]
    As an Administrator
    I want to be able to manage [resources]

    Background:
        Given I am logged in as an administrator
        And the following [resources] exist:
            | name | slug | status | createdAt |
            | [Resource] One | [resource]-one | active | 2024-01-01 |
            | [Resource] Two | [resource]-two | inactive | 2024-01-02 |

    @ui
    Scenario: Browsing [resources]
        When I browse [resources]
        Then I should see 2 [resources] in the list
        And I should see the [resource] "[Resource] One" in the list

    @ui
    Scenario: Adding a new [resource]
        When I want to create a new [resource]
        And I specify its name as "New [Resource]"
        And I specify its slug as "new-[resource]"
        And I add it
        Then I should be notified that it has been successfully created
        And the [resource] "New [Resource]" should appear in the list

    @ui
    Scenario: Updating a [resource]
        When I want to modify the "[Resource] One" [resource]
        And I rename it to "Updated [Resource]"
        And I save my changes
        Then I should be notified that it has been successfully updated
        And I should see the [resource] "Updated [Resource]" in the list
        But I should not see the [resource] "[Resource] One" in the list

    @ui
    Scenario: Deleting a [resource]
        When I delete the "[Resource] Two" [resource]
        Then I should be notified that it has been successfully deleted
        And the [resource] "[Resource] Two" should not appear in the list

    @ui @javascript
    Scenario: Deleting a [resource] with confirmation
        When I try to delete the "[Resource] One" [resource]
        And I confirm the deletion
        Then I should be notified that it has been successfully deleted

    @ui
    Scenario: Filtering [resources] by status
        Given there are 5 active and 3 inactive [resources]
        When I browse [resources]
        And I filter by "active" status
        Then I should see 5 [resources] in the list

    @ui
    Scenario: Searching for [resources]
        When I browse [resources]
        And I search for "One"
        Then I should see 1 [resource] in the list
        And I should see the [resource] "[Resource] One" in the list

    @ui
    Scenario: Sorting [resources] by name
        When I browse [resources]
        And I sort them by "name" in "ascending" order
        Then I should see "[Resource] One" as the first [resource]
        And I should see "[Resource] Two" as the last [resource]

    @ui
    Scenario: Paginating [resources]
        Given there are 30 [resources]
        When I browse [resources]
        Then I should see 10 [resources] in the list
        When I go to the 2nd page
        Then I should see 10 [resources] in the list

    @ui
    Scenario: Changing items per page
        Given there are 30 [resources]
        When I browse [resources]
        And I change items per page to 20
        Then I should see 20 [resources] in the list

    @ui
    Scenario: Bulk deleting [resources]
        When I browse [resources]
        And I check the "[Resource] One" [resource]
        And I check the "[Resource] Two" [resource]
        And I delete them using the batch action
        Then I should be notified that they have been successfully deleted
        And I should see 0 [resources] in the list

    @ui
    Scenario: Trying to add [resource] with existing slug
        Given there is a [resource] with slug "[resource]-one"
        When I want to create a new [resource]
        And I specify its name as "Another [Resource]"
        And I specify its slug as "[resource]-one"
        And I try to add it
        Then I should be notified that slug must be unique
        And the [resource] should not be added

    @ui
    Scenario: Trying to add [resource] without required fields
        When I want to create a new [resource]
        And I try to add it
        Then I should be notified that name is required
        And I should be notified that slug is required

    @ui
    Scenario: Navigating from list to create form
        When I browse [resources]
        And I click on "Create" button
        Then I should be on the [resource] creation page

    @ui
    Scenario: Navigating back from form to list
        When I want to create a new [resource]
        And I click on "Cancel" button
        Then I should be on the [resources] list page

    @ui
    Scenario: Viewing [resource] details
        When I browse [resources]
        And I view details of the "[Resource] One" [resource]
        Then I should see the [resource] name "[Resource] One"
        And I should see the [resource] slug "[resource]-one"

    @ui @workflow
    Scenario: Publishing a draft [resource]
        Given there is a draft [resource] "[Resource] Draft"
        When I browse [resources]
        And I publish the "[Resource] Draft" [resource]
        Then I should be notified that it has been successfully published
        And the [resource] "[Resource] Draft" should have status "published"

    @ui @permissions
    Scenario: Managing [resources] with limited permissions
        Given I am logged in as a content editor
        When I browse [resources]
        Then I should not see the delete button
        And I should be able to create new [resources]
        And I should be able to edit existing [resources]
```

## Feature File for Complex Resource

```gherkin
@managing_[resources] @complex
Feature: Managing [resources] with relationships
    In order to organize complex data
    As an Administrator
    I want to manage [resources] with their relationships

    Background:
        Given I am logged in as an administrator
        And the following categories exist:
            | name | slug |
            | Category A | category-a |
            | Category B | category-b |
        And the following tags exist:
            | name |
            | Tag 1 |
            | Tag 2 |

    @ui
    Scenario: Creating [resource] with relationships
        When I want to create a new [resource]
        And I specify its name as "Complex [Resource]"
        And I select "Category A" from categories
        And I select "Tag 1" and "Tag 2" from tags
        And I add it
        Then I should be notified that it has been successfully created
        And the [resource] should be in "Category A"
        And the [resource] should have tags "Tag 1" and "Tag 2"

    @ui
    Scenario: Filtering by relationship
        Given the following [resources] exist:
            | name | category |
            | [Resource] A1 | Category A |
            | [Resource] A2 | Category A |
            | [Resource] B1 | Category B |
        When I browse [resources]
        And I filter by category "Category A"
        Then I should see 2 [resources] in the list
```

## Edge Cases and Error Scenarios

```gherkin
@ui @edge_cases
Scenario: Handling server errors gracefully
    Given the server will return an error
    When I try to create a new [resource]
    Then I should see an error message
    And I should be able to retry the operation

@ui @validation
Scenario: Complex validation rules
    When I want to create a new [resource]
    And I specify its name with special characters "Test@#$%"
    And I specify a very long description exceeding the limit
    And I try to add it
    Then I should be notified about invalid characters in name
    And I should be notified that description is too long

@ui @concurrency
Scenario: Handling concurrent modifications
    Given another admin is editing the "[Resource] One" [resource]
    When I try to modify the same [resource]
    And I save my changes
    Then I should be notified about concurrent modification
    And I should see the option to reload or force save
```

## Usage Instructions

1. Replace `[resource]` with lowercase singular (e.g., `category`)
2. Replace `[resources]` with lowercase plural (e.g., `categories`)
3. Replace `[Resource]` with capitalized singular (e.g., `Category`)
4. Replace `[Resources]` with capitalized plural (e.g., `Categories`)
5. Add domain-specific fields and scenarios
6. Remove scenarios not applicable to your resource
7. Add custom business logic scenarios