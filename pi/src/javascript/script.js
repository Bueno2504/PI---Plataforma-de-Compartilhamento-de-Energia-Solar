// ===========================
// VARIÁVEIS GLOBAIS
// ===========================
let moradoresData = [];

// ===========================
// TEMA GLOBAL UNIFICADO
// ===========================
function loadTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light-mode';
    const isDarkMode = savedTheme === 'dark-mode';
    const btn = document.getElementById('theme-toggle');
    
    // Aplicar tema
    document.body.classList.toggle('dark-mode', isDarkMode);
    document.documentElement.classList.toggle('dark-mode', isDarkMode);
    
    // Atualizar ícone do botão
    if (btn) {
        btn.innerHTML = isDarkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        btn.setAttribute('aria-label', isDarkMode ? 'Ativar modo claro' : 'Ativar modo escuro');
    }
    
    console.log('✓ Tema carregado:', savedTheme);
}

function toggleTheme() {
    const isCurrentlyDark = document.body.classList.contains('dark-mode');
    const newTheme = isCurrentlyDark ? 'light-mode' : 'dark-mode';
    const willBeDark = !isCurrentlyDark;
    
    // Aplicar tema
    document.body.classList.toggle('dark-mode', willBeDark);
    document.documentElement.classList.toggle('dark-mode', willBeDark);
    
    // Salvar preferência
    localStorage.setItem('theme', newTheme);
    
    // Atualizar botão
    const btn = document.getElementById('theme-toggle');
    if (btn) {
        btn.innerHTML = willBeDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        btn.setAttribute('aria-label', willBeDark ? 'Ativar modo claro' : 'Ativar modo escuro');
    }
    
    console.log('✓ Tema alterado para:', newTheme);
}

// Carregar tema imediatamente
loadTheme();

// Inicializar quando DOM carregar
document.addEventListener('DOMContentLoaded', () => {
    // Garantir que tema está aplicado
    loadTheme();
    
    // Event listener no botão
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.replaceWith(themeToggleBtn.cloneNode(true));
        const newBtn = document.getElementById('theme-toggle');
        
        newBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleTheme();
        });
    }

    // ===========================
    // Back to Top
    // ===========================
    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ===========================
    // FAQ Toggle
    // ===========================
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const answer = btn.nextElementSibling;
            const faqItem = btn.parentElement;
            const icon = btn.querySelector('.faq-icon');

            answer.classList.toggle('active');
            faqItem.classList.toggle('active');

            if (icon) {
                if (answer.classList.contains('active')) {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                } else {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                }
            }
        });
    });

    // ===========================
    // Animações (Intersection Observer)
    // ===========================
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');

                if (entry.target.classList.contains('stat-item')) {
                    const statNumberEl = entry.target.querySelector('.stat-number');
                    if (statNumberEl && !statNumberEl.classList.contains('animated')) {
                        animateStatNumber(statNumberEl);
                        statNumberEl.classList.add('animated');
                    }
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('.stat-item, .step, .testimonial, .feature').forEach(el => observer.observe(el));

    // ===========================
    // Scroll suave
    // ===========================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ===========================
    // Login
    // ===========================
    const form = document.getElementById('loginForm');
    const container = document.getElementById('loginContainer');

    if (form && container) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const username = document.getElementById('usuario').value;
            const password = document.getElementById('senha').value;

            if (username && password) {
                container.classList.add('login-active');

                const successMsg = document.createElement('div');
                successMsg.style.cssText = 'color: #10b981; text-align: center; margin-top: 10px; font-weight: 500;';
                successMsg.textContent = '✓ Login realizado com sucesso!';
                form.appendChild(successMsg);

                setTimeout(() => {
                    window.location.href = 'admin.php';
                }, 1500);
            } else {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.style.cssText = 'color: #ef4444; text-align: center; margin-top: 10px; font-weight: 500;';
                errorMsg.textContent = '✗ Por favor, preencha todos os campos!';

                const existingError = form.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }

                form.appendChild(errorMsg);

                setTimeout(() => {
                    errorMsg.remove();
                }, 3000);
            }
        });
    }

    // ===========================
    // Admin Dashboard - Carregar dados do banco
    // ===========================
    const moradoresBody = document.getElementById('moradoresBody');
    if (moradoresBody) {
        carregarMoradores();

        const searchInput = document.getElementById('searchMorador');
        if (searchInput) {
            searchInput.addEventListener('input', buscarMorador);
        }
    }

    // ===========================
    // INICIALIZAR FUNÇÕES DO CADASTRO DE CONDOMÍNIO
    // ===========================
    initMasks();
    initCepSearch();
    initFormValidation();

    // ===========================
    // MENU DROPDOWN ADMIN
    // ===========================
    const adminBtn = document.getElementById('admin_btn');
    const adminMenu = document.getElementById('adminMenu');

    if (adminBtn && adminMenu) {
        adminBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            adminMenu.classList.toggle('show');
            adminBtn.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!adminMenu.contains(e.target) && !adminBtn.contains(e.target)) {
                adminMenu.classList.remove('show');
                adminBtn.classList.remove('active');
            }
        });
    }
});

// ===========================
// Toggle password visibility
// ===========================
window.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.querySelector('.toggle-password');
    const senhaInput = document.getElementById('senha');

    if (togglePassword && senhaInput) {
        togglePassword.addEventListener('click', function (e) {
            e.preventDefault();

            const icon = this.querySelector('i');

            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Ocultar senha');
            } else {
                senhaInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Mostrar senha');
            }
        });
    }
});

// ===========================
// Menu mobile
// ===========================
document.addEventListener('DOMContentLoaded', function () {
    const mobileBtn = document.getElementById('mobile_btn');
    const mobileMenu = document.getElementById('mobile_menu');

    if (mobileBtn && mobileMenu) {
        const icon = mobileBtn.querySelector('i');
        mobileBtn.addEventListener('click', function () {
            mobileMenu.classList.toggle('active');
            if (icon) {
                icon.classList.toggle('fa-x');
            }
        });
    }
});

// ===========================
// CADASTRO DE CONDOMÍNIO - MÁSCARAS
// ===========================

function applyCepMask(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{5})(\d)/, '$1-$2')
        .substring(0, 9);
}

function applyCpfMask(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})/, '$1-$2')
        .substring(0, 14);
}

function applyCnpjMask(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{2})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2')
        .substring(0, 18);
}

function applyPhoneMask(value) {
    value = value.replace(/\D/g, '');
    if (value.length <= 10) {
        return value
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .substring(0, 14);
    } else {
        return value
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .substring(0, 15);
    }
}

function initMasks() {
    document.querySelectorAll('input[data-mask="cep"]').forEach(input => {
        input.addEventListener('input', (e) => {
            e.target.value = applyCepMask(e.target.value);
        });
    });

    document.querySelectorAll('input[data-mask="cpf"]').forEach(input => {
        input.addEventListener('input', (e) => {
            e.target.value = applyCpfMask(e.target.value);
        });
    });

    document.querySelectorAll('input[data-mask="cnpj"]').forEach(input => {
        input.addEventListener('input', (e) => {
            e.target.value = applyCnpjMask(e.target.value);
        });
    });

    document.querySelectorAll('input[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', (e) => {
            e.target.value = applyPhoneMask(e.target.value);
        });
    });
}

// ===========================
// BUSCA DE CEP - API BRASIL API
// ===========================

function initCepSearch() {
    const cepInput = document.getElementById('cep');
    if (!cepInput) {
        return;
    }

    cepInput.addEventListener('blur', function () {
        let cep = this.value.replace(/\D/g, '');

        if (cep.length !== 8) {
            return;
        }

        const statusElement = document.getElementById('cep-status');
        const form = document.getElementById('condoForm');
        const ruaInput = document.getElementById('rua');
        const bairroInput = document.getElementById('bairro');
        const cidadeInput = document.getElementById('cidade');
        const estadoInput = document.getElementById('estado');

        if (statusElement) {
            statusElement.textContent = '🔍 Buscando CEP...';
            statusElement.style.color = '#667eea';
        }

        if (form) {
            form.classList.add('loading');
        }

        if (ruaInput) ruaInput.value = '';
        if (bairroInput) bairroInput.value = '';
        if (cidadeInput) cidadeInput.value = '';
        if (estadoInput) estadoInput.value = '';

        fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('CEP não encontrado!');
                }
                return response.json();
            })
            .then(data => {
                if (ruaInput) {
                    ruaInput.value = data.street || '';
                }

                if (bairroInput) {
                    bairroInput.value = data.neighborhood || '';
                }

                if (cidadeInput) {
                    cidadeInput.value = data.city || '';
                }

                if (estadoInput) {
                    const opcoes = estadoInput.options;
                    for (let i = 0; i < opcoes.length; i++) {
                        if (opcoes[i].value === data.state) {
                            estadoInput.selectedIndex = i;
                            break;
                        }
                    }
                }

                const numeroInput = document.getElementById('numero');
                if (numeroInput) {
                    numeroInput.focus();
                }

                if (statusElement) {
                    statusElement.textContent = '✅ CEP encontrado!';
                    statusElement.style.color = '#10b981';
                    setTimeout(() => {
                        statusElement.textContent = '';
                    }, 3000);
                }
            })
            .catch(error => {
                if (statusElement) {
                    statusElement.textContent = '❌ CEP não encontrado!';
                    statusElement.style.color = '#ef4444';
                }

                alert('CEP não encontrado. Por favor, verifique e tente novamente.');
            })
            .finally(() => {
                if (form) {
                    form.classList.remove('loading');
                }
            });
    });
}

// ===========================
// VALIDAÇÃO DO FORMULÁRIO
// ===========================

function initFormValidation() {
    const form = document.getElementById('condoForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {
        const ruaInput = document.getElementById('rua');
        const bairroInput = document.getElementById('bairro');
        const cidadeInput = document.getElementById('cidade');
        const estadoInput = document.getElementById('estado');

        if (ruaInput && bairroInput && cidadeInput && estadoInput) {
            const rua = ruaInput.value.trim();
            const bairro = bairroInput.value.trim();
            const cidade = cidadeInput.value.trim();
            const estado = estadoInput.value.trim();

            if (!rua || !bairro || !cidade || !estado) {
                e.preventDefault();
                alert('Por favor, busque um CEP válido antes de cadastrar!');
                return false;
            }
        }
    });
}

// ===========================
// ADMIN DASHBOARD - CARREGAR MORADORES
// ===========================

async function carregarMoradores() {
    try {
        const response = await fetch('get_moradores.php');
        const data = await response.json();
        
        console.log('📊 Dados recebidos:', data); // DEBUG
        
        if (data.success) {
            moradoresData = data.moradores;
            renderizarMoradores(moradoresData);
            atualizarDashboard(data.dadosGerais);
        } else {
            console.error('❌ Erro ao carregar moradores:', data.error);
            mostrarErro('Erro ao carregar dados: ' + data.error);
        }
    } catch (error) {
        console.error('❌ Erro na requisição:', error);
        mostrarErro('Erro ao conectar com o servidor');
    }
}

// ===========================
// RENDERIZAR TABELA DE MORADORES
// ===========================

function renderizarMoradores(moradores) {
    const tbody = document.getElementById('moradoresBody');
    if (!tbody) return;
    
    if (!moradores || moradores.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                    Nenhuma residência encontrada
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = '';
    
    moradores.forEach(morador => {
        const tr = document.createElement('tr');
        const numero = morador.numero_residencia || 'S/N';
        const consumo = parseFloat(morador.consumo_kwh || 0);
        const energiaRecebida = parseFloat(morador.energia_recebida_kwh || 0);
        const saldo = parseFloat(morador.saldo_kwh || 0);
        const status = morador.status || 'CREDITO';
        
        const saldoClass = saldo >= 0 ? 'saldo-positivo' : 'saldo-negativo';
        const saldoSinal = saldo >= 0 ? '+' : '';
        const statusClass = status.toLowerCase();
        const statusIcon = status === 'CREDITO' ? 
            '<i class="fas fa-check-circle"></i> CRÉDITO' : 
            '<i class="fas fa-exclamation-circle"></i> DÉBITO';
        
        tr.innerHTML = `
            <td><strong>${numero}</strong></td>
            <td>${formatarNumero(consumo)} kWh</td>
            <td>${formatarNumero(energiaRecebida)} kWh</td>
            <td class="${saldoClass}">
                <strong>${saldoSinal}${formatarNumero(saldo)} kWh</strong>
            </td>
            <td class="text-center">
                <span class="status-badge status-${statusClass}">
                    ${statusIcon}
                </span>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

// ===========================
// ATUALIZAR DASHBOARD
// ===========================

function atualizarDashboard(dadosGerais) {
    if (!dadosGerais) return;
    
    const energiaGerada = parseFloat(dadosGerais.energia_gerada || 0);
    const consumoTotal = parseFloat(dadosGerais.consumo_total || 0);
    const creditos = parseFloat(dadosGerais.creditos || 0);
    const saldo = energiaGerada - consumoTotal;
    const economia = saldo * 0.80; // R$ 0,80 por kWh
    
    const energiaGeradaEl = document.getElementById('energiaGerada');
    const consumoTotalEl = document.getElementById('consumoTotal');
    const creditosEl = document.getElementById('creditos');
    const economiaEl = document.getElementById('economia');
    
    if (energiaGeradaEl) {
        energiaGeradaEl.textContent = formatarNumero(energiaGerada);
    }
    
    if (consumoTotalEl) {
        consumoTotalEl.textContent = formatarNumero(consumoTotal);
    }
    
    if (creditosEl) {
        creditosEl.textContent = formatarNumero(creditos);
    }
    
    if (economiaEl) {
        economiaEl.textContent = formatarMoeda(Math.abs(economia));
    }
}

// ===========================
// BUSCAR MORADOR (FILTRO)
// ===========================

function buscarMorador() {
    const searchInput = document.getElementById('searchMorador');
    if (!searchInput) return;

    const termoBusca = searchInput.value.toLowerCase();
    const moradoresFiltrados = moradoresData.filter(morador =>
        (morador.numero_residencia || '').toLowerCase().includes(termoBusca) ||
        (morador.nome_condominio || '').toLowerCase().includes(termoBusca)
    );
    renderizarMoradores(moradoresFiltrados);
}

// ===========================
// EXPORTAR RELATÓRIO
// ===========================

function exportarRelatorio() {
    const dataAtual = new Date().toLocaleDateString('pt-BR');
    let conteudo = `RELATÓRIO INOVATECH - ${dataAtual}\n\n`;
    
    conteudo += '=== RESUMO GERAL ===\n';
    conteudo += `Energia Gerada: ${document.getElementById('energiaGerada').textContent} kWh\n`;
    conteudo += `Consumo Total: ${document.getElementById('consumoTotal').textContent} kWh\n`;
    conteudo += `Créditos: ${document.getElementById('creditos').textContent} kWh\n`;
    conteudo += `Economia: ${document.getElementById('economia').textContent}\n\n`;
    
    conteudo += '=== RESIDÊNCIAS ===\n';
    moradoresData.forEach(morador => {
        conteudo += `\nCasa ${morador.numero_residencia || 'S/N'}\n`;
        conteudo += `  Condomínio: ${morador.nome_condominio}\n`;
        conteudo += `  Consumo: ${morador.consumo_kwh} kWh\n`;
        conteudo += `  Energia Recebida: ${morador.energia_recebida_kwh} kWh\n`;
        conteudo += `  Saldo: ${morador.saldo_kwh} kWh\n`;
        conteudo += `  Status: ${morador.status}\n`;
    });
    
    const blob = new Blob([conteudo], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `relatorio-inovatech-${dataAtual.replace(/\//g, '-')}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    alert('Relatório exportado com sucesso!');
}

// ===========================
// LOGOUT
// ===========================

function logout() {
    const menu = document.getElementById('adminMenu');
    const adminBtn = document.getElementById('admin_btn');
    if (menu) menu.classList.remove('show');
    if (adminBtn) adminBtn.classList.remove('active');

    if (confirm('Deseja realmente sair do sistema?')) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('userRole');
        window.location.href = 'logout.php';
    }
}

// ===========================
// FUNÇÕES AUXILIARES
// ===========================

function formatarNumero(numero) {
    return Math.round(numero).toLocaleString('pt-BR');
}

function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
}

function mostrarErro(mensagem) {
    const tbody = document.getElementById('moradoresBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i> ${mensagem}
                </td>
            </tr>
        `;
    }
}

// ===========================
// Carrossel de Depoimentos
// ===========================
const testimonialsGrid = document.querySelector('.testimonials-grid');
const prevButton = document.querySelector('.carousel-control.prev');
const nextButton = document.querySelector('.carousel-control.next');

if (testimonialsGrid && prevButton && nextButton) {
    const checkScrollButtons = () => {
        if (testimonialsGrid.scrollWidth <= testimonialsGrid.clientWidth) {
            prevButton.style.display = 'none';
            nextButton.style.display = 'none';
        } else {
            prevButton.style.display = 'flex';
            nextButton.style.display = 'flex';
        }

        prevButton.disabled = testimonialsGrid.scrollLeft === 0;
        nextButton.disabled = testimonialsGrid.scrollLeft + testimonialsGrid.clientWidth >= testimonialsGrid.scrollWidth - 1;
    };

    checkScrollButtons();

    const resizeObserver = new ResizeObserver(checkScrollButtons);
    resizeObserver.observe(testimonialsGrid);

    testimonialsGrid.addEventListener('scroll', checkScrollButtons);

    prevButton.addEventListener('click', () => {
        const scrollAmount = testimonialsGrid.clientWidth;
        testimonialsGrid.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    });

    nextButton.addEventListener('click', () => {
        const scrollAmount = testimonialsGrid.clientWidth;
        testimonialsGrid.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    });
}

// ===========================
// Animação Contadores (Stats)
// ===========================
function animateStatNumber(element) {
    const finalValue = parseInt(element.getAttribute('data-value'), 10);
    const suffix = element.textContent.replace(/[\d,.]/g, '');
    
    if (isNaN(finalValue)) return;

    let currentValue = 0;
    const duration = 1200;
    const steps = 50;
    const increment = finalValue / steps;
    const stepTime = duration / steps;

    const interval = setInterval(() => {
        currentValue += increment;

        if (currentValue >= finalValue) {
            currentValue = finalValue;
            clearInterval(interval);
        }

        element.textContent = Math.round(currentValue) + suffix;
    }, stepTime);
}

// ============================================
// ANIMAÇÃO DE LOADING INICIAL
// ============================================
window.addEventListener('load', function() {
    // Remove o loader após a página carregar
    setTimeout(() => {
        const loader = document.querySelector('.page-loader');
        if (loader) {
            loader.style.display = 'none';
        }
        
        // Ativa animações de entrada em cascata
        activateStaggeredAnimations();
    }, 1500);
});

// Ativa animações com delays
function activateStaggeredAnimations() {
    const elements = document.querySelectorAll('[data-delay]');
    elements.forEach(el => {
        el.style.animationPlayState = 'running';
    });
}

// ============================================
// MENU DO ADMINISTRADOR
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const adminBtn = document.getElementById('admin_btn');
    const adminMenu = document.getElementById('adminMenu');
    
    if (adminBtn && adminMenu) {
        adminBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            adminMenu.classList.toggle('active');
        });
        
        // Fecha o menu ao clicar fora
        document.addEventListener('click', function(e) {
            if (!adminMenu.contains(e.target) && !adminBtn.contains(e.target)) {
                adminMenu.classList.remove('active');
            }
        });
    }
});

// ============================================
// TEMA CLARO/ESCURO
// ============================================
const themeToggle = document.getElementById('theme-toggle');
const currentTheme = localStorage.getItem('theme') || 'light';

// Aplica tema salvo
document.documentElement.setAttribute('data-theme', currentTheme);
updateThemeIcon(currentTheme);

if (themeToggle) {
    themeToggle.addEventListener('click', function() {
        const theme = document.documentElement.getAttribute('data-theme');
        const newTheme = theme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });
}

function updateThemeIcon(theme) {
    if (themeToggle) {
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }
    }
}

// ============================================
// LOGOUT
// ============================================
function logout() {
    if (confirm('Deseja realmente sair?')) {
        window.location.href = 'logout.php';
    }
}

// ============================================
// BUSCAR DADOS DOS MORADORES (ADMIN)
// ============================================
function carregarDadosMoradores() {
    fetch('get_moradores.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar cards do dashboard
                atualizarCardsAdmin(data.dadosGerais);
                
                // Atualizar tabela de moradores
                atualizarTabelaMoradores(data.moradores);
            } else {
                console.error('Erro ao carregar dados:', data.error);
                mostrarErroTabela();
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            mostrarErroTabela();
        });
}

function atualizarCardsAdmin(dados) {
    // Energia Gerada
    const energiaEl = document.getElementById('energiaGerada');
    if (energiaEl) {
        animateValue(energiaEl, 0, dados.energia_gerada, 1500);
    }
    
    // Consumo Total
    const consumoEl = document.getElementById('consumoTotal');
    if (consumoEl) {
        animateValue(consumoEl, 0, dados.consumo_total, 1500);
    }
    
    // Créditos
    const creditosEl = document.getElementById('creditos');
    if (creditosEl) {
        animateValue(creditosEl, 0, dados.creditos, 1500);
    }
    
    // Economia (simulado)
    const economiaEl = document.getElementById('economia');
    if (economiaEl) {
        const economia = (dados.energia_gerada * 0.65).toFixed(2);
        animateValue(economiaEl, 0, economia, 1500, 'R$ ');
    }
}

function animateValue(element, start, end, duration, prefix = '') {
    const startTime = performance.now();
    const endValue = parseFloat(end) || 0;
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = start + (endValue - start) * easeOutQuad(progress);
        element.textContent = prefix + current.toFixed(1);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = prefix + endValue.toFixed(1);
        }
    }
    
    requestAnimationFrame(update);
}

function easeOutQuad(t) {
    return t * (2 - t);
}

function atualizarTabelaMoradores(moradores) {
    const tbody = document.getElementById('moradoresBody');
    if (!tbody) return;
    
    if (moradores.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-secondary);">
                    <i class="fas fa-inbox"></i><br>
                    Nenhum morador encontrado
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = moradores.map(m => {
        const statusClass = m.status === 'CREDITO' ? 'status-credito' : 'status-debito';
        const statusIcon = m.status === 'CREDITO' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        return `
            <tr>
                <td>
                    <strong>${m.numero_residencia}</strong>
                    <br>
                    <small style="color: var(--text-secondary);">${m.nome_condominio}</small>
                </td>
                <td>${parseFloat(m.consumo_kwh).toFixed(2)}</td>
                <td>${parseFloat(m.energia_recebida_kwh || 0).toFixed(2)}</td>
                <td class="${m.saldo_kwh >= 0 ? 'text-success' : 'text-danger'}">
                    ${parseFloat(m.saldo_kwh || 0).toFixed(2)}
                </td>
                <td>
                    <span class="status-badge ${statusClass}">
                        <i class="fas ${statusIcon}"></i>
                        ${m.status}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
}

function mostrarErroTabela() {
    const tbody = document.getElementById('moradoresBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: var(--danger-color);">
                    <i class="fas fa-exclamation-triangle"></i><br>
                    Erro ao carregar dados. Tente novamente.
                </td>
            </tr>
        `;
    }
}

// ============================================
// BUSCAR MORADOR (FILTRO)
// ============================================
function buscarMorador() {
    const input = document.getElementById('searchMorador');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('moradoresTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const firstCell = rows[i].getElementsByTagName('td')[0];
        if (firstCell) {
            const textValue = firstCell.textContent || firstCell.innerText;
            rows[i].style.display = textValue.toLowerCase().includes(filter) ? '' : 'none';
        }
    }
}

// ============================================
// VER DETALHES DO CONDOMÍNIO
// ============================================
function verDetalhes(idCondominio) {
    // Modal ou redirecionamento para página de detalhes
    console.log('Ver detalhes do condomínio:', idCondominio);
    
    // Exemplo: abrir modal (implementar HTML do modal)
    alert(`Detalhes do condomínio ID: ${idCondominio}\n\nFuncionalidade em desenvolvimento.`);
    
}

// ============================================
// INICIALIZAÇÃO
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Carregar dados dos moradores se estiver na página admin
    if (document.getElementById('moradoresBody')) {
        carregarDadosMoradores();
    }
    
    // Adicionar estilos CSS inline para badges
    const style = document.createElement('style');
    style.textContent = `
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-credito {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-debito {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .text-success {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .text-danger {
            color: var(--danger-color);
            font-weight: 600;
        }
    `;
    document.head.appendChild(style);
});

// ============================================
// SCROLL SUAVE PARA SEÇÕES
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ============================================
// ANIMAÇÃO AO SCROLL
// ============================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observa elementos que devem animar ao aparecer
document.querySelectorAll('.fade-in-up').forEach(el => {
    observer.observe(el);
});