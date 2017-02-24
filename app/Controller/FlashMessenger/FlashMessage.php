<?php
/**
 * Copyright (c) 2016. Benjamin Ißleib
 */

namespace Controller\FlashMessenger;



class FlashMessage
{
    const FLASHMESSAGE_WARNING = 'warning';
    const FLASHMESSAGE_ERROR = 'danger';
    const FLASHMESSAGE_SUCCESS = 'success';

    private $title = '';
    private $body = '';
    private $kind = self::FLASHMESSAGE_SUCCESS;

    public function __construct($body = '',$title = '',$kind = self::FLASHMESSAGE_SUCCESS)
    {
        $this -> setTitle($title);
        $this -> setBody($body);
        $this -> setKind($kind);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {

        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {

        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {

        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {

        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getKind(): string
    {

        return $this->kind;
    }

    /**
     * @param string $kind
     */
    public function setKind(string $kind)
    {

        $this->kind = $kind;
    }



}