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

namespace Model\Auth;


use DB\Cortex;
use DB\SQL\Schema;


class User extends Cortex
{
    const BUCHUNGSART_SOLL = 'S';
    const BUCHUNGSART_HABEN = 'H';

    protected $db = 'DB';
    protected $table = 'fi_user';
    protected $primary = 'user_id';

    protected $fieldConf = array(
        'user_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => false,
        ),
        'user_name' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'user_password' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => false,
        ),
        'user_description' => array(
            'type' => Schema::DT_VARCHAR256,
            'nullable' => true,
        ),
        'mandant_id' => array(
            'type' => Schema::DT_INT,
            'nullable' => false,
        ),
        'create_date' => array(
            'type' => Schema::DT_DATE,
            'nullable' => false,
        ),
    );

    public function passwordIsEqualTo($password)
    {
        return password_verify($password,$this -> user_password);
    }



}