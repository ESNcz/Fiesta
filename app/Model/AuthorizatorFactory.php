<?php
/**
 * User: thanh.dolong
 * Date: 29/09/2017
 * Time: 00:26
 */

namespace App\Model;

use Nette\Database\Context;
use Nette\Security\Permission;

/**
 * Authorizator checks if a given role has authorization
 * to access a given resource.
 */
interface IAuthorizator
{
    function allow($roles, $resources, $privileges);
}

/**
 * Users Authorization.
 */
class AuthorizationFactory
{

    /**
     * Connect to database
     * @var Context
     */
    private $db;

    /**
     *  AuthorizatorFactory Constructor.
     *
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->db = $database;
    }

    /**
     * Create ACL with all permissions
     * @return Permission
     */
    public function create()
    {
        $modules = $this->db->table('module')->fetchPairs("presenter_id", "presenter_id");

        $permission = new Permission;
        // definujeme role
        $permission->addRole('guest');
        $permission->addRole('authenticated', 'guest');

        // member and international
        $permission->addRole('international', 'authenticated');
        $permission->addRole('member', 'authenticated');

        // admin and superadmin

        $permission->addRole('editor', 'member');
        $permission->addRole('esnchallenge', 'editor');
        $permission->addRole('board', 'editor');
        $permission->addRole('admin', 'board');
        $permission->addRole('globalAdmin', 'admin');

        $permission->addResource('Internal:Users');

        $permission->addResource('Admin:Error4xx');
        $permission->addResource('Admin:Sign');
        $permission->addResource('Admin:Homepage');
        $permission->addResource('Admin:Profile');
        $permission->addResource('Admin:Settings');
        $permission->addResource('Admin:Event');
        $permission->addResource('Admin:Sandbox');
        $permission->addResource("Admin:Mail");
        $permission->addResource("Admin:Vote");
        $permission->addResource("Admin:Status");
        $permission->addResource("Admin:User");
        $permission->addResource("Admin:Bug");

        //module
        foreach ($modules as $key => $val) {
            $permission->addRole('Admin:' . (string)$key);
            $permission->addResource('Admin:' . (string)$val);
            $permission->allow('Admin:' . (string)$key, 'Admin:' . (string)$val);
        }


        $permission->allow('guest', "Admin:Sign");
        $permission->allow('guest', "Admin:Error4xx");
        $permission->allow("authenticated", "Internal:Users");
        $permission->allow("authenticated", "Admin:Status");
        $permission->allow("authenticated", "Admin:Dictionary", "default");
        $permission->allow("authenticated", "Admin:Profile", "view");
        $permission->allow('authenticated', "Admin:Vote");
        $permission->allow('authenticated', "Admin:Bug");
        $permission->allow('authenticated', array("Admin:Homepage", "Admin:Profile", "Admin:Settings"), "default");


        /*
         * Buddy Management
         */
        $permission->allow("admin", "Admin:BuddyManagement", "settings");
        $permission->allow("editor", "Admin:BuddyManagement", "manualConnection");
        $permission->allow("editor", "Admin:BuddyManagement", "buddyConnection");
        $permission->allow("member", "Admin:BuddyManagement", "buddy");
        $permission->allow("member", "Admin:BuddyManagement", "request");
        $permission->allow("international", "Admin:BuddyManagement", "create");
        $permission->allow("authenticated", "Admin:BuddyManagement", "default");

        /*
         * Pick Up Management
         */
        $permission->allow("admin", "Admin:PickupManagement", "settings");
        $permission->allow("editor", "Admin:PickupManagement", "pickupConnection");
        $permission->allow("editor", "Admin:PickupManagement", "manualConnection");
        $permission->allow("member", "Admin:PickupManagement", "pickup");
        $permission->allow("member", "Admin:PickupManagement", "request");
        $permission->allow("international", "Admin:PickupManagement", "create");
        $permission->allow("authenticated", "Admin:PickupManagement", "default");

        /*
        * Event Management
        */
        $permission->allow("authenticated", "Admin:EventManager", "default");
        $permission->allow("authenticated", "Admin:EventManager", "past");
        $permission->allow("authenticated", "Admin:EventManager", "view");
        $permission->allow("editor", "Admin:EventManager", "create");
        $permission->allow("editor", "Admin:EventManager", "settings");
        $permission->allow("editor", "Admin:EventManager", "edit");
        $permission->allow("editor", "Admin:EventManager", "editEsn");

        /*
         * HR Management
         */
        $permission->allow("editor", "Admin:HRmanager", "default");

        /*
         * See profiles (users)
         */
        $permission->allow('member', "Admin:User", "members");
        $permission->allow('editor', "Admin:User", "internationals");
        $permission->allow('authenticated', "Admin:User", "localmembers");


        /*
         * Sandbox
         */
        $permission->allow('member', "Admin:Sandbox");
        $permission->allow('member', "Admin:Mail");
        $permission->allow('international', "Admin:Sandbox");
        $permission->allow('international', "Admin:Mail");

        /*
         * Special permission for admin
         */
        $permission->allow('admin', "Admin:Homepage", "edit");
        $permission->allow('admin', "Admin:Homepage", "deleteBuddy");
        $permission->allow('admin', "Admin:Settings", "roles");
        $permission->allow('admin', "Admin:Settings", "plugins");
        $permission->allow('editor', "Admin:Settings", "edit");

        $permission->allow('globalAdmin');
        return $permission;
    }

}
