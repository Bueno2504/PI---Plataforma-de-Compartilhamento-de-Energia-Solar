// Variáveis globais
let elements = {};
let condominiosCache = [];
let unidadesCache = {};

// Inicialização quando o DOM carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Sistema de leitura iniciando...');
    initializeElements();
    initializeEventListeners();
    setDataAtual();
    carregarCondominios();
    atualizarCards();
    loadHistorico();
    console.log('✅ Sistema de leitura inicializado!');

});
// Inicializar elementos do DOM
function initializeElements() {
    elements = {
        // Formulário de leitura
        condominioSelect: document.getElementById('condominio'),
        residenciaSelect: document.getElementById('residencia'),
        dataLeitura: document.getElementById('data_leitura'),
        consumoCasa: document.getElementById('consumo_casa'),
        observacoes: document.getElementById('observacoes'),
        leituraForm: document.getElementById('leituraForm'),
        
        // Formulário de energia gerada
        condominioEnergia: document.getElementById('condominio_energia'),
        capacidadeGeracao: document.getElementById('capacidade_geracao'),
        energiaGeradaForm: document.getElementById('energiaGeradaForm'),
        
        // Outros elementos
        loadingOverlay: document.getElementById('loadingOverlay'),
        filtroCondominio: document.getElementById('filtroCondominio'),
        filtroUnidade: document.getElementById('filtroUnidade'),
        filtrarBtn: document.getElementById('filtrarBtn'),
        historicoBody: document.getElementById('historicoBody'),
        
        // Cards
        leiturasHoje: document.getElementById('leiturasHoje'),
        leiturasTotal: document.getElementById('leiturasTotal'),
        mediaConsumo: document.getElementById('mediaConsumo'),
        pendentes: document.getElementById('pendentes')
    };
}

// Inicializar event listeners
function initializeEventListeners() {
    // Formulário de leitura - Mudança no select de condomínio
    if (elements.condominioSelect) {
        elements.condominioSelect.addEventListener('change', function() {
            loadUnidades(this.value, elements.residenciaSelect);
        });
    }

    // Formulário de energia - Mudança no select de condomínio
    if (elements.condominioEnergia) {
        elements.condominioEnergia.addEventListener('change', function() {
            carregarCapacidadeAtual(this.value);
        });
    }

    // Mudança no filtro de condomínio
    if (elements.filtroCondominio) {
        elements.filtroCondominio.addEventListener('change', function() {
            loadUnidades(this.value, elements.filtroUnidade, 'Todas as unidades');
        });
    }

    // Botão filtrar
    if (elements.filtrarBtn) {
        elements.filtrarBtn.addEventListener('click', filtrarHistorico);
    }

    // Submit do formulário de leitura
    if (elements.leituraForm) {
        elements.leituraForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitLeitura();
        });
    }

    // Submit do formulário de energia gerada
    if (elements.energiaGeradaForm) {
        elements.energiaGeradaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEnergiaGerada();
        });
    }

    // Validação em tempo real
    setupRealTimeValidation();
}

// Carregar condomínios do banco de dados
function carregarCondominios() {
    showLoading('Carregando condomínios...');
    
    fetch('processar_leitura.php?acao=condominios')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success && data.condominios) {
                condominiosCache = data.condominios;
                popularSelectCondominios(data.condominios);
                console.log(`✅ ${data.condominios.length} condomínios carregados`);
            } else {
                throw new Error('Erro ao carregar condomínios');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('❌ Erro ao carregar condomínios:', error);
            showNotification('Erro ao carregar condomínios. Verifique a conexão.', 'error');
        });
}

// Popular selects de condomínio
function popularSelectCondominios(condominios) {
    const selects = [
        elements.condominioSelect, 
        elements.condominioEnergia, 
        elements.filtroCondominio
    ].filter(Boolean);
    
    selects.forEach(select => {
        const opcaoPadrao = select.querySelector('option[value=""]');
        const textoPadrao = opcaoPadrao ? opcaoPadrao.textContent : 'Selecione';
        
        select.innerHTML = `<option value="">${textoPadrao}</option>`;
        
        condominios.forEach(cond => {
            const option = document.createElement('option');
            option.value = cond.id;
            option.textContent = cond.nome;
            select.appendChild(option);
        });
    });
}

// Carregar unidades/residências
function loadUnidades(condominioId, selectElement, textoPadrao = 'Selecione a unidade') {
    if (!selectElement || !condominioId) {
        if (selectElement) {
            selectElement.innerHTML = `<option value="">${textoPadrao}</option>`;
        }
        return;
    }
    
    // Verificar cache
    if (unidadesCache[condominioId]) {
        popularSelectUnidades(selectElement, unidadesCache[condominioId], textoPadrao);
        return;
    }
    
    selectElement.innerHTML = '<option value="">Carregando...</option>';
    selectElement.disabled = true;
    
    fetch(`processar_leitura.php?acao=unidades&condominio_id=${condominioId}`)
        .then(response => response.json())
        .then(data => {
            selectElement.disabled = false;
            
            if (data.success && data.unidades) {
                unidadesCache[condominioId] = data.unidades;
                popularSelectUnidades(selectElement, data.unidades, textoPadrao);
                console.log(`✅ ${data.unidades.length} unidades carregadas`);
            } else {
                throw new Error('Erro ao carregar unidades');
            }
        })
        .catch(error => {
            selectElement.disabled = false;
            selectElement.innerHTML = `<option value="">Erro ao carregar</option>`;
            console.error('❌ Erro ao carregar unidades:', error);
        });
}

// Popular select de unidades
function popularSelectUnidades(selectElement, unidades, textoPadrao) {
    selectElement.innerHTML = `<option value="">${textoPadrao}</option>`;
    
    unidades.forEach(unidade => {
        const option = document.createElement('option');
        option.value = unidade.id;
        option.textContent = unidade.numero || `Unidade ${unidade.id}`;
        selectElement.appendChild(option);
    });
}

// Carregar capacidade atual da usina
function carregarCapacidadeAtual(condominioId) {
    if (!condominioId || !elements.capacidadeGeracao) return;
    
    elements.capacidadeGeracao.value = '';
    elements.capacidadeGeracao.placeholder = 'Carregando...';
    elements.capacidadeGeracao.disabled = true;
    
    fetch(`get_usina.php?condominio_id=${condominioId}`)
        .then(response => response.json())
        .then(data => {
            elements.capacidadeGeracao.disabled = false;
            elements.capacidadeGeracao.placeholder = 'Digite a energia gerada';
            
            if (data.success && data.usina) {
                elements.capacidadeGeracao.value = data.usina.capacidade_geracao_kwh;
                showNotification('Capacidade atual carregada', 'info');
            } else {
                elements.capacidadeGeracao.value = '';
            }
        })
        .catch(error => {
            elements.capacidadeGeracao.disabled = false;
            elements.capacidadeGeracao.placeholder = 'Digite a energia gerada';
            console.error('❌ Erro ao carregar capacidade:', error);
        });
}

// Definir data atual
function setDataAtual() {
    if (elements.dataLeitura) {
        const hoje = new Date();
        const dataFormatada = hoje.toISOString().split('T')[0];
        elements.dataLeitura.value = dataFormatada;
        elements.dataLeitura.max = dataFormatada;
    }
}

// Atualizar cards informativos
function atualizarCards() {
    fetch('processar_leitura.php?acao=estatisticas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                animarValor(elements.leiturasHoje, data.leituras_hoje || 0);
                animarValor(elements.leiturasTotal, data.leituras_total || 0);
                animarValor(elements.mediaConsumo, data.media_consumo || 0, true);
                animarValor(elements.pendentes, data.pendentes || 0);
            }
        })
        .catch(error => {
            console.error('❌ Erro ao atualizar cards:', error);
        });
}

// Animar valores dos cards
function animarValor(elemento, valorFinal, isDecimal = false) {
    if (!elemento) return;
    
    let valorAtual = 0;
    const incremento = valorFinal / 30;
    const intervalo = setInterval(() => {
        valorAtual += incremento;
        if (valorAtual >= valorFinal) {
            valorAtual = valorFinal;
            clearInterval(intervalo);
        }
        elemento.textContent = isDecimal ? valorAtual.toFixed(1) : Math.round(valorAtual);
    }, 30);
}

// Submeter leitura de consumo
function submitLeitura() {
    if (!validateForm(elements.leituraForm)) {
        return;
    }
    
    showLoading('Salvando leitura...');
    
    const formData = new FormData(elements.leituraForm);
    
    fetch('processar_leitura.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showNotification(data.message || 'Leitura salva com sucesso!', 'success');
            
            // Resetar formulário
            elements.leituraForm.reset();
            setDataAtual();
            
            if (elements.residenciaSelect) {
                elements.residenciaSelect.innerHTML = '<option value="">Selecione primeiro o condomínio</option>';
            }
            
            // Atualizar interface
            setTimeout(() => {
                atualizarCards();
                loadHistorico();
            }, 500);
        } else {
            showNotification(data.message || 'Erro ao salvar leitura', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('❌ Erro:', error);
        showNotification('Erro ao comunicar com o servidor', 'error');
    });
}

// Submeter energia gerada
function submitEnergiaGerada() {
    const condominioId = elements.condominioEnergia?.value;
    const capacidade = elements.capacidadeGeracao?.value;
    
    // Validações
    if (!condominioId) {
        showNotification('Selecione um condomínio', 'error');
        elements.condominioEnergia?.classList.add('error');
        return;
    }
    
    if (!capacidade || isNaN(capacidade) || parseFloat(capacidade) < 0) {
        showNotification('Digite uma capacidade válida', 'error');
        elements.capacidadeGeracao?.classList.add('error');
        return;
    }
    
    elements.condominioEnergia?.classList.remove('error');
    elements.capacidadeGeracao?.classList.remove('error');
    
    showLoading('Salvando energia gerada...');
    
    // Enviar dados via PUT
    fetch('processar_leitura.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `condominio_id=${condominioId}&capacidade_geracao_kwh=${capacidade}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showNotification(data.message || 'Energia gerada salva com sucesso!', 'success');
            
            // Limpar formulário
            elements.energiaGeradaForm?.reset();
            
            console.log('✅ Energia gerada atualizada no banco de dados');
        } else {
            showNotification(data.message || 'Erro ao salvar', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('❌ Erro:', error);
        showNotification('Erro ao comunicar com o servidor', 'error');
    });
}

// Validar formulário
function validateForm(form) {
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required]');
    let valido = true;
    let mensagensErro = [];
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            const label = form.querySelector(`label[for="${input.id}"]`);
            if (label) {
                mensagensErro.push(label.textContent.replace(/\s*\n\s*/g, '').trim());
            }
            valido = false;
        } else {
            input.classList.remove('error');
            
            // Validação específica para números
            if (input.type === 'number' && (isNaN(input.value) || parseFloat(input.value) < 0)) {
                input.classList.add('error');
                showNotification('Valor numérico inválido', 'error');
                return false;
            }
        }
    });
    
    if (!valido && mensagensErro.length > 0) {
        showNotification(`Campos obrigatórios: ${mensagensErro.join(', ')}`, 'error');
    }
    
    return valido;
}

// Carregar histórico
function loadHistorico() {
    if (!elements.historicoBody) return;
    
    const condominioId = elements.filtroCondominio?.value || '';
    const unidadeId = elements.filtroUnidade?.value || '';
    
    let url = 'processar_leitura.php?acao=historico&limite=50';
    if (condominioId) url += `&condominio_id=${condominioId}`;
    if (unidadeId) url += `&residencia_id=${unidadeId}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.historico) {
                renderizarHistorico(data.historico);
            } else {
                elements.historicoBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Nenhum registro encontrado</td></tr>';
            }
        })
        .catch(error => {
            console.error('❌ Erro ao carregar histórico:', error);
            elements.historicoBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Erro ao carregar dados</td></tr>';
        });
}

// Renderizar histórico na tabela
function renderizarHistorico(historico) {
    if (!elements.historicoBody) return;
    
    if (historico.length === 0) {
        elements.historicoBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    Nenhuma leitura encontrada
                </td>
            </tr>
        `;
        return;
    }
    
    elements.historicoBody.innerHTML = '';
    
    historico.forEach(leitura => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatarData(leitura.data_coleta)}</td>
            <td>${leitura.nome_condominio}</td>
            <td>${leitura.numero_residencia || 'N/A'}</td>
            <td><strong>${parseFloat(leitura.valor_kwh).toFixed(1)}</strong></td>
            <td>${leitura.observacao || '-'}</td>
        `;
        elements.historicoBody.appendChild(tr);
    });
}


// Filtrar histórico
function filtrarHistorico() {
    const condominioId = elements.filtroCondominio?.value || '';
    const unidadeId = elements.filtroUnidade?.value || '';
    
    // Verificar se há pelo menos um filtro selecionado
    if (!condominioId && !unidadeId) {
        showNotification('Selecione pelo menos um filtro', 'error');
        return;
    }
    
    showLoading('Filtrando dados...');
    
    // Chamar loadHistorico que já usa os valores dos selects
    loadHistorico();
    
    setTimeout(() => {
        hideLoading();
        showNotification('Filtros aplicados com sucesso!', 'success');
    }, 500);
}


// Exportar histórico
function exportarHistorico() {
    const condominioId = elements.filtroCondominio?.value || '';
    const unidadeId = elements.filtroUnidade?.value || '';
    
    let url = 'processar_leitura.php?acao=historico&limite=1000';
    if (condominioId) url += `&condominio_id=${condominioId}`;
    if (unidadeId) url += `&residencia_id=${unidadeId}`;
    
    showLoading('Gerando relatório...');
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success && data.historico) {
                gerarArquivoExportacao(data.historico);
            } else {
                showNotification('Nenhum dado para exportar', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('❌ Erro ao exportar:', error);
            showNotification('Erro ao gerar relatório', 'error');
        });
}

// Gerar arquivo de exportação
function gerarArquivoExportacao(historico) {
    const dataAtual = new Date().toLocaleDateString('pt-BR');
    let conteudo = `RELATÓRIO DE LEITURAS - INOVATECH\n`;
    conteudo += `Data: ${dataAtual}\n\n`;
    conteudo += `===========================================\n\n`;
    
    historico.forEach(leitura => {
        conteudo += `Data: ${formatarData(leitura.data_coleta)}\n`;
        conteudo += `Condomínio: ${leitura.nome_condominio}\n`;
        conteudo += `Unidade: ${leitura.numero_residencia}\n`;
        conteudo += `Consumo: ${parseFloat(leitura.valor_kwh).toFixed(1)} kWh\n`;
        conteudo += `Observações: ${leitura.observacao || 'Nenhuma'}\n`;
        conteudo += `Leitor: ${leitura.nome_leitor}\n`;
        conteudo += `-------------------------------------------\n`;
    });
    
    const blob = new Blob([conteudo], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `leituras-${dataAtual.replace(/\//g, '-')}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    showNotification('Relatório exportado com sucesso!', 'success');
}

// Validação em tempo real
function setupRealTimeValidation() {
    const campos = document.querySelectorAll('input[required], select[required]');
    campos.forEach(campo => {
        campo.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        campo.addEventListener('input', function() {
            if (this.classList.contains('error') && this.value.trim()) {
                this.classList.remove('error');
            }
        });
    });
}

// Funções utilitárias
function showLoading(message = 'Carregando...') {
    if (!elements.loadingOverlay) return;
    
    const loadingText = elements.loadingOverlay.querySelector('p');
    if (loadingText) {
        loadingText.textContent = message;
    }
    elements.loadingOverlay.style.display = 'flex';
}

function hideLoading() {
    if (elements.loadingOverlay) {
        elements.loadingOverlay.style.display = 'none';
    }
}

function showNotification(message, type) {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-triangle' : 
                 'fa-info-circle';
    
    notification.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                background: white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 10000;
                animation: slideInNotif 0.3s ease-out;
                max-width: 400px;
            }
            .notification-success { border-left: 4px solid #10b981; }
            .notification-error { border-left: 4px solid #ef4444; }
            .notification-info { border-left: 4px solid #3b82f6; }
            .notification i { font-size: 1.5rem; }
            .notification-success i { color: #10b981; }
            .notification-error i { color: #ef4444; }
            .notification-info i { color: #3b82f6; }
            .notification-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #6b7280;
                margin-left: auto;
            }
            @keyframes slideInNotif {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(400px)';
            notification.style.transition = 'all 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function formatarData(dataStr) {
    try {
        const data = new Date(dataStr);
        return data.toLocaleDateString('pt-BR') + ' ' + 
               data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    } catch (error) {
        return dataStr;
    }
}

// Tornar funções disponíveis globalmente
window.exportarHistorico = exportarHistorico;

console.log('✅ leitor.js carregado completamente!');

// ===========================
// LOGOUT COM CONFIRMAÇÃO
// ===========================
function logout() {
    const menu = document.getElementById('leitorMenu');
    const leitorBtn = document.getElementById('leitor_btn');
    
    // Fechar menu
    if (menu) menu.classList.remove('show');
    if (leitorBtn) leitorBtn.classList.remove('active');

    // Confirmação de logout
    if (confirm('Deseja realmente sair do sistema?')) {
        // Limpar dados de sessão
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('userRole');
        
        // Redirecionar para logout
        window.location.href = 'logout.php';
    }
}

// Tornar função disponível globalmente
window.logout = logout;

// ===========================
// MENU DROPDOWN LEITOR
// ===========================
const leitorBtn = document.getElementById('leitor_btn');
const leitorMenu = document.getElementById('leitorMenu');

if (leitorBtn && leitorMenu) {
    leitorBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        leitorMenu.classList.toggle('show');
        leitorBtn.classList.toggle('active');
    });

    document.addEventListener('click', function (e) {
        if (!leitorMenu.contains(e.target) && !leitorBtn.contains(e.target)) {
            leitorMenu.classList.remove('show');
            leitorBtn.classList.remove('active');
        }
    });
}

