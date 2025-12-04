<?php
session_start();
require("conexao.php");

/*if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}*/

// Função para limpar máscaras (CNPJ, CPF, etc) - MANTÉM APENAS NÚMEROS
function limparMascara($valor) {
    return preg_replace('/\D/', '', $valor);
}

// Função para formatar CEP (adiciona o hífen)
function formatarCep($cep) {
    $cep = limparMascara($cep);
    if (strlen($cep) == 8) {
        return substr($cep, 0, 5) . '-' . substr($cep, 5);
    }
    return $cep;
}

// Função para formatar CNPJ
function formatarCnpj($cnpj) {
    $cnpj = limparMascara($cnpj);
    if (strlen($cnpj) == 14) {
        return substr($cnpj, 0, 2) . '.' . 
               substr($cnpj, 2, 3) . '.' . 
               substr($cnpj, 5, 3) . '/' . 
               substr($cnpj, 8, 4) . '-' . 
               substr($cnpj, 12);
    }
    return $cnpj;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Receber dados do formulário
    $nomeCondominio = trim($_POST["nomeCondominio"] ?? '');
    $cnpjOriginal = $_POST["cnpj"] ?? '';
    $cnpj = formatarCnpj($cnpjOriginal); // Formata com pontos e traços
    $totalUnidades = $_POST["totalUnidades"] ?? '';
    $cepOriginal = $_POST["cep"] ?? '';
    $cep = formatarCep($cepOriginal); // Formata como 00000-000
    $rua = trim($_POST["rua"] ?? '');
    $numero_condomino = trim($_POST["numero_condominio"] ?? '');
    $complemento = trim($_POST["complemento"] ?? '');
    $bairro = trim($_POST["bairro"] ?? '');
    $cidade = trim($_POST["cidade"] ?? '');
    $estadoUf = trim($_POST["estado"] ?? '');
    $idAdministrador = $_SESSION['usuario_id'] ?? 1; // usuário logado (temporariamente 1 para teste)

    // Validações básicas
    if (empty($nomeCondominio) || empty($cnpj) || empty($totalUnidades) || empty($cep) || 
        empty($rua) || empty($numero_condomino) || empty($bairro) || empty($cidade) || empty($estadoUf)) {
        header("Location: cadcond.php?error=empty_fields");
        exit;
    }

    // Validar tamanho do CNPJ (deve ter 14 dígitos sem máscara)
    if (strlen(limparMascara($cnpjOriginal)) != 14) {
        header("Location: cadcond.php?error=invalid_cnpj");
        exit;
    }

    // Validar tamanho do CEP (deve ter 8 dígitos sem máscara)
    if (strlen(limparMascara($cepOriginal)) != 8) {
        header("Location: cadcond.php?error=invalid_cep");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Buscar id_estado pelo UF (não insere, pois já existem)
        $stmt = $pdo->prepare("SELECT id_estado FROM estado WHERE uf = ?");
        $stmt->execute([$estadoUf]);
        $idEstado = $stmt->fetchColumn();   

        if (!$idEstado) {
            throw new Exception("Estado inválido: $estadoUf não encontrado no banco");
        }

        // 2. Cidade
        $stmt = $pdo->prepare("SELECT id_cidade FROM cidade WHERE nome = ? AND id_estado = ?");
        $stmt->execute([$cidade, $idEstado]);
        $idCidade = $stmt->fetchColumn();

        if (!$idCidade) {
            $stmt = $pdo->prepare("INSERT INTO cidade (nome, id_estado) VALUES (?, ?)");
            $stmt->execute([$cidade, $idEstado]);
            $idCidade = $pdo->lastInsertId();
        }

        // 3. Bairro
        $stmt = $pdo->prepare("SELECT id_bairro FROM bairro WHERE nome = ? AND id_cidade = ?");
        $stmt->execute([$bairro, $idCidade]);
        $idBairro = $stmt->fetchColumn();

        if (!$idBairro) {
            $stmt = $pdo->prepare("INSERT INTO bairro (nome, id_cidade) VALUES (?, ?)");
            $stmt->execute([$bairro, $idCidade]);
            $idBairro = $pdo->lastInsertId();
        }

        // 4. CEP (agora com formato 00000-000)
        $stmt = $pdo->prepare("SELECT id_cep FROM cep WHERE cep = ?");
        $stmt->execute([$cep]);
        $idCep = $stmt->fetchColumn();

        if (!$idCep) {
            $stmt = $pdo->prepare("INSERT INTO cep (cep, logradouro, id_bairro) VALUES (?, ?, ?)");
            $stmt->execute([$cep, $rua, $idBairro]);
            $idCep = $pdo->lastInsertId();
        }

        // 5. Endereço
        $stmt = $pdo->prepare("INSERT INTO endereco (id_cep, complemento) VALUES (?, ?)");
        $stmt->execute([$idCep, $complemento]);
        $idEndereco = $pdo->lastInsertId();

        // 6. Condomínio (CNPJ agora formatado)
        $stmt = $pdo->prepare("INSERT INTO condominio (nome_condominio, cnpj, total_unidades, numero_condominio, id_endereco, id_administrador) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeCondominio, $cnpj, $totalUnidades,$numero_condomino, $idEndereco, $idAdministrador]);

        $pdo->commit();

        header("Location: cadcond.php?success=registered");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        
        // Tratamento de erros específicos
        if ($e->getCode() == 23000) {
            // Violação de chave única (CNPJ duplicado)
            error_log("Erro de duplicação: " . $e->getMessage());
            header("Location: cadcond.php?error=duplicate_cnpj");
        } else {
            error_log("Erro no banco de dados: " . $e->getMessage());
            header("Location: cadcond.php?error=database_error");
        }
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro geral: " . $e->getMessage());
        header("Location: cadcond.php?error=general_error");
        exit;
    }
}

// Buscar estados para popular o select
$stmtEstados = $pdo->query("SELECT uf, nome FROM estado ORDER BY nome");
$estados = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cadastro de Condomínio - InovaTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="src/styles/cadcond.css" />
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
            <h1>Cadastro de Condomínio</h1>
            <p class="subtitle">Registre seu condomínio para implementar o sistema de energia solar compartilhada</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message" style="background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php
                    switch($_GET['error']) {
                        case 'empty_fields':
                            echo 'Por favor, preencha todos os campos obrigatórios.';
                            break;
                        case 'invalid_cnpj':
                            echo 'CNPJ inválido. Deve conter 14 dígitos.';
                            break;
                        case 'invalid_cep':
                            echo 'CEP inválido. Deve conter 8 dígitos.';
                            break;
                        case 'duplicate_cnpj':
                            echo 'Este CNPJ já está cadastrado no sistema.';
                            break;
                        case 'database_error':
                            echo 'Erro ao salvar no banco de dados. Tente novamente.';
                            break;
                        default:
                            echo 'Ocorreu um erro. Tente novamente.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
                <div class="success-message" style="background: #efe; color: #2a2; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    Condomínio cadastrado com sucesso! Em breve entraremos em contato.
                </div>
            <?php endif; ?>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p><strong>Atenção:</strong> Este cadastro deve ser realizado pelo síndico ou administrador do condomínio.</p>
            </div>

            <form id="condoForm" method="post" action="">

                <!-- Dados do Condomínio -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-building"></i>
                        Dados do Condomínio
                    </h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="nomeCondominio">Nome do Condomínio *</label>
                            <input type="text" id="nomeCondominio" name="nomeCondominio" 
                                   placeholder="Ex: Residencial Jardim Solar" 
                                   value="<?= htmlspecialchars($_POST['nomeCondominio'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="form-group">
                            <label for="cnpj">CNPJ *</label>
                            <input type="text" id="cnpj" name="cnpj" data-mask="cnpj" 
                                   placeholder="00.000.000/0000-00" 
                                   value="<?= htmlspecialchars($_POST['cnpj'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="form-group">
                            <label for="totalUnidades">Total de Unidades *</label>
                            <input type="number" id="totalUnidades" name="totalUnidades" min="1" 
                                   placeholder="Ex: 120" 
                                   value="<?= htmlspecialchars($_POST['totalUnidades'] ?? '') ?>" 
                                   required />
                        </div>
                    </div>
                </div>

                <!-- Endereço do Condomínio -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Endereço do Condomínio
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="cep">CEP *</label>
                            <input type="text" id="cep" name="cep" data-mask="cep" 
                                   placeholder="00000-000" 
                                   value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>" 
                                   required />
                            <div id="cep-status" style="margin-top: 5px; font-size: 0.9em; color: #667eea;"></div>
                        </div>
                        <div class="form-group">
                            <label for="rua">Rua/Avenida *</label>
                            <input type="text" id="rua" name="rua" 
                                   value="<?= htmlspecialchars($_POST['rua'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="form-group">
                            <label for="numero_condominio">Número *</label>
                            <input type="text" id="numero_condomino" name="numero_condominio" 
                                   value="<?= htmlspecialchars($_POST['numero_condominio'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento" name="complemento" 
                                   placeholder="Entrada principal, Portaria, etc." 
                                   value="<?= htmlspecialchars($_POST['complemento'] ?? '') ?>" />
                        </div>
                        <div class="form-group">
                            <label for="bairro">Bairro *</label>
                            <input type="text" id="bairro" name="bairro" 
                                   value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="form-group">
                            <label for="cidade">Cidade *</label>
                            <input type="text" id="cidade" name="cidade" 
                                   value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado *</label>
                            <select id="estado" name="estado" required>
                                <option value="">Selecione</option>
                                <?php foreach ($estados as $estado): ?>
                                <option value="<?= htmlspecialchars($estado['uf']) ?>"
                                    <?= (isset($_POST['estado']) && $_POST['estado'] == $estado['uf']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> Cadastrar Condomínio
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