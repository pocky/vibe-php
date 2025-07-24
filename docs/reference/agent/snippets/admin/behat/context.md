# Admin Behat Context Template

## Managing Context Template

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use App\Tests\[Context]Context\Behat\Page\Admin\[Resource]\CreatePageInterface;
use App\Tests\[Context]Context\Behat\Page\Admin\[Resource]\IndexPageInterface;
use App\Tests\[Context]Context\Behat\Page\Admin\[Resource]\UpdatePageInterface;
use App\Tests\Shared\Behat\Service\NotificationCheckerInterface;
use Webmozart\Assert\Assert;

final class Managing[Resources]Context implements Context
{
    public function __construct(
        private readonly IndexPageInterface $indexPage,
        private readonly CreatePageInterface $createPage,
        private readonly UpdatePageInterface $updatePage,
        private readonly NotificationCheckerInterface $notificationChecker,
    ) {
    }

    /**
     * @When I browse [resources]
     * @When I want to browse [resources]
     */
    public function iBrowse[Resources](): void
    {
        $this->indexPage->open();
    }

    /**
     * @When I want to create a new [resource]
     */
    public function iWantToCreateANew[Resource](): void
    {
        $this->createPage->open();
    }

    /**
     * @When I want to modify the :name [resource]
     * @When I want to edit the :name [resource]
     */
    public function iWantToModifyThe[Resource](string $name): void
    {
        $this->indexPage->open();
        $this->indexPage->chooseToEdit($name);
    }

    /**
     * @When I specify its name as :name
     * @When I name it :name
     */
    public function iSpecifyItsNameAs(string $name): void
    {
        $this->createPage->specifyName($name);
    }

    /**
     * @When I specify its slug as :slug
     */
    public function iSpecifyItsSlugAs(string $slug): void
    {
        $this->createPage->specifySlug($slug);
    }

    /**
     * @When I rename it to :name
     * @When I change its name to :name
     */
    public function iRenameItTo(string $name): void
    {
        $this->updatePage->changeName($name);
    }

    /**
     * @When I add it
     * @When I try to add it
     */
    public function iAddIt(): void
    {
        $this->createPage->create();
    }

    /**
     * @When I save my changes
     * @When I try to save my changes
     */
    public function iSaveMyChanges(): void
    {
        $this->updatePage->saveChanges();
    }

    /**
     * @When I delete the :name [resource]
     */
    public function iDeleteThe[Resource](string $name): void
    {
        $this->indexPage->open();
        $this->indexPage->deleteResourceOnPage(['name' => $name]);
    }

    /**
     * @When I filter by :status status
     */
    public function iFilterByStatus(string $status): void
    {
        $this->indexPage->specifyFilterType('status', 'equals');
        $this->indexPage->specifyFilterValue('status', $status);
        $this->indexPage->filter();
    }

    /**
     * @When I search for :phrase
     */
    public function iSearchFor(string $phrase): void
    {
        $this->indexPage->search($phrase);
    }

    /**
     * @When I sort them by :field in :order order
     */
    public function iSortThemBy(string $field, string $order = 'ascending'): void
    {
        $this->indexPage->sortBy($field, $order);
    }

    /**
     * @When I go to the :number page
     * @When I go to the :number(st|nd|rd|th) page
     */
    public function iGoToThePage(int $number): void
    {
        $this->indexPage->goToPage($number);
    }

    /**
     * @When I change items per page to :limit
     */
    public function iChangeItemsPerPageTo(int $limit): void
    {
        $this->indexPage->changeItemsPerPage($limit);
    }

    /**
     * @When I check the :name [resource]
     */
    public function iCheckThe[Resource](string $name): void
    {
        $this->indexPage->checkResourceOnPage(['name' => $name]);
    }

    /**
     * @When I delete them using the batch action
     */
    public function iDeleteThemUsingTheBatchAction(): void
    {
        $this->indexPage->bulkDelete();
    }

    /**
     * @Then I should see :count [resources] in the list
     */
    public function iShouldSee[Resources]InTheList(int $count): void
    {
        Assert::same($this->indexPage->countItems(), $count);
    }

    /**
     * @Then I should( still) see the [resource] :name in the list
     */
    public function iShouldSeeThe[Resource]InTheList(string $name): void
    {
        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $name]));
    }

    /**
     * @Then I should not see the [resource] :name in the list
     * @Then the [resource] :name should not appear in the list
     */
    public function iShouldNotSeeThe[Resource]InTheList(string $name): void
    {
        Assert::false($this->indexPage->isSingleResourceOnPage(['name' => $name]));
    }

    /**
     * @Then the [resource] :name should appear in the list
     * @Then the [resource] :name should exist in the list
     */
    public function the[Resource]ShouldAppearInTheList(string $name): void
    {
        $this->indexPage->open();
        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $name]));
    }

    /**
     * @Then I should be notified that it has been successfully created
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyCreated(): void
    {
        $this->notificationChecker->checkNotification(
            '[Resource] has been successfully created.',
            'success'
        );
    }

    /**
     * @Then I should be notified that it has been successfully updated
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyUpdated(): void
    {
        $this->notificationChecker->checkNotification(
            '[Resource] has been successfully updated.',
            'success'
        );
    }

    /**
     * @Then I should be notified that it has been successfully deleted
     * @Then I should be notified that they have been successfully deleted
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyDeleted(): void
    {
        $this->notificationChecker->checkNotification(
            '[Resource](s) ha(s|ve) been successfully deleted.',
            'success'
        );
    }

    /**
     * @Then I should be notified that :field is required
     */
    public function iShouldBeNotifiedThatFieldIsRequired(string $field): void
    {
        Assert::same(
            $this->createPage->getValidationMessage($field),
            sprintf('Please enter [resource] %s.', $field)
        );
    }

    /**
     * @Then I should be notified that slug must be unique
     */
    public function iShouldBeNotifiedThatSlugMustBeUnique(): void
    {
        Assert::same(
            $this->createPage->getValidationMessage('slug'),
            'This slug is already used.'
        );
    }
}
```

## Setup Context Template

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use App\[Context]Context\Domain\Shared\ValueObject\[Resource]Id;
use App\[Context]Context\Domain\Shared\ValueObject\[Resource]Slug;
use App\[Context]Context\Domain\Shared\ValueObject\[Resource]Status;
use App\Tests\[Context]Context\Foundry\[Resource]Factory;
use Symfony\Component\Uid\Uuid;

final class [Resource]Context implements Context
{
    /**
     * @Given the following [resources] exist:
     */
    public function theFollowing[Resources]Exist(TableNode $table): void
    {
        foreach ($table->getHash() as $row) {
            $data = [
                'id' => isset($row['id']) ? Uuid::fromString($row['id']) : Uuid::v7(),
                'name' => $row['name'],
                'slug' => $row['slug'] ?? null,
                'status' => $row['status'] ?? 'active',
                'createdAt' => isset($row['createdAt']) 
                    ? new \DateTimeImmutable($row['createdAt']) 
                    : new \DateTimeImmutable(),
            ];

            if (isset($row['description'])) {
                $data['description'] = $row['description'];
            }

            [Resource]Factory::createOne($data);
        }
    }

    /**
     * @Given there is a [resource] with slug :slug
     */
    public function thereIsA[Resource]WithSlug(string $slug): void
    {
        [Resource]Factory::createOne([
            'name' => ucfirst($slug) . ' [Resource]',
            'slug' => $slug,
            'status' => 'active',
        ]);
    }

    /**
     * @Given there are :count [resources]
     */
    public function thereAre[Resources](int $count): void
    {
        [Resource]Factory::createMany($count);
    }

    /**
     * @Given there are :activeCount active and :inactiveCount inactive [resources]
     */
    public function thereAreActiveAndInactive[Resources](int $activeCount, int $inactiveCount): void
    {
        [Resource]Factory::createMany($activeCount, ['status' => 'active']);
        [Resource]Factory::createMany($inactiveCount, ['status' => 'inactive']);
    }

    /**
     * @Given there is a draft [resource] :name
     */
    public function thereIsADraft[Resource](string $name): void
    {
        [Resource]Factory::createOne([
            'name' => $name,
            'status' => 'draft',
        ]);
    }
}
```

## Hook Context Template

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class HookContext implements Context
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase(BeforeScenarioScope $scope): void
    {
        $managers = $this->doctrine->getManagers();
        
        foreach ($managers as $manager) {
            if (!$manager instanceof EntityManagerInterface) {
                continue;
            }

            $connection = $manager->getConnection();
            $schemaManager = $connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();

            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
            
            foreach ($tables as $table) {
                if ($table === 'doctrine_migration_versions') {
                    continue;
                }
                
                $connection->executeStatement(sprintf('TRUNCATE TABLE `%s`', $table));
            }
            
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
```

## Notification Context Template

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Context;

use Behat\Behat\Context\Context;
use App\Tests\Shared\Behat\Service\NotificationCheckerInterface;
use Webmozart\Assert\Assert;

final class NotificationContext implements Context
{
    public function __construct(
        private readonly NotificationCheckerInterface $notificationChecker,
    ) {
    }

    /**
     * @Then I should be notified that :message
     */
    public function iShouldBeNotifiedThat(string $message): void
    {
        $this->notificationChecker->checkNotification($message, 'success');
    }

    /**
     * @Then I should be notified about :message error
     * @Then I should be notified about an error :message
     */
    public function iShouldBeNotifiedAboutError(string $message): void
    {
        $this->notificationChecker->checkNotification($message, 'error');
    }

    /**
     * @Then I should see an error message
     */
    public function iShouldSeeAnErrorMessage(): void
    {
        Assert::true($this->notificationChecker->hasErrorNotification());
    }

    /**
     * @Then I should see a success notification
     */
    public function iShouldSeeASuccessNotification(): void
    {
        Assert::true($this->notificationChecker->hasSuccessNotification());
    }
}
```

## Complex Workflow Context Template

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use App\Tests\[Context]Context\Behat\Page\Admin\[Resource]\IndexPageInterface;
use App\Tests\[Context]Context\Behat\Page\Admin\[Resource]\ShowPageInterface;
use App\Tests\Shared\Behat\Service\NotificationCheckerInterface;
use Webmozart\Assert\Assert;

final class [Workflow][Resources]Context implements Context
{
    public function __construct(
        private readonly IndexPageInterface $indexPage,
        private readonly ShowPageInterface $showPage,
        private readonly NotificationCheckerInterface $notificationChecker,
    ) {
    }

    /**
     * @When I publish the :name [resource]
     */
    public function iPublishThe[Resource](string $name): void
    {
        $this->indexPage->open();
        $this->indexPage->executeActionOnResource(['name' => $name], 'publish');
    }

    /**
     * @When I archive the :name [resource]
     */
    public function iArchiveThe[Resource](string $name): void
    {
        $this->indexPage->open();
        $this->indexPage->executeActionOnResource(['name' => $name], 'archive');
    }

    /**
     * @When I view details of the :name [resource]
     */
    public function iViewDetailsOfThe[Resource](string $name): void
    {
        $this->indexPage->open();
        $this->indexPage->showResource(['name' => $name]);
    }

    /**
     * @Then the [resource] :name should have status :status
     */
    public function the[Resource]ShouldHaveStatus(string $name, string $status): void
    {
        $this->indexPage->open();
        Assert::same(
            $this->indexPage->getFieldFromRow(['name' => $name], 'status'),
            $status
        );
    }

    /**
     * @Then I should see the [resource] name :name
     */
    public function iShouldSeeThe[Resource]Name(string $name): void
    {
        Assert::same($this->showPage->getName(), $name);
    }

    /**
     * @Then I should be notified that it has been successfully published
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyPublished(): void
    {
        $this->notificationChecker->checkNotification(
            '[Resource] has been successfully published.',
            'success'
        );
    }
}
```

## Usage Instructions

1. Replace placeholders:
   - `[Context]` → Your context name (e.g., `Blog`)
   - `[Resource]` → Capitalized singular (e.g., `Category`)
   - `[Resources]` → Capitalized plural (e.g., `Categories`)
   - `[resource]` → Lowercase singular (e.g., `category`)
   - `[resources]` → Lowercase plural (e.g., `categories`)

2. Implement Page Objects matching the interfaces

3. Register contexts in behat configuration

4. Add domain-specific step definitions

5. Use dependency injection for services