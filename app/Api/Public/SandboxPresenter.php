<?php
/**
 * Created by PhpStorm.
 * User: thanh.dolong
 * Date: 17/08/2018
 * Time: 12:43
 */

namespace App\Api\Presenters;

use App\Model\TokenRepository;
use Firebase\JWT\JWT;
use Nette\Application\UI\Presenter;
use Nette\Database\Context;
use Nette\Security\Passwords;


class SandboxPresenter extends Presenter
{
    const
        TABLE_NAME = 'user',
        TABLE_MODULES = "module_assignment",
        COLUMN_STATUS = 'status',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_EMAIL = 'user_id',
        COLUMN_ROLE = 'role',
        COLUMN_UNIVERSITY = 'university',
        COLUMN_VALID = 'valid',
        COLUMN_LAST_LOGIN = 'last_login';

    /** @var Context @inject */
    private $database;


    public function actionRead($id, array $query, $data, array $associations)
    {
        $response = $this->getHttpResponse();
        $response->setCode(200);
        $this->sendJson([
            "id" => $id,
            "query" => $query,
            "data" => $data,
            "ass" => $associations]);
    }

    public function actionReadAll(array $query, $data)
    {
        $response = $this->getHttpResponse();
        $response->setCode(200);
        $this->sendJson([
            "query" => $query,
            "data" => $data,
            "ass" => $this->getHttpRequest()->getHeader("X-Auth-Token")]);
    }

    public function actionCreate(array $query, $data)
    {

        $response = $this->getHttpResponse();
        $response->setCode(200);
        $this->sendJson([
            "query" => $query,
            "data" => $data]);

        /*
         NOTE: This will now be an object instead of an associative array. To get
         an associative array, you will need to cast it as such:
        */

        //$decoded_array = (array) $decoded;
    }
}
