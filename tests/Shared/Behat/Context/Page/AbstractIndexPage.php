<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Context\Page;

use App\Tests\Shared\Behat\Service\Accessor\TableAccessorInterface;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

abstract class AbstractIndexPage extends AbstractAdminPage
{
    final public const DELETE_BUTTON_SELECTOR = 'Delete';

    public function __construct(
        Session $session,
        \ArrayAccess $minkParameters,
        RouterInterface $router,
        private readonly TableAccessorInterface $tableAccessor,
    ) {
        parent::__construct($session, $minkParameters, $router);
    }

    #[\Override]
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'title' => '[data-page-title]',
            'table' => '[data-test-grid-table]',

            'bulk_actions' => '.sylius-grid-nav__bulk',
            'confirmation_button' => '#confirmation-button',
            'filter' => '[data-test-filter]',
            'filters_form' => '[data-test-filters-form]',
            'filters_toggle' => '.accordion-button',
        ]);
    }

    public function getTitle(): NodeElement
    {
        return $this->getElement('title');
    }

    public function getGrid(): NodeElement
    {
        return $this->getElement('table');
    }

    public function findRowContaining(string $text): NodeElement
    {
        return $this->getTableAccessor()->getFieldFromRow(
            $this->getElement('table'),
            $this->getElement('row')
        );
    }

    public function deleteResourceOnPage(array $parameters): void
    {
        $tableAccessor = $this->getTableAccessor();
        $table = $this->getElement('table');

        $deletedRow = $tableAccessor->getRowWithFields($table, $parameters);
        $actionButtons = $tableAccessor->getFieldFromRow($table, $deletedRow, 'actions');

        $actionButtons->pressButton(self::DELETE_BUTTON_SELECTOR);
    }

    public function bulkDelete(): void
    {
        $this->getElement('bulk_actions')->pressButton(self::DELETE_BUTTON_SELECTOR);
        $this->getElement('confirmation_button')->click();
    }

    public function isSingleResourceOnPage(array $parameters): bool
    {
        try {
            $rows = $this->tableAccessor->getRowsWithFields($this->getElement('table'), $parameters);

            return 1 === count($rows);
        } catch (\InvalidArgumentException|ElementNotFoundException) {
            return false;
        }
    }

    public function getColumnFields($columnName): array
    {
        return $this->tableAccessor->getIndexedColumn($this->getElement('table'), $columnName);
    }

    public function sortBy($fieldName, string|null $order = null): void
    {
        $sortableHeaders = $this->tableAccessor->getSortableHeaders($this->getElement('table'));
        Assert::keyExists($sortableHeaders, $fieldName, sprintf('Column "%s" does not exist or is not sortable.', $fieldName));

        /** @var NodeElement $sortingHeader */
        $sortingHeader = $sortableHeaders[$fieldName]->find('css', 'a');
        preg_match('/\?sorting[^=]+\=([acdes]+)/i', (string) $sortingHeader->getAttribute('href'), $matches);
        $nextSortingOrder = $matches[1] ?? 'desc';

        $sortableHeaders[$fieldName]->find('css', 'a')->click();

        if (null !== $order && ($order !== $nextSortingOrder)) {
            $sortableHeaders[$fieldName]->find('css', 'a')->click();
        }
    }

    public function isSingleResourceWithSpecificElementOnPage(array $parameters, $element): bool
    {
        try {
            $rows = $this->tableAccessor->getRowsWithFields($this->getElement('table'), $parameters);

            if (1 !== count($rows)) {
                return false;
            }

            return null !== $rows[0]->find('css', $element);
        } catch (\InvalidArgumentException|ElementNotFoundException) {
            return false;
        }
    }

    public function countItems(): int
    {
        try {
            return $this->getTableAccessor()->countTableBodyRows($this->getElement('table'));
        } catch (ElementNotFoundException) {
            return 0;
        }
    }

    public function getActionsForResource(array $parameters): NodeElement
    {
        $tableAccessor = $this->getTableAccessor();
        $table = $this->getElement('table');

        $resourceRow = $tableAccessor->getRowWithFields($table, $parameters);

        return $tableAccessor->getFieldFromRow($table, $resourceRow, 'actions');
    }

    public function checkResourceOnPage(array $parameters): void
    {
        $tableAccessor = $this->getTableAccessor();
        $table = $this->getElement('table');

        $resourceRow = $tableAccessor->getRowWithFields($table, $parameters);
        $bulkCheckbox = $resourceRow->find('css', '.bulk-select-checkbox');

        Assert::notNull($bulkCheckbox);

        $bulkCheckbox->check();
    }

    public function filter(): void
    {
        $this->getElement('filter')->press();
    }

    protected function getTableAccessor(): TableAccessorInterface
    {
        return $this->tableAccessor;
    }

    private function areFiltersVisible(): bool
    {
        return !$this->getElement('filters_toggle')->hasClass('collapsed');
    }

    private function toggleFilters(): void
    {
        $filtersToggle = $this->getElement('filters_toggle');
        $filtersToggle->click();
        $this->getDocument()->waitFor(1, function () use ($filtersToggle) {
            $accordionCollapse = $filtersToggle->find('css', '.accordion-collapse');

            return null !== $accordionCollapse && !$accordionCollapse->hasClass('collapsing');
        });
    }

    private function waitForFormUpdate(): void
    {
        $form = $this->getElement('filters_form');
        usleep(500000); // we need to sleep, as sometimes the check below is executed faster than the form sets the busy attribute
        $form->waitFor(1500, fn () => !$form->hasAttribute('busy'));
    }
}
