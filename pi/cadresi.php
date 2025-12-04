<?php
require_once 'verifica_login.php';
require("conexao.php");

// Pegar dados do usuário logado
$usuarioLogado = getUsuarioLogado();

// BUSCA CONDOMÍNIOS APENAS DO ADMINISTRADOR LOGADO
try {
    $stmtCondominio = $pdo->prepare("
        SELECT id_condominio, nome_condominio, total_unidades 
        FROM condominio 
        WHERE id_administrador = ?
        ORDER BY nome_condominio
    ");
    $stmtCondominio->execute([$usuarioLogado['id']]);
    $condominios = $stmtCondominio->fetchAll(PDO::FETCH_ASSOC); 
} catch (PDOException $e) {
    error_log("Erro ao buscar condomínios: " . $e->getMessage());
    $condominios = [];
}

// PROCESSAR FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $idCondominio = trim($_POST['condominio'] ?? ''); 
    $numeroResidencia = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    if (empty($idCondominio) || empty($numeroResidencia)) {
        header("Location: cadresi.php?error=empty_fields");
        exit;
    }
    
    try {
        // VERIFICAR SE O CONDOMÍNIO PERTENCE AO ADMINISTRADOR LOGADO
        $stmtVerifica = $pdo->prepare("
            SELECT id_condominio 
            FROM condominio 
            WHERE id_condominio = ? AND id_administrador = ?
        ");
        $stmtVerifica->execute([$idCondominio, $usuarioLogado['id']]);
        
        if (!$stmtVerifica->fetch()) {
            header("Location: cadresi.php?error=unauthorized_condo");
            exit;
        }
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO residencia (id_condominio, numero_residencia, ativa) VALUES (?, ?, 1)");
        $stmt->execute([$idCondominio, $numeroResidencia]);
        
        $pdo->commit();
        header("Location: cadresi.php?success=registered");
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erro: " . $e->getMessage());
        header("Location: cadresi.php?error=database_error");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Residência - InovaTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="src/styles/cadresi.css">
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

    <div class="register-wrapper">
        <div class="register-container">
            <h1>Cadastro de Residência</h1>
            <p class="subtitle">Registre uma unidade nos seus condomínios gerenciados</p>
            
            <div class="info-box" style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 12px 15px; margin-bottom: 20px; border-radius: 4px;">
                <i class="fas fa-user-shield" style="color: #2196F3;"></i>
                <span style="color: #1565C0; margin-left: 8px;">
                    Administrador: <strong><?= htmlspecialchars($usuarioLogado['nome']) ?></strong>
                </span>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div style="background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php
                    switch($_GET['error']) {
                        case 'empty_fields': 
                            echo 'Preencha todos os campos obrigatórios.'; 
                            break;
                        case 'unauthorized_condo': 
                            echo 'Você não tem permissão para cadastrar residências neste condomínio.'; 
                            break;
                        case 'database_error': 
                            echo 'Erro ao salvar no banco de dados.'; 
                            break;
                        default: 
                            echo 'Ocorreu um erro.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: #efe; color: #2a2; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    Residência cadastrada com sucesso!
                </div>
            <?php endif; ?>

            <?php if (empty($condominios)): ?>
                <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Atenção:</strong> Você ainda não possui condomínios cadastrados. 
                    <a href="cadcond.php" style="color: #856404; text-decoration: underline;">Cadastre um condomínio primeiro</a>.
                </div>
            <?php endif; ?>

            <form id="residenceForm" method="post" action="">

                <!-- Cadastro de Residência -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-home"></i>
                        Cadastro de Residência
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="condominio">
                                Condomínio *
                                <?php if (!empty($condominios)): ?>
                                    <span style="font-size: 0.85em; color: #666; font-weight: normal;">
                                        (<?= count($condominios) ?> disponível<?= count($condominios) > 1 ? 'is' : '' ?>)
                                    </span>
                                <?php endif; ?>
                            </label>
                            <select id="condominio" name="condominio" required <?= empty($condominios) ? 'disabled' : '' ?>>
                                <option value="">Selecione</option>
                                <?php foreach ($condominios as $cond): ?>
                                    <option value="<?= htmlspecialchars($cond['id_condominio']) ?>"
                                        <?= (isset($_POST['condominio']) && $_POST['condominio'] == $cond['id_condominio']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cond['nome_condominio']) ?>
                                        (<?= $cond['total_unidades'] ?> unidades)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="numero">Número *</label>
                            <input type="text" id="numero" name="numero" 
                                   placeholder="Ex: Casa 15, Apto 302"
                                   value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>"
                                   <?= empty($condominios) ? 'disabled' : '' ?>
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento" name="complemento" 
                                   placeholder="Apto, Bloco, etc."
                                   value="<?= htmlspecialchars($_POST['complemento'] ?? '') ?>"
                                   <?= empty($condominios) ? 'disabled' : '' ?>>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="form-section">
                    <div class="form-group full-width">
                        <label for="observacoes">Observações Adicionais</label>
                        <textarea id="observacoes" name="observacoes" 
                                  placeholder="Informações relevantes sobre a residência"
                                  <?= empty($condominios) ? 'disabled' : '' ?>><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Termos -->
                <div class="checkbox-group">
                    <input type="checkbox" id="termos" name="termos" 
                           <?= empty($condominios) ? 'disabled' : '' ?> required>
                    <label for="termos">Aceito os termos e condições do programa de energia solar compartilhada *</label>
                </div>

                <button type="submit" class="btn-submit" <?= empty($condominios) ? 'disabled' : '' ?>>
                    <i class="fas fa-check-circle"></i> Cadastrar Residência
                </button>
            </form>

            <div class="back-link">
                <a href="admin.php"><i class="fas fa-arrow-left"></i> Voltar para o Dashboard</a>
            </div>
        </div>
    </div>

    <script src="src/javascript/script.js"></script>
</body>
</html>