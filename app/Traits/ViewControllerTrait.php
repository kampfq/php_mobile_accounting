<?php
/**
 * Copyright (c) 2016. Benjamin Ißleib
 */

namespace Traits;


use Controller\FlashMessenger\FlashMessage;
use Controller\FlashMessenger\FlashMessengerBag;
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
        $messages = $this -> prepareFlashMessages($this -> flashMessenger);
        $f3->set('flashMessages', $messages);

        /**
         * return the rendered template
         */
        $response = $this -> getResponse();
        $f3 -> set('PSR7_RESPONSE',$response);
        return $response;
    }

    abstract public function execute();

    protected function prepareFlashMessages(FlashMessengerBag $flashMessengerBag): array {
        $messages = array();
        foreach ($flashMessengerBag -> getFlashMessages() as $message){
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
        return $messages;
    }

    protected function getResponse(){
        return new Response\HtmlResponse(\Template::instance()->render('Accounting/index.htm'));
    }

    abstract public function getTemplate(): string;

}