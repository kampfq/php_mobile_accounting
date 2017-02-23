<?php
/**
 * Templates.php
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

class Template extends Cortex
{
    protected $db = 'DB';
    protected $table = 'fi_quick_config';
    protected $primary = 'config_id';
    protected $fieldConf = array(
        'config_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
        'mandant_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),

        'config_knz' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'sollkonto' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'habenkonto' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'buchungstext' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ),
        'betrag' => array(
            'type' => Schema::DT_FLOAT,
            'nullable' => true,
        ),
    );

}