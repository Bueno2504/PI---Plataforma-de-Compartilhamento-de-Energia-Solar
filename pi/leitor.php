<?php
require_once 'verifica_login.php';
$usuario = getUsuarioLogado();

// Processar requisições da API
$isApiRequest = isset($_GET['acao']) || $_SERVER['REQUEST_METHOD'] === 'POST';

if ($isApiRequest) {
    require_once 'processar_leitura.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InovaTech - Leitor de Energia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="src/styles/leitor.css">
</head>

<body>
    <header>
        <nav>
            <div class="logo-container">
                <a href="index.html" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                    <img src="src/images/painelSolar.png" alt="InovaTech">
                    <span class="brand">InovaTech</span>
                </a>
            </div>
            <div class="actions">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-greeting">
                    <span>Olá, <?php echo htmlspecialchars($usuario['nome']); ?></span>
                    <i class="fa-solid fa-circle-user"></i>
                    <button id="leitor_btn" class="dropdown-toggle">
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    
                   <div id="leitorMenu" class="admin-menu">
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

    <!-- Cards de Informações -->
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-title">Leituras Hoje</div>
            <div class="card-value" id="leiturasHoje">0</div>
            <div class="card-subtitle">Registradas no dia</div>
        </div>

        <div class="card">
            <div class="card-title">Total do Mês</div>
            <div class="card-value" id="leiturasTotal">0</div>
            <div class="card-subtitle">Leituras realizadas</div>
        </div>

        <div class="card">
            <div class="card-title">Média de Consumo</div>
            <div class="card-value card-positive" id="mediaConsumo">0</div>
            <div class="card-subtitle">kWh por unidade</div>
        </div>

        <div class="card">
            <div class="card-title">Pendentes</div>
            <div class="card-value" id="pendentes">0</div>
            <div class="card-subtitle">Aguardando leitura</div>
        </div>
    </div>

     <!-- NOVA SEÇÃO: Energia Gerada pelo Condomínio -->
    <div class="section energia-gerada-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-solar-panel"></i> 
                Energia Gerada pelo Condomínio
            </h2>
        </div>

        <form id="energiaGeradaForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="condominio_energia">
                        <i class="fas fa-building"></i>
                        Condomínio
                    </label>
                    <select id="condominio_energia" name="condominio_energia" required>
                        <option value="">Selecione o condomínio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="capacidade_geracao">
                        <i class="fas fa-bolt"></i>
                        Energia Gerada no Mês (kWh)
                    </label>
                    <input 
                        type="number" 
                        id="capacidade_geracao" 
                        name="capacidade_geracao" 
                        step="0.01" 
                        min="0"
                        placeholder="Digite a energia gerada"
                        required
                    >
                    <small style="color: #6b7280; margin-top: 5px; display: block;">
                        <i class="fas fa-info-circle"></i> 
                        Capacidade mensal de geração da usina solar
                    </small>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Salvar Energia Gerada
                </button>
                <button type="reset" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Limpar
                </button>
            </div>
        </form>
    </div>

    <!-- Seção de Registro de Leitura -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fa-solid fa-bolt"></i> Registrar Nova Leitura</h2>
        </div>

        <form id="leituraForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="condominio">
                        <i class="fas fa-building"></i>
                        Condomínio
                    </label>
                    <select id="condominio" name="condominio" required>
                        <option value="">Selecione o condomínio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="residencia">
                        <i class="fas fa-home"></i>
                        Unidade
                    </label>
                    <select id="residencia" name="residencia" required>
                        <option value="">Selecione primeiro o condomínio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_leitura">
                        <i class="fas fa-calendar"></i>
                        Data da Leitura
                    </label>
                    <input type="date" id="data_leitura" name="data_leitura" required>
                </div>

                <div class="form-group">
                    <label for="consumo_casa">
                        <i class="fas fa-tachometer-alt"></i>
                        Consumo (kWh)
                    </label>
                    <input type="number" id="consumo_casa" name="consumo_casa" step="0.01" required
                        placeholder="Digite o consumo">
                </div>
            </div>

            <div class="form-group">
                <label for="observacoes">
                    <i class="fas fa-sticky-note"></i>
                    Observações
                </label>
                <textarea id="observacoes" name="observacoes" rows="3"
                    placeholder="Observações sobre a leitura (opcional)"></textarea>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Salvar Leitura
                </button>
                <button type="reset" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Limpar
                </button>
            </div>
        </form>
    </div>

    <!-- Seção de Filtros -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fa-solid fa-filter"></i> Filtrar Histórico</h2>
        </div>

        <div class="filter-bar">
            <select id="filtroCondominio">
                <option value="">Todos os condomínios</option>
            </select>
            <select id="filtroUnidade">
                <option value="">Todas as unidades</option>
            </select>
            <button id="filtrarBtn" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </div>

    <!-- Seção de Histórico -->
    <div class="section-f">
        <div class="section-header">
            <h2 class="section-title"><i class="fa-solid fa-history"></i> Histórico de Leituras</h2>
            <button class="btn btn-outline" onclick="exportarHistorico()">
                <i class="fas fa-download"></i>
                Exportar
            </button>
        </div>

        <table id="historicoTable">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Condomínio</th>
                    <th>Casa</th>
                    <th>Consumo (kWh)</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody id="historicoBody">
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        <i class="fas fa-spinner fa-spin"></i> Carregando dados...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Processando...</p>
        </div>
    </div>

    <script src="src/javascript/leitor.js"></script>
    <script src="src/javascript/script.js"></script>
</body>

</html>