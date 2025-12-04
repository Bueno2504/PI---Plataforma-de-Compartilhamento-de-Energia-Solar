<?php
session_start();
require("conexao.php");

if(strlen($_POST["email"]) == 0){
   header("Location: login.php?error=empty_email");
   exit;
}

if(strlen($_POST["senha"]) == 0){
   header("Location: login.php?error=empty_password");
   exit;
}

// Buscar dados de entrada
$email = $_POST["email"];
$senha = $_POST["senha"];

// Query com prepared statement
$sql_code = "SELECT * FROM usuario WHERE email = ?";
$stmt = $pdo->prepare($sql_code);
$stmt->execute([$email]);

// Buscar resultado
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // Verificar senha
    if (strpos($usuario['senha'], '$2y$') === 0) {
        $senhaValida = password_verify($senha, $usuario['senha']);
    } else {
        $senhaValida = ($senha === $usuario['senha']);
    }

    if ($senhaValida) {
        // Definir variáveis de sessão
        $_SESSION['logado'] = true;
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_perfil'] = $usuario['perfil'];
        
        // Redirecionar para admin.php
        if ($usuario['perfil'] === "LEITOR") {
            header("Location: leitor.php");
            exit();
        } else {
            header("Location: admin.php");
            exit();
        }
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
} else {
    header("Location: login.php?error=invalid_credentials");
    exit;
}
?>