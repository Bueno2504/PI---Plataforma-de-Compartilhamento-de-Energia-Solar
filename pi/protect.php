<?php

    if(!isset($_SESSION)){
        session_start();
    }

    if(!isset($_SESSION["id"])){
       die("Você não pode acessar esta página. <p> <a href=\"login.php\"> Entrar </a> </p>");      
      //header("Location: login.php");    
    }

?>