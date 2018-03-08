<?php
require_once 'ConnectSQL.php';
use Firebase\JWT\JWT;


class Auth
{
    //Declaración de variables
    private static $secret_key = 'izv';
    private static $encrypt = ['HS256'];
    private static $aud = null;
    
    //Creara el token dependiendo
    //de los datos (Este método sera llamado)
    public static function SignIn($data)
    {
        $time = time();
        
        $token = array(
            'expire' => $time + (60*60),
            'aud' => self::Aud(),
            'data' => $data,
        );

        return array(
                'auth' => true,
                't' => JWT::encode($token, self::$secret_key),
        );
    }
    
    public static function Check($token)
    {
        if(empty($token))
        {
            throw new Exception("Invalid token supplied.");
        }
        
        $decode = JWT::decode(
            $token,
            self::$secret_key,
            self::$encrypt
        );
        
        if($decode->aud !== self::Aud())
        {
            throw new Exception("Invalid user logged in.");
        }
    }
    
    public static function GetData($token)
    {
        return JWT::decode(
            $token,
            self::$secret_key,
            self::$encrypt
        )->data;
    }
    
    private static function Aud()
    {
        $aud = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }
        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();
        
        return sha1($aud);
    }
    
    //Obtendra los datos de la cabecera
    static function getArraydcd()
    {
        $headers = getallheaders();//array asociativo
        $authorization = '';
        if(isset($headers['authorization'])) {
            $authorization = $headers['authorization'];
        }
        
        $dcdArray = explode(' ', $authorization);
        
        return $dcdArray;
    }
    
    //Obtiene los datos del token decodificado
    static function getdcdToken($dcdArray){
        return JWT::decode($dcdArray[1], self::$secret_key, array('HS256'));
    }
    
    //Comprobara si la cabecera el
    //Basic o Bearer
    static function checkData()
    {
        $dcdArray = self::getArraydcd();
        
        if(count($dcdArray) === 2){
            if($dcdArray[0] === 'Basic'){
                return self::isBasic($dcdArray);
            }else if($dcdArray[0] === 'Bearer'){
                return self::isBearer($dcdArray);
            }
        }
    }
    
    //Si es Basic, creara un token con los datos
    //del usuario y lo devolvera
    private function isBasic($dcdArray)
    {
        $userPass = base64_decode($dcdArray[1]);
        $dcdUser = explode(':', $userPass);
        
        if(count($dcdUser) === 2) {
            if(QueryDatabase::checkUser($dcdUser[0], sha1($dcdUser[1])) === true) {
                return self::SignIn(array(
                    'user' => $dcdUser[0],
                    'pass' => sha1($dcdUser[1]),
                    )
                );
            }else{
                return array(
                        'auth' => false,
                        't' => null
                );
            }
        }
    }
    
    //Si es Bearer, actualizara el tiempo del token
    //y lo devolvera
    private function isBearer($dcdArray)
    {
            $dcdToken = self::getdcdToken($dcdArray);
            $time = time();
            if($time < $dcdToken->expire) {
                
                $tokenBody = array(
                    'expire' => $time + (60*60),
                    'aud' => self::Aud(),
                    'data' => $dcdToken->data
                );
                
                return array(
                        'auth' => true,
                        't' => JWT::encode($tokenBody, self::$secret_key),
                    );
            }else{
                return array(
                        'auth' => false,
                        't' => null
                );
            }
    }
}