<?php
/**
 * Index.php
 *
 * Made with <3 with PhpStorm
 * @author kampfq
 * @copyright 2017 Benjamin Issleib
 * @license    NO LICENSE AVAILIABLE
 * @see
 * @since      File available since Release
 * @deprecated File deprecated in Release
 */

namespace Controller;


use Traits\ViewControllerTrait;

class Index
{
    use ViewControllerTrait;

    public function execute()
    {
        // TODO: Implement execute() method.
    }

    public function getTemplate(): string
    {
        return 'Accounting/index.htm';
    }


}