<?php

require('../include/routeros_api.class.php');

class ApiCall {
    
    private $conn;
    private $table_name = "apicall";
    
    public $uniqueid;
    public $mac;
    public $hostname;
    public $windowsuser;
    public $approved;
    public $src_ip;
    public $groupid;
 
    public function __construct($db){
        $this->conn = $db;
    }
    
    public function getRealIp(){
        switch(true){
            case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
            case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
            case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
            default : return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function create_uid() {   
        $this->src_ip = $_SERVER['REMOTE_ADDR'];
        $query = "INSERT INTO " . $this->table_name . " SET mac=:mac, uniqueid=:uniqueid, hostname=:hostname, 
            windowsuser=:windowsuser, approved=:approved, ip =:src_ip, dt_create=NOW()";
        $stmt = $this->conn->prepare($query);
        
        $this->uniqueid=htmlspecialchars(strip_tags($this->uniqueid));
        $this->hostname=htmlspecialchars(strip_tags($this->hostname));
        $this->mac=htmlspecialchars(strip_tags($this->mac));
        $this->windowsuser=htmlspecialchars(strip_tags($this->windowsuser));
        $this->approved=htmlspecialchars(strip_tags($this->approved));
        
        $stmt->bindParam(":uniqueid", $this->uniqueid);
        $stmt->bindParam(":hostname", $this->hostname);
        $stmt->bindParam(":mac", $this->mac);
        $stmt->bindParam(":approved", $this->approved);
        $stmt->bindParam(":windowsuser", $this->windowsuser);
        $stmt->bindParam(":src_ip", $this->src_ip);
        if($stmt->execute()){
            return true;
        }
        return false;   
    }
    
    public function check_uid_approved() {
        $query = "select count(id) as amount from " . $this->table_name . "
            where uniqueid=:uniqueid and approved=1";
        $stmt = $this->conn->prepare($query); 
        $this->uniqueid=htmlspecialchars(strip_tags($this->uniqueid)); 
        $stmt->bindParam(":uniqueid", $this->uniqueid);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            return ($row['amount'] > 0 );
        }
        
    }
    
    public function check_uid_exist() {
        $query = "select count(id) as amount from " . $this->table_name . "
            where uniqueid=:uniqueid";
 
        $stmt = $this->conn->prepare($query);
        $this->uniqueid=htmlspecialchars(strip_tags($this->uniqueid)); 
        $stmt->bindParam(":uniqueid", $this->uniqueid);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            return ($row['amount'] > 0);
        }
    }
    
    public function get_current_ip() {
        $query = "select ip from apicall where uniqueid=:uniqueid";
        $stmt = $this->conn->prepare($query);
        $this->uniqueid=htmlspecialchars(strip_tags($this->uniqueid)); 
        $stmt->bindParam(":uniqueid", $this->uniqueid);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            return ($row['ip']);
        }    
    }
    
    
    public function get_groupid() {
        $query = "select groupid from apicall where uniqueid=:uniqueid";
        $stmt = $this->conn->prepare($query);
        $this->uniqueid=htmlspecialchars(strip_tags($this->uniqueid)); 
        $stmt->bindParam(":uniqueid", $this->uniqueid);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            return ($row['groupid']);
        }    
    }    
    
    public function compare_ip() {
        return ($this->get_current_ip() == $this->getRealIp());              
    }
    
    public function change_current_ip() {
        $query = "update apicall set ip=:ip where uniqueid=:uniqueid";
        $stmt = $this->conn->prepare($query);
        $this->uniqueid=htmlspecialchars(strip_tags($this->uniqueid));     
        $stmt->bindParam(":uniqueid", $this->uniqueid);
        $stmt->bindParam(":ip", $this->getRealIp());
        $stmt->execute();             
    }    
    
    public function ros_change_ip() {
        $query = "select router_ip, router_pwd, router_login from routers where groupid = :groupid ";
        $stmt = $this->conn->prepare($query);
        $this->uniqueid=htmlspecialchars(strip_tags($this->uniqueid)); 
        $stmt->bindParam(":groupid", $this->get_groupid());
        $stmt->execute();        
        $row = $stmt->fetch();
        
        $router_ip = $row['router_ip'];
        $router_login = $row['router_login'];
        $router_pwd = $row['router_pwd'];
        $API = new RouterosAPI();
       
        if ($API->connect($router_ip, $router_login, $router_pwd)) {  
            $ARRAY = $API->comm("/ip/firewall/address-list/print", array(
                ".proplist" => ".id",
                "?address" => $this->get_current_ip()));
            $API->write('/ip/firewall/address-list/remove', false);
            $API->write('=.id=' . $ARRAY[0]['.id']);
            $READ = $API->read();
            
            $ARRAY = $API->comm("/ip/firewall/address-list/add", array(
                "list" => $this->get_groupid(),
                "address" => $this->getRealIp()));
            $API->disconnect(); 
    }
    }
    
}
