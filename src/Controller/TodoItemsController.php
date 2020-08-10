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
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->request->allowMethod('get');
        $user_id = $this->Authentication->getIdentity()['id'];
        $todo_items = $this->TodoItems->find()->where(['user_id' => $user_id ])->toArray();

        if (count($todo_items) === 0) {
            return $this->respond(400, [], 'Error fetching data');
        }
        return $this->respond(200, $todo_items, 'Items fetched from the database');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function create()
    {
        //done without authentication
        $this->request->allowMethod('post');

        $user_id = $this->Authentication->getIdentity()['id'];

        $todo_item = $this->TodoItems->newEntity($this->request->getData());
        $todo_item->user_id = $user_id;

        if (!$this->TodoItems->save($todo_item)) {
            return $this->respond(400, $todo_item, 'Error saving item to the database');
        }

        return $this->respond(200, $todo_item, 'Item Saved to the database');

    }

    /**
     * Edit method
     *
     * @param string|null $id Todo Item id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function update()
    {
        //done without authentication
        $this->request->allowMethod(['post', 'put']);
        $user_id = $this->Authentication->getIdentity()['id'];
        try {
            $item = $this->TodoItems->find()
                ->where(['id' => $this->request->getData('id'), 'user_id'=>$user_id])->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $this->respond(400, [], 'Record not found');
        }
        $this->TodoItems->patchEntity($item, $this->request->getData());

        if ($this->TodoItems->save($item)) {
            return $this->respond(200, $item, 'Item updated');
        }
        return $this->respond(400, $item, 'Error updating item');
    }

    /**
     * Delete method
     *
     * @param string|null $id Todo Item id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $this->request->allowMethod(['post']);
        $user_id = $this->Authentication->getIdentity()['id'];
        try {
            $item = $this->TodoItems->find()
                ->where(['id' => $this->request->getData('id'), 'user_id'=>$user_id])->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $this->respond(400, $this->request->getData(), 'Record not found');
        }

        if ($this->TodoItems->delete($item)) {
            return $this->respond(200, $item, "Deletion Successful");
        }
        return $this->respond(400, [], 'Error deleting data');
    }

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
