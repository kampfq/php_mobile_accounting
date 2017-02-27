<?php
/**
 * Copyright (c) 2016. Benjamin Ißleib
 */

namespace Traits;


use Controller\FlashMessenger\FlashMessage;
use Controller\FlashMessenger\FlashMessengerBag;
use DB\Cortex;
use DB\CortexCollection;
use DB\SQL;
use Model\Accounting\Client;
use Model\Auth\User;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

trait ViewControllerTrait
{
    /**
     * @var Request
     */
    protected $request;
    protected $errors = [];
    /**
     * @var FlashMessengerBag
     */
    protected $flashMessenger = null;
    /**
     * @var \Base
     */
    protected $f3;
    /**
     * @var null | User
     */
    protected $user = null;
    /**
     * @var null| Client
     */
    protected $client = null;
    /**
     * @var SQL
     */
    protected $database = null;

    protected $idParsedFromRequest = null;
    protected $firstOptionParsedFromRequest = null;

    public function __construct()
    {
        $this -> f3 = \Base::instance();
        $this -> getRequest();
        $this -> flashMessenger = FlashMessengerBag::getInstance();
        $this -> database = $this -> f3 -> get('DB');
        $this -> idParsedFromRequest = $this -> f3 -> get('PARAMS.id');
        $this -> firstOptionParsedFromRequest = $this -> f3 -> get('PARAMS.option1');

    }

    public function getRequest():ServerRequest{
        if(!$this -> request){
            $this -> request = $this -> f3 -> get('PSR7_REQUEST');
        }
        return $this -> request;
    }

    public function render():ResponseInterface
    {
        $this -> f3-> set('mainContent', $this->getTemplate());
        $messages = $this -> prepareFlashMessages($this -> flashMessenger);
        $this -> f3 -> set('flashMessages', $messages);

        /**
         * return the rendered template
         */
        $response = $this -> getResponse();
        $this->f3 -> set('PSR7_RESPONSE',$response);
        return $response;
    }

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

    public function getTemplate(): string{
        return '';
    }

    public function wrap_response($data,$format = 'json'){

        if(is_subclass_of($data,Cortex::class)){
            /**
             * @var $data Cortex
             */
            $data = $data -> cast();
        }
        if(is_subclass_of($data,CortexCollection::class)){
            /**
             * @var $data Cortex
             */
            $normalizedData = [];
            foreach($data as $obj){
                $normalizedData[]=$obj-> cast();
            }
            $data = $normalizedData;
        }

        switch ($format){
            case 'json':
                $response = new Response\JsonResponse($data);
                break;
            case 'csv':
                $response = new Response\TextResponse($data);
                break;
            default:
                $response = new Response\HtmlResponse($data);
                break;
        }
        $this -> f3 -> set('PSR7_RESPONSE',$response);
        return $response;

    }

    public function beforeRoute(){
        $username = null;
        if(isset($_SERVER['REMOTE_USER'])) {
            $username = $_SERVER['REMOTE_USER'];
        }
        // für PHP5-FPM mit nginx
        else if(isset($_SERVER["PHP_AUTH_USER"])) {
            $username = $_SERVER["PHP_AUTH_USER"];
        } else {
            throw new \Exception("Fehler: Benutzer nicht über \$_SERVER['REMOTE_USER'] oder \$_SERVER['PHP_AUTH_USER'] ermittelbar");
        }

        $user = new User();
        $user -> load([
            'user_name = ?',$username
        ]);

        $client = new Client();
        $client -> load([
            'mandant_id = ?', $user -> mandant_id
        ]);
        if(null === $user -> user_id || null === $client -> mandant_id){
            throw new \Exception("Kein Mandant für den Benutzer $username konfiguriert");
        }else {
            $this -> user = $user;
            $this -> client = $client;
        }
    }

    /**
     * @return FlashMessengerBag
     */
    public function getFlashMessenger(): FlashMessengerBag
    {

        return $this->flashMessenger;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {

        return $this->user;
    }

    /**
     * @return Client
     */
    public function getClient()
    {

        return $this->client;
    }

    /**
     * @return SQL
     */
    public function getDatabase(): SQL
    {

        return $this->database;
    }

    /**
     * @return mixed|null
     */
    public function getIdParsedFromRequest()
    {

        return $this->idParsedFromRequest;
    }

    /**
     * @return mixed|null
     */
    public function getFirstOptionParsedFromRequest()
    {

        return $this->firstOptionParsedFromRequest;
    }




}