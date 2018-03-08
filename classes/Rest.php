<?php

class Rest {

    private $headers, $json, $method, $parameters, $pathParameters;

    function __construct () {
        $this->headers        = getallheaders();
        $this->json           = json_decode(file_get_contents('php://input'), true);
        $this->method         = $_SERVER['REQUEST_METHOD'];
        $this->parameters     = $_GET;
        $this->pathParameters = array();

        if($this->json === null) {
            $this->json = array();
        }

        if(isset($_GET['url'])) {
            $this->pathParameters = explode('/', $_GET['url']);
        }

        /*echo 'headers'        . Util::varDump($this->headers);
        echo 'json'           . Util::varDump($this->json);
        echo 'method'         . Util::varDump($this->method);
        echo 'parameters'     . Util::varDump($this->parameters);
        echo 'pathParameters' . Util::varDump($this->pathParameters);*/
    }

    private static function _getParam (array $array, $name = null) {
        if($name === null) {
            return $array;
        }
        if(!isset($array[$name])) {
            return null;
        }
        return $array[$name];
    }

    function getAction () {
        return $this->getUrlParam(1);
    }

    function getHeader ($name) {
        return self::_getParam($this->headers, $name);
    }

    function getJson ($name = null) {
        return self::_getParam($this->json, $name);
    }

    function getMethod () {
        return $this->method;
    }

    function getParam ($name = null) {
        return self::_getParam($this->parameters, $name);
    }

    function getRoute() {
        return $this->getUrlParam(0);
    }

    function getUrlParam ($number = null) {
        return self::_getParam($this->pathParameters, $number);
    }
}