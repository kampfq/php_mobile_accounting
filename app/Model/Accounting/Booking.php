<?php
/**
 * Buchung.php
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


class Booking extends Cortex
{
    const SOLL = 'S';
    const HABEN = 'H';

    protected $db = 'DB';
    protected $table = 'fi_buchungen';
    protected $primary = 'buchungsnummer';
    protected $fieldConf = array(
        'mandant_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => false,
        ),
        'buchungsnummer' => array(
            'type' => Schema::DT_INT,
            'nullable' => false,
        ),
        'buchungstext' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ),
        'sollkonto' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ),
        'habenkonto' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ),
        'betrag' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ),
        'datum' => array(
            'type' => Schema::DT_FLOAT,
            'nullable' => false,
        ),
        'bearbeiter_user_id' => array(
            'type' => Schema::DT_TINYINT,
            'nullable' => false,
        ),
        'is_offener_posten' => array(
            'type' => Schema::DT_BOOL,
            'nullable' => false,
        )
    );


}