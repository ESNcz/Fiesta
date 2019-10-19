<?php

namespace App\Model;

use Exception;
use Firebase\JWT\JWT;
use Nette\Application\UI\Control;
use Nette\Neon\Neon;

class TokenRepository extends Control
{
    private $token;
    private $key;
    private $decoded;
    private $issuedAt;
    private $notBefore;
    private $expire;

    public function __construct()
    {
        $this->issuedAt = time();
        $this->notBefore = $this->issuedAt;
        $this->expire = strtotime('+1 hour', $this->notBefore);

        if(file_exists(__DIR__ . '/../Config/config.prod.neon')) {
            $config = file_get_contents(__DIR__ . "/../Config/config.prod.neon");
        } else {
            $config = file_get_contents(__DIR__ . "/../Config/config.dev.neon");
        }

        $this->key = Neon::decode($config)["parameters"]["JWTkey"];
    }

    function createToken($data)
    {
        $this->create($data);
    }

    private function create($data)
    {
        $token = array(
            'iat' => $this->issuedAt,         // Issued at: time when the token was generated
            'nbf' => $this->notBefore,        // Not before
            'exp' => $this->expire,           // Expire
            "data" => $data
        );

        JWT::$leeway = 60;
        $this->token = JWT::encode($token, $this->key);
    }

    function isTokenValid($token)
    {
        try {
            $this->token = $token;
            $this->decoded = JWT::decode($this->token, $this->key, array('HS256'));

            return true;
        } catch (Exception $e) {
            bdump($e->getMessage(), "Error message");
            return false;
        }
    }

    function setExpire($expire)
    {
        $this->expire = strtotime($expire, $this->notBefore);
    }

    function getData()
    {
        return $this->decoded->data;
    }

    function getJWTToken()
    {
        return $this->token;
    }
}