<?php

namespace App\Model;

use Exception;
use Nette;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nette\Utils\Random;


/**
 * Users management.
 */
class AuthenticatorFactory implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    const
        TABLE_NAME = 'user',
        TABLE_MODULES = "module_assignment",
        COLUMN_STATUS = 'status',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_EMAIL = 'user_id',
        COLUMN_ROLE = 'role',
        COLUMN_UNIVERSITY = 'university',
        COLUMN_SIGNATURE = 'signature',
        COLUMN_VALID = 'valid',
        COLUMN_LAST_LOGIN = 'last_login';


    private $database;


    /**
     * UserManager constructor.
     *
     * @param Nette\Database\Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }


    /**
     * Performs an authentication.
     *
     * @param array $credentials
     *
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('The email is incorrect.', self::IDENTITY_NOT_FOUND);

        } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

        } elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update([
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password)
            ]);
        }

        $row->update([
            self::COLUMN_LAST_LOGIN => date("Y-m-d H:i:s")
        ]);

        $arr = $row->toArray();
        $data = $this->database->table('data_user')->get($row[self::COLUMN_EMAIL])->toArray();

        $roles = $this->database->table("role_assignment")->where("data_user", $row[self::COLUMN_EMAIL])->fetchPairs(null, "role");

        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_EMAIL], $roles, array_merge($arr, $data));
    }


    /**
     * Adds new user.
     *
     * @param $email
     * @param $password
     * @param $session
     * @return void
     * @throws Exception
     */
    public function setNewUser($email, $password, $session)
    {
        try {
            $date = new DateTime();
            $this->database->beginTransaction();
            if ($session["role"] == "member") {
                $this->database->table(self::TABLE_NAME)->insert([
                    self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
                    self::COLUMN_EMAIL => $email,
                    self::COLUMN_UNIVERSITY => strtoupper($session["section"]),
                    self::COLUMN_SIGNATURE => Random::generate(10),
                    self::COLUMN_VALID => $date->modify("+6 months")
                ]);
            } else {
                $this->database->table(self::TABLE_NAME)->insert([
                    self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
                    self::COLUMN_EMAIL => $email,
                    self::COLUMN_STATUS => "uncompleted",
                    self::COLUMN_UNIVERSITY => strtoupper($session["section"]),
                    self::COLUMN_SIGNATURE => Random::generate(10),
                    self::COLUMN_VALID => $date->modify("+3 months")
                ]);
            }

            $this->database->table("data_user")->insert([
                "user_id" => $email,
                "registered" => new DateTime()
            ]);

            $this->database->table("role_assignment")->insert([
                "data_user" => $email,
                "role" => strtolower($session["role"])
            ]);
            $this->database->commit();
        } catch (UniqueConstraintViolationException $e) {
            $this->database->rollBack();
            throw new DuplicateNameException;
        }
    }
}