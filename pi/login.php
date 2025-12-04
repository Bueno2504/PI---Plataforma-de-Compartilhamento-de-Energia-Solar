<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InovaTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="src/styles/login.css">
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
        <div class="login-container" id="loginContainer">
            <h1>Login</h1>
            
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="password-wrapper">
                        <input type="password" id="senha" name="senha" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar senha">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-entrar">Entrar</button>
            </form>

             <?php 
                if(isset($_GET['error'])){
                    echo '<div class="error-message">';
                    switch($_GET['error']){
                        case "invalid_credentials":
                            echo "Email ou senha incorretos!";
                            break;
                        case "empty_email":
                            echo "Informe um email!";
                            break;
                        case "empty_password":
                            echo "Informe uma senha!";
                            break;
                    }
                    echo '</div>';
                }
            ?>
            
            <div class="signup-link">
                Esqueceu sua senha ? <a href="esqueceusenha.php">Redefinir senha</a>
        </div>
    </div>

    <script src="src/javascript/script.js"></script>
</body>
</html>