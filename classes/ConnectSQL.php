<?php

class QueryDatabase
{
    private static $dbc;
    
    //Se iniciara la conexión
    private static function connect()
    {
        define('DB_HOST','localhost');
        define('DB_USER','vicjod');
        define('DB_PASSWORD','');
        define('DB_DATABASE','bakery');
        
        self::$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        
        if (!self::$dbc) {
            return ;
        }
    }
    
    //Se cerrara la conexión
    private static function closeConn()
    {
        mysqli_close(self::$dbc);
    }
    
    //Ejecuta una consulta,
    //devolviendo true/false
    static function executeQuery($query)
    {
        self::connect();
        
        $resultset = mysqli_query(self::$dbc, $query);
        
        self::closeConn();
        
        if (!$resultset) {
            return false;
        } else {
            return true;
        }
        
        
    }
    
    //Devolvera el id de la ultima tupla insertada
    //de una tabla
    static function getLastQuery($table){
        self::connect();
        
        $query = "select * from $table ORDER BY id DESC LIMIT 1";
        
        $resultset = mysqli_query(self::$dbc, $query);
        
        self::closeConn();
        
        if (!$resultset) {
            return false;
        } else {
            foreach($resultset as $value){
                $id = $value['id'];
            }
            
            return $id;
        }
    }
    
    //Comprueba si existe el usuario
    static function checkUser($user, $pass, $rId = false)
    {
        self::connect();
        
        $query = "select * from member where "
        . "login='$user'"
        . " and password='$pass'";

        $resultset = mysqli_query(self::$dbc, $query);
    
        if (!$resultset) {
            return 'Query error';
        } else {
            if (!mysqli_num_rows($resultset) != 0) {
                return 'No username/password';
            } else {
                foreach ($resultset as $row) {
                    $id = $row['id'];
                    $userdb = $row['login'];
                    $passworddb = $row['password'];
                }
                
                self::closeConn();
                
                if($user === $userdb && $pass === $passworddb){
                    if(!$rId){
                        return true;
                    }else if($rId){
                        return $id;
                    }
                }
            }
        }
        
        self::closeConn();
        
        return false;
    }
    
    //Obtiene la lista de productos
    static function getProducts()
    {
        self::connect();
        
        $query = "select * from product "
        . "order by idfamily"
        ;

        $resultset = mysqli_query(self::$dbc, $query);
    
        self::closeConn();
    
        if (!$resultset) {
            return 'Query error';
        } else {
            
            $products = null;
            $product = "";
            
            while($row=mysqli_fetch_array($resultset)){
                $product .= json_encode(array(
                        'id' => htmlentities($row['id']),
                        'idfamily' => htmlentities($row['idfamily']),
                        'product' => htmlentities($row['product']),
                        'price' => htmlentities($row['price']),
                        'description' => htmlentities($row['description']),
                    )) . 'delimitador';
                
            }
            
            // $products = explode("delimitador", $product);
            
            // echo $products[2];
            
             return $product;
        }
    }
    
    //Obtiene la lista de tickets
    static function getTickets()
    {
        self::connect();
        
        $query = "select * from ticket ORDER BY date DESC";

        $resultset = mysqli_query(self::$dbc, $query);
    
        self::closeConn();
    
        if (!$resultset) {
            return 'Query error';
        } else {
            
            $tickets = null;
            $ticket = "";
            
            while($row=mysqli_fetch_array($resultset)){
                $ticket .= json_encode(array(
                        'id' => htmlentities($row['id']),
                        'date' => htmlentities($row['date']),
                        'idmember' => htmlentities($row['idmember']),
                        'idclient' => htmlentities($row['idclient']),
                        'Tprice' => htmlentities($row['Tprice']),
                    )) . 'delimitador';
                
            }
            

            
             return $ticket;
        }
    }
    
    //Obtiene la lista de tickets detallados
    static function getTicketDetails()
    {
        self::connect();
        
        $query = "SELECT ticketdetail.*, product FROM `ticketdetail`, product WHERE ticketdetail.idproduct = product.id";

        $resultset = mysqli_query(self::$dbc, $query);
    
        self::closeConn();
    
        if (!$resultset) {
            return 'Query error';
        } else {
            
            $tickets = null;
            $ticket = "";
            
            while($row=mysqli_fetch_array($resultset)){
                $ticket .= json_encode(array(
                        'id' => htmlentities($row['id']),
                        'idticket' => htmlentities($row['idticket']),
                        'idproduct' => htmlentities($row['idproduct']),
                        'quantity' => htmlentities($row['quantity']),
                        'price' => htmlentities($row['price']),
                        'product' => htmlentities($row['product']),
                    )) . 'delimitador';
                
            }
            
            return $ticket;
        }
    }
    
    //Obtiene una búsqueda de tickets
    //dependiendo del filtro aplicado
    static function searchBy($searched){
        
        $search = json_decode($searched);
        
        $words = $search->words;
        $by = $search->searchby;
        
        self::connect();
        
        switch($by){
            case "member":
                $query = "SELECT * FROM ticket WHERE idmember = ( SELECT id FROM member WHERE login like '%$words%' ) ORDER BY date DESC ";
                break;
            case "date":
                $query = "SELECT * FROM ticket WHERE date like '%$words%' ORDER BY date DESC";
                break;
            case "family":
                $query = "SELECT ticket.* FROM ticket, ticketdetail
                    	    WHERE ticket.id = ticketdetail.idticket
                     		    AND ticketdetail.idproduct IN (SELECT id FROM product WHERE idfamily IN(
                             									SELECT id FROM family WHERE family like '%$words%'
                             									)
                             								  )
                     	    ORDER BY date DESC";
                break;
            default:
                $query = "select * from ticket ORDER BY date DESC";
        }
        $resultset = mysqli_query(self::$dbc, $query);
    
        self::closeConn();
    
        if (!$resultset) {
            return 'Query error';
        } else {
            
            $tickets = null;
            $ticket = "";
            
            while($row=mysqli_fetch_array($resultset)){
                $ticket .= json_encode(array(
                        'id' => htmlentities($row['id']),
                        'date' => htmlentities($row['date']),
                        'idmember' => htmlentities($row['idmember']),
                        'idclient' => htmlentities($row['idclient']),
                        'Tprice' => htmlentities($row['Tprice']),
                    )) . 'delimitador';
                
            }
            
            return $ticket;
        }
    }
    
    //Insertar tickets en la bd,
    //devuelve true/false
    static function insertTickets($ticket)
    {
        
        $ticketdcd = json_decode($ticket);
        
        $arraydcd = Auth::getArraydcd();
        
        if($arraydcd[0] === 'Basic'){
            $userPass = base64_decode($arraydcd[1]);
            $dcdUser = explode(':', $userPass);
            $user = $dcdUser[0];
            $pass = sha1($dcdUser[1]);
        }else if($arraydcd[0] === 'Bearer'){
            $dcdToken = Auth::getdcdToken($arraydcd);
            $userData = $dcdToken -> data;
            $user = $userData -> user;
            $pass = $userData -> pass;
        }
        
        $id = self::checkUser($user, $pass, true);
        $tPrice = 0.0;
        
        foreach($ticketdcd as $ticketsdetail){
            //Datos ticket
            $queryticket = "INSERT INTO `ticket`(`date`, `idmember`, `idclient`) VALUES (now(), $id, null)";
            $totaltd = count($ticketsdetail);
            $count = 0;
            if(self::executeQuery($queryticket)){
                foreach($ticketsdetail as $ticketd){
                    //Datos ticket detallado
                    $idTicket = self::getLastQuery('ticket');
                    $idProduct = $ticketd -> idproduct;
                    $quantity = $ticketd -> quantity;
                    $price = $ticketd -> price;
                    $tPrice += $price;
                      
                    $queryticketdet = "INSERT INTO `ticketdetail`(`idticket`, `idproduct`, `quantity`, `price`)
                    VALUES ($idTicket, $idProduct, $quantity, $price)";
                    
                    $count += 1;
                    if(self::executeQuery($queryticketdet) === true){
                        if($totaltd === $count){
                            // UPDATE Precio
                            $queryupdateprice = "UPDATE `ticket` SET `Tprice` = $tPrice  WHERE `id` = $idTicket";
                            if(self::executeQuery($queryupdateprice)){
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}


