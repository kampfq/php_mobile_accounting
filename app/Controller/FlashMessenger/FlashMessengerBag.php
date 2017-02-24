<?php
/**
 * Copyright (c) 2016. Benjamin Ißleib
 */

namespace Controller\FlashMessenger;


class FlashMessengerBag
{


    private static $flashMessenger = null;
    private $flashMessages = array();

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance(){
       if(null === self::$flashMessenger){
           self::$flashMessenger = new FlashMessengerBag();
       }
        return self::$flashMessenger;
    }

    public function addFlashMessage(FlashMessage $message)
    {
        $this -> flashMessages[] = $message;
    }

    public function getFlashMessages():array
    {
        return $this -> flashMessages;

    }

    public function flush()
    {
        $this -> flashMessages = array();
    }

}