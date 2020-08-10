<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TodoListsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TodoListsTable Test Case
 */
class TodoListsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TodoListsTable
     */
    protected $TodoLists;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.TodoLists',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TodoLists') ? [] : ['className' => TodoListsTable::class];
        $this->TodoLists = $this->getTableLocator()->get('TodoLists', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->TodoLists);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
