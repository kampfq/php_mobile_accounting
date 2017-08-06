<?php
/**
 * Login.php
 *
 * Made with <3 with PhpStorm
 * @author kampfq
 * @copyright 2017 Benjamin Issleib
 * @license    NO LICENSE AVAILIABLE
 * @see
 * @since      File available since Release
 * @deprecated File deprecated in Release
 */

namespace Controller\Security;



use DB\SQL\Session;
use Model\Auth\User;
use Traits\ViewControllerTrait;

class Auth
{
    use ViewControllerTrait;

    function loginAction(){
        new Session($this -> database);
        $requestUserName = $this -> getRequest() -> getParsedBody()['username'];
        $requestUserNamePassword = $this -> getRequest() -> getParsedBody()['password'];
        $user = new User();
        $user -> load([
            'user_name = ?',$requestUserName
        ]);
        if($user -> passwordIsEqualTo($requestUserNamePassword)){
            $this -> f3 -> set('SESSION.username',$user -> user_name);
        }
        return $this -> wrap_response('juhuu');
    }

}