<?php

$host = '127.0.0.1';
$porta = '3306';
$usuario = 'root';
$senha = '';        
$database = 'banco_pi';

try {
    $pdo = new PDO("mysql:host=$host;port=$porta;dbname=$database;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}


?>