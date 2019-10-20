<?php

namespace App\Internal\Presenters;

use App\Admin\Presenters\BasePresenter;
use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;
use Nette\Database\Context;
use Nette\Http\IResponse;


/**
 * Class UsersPresenter
 * @package App\Api\Presenters
 */
class UsersPresenter extends BasePresenter
{
    private $database;

    /**
     * UsersPresenter constructor.
     * @param Context $database
     */
    public function __construct(Context $database) {
        $this->database = $database;
    }

    /**
     * @GET Get specific user with role from system Fiesta
     *
     * @param array $query
     * @throws AbortException
     */
    public function actionReadAll(array $query)
    {

        $name = isset($query['input']) ? $query['input'] : null;
        $type = isset($query['type']) ? $query['type'] : null;

        if (is_null($name) || is_null($type)) {
            return $this->sendSpecific400Error("name or input");
        }

        $this->sendJson(['items' => $this->prepareJsonToGetSpecificUsers($name, $type)]);
    }

    private function sendSpecific400Error($missing)
    {
        $response = $this->getHttpResponse();
        $response->setCode(IResponse::S400_BAD_REQUEST);
        $this->sendResponse(new JsonResponse(["status" => "error", "errors" => array(["message" => "this values cannot be null", "fields" => $missing])]));
    }

    private function prepareJsonToGetSpecificUsers($name, $type)
    {
        $users = $this->database->table("role_assignment")->where("user.user_id.university", $this->userRepository->university);

        switch ($type) {
            case "members":
                $users->where("role", "member");
                break;
            case "section":
                $users->whereOr([
                    "role" => ["admin", "member", "globalAdmin"]
                ]);
                break;
        }

        $users
            ->where('data_user.name LIKE ? OR data_user.surname LIKE ? OR data_user LIKE ?', "%" . $name . "%", "%" . $name . "%", "%" . $name . "%")
            ->where("NOT (user.user_id.status?)", "uncompleted")
            ->select('data_user, data_user.name, data_user.surname')->group("data_user")->fetchAll();

        $results = array();
        foreach ($users as $user) {

            $img = "images/avatar/{$user->ref("user","data_user")["signature"]}.jpg";
            if (!file_exists($img)) $img = "images/avatar/empty.jpg";
            $results[] = [
                'id' => $user["data_user"],
                'full_name' => $user["name"] . " " . $user["surname"],
                'avatar_url' => $img,
                'email' => $user["data_user"],
            ];
        }

        return $results;
    }
}