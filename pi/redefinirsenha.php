<?php
    session_start();
    include("conexao.php");

    // Processar requisição POST (AJAX) ANTES de verificar sessão para HTML
    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['senha_nova'])){
        
        // Verificar se o usuário está logado para processar POST
        if(!isset($_SESSION['usuario_id'])){
            echo "Sessão expirada. Faça login novamente.";
            exit();
        }

        $nova_senha = trim($_POST['senha_nova']);

        // Pegar o id da sessão
        $id_sessao = $_SESSION['usuario_id'];

        // Verificar se o usuário existe
        $sql_verifica = "SELECT id_usuario FROM usuario WHERE id_usuario = :id"; 
        $stmt_verifica = $pdo->prepare($sql_verifica);
        $stmt_verifica->execute([':id' => $id_sessao]);
        $usuario = $stmt_verifica->fetch(PDO::FETCH_ASSOC);

        if($usuario){
            // Criptografar a senha antes de salvar!
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            
            $sql_update = "UPDATE usuario SET senha = :senha WHERE id_usuario = :id"; 
            $stmt_update = $pdo->prepare($sql_update);

            if($stmt_update->execute([':senha' => $senha_hash, ':id' => $id_sessao])){    
                echo "Senha alterada com sucesso!";
            }else{
                echo "Erro ao redefinir senha.";
            }
        }else{
            echo "Usuário não encontrado.";
        }
        exit(); 
    }

    // Verificar se o usuário está logado para exibir a página
    if(!isset($_SESSION['usuario_id'])){
        header("Location: login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - InovaTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="src/styles/login.css">
    <link rel="stylesheet" href="src/styles/redefinirsenha.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo-container">
                <img src="src/images/painelSolar.png" alt="InovaTech">
                <span class="brand">InovaTech</span>
            </div>
            <div class="actions">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </nav>
    </header>

    <div class="login-wrapper">
        <div class="login-container">
            <div class="reset-icon">
                <i class="fas fa-lock-open"></i>
            </div>
            <h1>Redefinir Senha</h1>
            <p class="subtitle">Crie uma nova senha segura para sua conta</p>

            <div id="messageBox"></div>

            <form id="resetPasswordForm">
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <div class="password-wrapper">
                        <input type="password" id="nova_senha" name="senha_nova" placeholder="Digite sua nova senha" required>
                        <button type="button" class="toggle-password" data-target="nova_senha" aria-label="Mostrar senha">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirma_senha">Confirmar Nova Senha</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Confirme sua nova senha" required>
                        <button type="button" class="toggle-password" data-target="confirma_senha" aria-label="Mostrar senha">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="password-requirements">
                    <h4>Requisitos da senha:</h4>
                    <div class="requirement" id="req-length">
                        <i class="fas fa-circle"></i>
                        <span>Mínimo de 8 caracteres</span>
                    </div>
                    <div class="requirement" id="req-uppercase">
                        <i class="fas fa-circle"></i>
                        <span>Pelo menos uma letra maiúscula</span>
                    </div>
                    <div class="requirement" id="req-lowercase">
                        <i class="fas fa-circle"></i>
                        <span>Pelo menos uma letra minúscula</span>
                    </div>
                    <div class="requirement" id="req-number">
                        <i class="fas fa-circle"></i>
                        <span>Pelo menos um número</span>
                    </div>
                    <div class="requirement" id="req-match">
                        <i class="fas fa-circle"></i>
                        <span>As senhas devem coincidir</span>
                    </div>
                </div>

                <button type="submit" class="btn-entrar" id="submitBtn" disabled>
                    <i class="fas fa-check-circle"></i> Redefinir Senha
                </button>

                <div class="back-link">
                <a href="admin.php"><i class="fas fa-arrow-left"></i> Voltar para o Dashboard</a>
                </div>
            </form>
        </div>
    </div>

    <script src="src/javascript/redefinirsenha.js"></script>
</body>
</html>