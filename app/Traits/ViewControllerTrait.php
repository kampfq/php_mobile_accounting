<?php
/**
 * Copyright (c) 2016. Benjamin Ißleib
 */

namespace Traits;


use Controllers\FlashMessenger\FlashMessage;
use Controllers\FlashMessenger\FlashMessengerBag;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

trait ViewControllerTrait
{
    protected $request;
    protected $errors = [];
    /**
     * @var FlashMessengerBag
     */
    protected $flashMessenger = null;

    public function __construct()
    {
        $this -> flashMessenger = FlashMessengerBag::getInstance();
    }

    public function getRequest():ServerRequest{
        if(!$this -> request){
            $f3 = \Base::instance();
            $this -> request = $f3 -> get('PSR7_REQUEST');
        }
        return $this -> request;
    }

    public function render():ResponseInterface
    {

        /**
         * get the fat free framework class
         */
        $f3 = \Base::instance();
        $this->execute();
        $f3->set('mainContent', $this->getTemplate());

        $messages = array();
        foreach ($this -> flashMessenger -> getFlashMessages() as $message){
            /**
             * @var $message FlashMessage
             */
            $messages[] = [
                'title' =>$message -> getTitle(),
                'body' =>$message -> getBody(),
                'kind' =>$message -> getKind()
            ];
        }
        $this -> flashMessenger -> flush();
        $f3->set('flashMessages', $messages);

        /**
         * return the rendered template
         */

        $response = new Response\HtmlResponse(\Template::instance()->render('base/layout.htm'));
        $f3 -> set('PSR7_RESPONSE',$response);
        return $response;
    }

    abstract public function execute();

    abstract public function getTemplate(): string;

}