<?php
/**
 * Kontenart.php
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

class Account extends Cortex
{
    const AKTIV = 1;
    const PASSIV = 2;
    const AUFWAND = 3;
    const ERTRAG = 4;
    const NEUTRAL = 5;

    protected $db = 'DB';
    protected $table = 'fi_konto';
    protected $fieldConf = array(
        'mandant_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
        'kontonummer' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'bezeichnung' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ),
        'kontenart_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => true,
        ),
    );
}