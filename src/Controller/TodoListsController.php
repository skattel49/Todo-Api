<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\AuthenticationService;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * TodoLists Controller
 *
 * @property \App\Model\Table\TodoListsTable $TodoLists
 * @property AuthenticationService Authentication
 * @method \App\Model\Entity\TodoList[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TodoListsController extends AppController
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
        $lists = $this->TodoLists->find()->where(['user_id'=>$user_id])->toArray();

        if (count($lists) === 0) {
            return $this->respond(400, [], 'No lists in the database');
        }

        return $this->respond(200, $lists, 'Lists fetched from the database');
    }

    public function findOne()
    {
        $this->request->allowMethod('get');

        $user_id = $this->Authentication->getIdentity()['id'];

        try {
            $list = $this->TodoLists->find()
                ->where([
                    'id' => $this->request->getQuery('id'),
                    'user_id'=>$user_id
                    ])
                ->contain('TodoItems')->firstOrFail();
        } catch (RecordNotFoundException $e) {
            $this->respond(400, [], 'Record not found');
        }

        $this->respond(200, $list, 'List fetched from the database');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function create()
    {
        $this->request->allowMethod('post');

        $user_id = $this->Authentication->getIdentity()['id'];

        $list = $this->TodoLists->newEntity($this->request->getData());
        $list->user_id = $user_id;

        if ($this->TodoLists->save($list)) {
            return $this->respond(200, $list, 'List saved in the database');
        }
        return $this->respond(400, $list, 'Error saving list to the database');
    }

    /**
     * Edit method
     *
     * @param string|null $id Todo List id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function update()
    {
        $this->request->allowMethod(['post', 'put']);

        $user_id = $this->Authentication->getIdentity()['id'];

        try {
            $list = $this->TodoLists->find()->where([
                'id' => $this->request->getData('id'),
                'user_id'=>$user_id
                ])
                ->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $this->respond(400, [], 'Record not found');
        }

        $this->TodoLists->patchEntity($list, $this->request->getData());

        if ($this->TodoLists->save($list)) {
            return $this->respond(200, $list, 'List updated');
        }
        return $this->respond(400, [], 'List could not be updated');
    }

    /**
     * Delete method
     *
     * @param string|null $id Todo List id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $this->request->allowMethod('post');

        $user_id = $this->Authentication->getIdentity()['id'];

        try {
            $list = $this->TodoLists->find()
                ->where([
                    'id' => $this->request->getData('id'),
                    'user_id'=>$user_id
                ])
                ->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $this->respond(400, [], 'Record not found');
        }

        if ($this->TodoLists->delete($list)) {
            return $this->respond(200, $list, 'List Deleted');
        }
        return $this->respond(400, [], 'Error deleting data');
    }
}
