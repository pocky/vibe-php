# Admin Behat Page Object Templates

## Base Page Interface

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Page;

interface PageInterface
{
    public function open(array $urlParameters = []): void;
    
    public function isOpen(): bool;
    
    public function getUrl(array $urlParameters = []): string;
}
```

## Base Symfony Page

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Page;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;

abstract class SymfonyPage implements PageInterface
{
    public function __construct(
        protected readonly Session $session,
        protected readonly RouterInterface $router,
        protected array $parameters = []
    ) {
    }

    public function open(array $urlParameters = []): void
    {
        $this->session->visit($this->getUrl($urlParameters));
    }

    public function isOpen(): bool
    {
        return $this->session->getCurrentUrl() === $this->getUrl();
    }

    public function getUrl(array $urlParameters = []): string
    {
        return $this->router->generate($this->getRouteName(), $urlParameters);
    }

    abstract protected function getRouteName(): string;

    protected function getDefinedElements(): array
    {
        return [];
    }

    protected function getElement(string $name): NodeElement
    {
        $elements = $this->getDefinedElements();
        
        if (!isset($elements[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Element "%s" is not defined. Available elements: %s',
                $name,
                implode(', ', array_keys($elements))
            ));
        }

        $element = $this->session->getPage()->find('css', $elements[$name]);
        
        if (null === $element) {
            throw new \RuntimeException(sprintf(
                'Element "%s" not found on the page by selector "%s"',
                $name,
                $elements[$name]
            ));
        }

        return $element;
    }

    protected function hasElement(string $name): bool
    {
        $elements = $this->getDefinedElements();
        
        if (!isset($elements[$name])) {
            return false;
        }

        return null !== $this->session->getPage()->find('css', $elements[$name]);
    }

    protected function waitForElement(string $name, int $timeout = 5): NodeElement
    {
        $elements = $this->getDefinedElements();
        
        if (!isset($elements[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Element "%s" is not defined',
                $name
            ));
        }

        $this->session->wait(
            $timeout * 1000,
            sprintf('document.querySelector("%s") !== null', $elements[$name])
        );

        return $this->getElement($name);
    }
}
```

## Index Page Interface

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\Admin\Crud\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function specifyFilterType(string $field, string $type): void;
    
    public function specifyFilterValue(string $field, string $value): void;
    
    public function filter(): void;
    
    public function search(string $phrase): void;
    
    public function sortBy(string $field, string $order = 'ascending'): void;
    
    public function goToPage(int $page): void;
    
    public function changeItemsPerPage(int $limit): void;
    
    public function checkResourceOnPage(array $parameters): void;
    
    public function bulkDelete(): void;
    
    public function executeActionOnResource(array $parameters, string $action): void;
    
    public function showResource(array $parameters): void;
    
    public function getFieldFromRow(array $parameters, string $field): string;
    
    public function clickCreateButton(): void;
}
```

## Index Page Implementation

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\Admin\Crud\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    protected function getRouteName(): string
    {
        return 'app_admin_[resource]_index';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'filter_status' => '#filters_status',
            'filter_search' => '#filters_search',
            'filter_button' => 'button[type="submit"]',
            'sort_name' => 'th a[data-sort="name"]',
            'sort_createdAt' => 'th a[data-sort="createdAt"]',
            'bulk_action_select' => '#bulk_action',
            'bulk_action_button' => '#bulk_action_button',
            'pagination' => '.pagination',
            'items_per_page' => '#items_per_page',
            'create_button' => 'a:contains("Create")',
        ]);
    }

    public function specifyFilterType(string $field, string $type): void
    {
        if ($field === 'status') {
            // Status filter is usually a select, not a type selector
            return;
        }
        
        $this->getElement(sprintf('filter_%s_type', $field))->selectOption($type);
    }

    public function specifyFilterValue(string $field, string $value): void
    {
        $element = $this->getElement(sprintf('filter_%s', $field));
        
        if ($element->getTagName() === 'select') {
            $element->selectOption($value);
        } else {
            $element->setValue($value);
        }
    }

    public function filter(): void
    {
        $this->getElement('filter_button')->click();
    }

    public function search(string $phrase): void
    {
        $this->getElement('filter_search')->setValue($phrase);
        $this->filter();
    }

    public function sortBy(string $field, string $order = 'ascending'): void
    {
        $sortElement = $this->getElement(sprintf('sort_%s', $field));
        $currentOrder = $sortElement->getAttribute('data-order');
        
        if ($currentOrder !== $order) {
            $sortElement->click();
        }
    }

    public function goToPage(int $page): void
    {
        $this->session->getPage()
            ->find('css', sprintf('.pagination a:contains("%d")', $page))
            ->click();
    }

    public function changeItemsPerPage(int $limit): void
    {
        $this->getElement('items_per_page')->selectOption((string) $limit);
    }

    public function checkResourceOnPage(array $parameters): void
    {
        $tableRow = $this->getTableRowWithFields($parameters);
        $checkbox = $tableRow->find('css', 'input[type="checkbox"]');
        $checkbox->check();
    }

    public function bulkDelete(): void
    {
        $this->getElement('bulk_action_select')->selectOption('delete');
        $this->getElement('bulk_action_button')->click();
    }

    public function executeActionOnResource(array $parameters, string $action): void
    {
        $tableRow = $this->getTableRowWithFields($parameters);
        $actionButton = $tableRow->find('css', sprintf('a[data-action="%s"]', $action));
        $actionButton->click();
    }

    public function showResource(array $parameters): void
    {
        $this->chooseToView($parameters);
    }

    public function getFieldFromRow(array $parameters, string $field): string
    {
        $tableRow = $this->getTableRowWithFields($parameters);
        $fieldElement = $tableRow->find('css', sprintf('[data-field="%s"]', $field));
        
        return $fieldElement->getText();
    }

    public function clickCreateButton(): void
    {
        $this->getElement('create_button')->click();
    }
}
```

## Create Page Interface

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function specifyName(string $name): void;
    
    public function specifySlug(string $slug): void;
    
    public function specifyDescription(string $description): void;
    
    public function selectStatus(string $status): void;
    
    public function getValidationMessage(string $field): string;
}
```

## Create Page Implementation

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    protected function getRouteName(): string
    {
        return 'app_admin_[resource]_create';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'name' => '#[resource]_name',
            'slug' => '#[resource]_slug',
            'description' => '#[resource]_description',
            'status' => '#[resource]_status',
            'submit' => 'button[type="submit"]',
            'cancel' => 'a:contains("Cancel")',
        ]);
    }

    public function specifyName(string $name): void
    {
        $this->getElement('name')->setValue($name);
    }

    public function specifySlug(string $slug): void
    {
        $this->getElement('slug')->setValue($slug);
    }

    public function specifyDescription(string $description): void
    {
        $this->getElement('description')->setValue($description);
    }

    public function selectStatus(string $status): void
    {
        $this->getElement('status')->selectOption($status);
    }

    public function getValidationMessage(string $field): string
    {
        $fieldElement = $this->getElement($field);
        $validationElement = $fieldElement
            ->getParent()
            ->find('css', '.invalid-feedback, .help-block');
        
        if (null === $validationElement) {
            throw new \RuntimeException(sprintf(
                'No validation message found for field "%s"',
                $field
            ));
        }
        
        return $validationElement->getText();
    }
}
```

## Update Page Interface

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\Admin\Crud\UpdatePageInterface as BaseUpdatePageInterface;

interface UpdatePageInterface extends BaseUpdatePageInterface
{
    public function changeName(string $name): void;
    
    public function changeSlug(string $slug): void;
    
    public function changeDescription(string $description): void;
    
    public function changeStatus(string $status): void;
}
```

## Update Page Implementation

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;

final class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    protected function getRouteName(): string
    {
        return 'app_admin_[resource]_update';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'name' => '#[resource]_name',
            'slug' => '#[resource]_slug',
            'description' => '#[resource]_description',
            'status' => '#[resource]_status',
            'submit' => 'button[type="submit"]',
            'cancel' => 'a:contains("Cancel")',
        ]);
    }

    public function changeName(string $name): void
    {
        $this->getElement('name')->setValue($name);
    }

    public function changeSlug(string $slug): void
    {
        $this->getElement('slug')->setValue($slug);
    }

    public function changeDescription(string $description): void
    {
        $this->getElement('description')->setValue($description);
    }

    public function changeStatus(string $status): void
    {
        $this->getElement('status')->selectOption($status);
    }
}
```

## Show Page Interface

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\PageInterface;

interface ShowPageInterface extends PageInterface
{
    public function getName(): string;
    
    public function getSlug(): string;
    
    public function getStatus(): string;
    
    public function hasEditButton(): bool;
    
    public function hasDeleteButton(): bool;
    
    public function clickEditButton(): void;
    
    public function clickBackButton(): void;
}
```

## Show Page Implementation

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Page\Admin\[Resource];

use App\Tests\Shared\Behat\Page\SymfonyPage;

final class ShowPage extends SymfonyPage implements ShowPageInterface
{
    protected function getRouteName(): string
    {
        return 'app_admin_[resource]_show';
    }

    protected function getDefinedElements(): array
    {
        return [
            'name' => '[data-test-name]',
            'slug' => '[data-test-slug]',
            'status' => '[data-test-status]',
            'edit_button' => 'a:contains("Edit")',
            'delete_button' => 'button:contains("Delete")',
            'back_button' => 'a:contains("Back to list")',
        ];
    }

    public function getName(): string
    {
        return $this->getElement('name')->getText();
    }

    public function getSlug(): string
    {
        return $this->getElement('slug')->getText();
    }

    public function getStatus(): string
    {
        return $this->getElement('status')->getText();
    }

    public function hasEditButton(): bool
    {
        return $this->hasElement('edit_button');
    }

    public function hasDeleteButton(): bool
    {
        return $this->hasElement('delete_button');
    }

    public function clickEditButton(): void
    {
        $this->getElement('edit_button')->click();
    }

    public function clickBackButton(): void
    {
        $this->getElement('back_button')->click();
    }
}
```

## Usage Instructions

1. Replace placeholders:
   - `[Context]` → Your context name (e.g., `Blog`)
   - `[Resource]` → Capitalized singular (e.g., `Category`)
   - `[resource]` → Lowercase singular (e.g., `category`)

2. Place files in correct structure:
   ```
   tests/[Context]Context/Behat/Page/Admin/[Resource]/
   ├── IndexPageInterface.php
   ├── IndexPage.php
   ├── CreatePageInterface.php
   ├── CreatePage.php
   ├── UpdatePageInterface.php
   ├── UpdatePage.php
   ├── ShowPageInterface.php
   └── ShowPage.php
   ```

3. Register pages as services with dependency injection

4. Customize element selectors based on actual HTML

5. Add domain-specific methods as needed