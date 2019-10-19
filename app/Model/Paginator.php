<?php
/**
 * Created by PhpStorm.
 * User: thanh.dolong
 * Date: 08/03/2018
 * Time: 00:53
 */

namespace App\Model;


/**
 * Class Paginator
 * @package App\Model
 */
class Paginator
{
    /**
     * @var
     */
    private $steps;

    /**
     * Paginator constructor.
     *
     * @param     $page
     * @param int $lastPage
     */
    public function __construct($page, $lastPage = 0)
    {
        $this->create($page, $lastPage);
    }

    /**
     * Create paginator
     * @param $page
     * @param $lastPage
     */
    private function create($page, $lastPage)
    {
        $steps = array($page);
        if ($lastPage) {
            // actual page
            if ($lastPage <= 5) $steps = array_merge($steps, range(1, $lastPage));
            //border pages
            $steps = array_merge($steps, [1, $lastPage]);

            if ($page > 0 && $page < $lastPage) {
                if ($page <= 2) $steps = array_merge($steps, range(1, $page));
                else $steps = array_merge($steps, range($page - 2, $page));

                if ($page + 2 >= $lastPage) $steps = array_merge($steps, range($page, $lastPage));
                else $steps = array_merge($steps, range($page, $page + 2));
            }

            $steps = array_values(array_unique($steps));
            asort($steps);
        }
        $this->steps = $steps;
    }

    /**
     * @return mixed
     */
    public function getSteps()
    {
        return $this->steps;
    }

}