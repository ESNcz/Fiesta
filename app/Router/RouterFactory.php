<?php

namespace App\Router;

use AdamStipak\RestRoute;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory
{
    use Nette\StaticClass;

    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter()
    {


        $router = new RouteList();
        // With module.
        $router[] = new RestRoute('Internal', "json");
        $router[] = new RestRoute('Satelite', "json");
        $router[] = new RestRoute('Api', "json");

        $router[] = new Route('create-event', [
            'presenter' => 'Admin:EventManager',
            'action' => 'create'
        ]);

        $router[] = new Route('view-events', [
            'presenter' => 'Admin:EventManager',
            'action' => 'default'
        ]);

        $router[] = new Route('event[/<event>]', [
            'presenter' => 'Admin:EventManager',
            'action' => 'view'
        ]);

        $router[] = new Route('[<locale=en en|cs>/]login', [
            'presenter' => 'Admin:Sign',
            'action' => 'in'
        ]);

        $router[] = new Route('[<locale=en en|cs>/]setup[/<university>/<role>]', [
            'presenter' => 'Admin:Sign',
            'action' => 'setup'
        ]);

        $router[] = new Route('[<locale=en en|cs>/]join', [
            'presenter' => 'Admin:Sign',
            'action' => 'up'
        ]);

        $router[] = new Route('[<locale=en en|cs>/]next', [
            'presenter' => 'Admin:Sign',
            'action' => 'continue'
        ]);

        $router[] = new Route('[<locale=en en|cs>/]password/forgot/', [
            'presenter' => 'Admin:Sign',
            'action' => 'forgot'
        ]);

        $router[] = new Route('[<locale=en en|cs>/]password/reset/', [
            'presenter' => 'Admin:Sign',
            'action' => 'reset'
        ]);


        $router[] = new Route('[<locale=en en|cs>/]docs/', array(
            'module' => 'Front',
            'presenter' => 'Docs',
            'action' => 'default'));


        $router[] = new Route('[<locale=en en|cs>/]<presenter>/<action>[/<id>]', array(
            'module' => 'Admin',
            'presenter' => 'Homepage',
            'action' => 'default'));


        return $router;
    }
}
