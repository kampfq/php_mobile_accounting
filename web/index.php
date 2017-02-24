<?php
/**
 * index.php
 *
 * Made with <3 with PhpStorm
 * @author kampfq
 * @copyright 2016 Benjamin Issleib
 * @license    NO LICENSE AVAILIABLE
 * @see
 * @since      File available since Release
 * @deprecated File deprecated in Release
 */

/**
 * setup Autoloader
 */

//composer autoloader
(@include_once ('../app/vendor/autoload.php')) OR die("You need to run php
composer.phar install for your application to run.");

$f3 = \Base::instance();
//fat free autoloader
$f3->set('AUTOLOAD','../app/');
/**
 * include configuration files
 */
$f3->config('../app/config/config.ini');
$f3->config('../app/config/routes.ini');

/**
 * Bootstrap Database Connection
 */
$host               = $f3->get('DBHOST');
$port               = $f3->get('DBPORT');
$databaseName       = $f3->get('DBNAME');
$databaseUser       = $f3->get('DBUSER');
$databasePassword   = $f3->get('DBPASSWORD');
$f3->set('DB',
    new DB\SQL(
        'mysql:host='.$host.';
        port='.$port.';
        dbname='.$databaseName,
        $databaseUser,
        $databasePassword
    )
);


/**
 * init PSR7
 */
$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$f3 -> set('PSR7_REQUEST',$request);
/**
 * run the Fat Free Framework
 */
$response = $f3->run();

/**
 * emit the result
 */
$emitter = new \Zend\Diactoros\Response\SapiEmitter();
$emitter -> emit($response);