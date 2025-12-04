<?php 
// Debug e tratamento de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar buffer de saída para evitar problemas com headers
ob_start();

require_once 'verifica_login.php';

// Pegar dados do usuário logado
$usuarioLogado = getUsuarioLogado();

// Debug - remova após corrigir
if (!is_array($usuarioLogado)) {
    die("ERRO: getUsuarioLogado() não retornou um array. Valor: " . print_r($usuarioLogado, true));
}

// Verificar se o usuário tem permissão de administrador
if ($usuarioLogado['perfil'] !== 'ADMIN') {
    header('Location: leitor.php');
    exit;
}

// Buscar condomínios do administrador
require_once 'conexao.php';

// Inicializar array vazio
$condominios = [];

try {
    $stmtCondominios = $pdo->prepare("
        SELECT 
            c.id_condominio,
            c.nome_condominio,
            c.cnpj,
            c.total_unidades,
            c.numero_condominio,
            COUNT(r.id_residencia) as residencias_cadastradas,
            COALESCE(u.capacidade_geracao_kwh, 0) as capacidade_geracao_kwh
        FROM condominio c
        LEFT JOIN residencia r ON c.id_condominio = r.id_condominio AND r.ativa = 1
        LEFT JOIN usina u ON c.id_condominio = u.id_condominio AND u.ativa = 1
        WHERE c.id_administrador = ?
        GROUP BY c.id_condominio
        ORDER BY c.nome_condominio
    ");
    
    $stmtCondominios->execute([$usuarioLogado['id']]);
    $condominios = $stmtCondominios->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar condomínios: " . $e->getMessage());
    die("Erro no banco de dados: " . $e->getMessage());
}

// Limpar buffer
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InovaTech - Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="src/styles/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="fade-in">
        <nav>
            <div class="logo-container">
                <img src="src/images/painelSolar.png" alt="InovaTech">
                <span class="brand">InovaTech</span>
            </div>
            <div class="actions">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-greeting">
                    <span>Olá, <?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>
                    <i class="fa-solid fa-circle-user"></i>
                    <button id="admin_btn">
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    
                    <div id="adminMenu" class="admin-menu">
                        <a href="cadcond.php" class="menu-item">
                            <i class="fa-solid fa-building"></i>
                            <span>Cadastrar Condomínio</span>
                        </a>
                        <a href="cadresi.php" class="menu-item">
                            <i class="fa-solid fa-house-user"></i>
                            <span>Cadastrar Residência</span>
                        </a>
                        <a href="registeer.php" class="menu-item">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Cadastrar Leitor</span>
                        </a>
                        <a href="redefinirsenha.php" class="menu-item">
                            <i class="fa-solid fa-key"></i>
                            <span>Redefinir Senha</span>
                        </a>
                        <div class="menu-divider"></div>
                        <a class="menu-item logout" onclick="logout()">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Sair</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Dashboard Cards -->
    <div class="dashboard-grid fade-in-up">
        <div class="card" data-delay="0">
            <div class="card-icon">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div class="card-content">
                <div class="card-title">Energia Gerada</div>
                <div class="card-value" id="energiaGerada">-</div>
                <div class="card-subtitle">kWh este mês</div>
            </div>
        </div>

        <div class="card" data-delay="100">
            <div class="card-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="card-content">
                <div class="card-title">Consumo Total</div>
                <div class="card-value" id="consumoTotal">-</div>
                <div class="card-subtitle">kWh no condomínio</div>
            </div>
        </div>

        <div class="card" data-delay="200">
            <div class="card-icon card-icon-success">
                <i class="fa-solid fa-battery-full"></i>
            </div>
            <div class="card-content">
                <div class="card-title">Créditos de Energia</div>
                <div class="card-value card-positive" id="creditos">-</div>
                <div class="card-subtitle">kWh disponíveis</div>
            </div>
        </div>

        <div class="card" data-delay="300">
            <div class="card-icon card-icon-warning">
                <i class="fa-solid fa-piggy-bank"></i>
            </div>
            <div class="card-content">
                <div class="card-title">Economia do Mês</div>
                <div class="card-value card-positive" id="economia">R$ -</div>
                <div class="card-subtitle">Comparado ao anterior</div>
            </div>
        </div>
    </div>

    <!-- Seção: Meus Condomínios -->
    <div class="condominios-section fade-in-up" data-delay="400">
        <div class="condominios-header">
            <h2>
                <i class="fa-solid fa-building"></i>
                Meus Condomínios
                <?php if (count($condominios) > 0): ?>
                    <span class="count-badge"><?= count($condominios) ?></span>
                <?php endif; ?>
            </h2>
        </div>

        <?php if (empty($condominios)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-solid fa-building-circle-xmark"></i>
                </div>
                <h3>Nenhum condomínio cadastrado</h3>
                <p>Comece cadastrando seu primeiro condomínio para gerenciar a energia solar compartilhada</p>
            </div>
        <?php else: ?>
            <div class="condominios-grid">
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Condomínio</th>
                            <th>CNPJ</th>
                            <th>Total de Unidades</th>
                            <th>Unidades cadastradas</th>
                            <th>Progresso</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($condominios as $condo): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($condo['nome_condominio']) ?></strong><br>
                                    <small style="color: var(--text-secondary);">Nº <?= htmlspecialchars($condo['numero_condominio']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($condo['cnpj']) ?></td>
                                <td><?= $condo['total_unidades'] ?></td>
                                <td><?= $condo['residencias_cadastradas'] ?></td>
                                <td class="condo-progress-cell">
                                    <div class="progress-wrapper">
                                        <div class="progress-label">
                                            <span>Cadastro</span>
                                            <span><?= $condo['total_unidades'] > 0 ? round(($condo['residencias_cadastradas'] / $condo['total_unidades']) * 100) : 0 ?>%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $condo['total_unidades'] > 0 ? ($condo['residencias_cadastradas'] / $condo['total_unidades']) * 100 : 0 ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="condo-status-badge">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Ativo
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Seção: Gestão dos Moradores -->
    <div class="section fade-in-up" data-delay="500">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-house"></i> 
                Gestão dos Moradores
            </h2>
            <div class="filter-bar">
                <input type="text" id="searchMorador" placeholder="Buscar..." onkeyup="buscarMorador()">
            </div>
        </div>

        <table id="moradoresTable">
            <thead>
                <tr>
                    <th>Número da residência</th>
                    <th>Consumo (kWh)</th>
                    <th>Energia Recebida (kWh)</th>
                    <th>Saldo (kWh)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="moradoresBody">
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        <i class="fas fa-spinner fa-spin"></i> Carregando dados...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Seção: Financeiro -->
    <div class="section-f fade-in-up" data-delay="600">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-money-bill"></i> 
                Financeiro
            </h2>
            <button class="btn btn-outline" onclick="exportarRelatorio()">
                <i class="fas fa-download"></i>
                Exportar Relatório
            </button>
        </div>

        <div class="comparison-grid">
            <div class="comparison-item">
                <div class="comparison-label">Conta Média Antes do Sistema</div>
                <div class="comparison-value">R$ 8.450</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 100%"></div>
                </div>
            </div>

            <div class="comparison-item">
                <div class="comparison-label">Conta Média Depois do Sistema</div>
                <div class="comparison-value">R$ 422</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 5%"></div>
                </div>
                <div class="savings">↓ Economia de 95%</div>
            </div>
        </div>

        <div class="total-savings">
            <div class="savings-label">Economia Total Acumulada</div>
            <div class="savings-value">R$ 145.680</div>
            <div class="savings-since">Desde a instalação do sistema</div>
        </div>

        <div class="projection-section">
            <div class="projection-title">
                <i class="fa-solid fa-arrow-trend-up"></i> 
                Projeção de Economia Futura
            </div>
            <div class="projection-grid">
                <div>
                    <div class="projection-period">Próximos 12 meses</div>
                    <div class="projection-amount">R$ 96.336</div>
                </div>
                <div>
                    <div class="projection-period">Próximos 5 anos</div>
                    <div class="projection-amount">R$ 481.680</div>
                </div>
                <div>
                    <div class="projection-period">Próximos 25 anos</div>
                    <div class="projection-amount">R$ 2.408.400</div>
                </div>
            </div>
        </div>
    </div>

    <script src="src/javascript/script.js"></script>
</body>
</html>