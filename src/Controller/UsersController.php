<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\UsersTable;
use Authentication\AuthenticationService;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use phpDocumentor\Reflection\Type;

/**
 * Users Controller
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 * @property UsersTable Users
 * @property AuthenticationService Authentication
 */
class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication
            ->allowUnauthenticated(['login', 'signup']);
    }

    public function login()
    {
        //manual authentication because the Authentication->getResult() did not work
        $user_data = $this->request->getData();
        try {
            $user = $this->Users->find()->where(['username' => $user_data['username']])->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $this->respond(401, [], 'Invalid Credentials');
        }
        $result = password_verify($user_data['password'], $user['password']);

        if ($result) {
            $privateKey = Security::getSalt();
            $payload = [
                'iss' => 'myapp',
                'sub' => $user->id,
            ];
            $json = [
                'username' => $user->username,
                'token' => JWT::encode($payload, $privateKey, 'HS256'),
            ];
        } else {
            return $this->respond(401, [], 'Invalid Credentials');
        }
        return $this->respond(200, $json, 'Login Successful');
    }

    public function signup()
    {
        $user = $this->Users->newEntity($this->request->getData());

        if (!$this->Users->save($user)) {
            return $this->respond(400, $user, 'Signup Unsuccessful');
        }

        $privateKey = Security::getSalt();
        $payload = [
            'iss' => 'myapp',
            'sub' => $user->id,
        ];

        $json = [
            //using RS256 will not work
            'token' => JWT::encode($payload, $privateKey, 'HS256'),
            'user' => $user->username,
        ];

        return $this->respond(200, $json, 'Signup Successful');
    }
}
