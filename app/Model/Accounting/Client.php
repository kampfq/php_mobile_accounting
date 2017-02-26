<?php
/**
 * Mandant.php
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

class Client extends Cortex
{
    protected $db = 'DB';
    protected $table = 'fi_mandant';
    protected $primary = 'mandant_id';
    protected $fieldConf = array(
        'mandant_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
        'mandant_description' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ),
        'primary_user_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
        'create_date' => array(
            'type' => Schema::DT_DATE,
            'nullable' => true,
        )
    );
}