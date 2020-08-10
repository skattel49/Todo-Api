<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\AuthenticationService;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * TodoItems Controller
 *
 * @property \App\Model\Table\TodoItemsTable $TodoItems
 * @property AuthenticationService Authentication
 * @method \App\Model\Entity\TodoItem[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TodoItemsController extends AppController
{
    /**
     * all todo_items will be displayed
     */
    public function index()
    {
        //only allow get methods
        $this->request->allowMethod('get');

        //get the user_id of the logged in user
        $user_id = $this->Authentication->getIdentity()['id'];

        //query the database
        $todo_items = $this->TodoItems->find()->where(['user_id' => $user_id])->toArray();

        if (count($todo_items) === 0) {
            return $this->respond(400, [], 'Error fetching data');
        }
        return $this->respond(200, $todo_items, 'Items fetched from the database');
    }

    /**
     * endpoint to create new items
     */
    public function create()
    {
        $this->request->allowMethod('post');

        $user_id = $this->Authentication->getIdentity()['id'];
        //create a new item entity with the provided values
        $todo_item = $this->TodoItems->newEntity($this->request->getData());

        //update the items' user_id so that it corresponds to the logged in user
        $todo_item->user_id = $user_id;

        //try saving the entity to the database
        if (!$this->TodoItems->save($todo_item)) {
            return $this->respond(400, $todo_item, 'Error saving item to the database');
        }

        return $this->respond(200, $todo_item, 'Item Saved to the database');

    }

    /**
     * Update a todo_item in a list
     */
    public function update()
    {
        //done without authentication
        $this->request->allowMethod(['post', 'put']);

        $user_id = $this->Authentication->getIdentity()['id'];

        try {
            $item = $this->TodoItems->find()
                ->where(['id' => $this->request->getData('id'), 'user_id' => $user_id])->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $this->respond(400, [], 'Record not found');
        }
        //update the todo_item with values provided from the request
        $this->TodoItems->patchEntity($item, $this->request->getData());

        if ($this->TodoItems->save($item)) {
            return $this->respond(200, $item, 'Item updated');
        }
        return $this->respond(400, $item, 'Error updating item');
    }

    /**
     * Delete a todo_item from a list
     */
    public function delete()
    {
        $this->request->allowMethod(['post']);

        $user_id = $this->Authentication->getIdentity()['id'];

        try {
            $item = $this->TodoItems->find()
                ->where(['id' => $this->request->getData('id'), 'user_id' => $user_id])->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $this->respond(400, $this->request->getData(), 'Record not found');
        }
        //try deleting the item
        if ($this->TodoItems->delete($item)) {
            return $this->respond(200, $item, "Deletion Successful");
        }
        return $this->respond(400, [], 'Error deleting data');
    }

    /**
     * Display one todo_item
     */
    public function findOne()
    {
        $this->request->allowMethod('get');

        $user_id = $this->Authentication->getIdentity()['id'];

        try {
            $item = $this->TodoItems->find()
                ->where(['id' => $this->request->getQuery('id'), 'user_id' => $user_id])
                ->firstOrFail();

        } catch (RecordNotFoundException $e) {
            return $this->respond(400, [], 'Record not found');
        }

        return $this->respond(200, $item, 'Item fetched from the database');
    }
}
