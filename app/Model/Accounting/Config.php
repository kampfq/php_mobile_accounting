<?php
/**
 * Config.php
 *
 * Made with <3 with PhpStorm
 * @author kampfq
 * @copyright 2017 Benjamin Issleib
 * @license    NO LICENSE AVAILIABLE
 * @see
 * @since      File available since Release
 * @deprecated File deprecated in Release
 */

namespace Model\Accounting;


use DB\Cortex;
use DB\SQL\Schema;

class Config extends Cortex
{
    protected $db = 'DB';
    protected $table = 'poll';
    protected $primary = 'param_id';
    protected $fieldConf = array(
        'mandant_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
        'param_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
        'param_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
        'param_knz' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'param_desc' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'param_value' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        )

    );
}