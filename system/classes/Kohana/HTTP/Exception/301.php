<?php

class Kohana_HTTP_Exception_301 extends HTTP_Exception_Redirect
{
    /**
     * @var int HTTP 301 Moved Permanently
     */
    protected $_code = 301;

}
