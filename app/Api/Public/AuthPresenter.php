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


class AuthPresenter extends Presenter
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

    private $database;

    /**
     * AuthPresenter constructor.
     * @param Context $database
     */
    public function __construct(Context $database){
        $this->database = $database;
    }

    public function actionReadAll()
    {
        $response = $this->getHttpResponse();
        $response->setCode(403);
        $this->sendJson([
            "code" => "403",
            "message" => 'Forbidden']);
    }

    public function actionCreate()
    {

        $params = $this->getHttpRequest()->getPost();

        $this->authenticate($params);
    }

    private function authenticate($params)
    {
        $response = $this->getHttpResponse();
        if (!isset($params["email"]) || !isset($params["password"])) {
            $response->setCode(403);
            $this->sendJson([
                "code" => "403",
                "message" => 'Email or password missing']);
        }

        $token = new TokenRepository();
        $email = $params["email"];
        $password = $params["password"];


        $row = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        if (!$row) {
            $response->setCode(403);
            $this->sendJson([
                "code" => "403",
                "message" => 'Oops, that\'s not the right email or password. Please try again!']);
        } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            $response->setCode(403);
            $this->sendJson([
                "code" => "403",
                "message" => 'Oops, that\'s not the right email or password. Please try again!']);
        } elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update([
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password)
            ]);
        }

        $row->update([
            self::COLUMN_LAST_LOGIN => date("Y-m-d H:i:s")
        ]);

        $arr = $row->toArray();

        $roles = $this->database->table("role_assignment")->where("data_user", $row[self::COLUMN_EMAIL])->fetchPairs(null, "role");

        unset($arr[self::COLUMN_PASSWORD_HASH]);

        $data = [
            "email" => $row[self::COLUMN_EMAIL],
            "roles" => $roles,
            "signature" => $arr["signature"],
            "university" => $arr["university"]
        ];

        $token->createToken($data);

        JWT::$leeway = 60; // $leeway in seconds
        $jwt = $token->getJWTToken();

        $this->sendJson([
            "access_token" => $jwt]);
    }
}
