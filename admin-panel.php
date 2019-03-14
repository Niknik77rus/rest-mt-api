<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">

    </head>

    <body>
        <script src="js/bootstrap.min.js"></script>
        <img src="include/logo.png" alt="logo">';
    <?php
        include('session.php');
        require_once 'include/config.php';
        require('include/routeros_api.class.php');
        
        if(isset($_SESSION['login_user'])) {
              echo '<h3>', 'Welcome ', $_SESSION['login_user'], '</h3>';
         }
        else {
            echo 'session is invalid'."<br>";
            die("Name parameter missing");
        } 
        echo '<p><a href = "logout.php">Sign Out</a></p>';
                
        echo '<H3>Unique ID list:</H3>';
        $sql = "select * from apicall where approved = 0 order by id";
        $stmt = $pdocon->prepare($sql);
        $stmt->execute();
        
        
        //prepare list of GID (binded to routers) for dropdown menu
        $sql1 = "select distinct(groupid) from routers";
        $stmt1 = $pdocon->prepare($sql1);
        
    
        while ($row= $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['ip'] , '  ', $row['uniqueid'], ' ', $row['hostname'],  ' ', $row['mac'],  ' ', $row['windowsuser'],
            "<form action='admin-panel.php' method='post'> <input type='submit' name='uniqueid_id' value='approve' /> ",
            "<select name='gid'>";

            $stmt1->execute();
            while ($row1= $stmt1->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value=" . $row1['groupid'] . " >" . $row1['groupid'] . "</option>";            
            }           
            echo "</select><input type='hidden' name='uniqueid' value=" . $row['uniqueid'] . " />", 
            "</form>", '<br>';
        }
    // approve UID here
    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['uniqueid'])) {   
        $sql = "update apicall set approved = 1, dt_modify = NOW(), groupid = :gid where uniqueid = :uid";   
        $stmt = $pdocon->prepare($sql);
        $stmt->execute(array(   
            ':uid' =>  $_POST['uniqueid'],
            ':gid' =>  $_POST['gid']));
        
        echo "<meta http-equiv='refresh' content='0'>";

        
        
        $sql = "select router_ip, router_pwd, router_login from routers where groupid = :gid ";
        $stmt = $pdocon->prepare($sql);
        $stmt->execute(array(':gid' =>  $_POST['gid']));
        $row= $stmt->fetch(PDO::FETCH_ASSOC);
        
        $API = new RouterosAPI();
        
        $router_ip = $row['router_ip'];
        $router_login = $row['router_login'];
        $router_pwd = $row['router_pwd'];
        
        $sql = "select ip from apicall where uniqueid = :uid ";
        $stmt = $pdocon->prepare($sql);
        $stmt->execute(array(   
            ':uid' =>  $_POST['uniqueid']));
        $row= $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($API->connect($router_ip, $router_login, $router_pwd)) {  
            $ARRAY = $API->comm("/ip/firewall/address-list/print", array(
                ".proplist" => ".id",
                "?address" => $_SERVER['REMOTE_ADDR']));  
        }
        if (!count($ARRAY)>0){
            echo "COUNT MT", count($ARRAY);
            if ($API->connect($router_ip, $router_login, $router_pwd)) {       
                $ARRAY = $API->comm("/ip/firewall/address-list/add", array(
                    "list" => $_POST['gid'],
                    "address" => $row['ip']));
                $API->disconnect();      
            }
        }
    }
    ?>
    </body>
</html>