<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
   include('include/config.php');
   session_start();
   
   $user_check = $_SESSION['login_user'];
   
   $ses_sql = "select user from users where user = :user_check ";
   
   $sth = $pdocon->prepare($ses_sql);
   $sth->execute(
                array(
                ':user_check' => $user_check));   
   
   $row = $sth->fetch();
   
   $login_session = $row['user'];
   
   if(!isset($_SESSION['login_user'])){
      header("location:login.php");
   }
?>
