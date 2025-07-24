# Admin Testing Quick Reference

## Page Object Pattern Quick Start

### Creating a New Admin Page Object

1. **Create the Interface**
```php
// tests/BlogContext/Behat/Page/Admin/MyEntity/IndexPageInterface.php
interface IndexPageInterface extends \App\Tests\BlogContext\Behat\Page\Admin\Crud\IndexPageInterface
{
    public function hasEntityWithName(string $name): bool;
    public function createNewEntity(): void;
    public function editEntity(string $name): void;
}
```

2. **Implement the Page Object**
```php
// tests/BlogContext/Behat/Page/Admin/MyEntity/IndexPage.php
final class IndexPage extends \App\Tests\BlogContext\Behat\Page\Admin\Crud\IndexPage implements IndexPageInterface
{
    public function getUrl(array $urlParameters = []): string
    {
        return '/admin/my-entities';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'create_button' => '.btn-primary:contains("Create")',
            'entity_table' => 'table.entities-grid',
        ]);
    }

    public function hasEntityWithName(string $name): bool
    {
        return $this->isSingleResourceOnPage(['name' => $name]);
    }
}
```

3. **Create the Context**
```php
// tests/BlogContext/Behat/Context/Ui/Admin/ManagingMyEntitiesContext.php
class ManagingMyEntitiesContext implements Context
{
    private IndexPageInterface $indexPage;

    public function __construct(
        private readonly Session $session,
        private readonly RouterInterface $router,
    ) {
        $this->indexPage = new IndexPage($this->session, $this->router);
    }

    #[\Behat\Step\Given('there are entities:')]
    public function thereAreEntities(TableNode $table): void
    {
        // Create test data using factories
    }

    #[\Behat\Step\Then('I should see :name in the entities grid')]
    public function iShouldSeeInTheEntitiesGrid(string $name): void
    {
        Assert::true($this->indexPage->hasEntityWithName($name));
    }
}
```

## Common Step Definitions

### Grid Operations
```php
#[\Behat\Step\Then('I should see the :entity grid')]
public function iShouldSeeTheGrid(string $entity): void
{
    Assert::true($this->indexPage->isOpen());
}

#[\Behat\Step\Then('I should see :count :entities in the grid')]
public function iShouldSeeEntitiesInTheGrid(int $count, string $entities): void
{
    Assert::eq($count, $this->indexPage->countItems());
}

#[\Behat\Step\Then('I should see no results in the grid')]
public function iShouldSeeNoResultsInTheGrid(): void
{
    Assert::true($this->indexPage->isEmpty() || $this->indexPage->hasNoResultMessage());
}
```

### CRUD Operations
```php
#[\Behat\Step\When('I create a new :entity')]
public function iCreateANew(string $entity): void
{
    $this->indexPage->clickCreateEntity();
}

#[\Behat\Step\When('I edit :entity :name')]
public function iEditEntity(string $entity, string $name): void
{
    $this->indexPage->editEntity($name);
}

#[\Behat\Step\When('I delete :entity :name')]
public function iDeleteEntity(string $entity, string $name): void
{
    $this->indexPage->deleteEntity($name);
}
```

### Filtering and Searching
```php
#[\Behat\Step\When('I filter by :field :value')]
public function iFilterBy(string $field, string $value): void
{
    $this->indexPage->filter([$field => $value]);
}

#[\Behat\Step\When('I search for :term')]
public function iSearchFor(string $term): void
{
    $this->indexPage->search($term);
}
```

## Feature File Templates

### Basic CRUD Feature
```gherkin
Feature: Managing [Entity] in admin
  In order to manage [business purpose]
  As an administrator
  I want to be able to create, read, update and delete [entities]

  Background:
    Given I am on the admin dashboard

  Scenario: View empty [entities] list
    Given there are 0 [entities]
    When I go to "/admin/[entities]"
    Then I should see the [entities] grid
    And I should see no results in the grid

  Scenario: View [entities] list with data
    Given there are [entities]:
      | name        | status |
      | First Item  | active |
      | Second Item | draft  |
    When I go to "/admin/[entities]"
    Then I should see "First Item" in the grid
    And I should see "Second Item" in the grid

  Scenario: Create new [entity]
    When I go to "/admin/[entities]"
    And I create a new [entity]
    Then I should see "Name" field
    And I should see "Status" field
```

### Grid Operations Feature
```gherkin
Feature: [Entity] grid operations
  In order to efficiently manage [entities]
  As an administrator
  I want to use grid features like filtering and pagination

  Background:
    Given I am on the admin dashboard

  Scenario: Filter [entities] by status
    Given there are [entities]:
      | name   | status |
      | Active | active |
      | Draft  | draft  |
    When I go to "/admin/[entities]"
    And I filter by status "active"
    Then I should see "Active" in the grid
    And I should not see "Draft" in the grid

  Scenario: Pagination with many [entities]
    Given there are 25 [entities]
    When I go to "/admin/[entities]"
    Then I should see 10 [entities] in the grid
    And I should see pagination controls
```

## Behat Configuration

### Add Context to Suite
```php
// behat.dist.php
->withSuite(
    (new Suite('admin'))
        ->withPaths('features/admin')
        ->withContexts(
            ManagingArticlesContext::class,
            ManagingMyEntitiesContext::class,  // Add your new context
            EditorialDashboardContext::class,
            DoctrineORMContext::class,
            CommonNavigationContext::class,
        )
)
```

## Common Element Selectors

### Sylius Admin UI Patterns
```php
protected function getDefinedElements(): array
{
    return [
        // Tables and grids
        'table' => 'table, .sylius-grid table',
        'rows' => 'tbody tr',
        'headers' => 'thead th',
        
        // Buttons
        'create_button' => '.btn-primary, a:contains("Create"), .actions a.btn-primary',
        'edit_button' => '.btn-warning, a:contains("Edit"), .actions a.btn-warning',
        'delete_button' => '.btn-danger, a:contains("Delete"), .actions a.btn-danger',
        
        // Forms
        'form' => 'form[name*="sylius"], form.ui-form',
        'submit_button' => 'button[type="submit"], input[type="submit"]',
        
        // Filters
        'filters' => '.filters, .sylius-grid-nav',
        'filter_form' => 'form[name*="criteria"]',
        
        // Pagination
        'pagination' => '.pagination, ul.pagination, .sylius-grid-pagination',
        'pagination_info' => '.pagination-info, .showing-entries',
        
        // Messages
        'success_message' => '.alert-success, .ui.positive.message',
        'error_message' => '.alert-danger, .ui.negative.message',
        'no_results' => '.empty-state, .no-results, tbody:empty',
    ];
}
```

## Testing Checklist

### Before Creating Admin Tests
- [ ] Does the admin route exist and respond with 200?
- [ ] Is the page using standard grid/form structure?
- [ ] Are there existing Page Objects I can extend?
- [ ] Do I need custom element selectors?

### Page Object Quality
- [ ] Methods use business language (not technical)
- [ ] CSS selectors are encapsulated in `getDefinedElements()`
- [ ] Error messages are descriptive
- [ ] Methods handle both success and failure cases
- [ ] Page object extends appropriate base class

### Context Quality
- [ ] Context focuses on single business domain
- [ ] Step definitions are reusable
- [ ] Proper data setup using factories
- [ ] Assertions use descriptive messages
- [ ] Navigation delegated to shared contexts

### Feature Quality
- [ ] Scenarios test business workflows
- [ ] Background provides common setup
- [ ] Data tables used for complex test data
- [ ] Scenario names are descriptive
- [ ] Both happy path and edge cases covered

## Debugging Tests

### Common Issues and Solutions

**Page Object Not Found**
```bash
# Check autoloader
docker compose exec app composer dump-autoload

# Verify class exists and namespace is correct
docker compose exec app php -r "var_dump(class_exists('App\\Tests\\BlogContext\\Behat\\Page\\Admin\\MyEntity\\IndexPage'));"
```

**Element Not Found**
```php
// Add debugging to page object
public function debugPageContent(): void
{
    echo "Page URL: " . $this->session->getCurrentUrl() . "\n";
    echo "Page Content: " . substr($this->session->getPage()->getContent(), 0, 1000) . "\n";
}
```

**Step Definition Not Found**
```bash
# List available steps
docker compose exec app vendor/bin/behat -dl

# Generate missing steps
docker compose exec app vendor/bin/behat --snippets-for="App\Tests\BlogContext\Behat\Context\Ui\Admin\MyContext"
```

## Performance Tips

1. **Use data factories for setup** instead of step-by-step creation
2. **Limit data creation** to minimum needed for test
3. **Use database transactions** in hooks to clean up
4. **Avoid UI interactions** for test data setup when possible
5. **Group related scenarios** to share setup costs

This quick reference should help you implement admin testing following the established patterns efficiently.