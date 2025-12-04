<?php
// Iniciar buffer de saída ANTES de qualquer coisa
ob_start();

// Iniciar sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require("conexao.php");
require_once 'verifica_login.php';

// Pegar dados do usuário logado (administrador)
$usuarioLogado = getUsuarioLogado();

if($_SERVER["REQUEST_METHOD"] == "POST"){

    //pegando os dados preenchidos no formulário
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];   
    $senha = $_POST["senha"];
    $perfil = $_POST["perfil"];
    
    // ID do administrador logado
    $idAdminResponsavel = $usuarioLogado['id'];

    //validações se campo esta vazio
    if(empty($nome)){
        header("Location: registeer.php?error=empty_nome");
        exit;
    }

    if(empty($cpf)){
        header("Location: registeer.php?error=empty_cpf");
        exit;
    }

    if(empty($telefone)){
        header("Location: registeer.php?error=empty_telefone");
        exit;
    }

    if(empty($email)){
        header("Location: registeer.php?error=empty_email");
        exit;
    }

    if(empty($senha)){
        header("Location: registeer.php?error=empty_senha");
        exit;
    }

    if(empty($perfil)){
        header("Location: registeer.php?error=empty_perfil");
        exit;
    }

    // Verifica se o email já existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt->execute([$email]);

    if($stmt->fetch()){
        header("Location: registeer.php?error=email_exists");
        exit;
    }

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

    // INSERT no banco com vínculo ao administrador
    // Se for LEITOR, vincula ao admin logado. Se for ADMIN, não vincula ninguém (NULL)
    if($perfil === 'LEITOR'){
        $stmt = $pdo->prepare("INSERT INTO usuario (nome, cpf, telefone, email, senha, perfil, id_administrador_responsavel) VALUES (?,?,?,?,?,?,?)");
        $resultado = $stmt->execute([$nome, $cpf, $telefone, $email, $senha, $perfil, $idAdminResponsavel]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO usuario (nome, cpf, telefone, email, senha, perfil) VALUES (?,?,?,?,?,?)");
        $resultado = $stmt->execute([$nome, $cpf, $telefone, $email, $senha, $perfil]);
    }

    //Cadastro foi realizado com sucesso
    if($resultado){
        header("Location: registeer.php?success=registered");
        exit;
    } else {
        header("Location: registeer.php?error=database_error");
        exit;
    }
}

// Limpar buffer de saída
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro - InovaTech</title>
  <link rel="stylesheet" href="src/styles/register.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

  <div class="register-container">
    <h2>Cadastro de Usuário</h2>
    <p class="subtitle-info">
        <i class="fas fa-info-circle"></i> 
        Leitores cadastrados terão acesso aos condomínios gerenciados por você
    </p>
    
    <form action="registeer.php" method="POST">
      <div class="input-group">
        <label for="nome">Nome completo</label>
        <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>
      </div>

      <div class="input-group">
        <label for="cpf">CPF</label>
        <input type="text" id="cpf" name="cpf" data-mask="cpf" placeholder="000.000.000-00" maxlength="14" required>
      </div>

      <div class="input-group">
        <label for="telefone">Telefone</label>
        <input type="tel" id="telefone" name="telefone" data-mask="phone" placeholder="(00) 00000-0000" maxlength="15" required>
      </div>

      <div class="input-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
      </div>

      <div class="input-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
      </div>

      <div class="input-group">
        <label for="confirm-password">Confirme a senha</label>
        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirme sua senha" required>
      </div>

      <!-- Campo oculto com valor fixo LEITOR -->
      <input type="hidden" id="perfil" name="perfil" value="LEITOR">

      <div class="perfil-info-box">
        <div class="checkbox-wrapper">
          <input type="checkbox" id="aceite-leitor" required>
          <label for="aceite-leitor">
            <i class="fas fa-user-check"></i>
            Confirmo que estou cadastrando um usuário com perfil de <strong>Leitor</strong>
          </label>
        </div>
      </div>

      

      <button type="submit" class="btn-register">Cadastrar</button>

      <?php if(isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
          <div class="success-message">
              <i class="fas fa-check-circle"></i>
              Usuário cadastrado com sucesso!
          </div>
      <?php endif; ?>

      <?php 
          if(isset($_GET['error'])){
              echo '<div class="error-message">';
              switch($_GET['error']){
                  case "empty_nome":
                      echo "<i class='fas fa-exclamation-circle'></i> Informe o nome!";
                      break;
                  case "empty_cpf":
                      echo "<i class='fas fa-exclamation-circle'></i> Informe o CPF!";
                      break;
                  case "empty_telefone":
                      echo "<i class='fas fa-exclamation-circle'></i> Informe o telefone!";
                      break;
                  case "empty_email":
                      echo "<i class='fas fa-exclamation-circle'></i> Informe o email!";
                      break;
                  case "empty_senha":
                      echo "<i class='fas fa-exclamation-circle'></i> Informe a senha!";
                      break;
                  case "empty_perfil":
                      echo "<i class='fas fa-exclamation-circle'></i> Selecione o perfil!";
                      break;
                  case "email_exists":
                      echo "<i class='fas fa-exclamation-circle'></i> Este email já está cadastrado!";
                      break;
                  case "database_error":
                      echo "<i class='fas fa-exclamation-circle'></i> Erro ao cadastrar. Tente novamente!";
                      break;
              }
              echo '</div>';
          }
      ?>
      
      <div class="back-link">
        <a href="admin.php"><i class="fas fa-arrow-left"></i> Voltar para o Dashboard</a>
      </div>
    </form>
  </div>

  <script src="src/javascript/script.js"></script>
</body>
    
</html>