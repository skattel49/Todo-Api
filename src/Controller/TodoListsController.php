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
     * view all todo_lists of a user
     */
    public function index()
    {
        //only allow get requests to this endpoint
        $this->request->allowMethod('get');

        //fetch user id of the logged in user
        $user_id = $this->Authentication->getIdentity()['id'];

        //query the database
        $lists = $this->TodoLists->find()->where(['user_id'=>$user_id])->toArray();

        if (count($lists) === 0) {
            return $this->respond(400, [], 'No lists in the database');
        }

        return $this->respond(200, $lists, 'Lists fetched from the database');
    }

    /**
     * view one todo_list with its contents
     */
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
     * Create a new todo_list
     */
    public function create()
    {
        $this->request->allowMethod('post');

        $user_id = $this->Authentication->getIdentity()['id'];
        //create a new list entity with the provided data
        $list = $this->TodoLists->newEntity($this->request->getData());
        //update the lists' user_id so that it corresponds to the logged in user
        $list->user_id = $user_id;

        if ($this->TodoLists->save($list)) {
            return $this->respond(200, $list, 'List saved in the database');
        }
        return $this->respond(400, $list, 'Error saving list to the database');
    }

    /**
     * update a todo_list of the authenticated user
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

        //update the list with provided data
        $this->TodoLists->patchEntity($list, $this->request->getData());
        //try saving the list
        if ($this->TodoLists->save($list)) {
            return $this->respond(200, $list, 'List updated');
        }
        return $this->respond(400, [], 'List could not be updated');
    }

    /**
     * Delete a todo_list of a user
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
        //try deleting the list
        if ($this->TodoLists->delete($list)) {
            return $this->respond(200, $list, 'List Deleted');
        }
        return $this->respond(400, [], 'Error deleting data');
    }
}
