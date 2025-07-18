---
description: Create Behat feature tests for Sylius Admin UI
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# Behat Admin UI Testing

Create comprehensive Behat tests for Sylius Admin interface using Page Object Model.

## Usage
`/admin:behat [context] [feature-name]`

Example: `/admin:behat Blog managing-categories`


## Symfony Maker Integration

This command complements the Symfony Maker bundle. You can also generate admin test structures using:

```bash
# Currently, there's no specific Maker for Behat admin tests
# But you can use the generated admin resources as a base
docker compose exec app bin/console make:admin:resource BlogContext Category
```

Then use this command to create the corresponding Behat tests.

## Process

1. **Create Feature File**
   ```
   features/
   └── blog/
       └── admin/
           └── managing-categories.feature
   ```

2. **Write Feature Scenarios**
   ```gherkin
   @managing_categories
   Feature: Managing categories
       In order to organize blog content
       As an Administrator
       I want to be able to manage categories

       Background:
           Given I am logged in as an administrator
           And the following categories exist:
               | name       | slug       | status  |
               | Technology | technology | active  |
               | Business   | business   | active  |

       @ui
       Scenario: Browsing categories
           When I browse categories
           Then I should see 2 categories in the list
           And I should see the category "Technology" in the list
           And I should see the category "Business" in the list

       @ui
       Scenario: Adding a new category
           When I want to create a new category
           And I specify its name as "Marketing"
           And I specify its slug as "marketing"
           And I add it
           Then I should be notified that it has been successfully created
           And the category "Marketing" should appear in the list

       @ui
       Scenario: Updating a category
           When I want to modify the "Technology" category
           And I rename it to "Tech & Innovation"
           And I save my changes
           Then I should be notified that it has been successfully updated
           And I should see the category "Tech & Innovation" in the list

       @ui
       Scenario: Deleting a category
           When I delete the "Business" category
           Then I should be notified that it has been successfully deleted
           And the category "Business" should not appear in the list
   ```

3. **Create Context Class**
   ```php
   namespace App\Tests\BlogContext\Behat\Context\Ui\Admin;

   use Behat\Behat\Context\Context;
   use App\Tests\BlogContext\Behat\Page\Admin\Category\IndexPageInterface;
   use App\Tests\BlogContext\Behat\Page\Admin\Category\CreatePageInterface;
   use App\Tests\BlogContext\Behat\Page\Admin\Category\UpdatePageInterface;
   use Webmozart\Assert\Assert;

   final class ManagingCategoriesContext implements Context
   {
       public function __construct(
           private readonly IndexPageInterface $indexPage,
           private readonly CreatePageInterface $createPage,
           private readonly UpdatePageInterface $updatePage,
       ) {}

       /**
        * @When I browse categories
        */
       public function iBrowseCategories(): void
       {
           $this->indexPage->open();
       }

       /**
        * @When I want to create a new category
        */
       public function iWantToCreateANewCategory(): void
       {
           $this->indexPage->open();
           $this->indexPage->clickCreateButton();
       }

       /**
        * @When I specify its name as :name
        */
       public function iSpecifyItsNameAs(string $name): void
       {
           $this->createPage->specifyName($name);
       }

       /**
        * @When I add it
        */
       public function iAddIt(): void
       {
           $this->createPage->create();
       }

       /**
        * @Then I should see :count categories in the list
        */
       public function iShouldSeeCategoriesInTheList(int $count): void
       {
           Assert::same($this->indexPage->countItems(), $count);
       }
   }
   ```

4. **Create Page Objects**
   ```php
   // IndexPageInterface.php
   namespace App\Tests\BlogContext\Behat\Page\Admin\Category;

   use App\Tests\Shared\Behat\Page\Admin\Crud\IndexPageInterface as BaseIndexPageInterface;

   interface IndexPageInterface extends BaseIndexPageInterface
   {
       public function deleteCategory(string $name): void;
   }

   // IndexPage.php
   namespace App\Tests\BlogContext\Behat\Page\Admin\Category;

   use App\Tests\Shared\Behat\Page\Admin\Crud\IndexPage as BaseIndexPage;

   final class IndexPage extends BaseIndexPage implements IndexPageInterface
   {
       public function getRouteName(): string
       {
           return 'app_admin_category_index';
       }

       public function deleteCategory(string $name): void
       {
           $this->deleteResourceOnPage(['name' => $name]);
       }
   }
   ```

5. **Test Grid Operations**
   ```gherkin
   Scenario: Filtering categories by status
       Given there are 5 active and 3 inactive categories
       When I browse categories
       And I filter by "active" status
       Then I should see 5 categories in the list

   Scenario: Sorting categories by name
       When I browse categories
       And I sort them by name
       Then I should see categories in alphabetical order
   ```

6. **Test Form Validation**
   ```gherkin
   Scenario: Trying to add category with duplicate slug
       Given there is a category with slug "technology"
       When I want to create a new category
       And I specify its name as "New Tech"
       And I specify its slug as "technology"
       And I try to add it
       Then I should be notified that slug must be unique
       And the category should not be added
   ```

7. **Test Bulk Actions**
   ```gherkin
   Scenario: Bulk deleting categories
       When I browse categories
       And I check the "Technology" category
       And I check the "Business" category
       And I delete them using the batch action
       Then I should be notified that they have been successfully deleted
       And I should see 0 categories in the list
   ```

8. **Configure Behat Suite**
   ```yaml
   # behat.dist.php
   'admin_ui' => [
       'contexts' => [
           ['App\Tests\BlogContext\Behat\Context\Ui\Admin\ManagingCategoriesContext'],
           ['App\Tests\Shared\Behat\Context\NotificationContext'],
           ['App\Tests\Shared\Behat\Context\HookContext'],
       ],
       'filters' => ['tags' => '@ui && @managing_categories'],
       'extensions' => [
           FriendsOfBehat\SymfonyExtension::class => [
               'bootstrap' => 'tests/bootstrap.php',
           ],
           FriendsOfBehat\VariadicExtension::class => [],
       ],
   ]
   ```

9. **Run Tests**
   ```bash
   # Run all admin UI tests
   docker compose exec app vendor/bin/behat --suite=admin_ui

   # Run specific feature
   docker compose exec app vendor/bin/behat features/blog/admin/managing-categories.feature

   # Run with specific tags
   docker compose exec app vendor/bin/behat --tags="@managing_categories && @ui"
   ```

## Page Object Model Structure

Following the patterns from @docs/testing/behat-sylius-patterns.md:

```
tests/[Context]/Behat/
├── Context/
│   └── Ui/Admin/
│       ├── Managing[Resource]Context.php    # CRUD operations
│       └── [Workflow]Context.php           # Business workflows
└── Page/
    └── Admin/
        └── [Resource]/
            ├── IndexPageInterface.php      # List/grid operations
            ├── IndexPage.php
            ├── CreatePageInterface.php     # Creation form
            ├── CreatePage.php
            ├── UpdatePageInterface.php     # Update form
            └── UpdatePage.php
```

## Best Practices

### Feature Organization
- One feature file per resource type
- Separate UI tests with `@ui` tag
- Use `@managing_[resource]` for resource management
- Background for common setup

### Context Design
- Single responsibility per context
- Use dependency injection for page objects
- Descriptive method names using domain language
- Assertions in context methods

### Page Object Patterns
- Encapsulate CSS selectors in page objects
- Business-focused method names
- Extend base page classes for common functionality
- Use interfaces for flexibility

### Data Management
- Use Foundry factories for test data
- Clean database between scenarios
- Meaningful test data that reflects real usage

## Quality Standards
- Follow patterns from @docs/testing/behat-sylius-patterns.md
- Maintain consistency with existing admin tests
- Cover happy paths and error scenarios
- Test accessibility and usability

## Common Scenarios

### Grid Testing
- Browsing with pagination
- Filtering by various criteria
- Sorting columns
- Bulk actions

### Form Testing
- Creating with valid data
- Validation errors
- Updating existing items
- Complex form interactions

### Workflow Testing
- Multi-step processes
- State transitions
- Permission-based actions
- Business rule validation

## Next Steps
1. Create page objects for complex UI elements
2. Add custom step definitions for domain logic
3. Implement shared contexts for common operations
4. Create performance scenarios for large datasets
