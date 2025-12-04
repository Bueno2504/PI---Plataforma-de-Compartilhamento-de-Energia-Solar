<?php
    include("conexao.php");
    
    
    // Importa o PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer-master/src/Exception.php';
    require 'PHPMailer-master/src/PHPMailer.php';
    require 'PHPMailer-master/src/SMTP.php';

    $mensagem = '';
    $tipo_mensagem = '';

    if(isset($_POST['ok'])){
        
        $email = trim($_POST['email']);

        if(empty($email)){
            $mensagem = "Por favor, informe seu e-mail.";
            $tipo_mensagem = "erro";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $mensagem = "E-mail inválido.";
            $tipo_mensagem = "erro";
        } else {
            
            // Verifica se o email existe no banco
            $sql_verifica = "SELECT * FROM usuario WHERE email = :email";
            $stmt_verifica = $pdo->prepare($sql_verifica);
            $stmt_verifica->execute([':email' => $email]);
            $usuario = $stmt_verifica->fetch(PDO::FETCH_ASSOC);
            
            if(!$usuario){
                $mensagem = "E-mail não encontrado em nossa base de dados.";
                $tipo_mensagem = "erro";
            } else {
                
                // Gera senha temporária (6 caracteres)
                $novasenha = substr(md5(time()), 0, 6);
                
                // Configuração do PHPMailer
                $mail = new PHPMailer(true);
                
                try {
                    // Configurações do servidor SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'inovatech473@gmail.com';
                    $mail->Password = 'xngj hxsj hpzj mgor';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    // Remetente e destinatário
                    $mail->setFrom('inovatech473@gmail.com', 'InovaTech');
                    $mail->addAddress($email);

                    // Conteúdo do e-mail
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperação de Senha - InovaTech';
                    $mail->Body = "
                        <html>
                        <body style='font-family: Arial, sans-serif;'>
                            <h2 style='color: #333;'>Recuperação de Senha</h2>
                            <p>Olá, <strong>{$usuario['nome']}</strong>!</p>
                            <p>Você solicitou a recuperação de senha.</p>
                            <p>Sua nova senha temporária é: <strong style='font-size: 18px; color: #4CAF50;'>$novasenha</strong></p>
                            <p style='color: #666;'>Por segurança, recomendamos que você altere sua senha após fazer login.</p>
                            <hr>
                            <p style='font-size: 12px; color: #999;'>Se você não solicitou esta recuperação, ignore este e-mail.</p>
                        </body>
                        </html>
                    ";

                    // Envia o e-mail
                    $mail->send();
                    
                    // Atualiza a senha no banco de dados
                    $sql_update = "UPDATE usuario SET senha = :senha WHERE email = :email";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->execute([
                        ':senha' => $novasenha,
                        ':email' => $email
                    ]);
                    
                    $mensagem = "Senha enviada com sucesso! Verifique seu e-mail.";
                    $tipo_mensagem = "sucesso";
                    
                } catch (Exception $e) {
                    $mensagem = "Erro ao enviar e-mail. Tente novamente mais tarde.";
                    $tipo_mensagem = "erro";
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - InovaTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="src/styles/login.css">
    <style>
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }
        
        .message-box.sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message-box.erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-voltar {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-voltar:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .recovery-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 25px;
            text-align: center;
        }
    </style>
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
        <div class="login-container" id="recoveryContainer">
            <div class="recovery-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1>Recuperar Senha</h1>
            <p class="subtitle">Informe seu e-mail cadastrado para receber uma nova senha</p>
            
            <?php if(!empty($mensagem)): ?>
                <div class="message-box <?php echo $tipo_mensagem; ?>">
                    <?php if($tipo_mensagem == 'sucesso'): ?>
                        <i class="fas fa-check-circle"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle"></i>
                    <?php endif; ?>
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="seu@email.com"
                        value="<?php echo $_POST['email'] ?? ''; ?>"
                        required
                    >
                </div>
                
                <button type="submit" name="ok" class="btn-entrar">
                    <i class="fas fa-paper-plane"></i> Enviar Nova Senha
                </button>
                
                <a href="login.php" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar para o Login
                </a>
            </form>
        </div>
    </div>

    <script src="src/javascript/script.js"></script>
</body>
</html>