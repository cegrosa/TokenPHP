<?php
require_once './vendor/autoload.php';
require_once './classes/Auth.php';
require_once './classes/Rest.php';
require_once './classes/ConnectSQL.php';

//Se crea un objeto de la clase Rest
$rest = new Rest();

//Se obtiene la url a traves del método 'getParam'
$url = '';
if(null !== ($rest->getParam('url'))) {
    $url = $rest->getParam('url');//string
}

//Se obtiene el método JSON
$method = $rest->getMethod();

//Dependiendo del método hara una cosa u otra
switch($method){
    case 'GET':
        if($url === 'getToken'){
            //Si entra aqui, obtendra el token
            $token = json_encode(Auth::checkData());
            
            echo $token;
            return ;
            
        } else if($url === 'getProducts'){
            //Si entra aqui se obtendra la lista de productos
            $token = Auth::checkData();
            
            if($token['auth']===true){
                $products = json_encode(array(
                        'auth' => true,
                        't' => $token['t'],
                        'getproduct' => true,
                        'products' => QueryDatabase::getProducts(),
                    )
                );
                echo $products;
                return ;
            }else{
                return json_encode(array(
                        'auth' => false,
                        't' => null,
                        'getproduct' => false,
                    ));
            }
        } else if($url === 'getTickets'){
            //Si entra aqui obtendra la lista de tickets
            $token = Auth::checkData();
            
            if($token['auth']===true){
                $tickets = json_encode(array(
                        'auth' => true,
                        't' => $token['t'],
                        'getticket' => true,
                        'tickets' => QueryDatabase::getTickets(),
                    )
                );
                echo $tickets;
                return ;
            }else{
                return json_encode(array(
                        'auth' => false,
                        't' => null,
                        'getticket' => false,
                    ));
            }
        }else if($url === 'getTicketDetails'){
            //Si entra aqui obtendra la lista de tickets detallados
            $token = Auth::checkData();
            
            if($token['auth']===true){
                $tickets = json_encode(array(
                        'auth' => true,
                        't' => $token['t'],
                        'getticketdetail' => true,
                        'ticketDetail' => QueryDatabase::getTicketDetails(),
                    )
                );
                echo $tickets;
                return ;
            }else{
                return json_encode(array(
                        'auth' => false,
                        't' => null,
                        'getticketdetail' => false,
                    ));
            }
        }else if($url === 'getProductsWP'){
            //Si entra aqui obtendra la lista de productos,
            //sin necesidad de token
            $product = QueryDatabase::getProducts();
            echo $product;
            return ;
        }
        
        break;
    case 'POST':
        if($url === 'postTicket'){
            //Si entra aqui insertara un ticket
            //en la base de datos
            $token = Auth::checkData();
            
            if($token['auth']===true){
                $body = file_get_contents('php://input');
                
                echo json_encode(array(
                        'auth' => $token['auth'],
                        't' => $token['t'],
                        'insertTicket' => QueryDatabase::insertTickets($body),
                    ));
                
                return ;
            }
        } else if($url === 'searchBy'){
            //Si entra aqui, sera para obtener la lista
            //de ticket dependiendo del filtro y lo que envia
            //el dispositivo
            $token = Auth::checkData();
            
            if($token['auth']===true){
                $body = file_get_contents('php://input');
                
                $tickets = json_encode(array(
                        'auth' => true,
                        't' => $token['t'],
                        'getsearch' => true,
                        'tickets' => QueryDatabase::searchBy($body),
                    )
                );
                echo $tickets;
                return ;
            }else{
                return json_encode(array(
                        'auth' => false,
                        't' => null,
                        'getsearch' => false,
                    ));
            }
        }
        break;
        
}

