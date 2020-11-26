<?php
/**
 * Created by PhpStorm.
 * User: thanh.dolong
 * Date: 17/03/2018
 * Time: 19:16
 */

namespace App\Admin\Presenters;

use Nette\Database\Context;
use Nette\Utils\DateTime;


/**
 * Class StatusPresenter
 * @package App\Admin\Presenters
 */
class StatusPresenter extends BasePresenter
{
    /**
     * @var Context
     * @inject
     */
    public $db;

    public function startup()
    {
        parent::startup();

        $status = $this->getUser()->getIdentity()->status;

        // banned, enabled, pending (`enabled` is actually `disabled`, but tech dept ¯\_(ツ)_/¯
        if ($status !== $this->action)
            $this->redirect(":Admin:Homepage:default");

    }

    function handleReactivateProfile()
    {
        $date = new DateTime($this->userRepository->getIdentity()->valid);
        if ($this->userRepository->isInRole("international")) {
            $this->db->table("user")->where("user_id", $this->userRepository->getId())->update([
                "status" => "active",
                "valid" => $date->modify("+3 months")
            ]);
        } else {
            $this->db->table("user")->where("user_id", $this->userRepository->getId())->update([
                "status" => "active",
                "valid" => $date->modify("+6 months")
            ]);
        }
        $this->redirect(":Admin:Homepage:default");
    }

    /**
     * User is on Status page
     * @return bool
     */
    protected function onStatusPage()
    {
        return true;
    }
}