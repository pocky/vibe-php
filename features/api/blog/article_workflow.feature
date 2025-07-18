@blog @api @editorial-workflow
Feature: Editorial workflow through API
    In order to maintain content quality and collaborate effectively
    As a content creator or editor using the API
    I want to manage article workflows including drafts, reviews, and publication

    Background:
        Given the following users exist:
            | name          | role           | email                    |
            | John Writer   | content_creator | john@example.com        |
            | Jane Editor   | editor         | jane.editor@example.com |
            | Mike Author   | content_creator | mike@example.com        |
        And the following articles exist:
            | id                                   | title                    | author      | status    | createdAt           |
            | 550e8400-e29b-41d4-a716-446655440010 | Draft Article           | John Writer | draft     | 2025-01-15 10:00:00 |
            | 550e8400-e29b-41d4-a716-446655440011 | Article Ready to Submit | John Writer | draft     | 2025-01-14 14:00:00 |
            | 550e8400-e29b-41d4-a716-446655440012 | Article Under Review    | Mike Author | pending   | 2025-01-13 09:00:00 |
            | 550e8400-e29b-41d4-a716-446655440013 | Approved Article        | Mike Author | approved  | 2025-01-12 11:00:00 |

    @api @auto-save @content-creation
    Scenario: Auto-save article draft while writing
        Given I am authenticated as "John Writer"
        And I am editing article "550e8400-e29b-41d4-a716-446655440010"
        When I send an auto-save request with:
            """
            {
                "content": "This is my updated content that gets auto-saved while I'm writing. It prevents data loss.",
                "lastSavedAt": "2025-01-15T10:30:00Z"
            }
            """
        Then the draft should be saved successfully with status code 200
        And the response should include:
            | field       | value                    |
            | status      | draft                    |
            | savedAt     | 2025-01-15T10:30:30Z    |
            | version     | 2                        |

    @api @auto-save @conflict-resolution
    Scenario: Handle concurrent auto-save conflicts
        Given I am authenticated as "John Writer"
        And another session is editing the same article
        When I send an auto-save request with outdated version
        Then the request should fail with status code 409
        And I should receive the latest version for conflict resolution
        And the response should include:
            | field              | value                           |
            | conflictType       | concurrent_edit                 |
            | latestVersion      | 3                               |
            | suggestion         | merge_changes                   |

    @api @submit-review @editorial-workflow
    Scenario: Submit article for editorial review
        Given I am authenticated as "John Writer"
        When I submit article "550e8400-e29b-41d4-a716-446655440011" for review with:
            """
            {
                "message": "Ready for review. I've addressed all the SEO requirements and added relevant images.",
                "requestedReviewer": "jane.editor@example.com"
            }
            """
        Then the article should be submitted successfully with status code 200
        And the article status should change to "pending"
        And "Jane Editor" should be notified of the review request
        And the response should include:
            | field           | value                    |
            | status          | pending                  |
            | submittedAt     | 2025-01-15T11:00:00Z    |
            | assignedTo      | Jane Editor              |

    @api @review @editorial-workflow
    Scenario: Editor reviews and approves article
        Given I am authenticated as "Jane Editor"
        When I review article "550e8400-e29b-41d4-a716-446655440012" with:
            """
            {
                "decision": "approve",
                "comments": [
                    {
                        "section": "introduction",
                        "text": "Excellent hook, very engaging!"
                    },
                    {
                        "section": "conclusion",
                        "text": "Strong call-to-action"
                    }
                ],
                "overallFeedback": "Great work! Ready for publication."
            }
            """
        Then the review should be processed successfully with status code 200
        And the article status should change to "approved"
        And "Mike Author" should be notified of the approval
        And the editorial comments should be saved

    @api @review @rejection
    Scenario: Editor rejects article with feedback
        Given I am authenticated as "Jane Editor"
        When I review article "550e8400-e29b-41d4-a716-446655440012" with:
            """
            {
                "decision": "reject",
                "comments": [
                    {
                        "section": "body",
                        "text": "Needs more supporting data and citations"
                    },
                    {
                        "section": "seo",
                        "text": "Meta description is missing"
                    }
                ],
                "overallFeedback": "Please address the inline comments and resubmit.",
                "requiredChanges": ["add_citations", "improve_seo", "expand_content"]
            }
            """
        Then the review should be processed successfully
        And the article status should change to "rejected"
        And "Mike Author" should receive detailed feedback
        And the article should be editable again by the author

    @api @publish @content-publishing
    Scenario: Publish approved article
        Given I am authenticated as "Mike Author"
        When I publish article "550e8400-e29b-41d4-a716-446655440013" with:
            """
            {
                "publishAt": "2025-01-16T09:00:00Z",
                "notifySubscribers": true,
                "socialMedia": {
                    "twitter": true,
                    "linkedin": true,
                    "facebook": false
                }
            }
            """
        Then the article should be published successfully with status code 200
        And the response should include:
            | field            | value                    |
            | status           | published                |
            | publishedAt      | 2025-01-16T09:00:00Z    |
            | url              | /blog/approved-article   |
        And subscribers should be notified
        And social media posts should be scheduled

    @api @publish @validation
    Scenario: Cannot publish unapproved article
        Given I am authenticated as "John Writer"
        When I try to publish article "550e8400-e29b-41d4-a716-446655440010"
        Then the request should fail with status code 422
        And I should receive error "Article must be approved before publication"

    @api @workflow-status @monitoring
    Scenario: Check article workflow status
        Given I am authenticated as "Mike Author"
        When I check workflow status for article "550e8400-e29b-41d4-a716-446655440012"
        Then I should receive the complete workflow history:
            | stage           | status    | timestamp            | actor       |
            | created         | completed | 2025-01-13T09:00:00Z | Mike Author |
            | submitted       | completed | 2025-01-14T10:00:00Z | Mike Author |
            | under_review    | active    | 2025-01-14T11:00:00Z | Jane Editor |

    @api @revisions @version-control
    Scenario: View article revision history
        Given I am authenticated as "John Writer"
        When I request revision history for article "550e8400-e29b-41d4-a716-446655440010"
        Then I should receive all revisions:
            | version | timestamp            | author      | changes                |
            | 1       | 2025-01-15T10:00:00Z | John Writer | Initial creation      |
            | 2       | 2025-01-15T10:30:30Z | John Writer | Auto-save: content update |
            | 3       | 2025-01-15T11:15:00Z | John Writer | Manual save: title change |

    @api @collaboration @real-time
    Scenario: Real-time collaboration notifications
        Given I am authenticated as "John Writer"
        And I have subscribed to article "550e8400-e29b-41d4-a716-446655440012" updates
        When "Jane Editor" adds a comment to the article
        Then I should receive a real-time notification:
            | type        | article_comment          |
            | from        | Jane Editor              |
            | message     | New comment on your article |
            | timestamp   | 2025-01-15T12:00:00Z    |

    @api @unpublish @content-management
    Scenario: Unpublish article for updates
        Given I am authenticated as "Mike Author"
        And article "550e8400-e29b-41d4-a716-446655440013" is published
        When I unpublish the article with:
            """
            {
                "reason": "updating_content",
                "message": "Updating statistics for 2025"
            }
            """
        Then the article should be unpublished successfully
        And the article status should change to "draft"
        And the article should remain accessible to the author
        But the article should not be publicly visible