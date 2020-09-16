<?php
/**
 * Created by PhpStorm.
 * User: thanh.dolong
 * Date: 17/08/2018
 * Time: 12:43
 */

namespace App\Api\Presenters;

use App\Model\TokenRepository;
use App\Model\UserRepository;
use Exception;
use Nette\Application\UI\Presenter;


class ProfilePresenter extends Presenter {

    private $userRepository;
    private $response;
    private $token;

    /**
     * ProfilePresenter constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function startup()
    {
        parent::startup();
        $this->token = $this->getHttpRequest()->getHeader("X-Auth-Token");
        $this->response = $this->getHttpResponse();
        $this->response->addHeader('Access-Control-Allow-Origin', "*");
        $this->response->addHeader('Access-Control-Allow-Credentials', "true");
        $this->response->addHeader('Access-Control-Allow-Headers', "Origin, Content-Type, X-Auth-Token");
        $this->response->addHeader('Access-Control-Allow-Method', "GET, POST, PATCH, PUT, DELETE, OPTIONS");

        if (!isset($this->token)) {
            $this->response->setCode(403);
            $this->sendJson([
                "code" => "403",
                "message" => "Forbidden"]);
        }
    }

    public function actionRead($id)
    {

        $profile = $this->getProfile($id);
        $this->sendJson($profile);

    }

    public function actionReadAll()
    {
        $tokenRepository = new TokenRepository();

        if ($tokenRepository->isTokenValid($this->token) == true) {
            $data = $tokenRepository->getData();
            $profile = $this->getProfile($data->email);
            $this->sendJson($profile);
        } else {
            $this->response->setCode(403);
            $this->sendJson([
                "code" => "403",
                "message" => $tokenRepository->getError()]);
        }
    }

    private function getProfile($email) {
        try {
            $result = $this->userRepository->getDataById($email);
            $uni = $this->userRepository->getUniversityById($email);
            $role = $this->userRepository->getRolesById($email);

            $university = [
                "name" => $uni["name"],
                "section_short" => $uni["section_short"],
                "section_long" => $uni["section_long"]
            ];
            return [
                "firstname" => $result["name"],
                "lastname" => $result["surname"],
                "gender" => $result["gender"],
                "email" => $result["user_id"],
                "university" => $university,
                "roles" => $role
            ];
        } catch (Exception $e) {
            $this->response->setCode(403);
            $this->sendJson([
                "code" => "403",
                "message" => $email]);
        }
    }
}
