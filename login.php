<html>
    <head>
        <meta charset="UTF-8"> 
        <title></title>
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">

    </head>
    <body>
        <script src="js/bootstrap.min.js"></script>
        <img src="include/logo.png" alt="logo">

        <div class="container">
            <h1>Please Log In</h1>
                <form method="POST">
                <label for="name">User Name</label>
                <input type="text" name="username" id="nam"><br/>
                <label for="password">Password</label>
                <input type="text" name="password" id="password"><br/>
                <input type="submit" value="Log In">
                <input type="submit" name="cancel" value="Cancel">
                </form>

        </div>';
        <?php
        require_once 'include/config.php';
        session_start();
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $myusername = $_POST['username'];
            $mypassword = $_POST['password'];
            $sql = "SELECT user FROM users WHERE user = :myusername and password = :mypassword";
            $sth = $pdocon->prepare($sql);
            $sth->execute(
                array(
                    ':myusername' => $myusername,
                    ':mypassword' => $mypassword));
        
            $count = $sth->rowCount();
            if($count === 1) {
                $_SESSION['login_user'] = $myusername;
                header("location: admin-panel.php");
            }
            else {
                $error = "Your Login Name or Password is invalid";
                echo $error;
            }
        }
        ?>
    </body>
</html>
