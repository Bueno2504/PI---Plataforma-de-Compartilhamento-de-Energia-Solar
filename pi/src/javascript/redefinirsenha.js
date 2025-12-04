// ===========================
// TEMA GLOBAL SINCRONIZADO
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
}

// Carregar tema imediatamente
loadTheme();

// ===========================
// INICIALIZAÇÃO
// ===========================
document.addEventListener('DOMContentLoaded', () => {
    // Garantir que tema está aplicado
    loadTheme();
    
    // Event listener no botão de tema
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleTheme();
        });
    }

    // ===========================
    // Toggle Password Visibility
    // ===========================
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // ===========================
    // Password Validation
    // ===========================
    const novaSenhaInput = document.getElementById('nova_senha');
    const confirmaSenhaInput = document.getElementById('confirma_senha');
    const submitBtn = document.getElementById('submitBtn');

    // Definir requisitos da senha
    const requirements = {
        length: { element: document.getElementById('req-length'), test: (pwd) => pwd.length >= 8 },
        uppercase: { element: document.getElementById('req-uppercase'), test: (pwd) => /[A-Z]/.test(pwd) },
        lowercase: { element: document.getElementById('req-lowercase'), test: (pwd) => /[a-z]/.test(pwd) },
        number: { element: document.getElementById('req-number'), test: (pwd) => /[0-9]/.test(pwd) },
        match: { element: document.getElementById('req-match'), test: (pwd) => pwd === confirmaSenhaInput.value && pwd.length > 0 }
    };

    // Função de validação da senha
    function validatePassword() {
        const password = novaSenhaInput.value;
        let allValid = password.length > 0;

        // Verificar cada requisito
        for (const [key, req] of Object.entries(requirements)) {
            const isValid = req.test(password);
            
            // Adicionar/remover classes de validação
            req.element.classList.toggle('valid', isValid);
            req.element.classList.toggle('invalid', !isValid && password.length > 0);
            
            // Atualizar ícone
            const icon = req.element.querySelector('i');
            if (isValid) {
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
            } else {
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            }

            // Se algum requisito não for válido, marcar allValid como false
            if (!isValid) allValid = false;
        }

        // Habilitar/desabilitar botão de submit
        submitBtn.disabled = !allValid;
    }

    // Event listeners para validação em tempo real
    novaSenhaInput.addEventListener('input', validatePassword);
    confirmaSenhaInput.addEventListener('input', validatePassword);

    // ===========================
    // Form Submission com AJAX
    // ===========================
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const novaSenha = novaSenhaInput.value;
        const confirmaSenha = confirmaSenhaInput.value;

        // Verificar se as senhas coincidem
        if (novaSenha !== confirmaSenha) {
            showMessage('As senhas não coincidem!', 'error');
            return;
        }

        // Validar requisitos de segurança
        if (novaSenha.length < 8 || 
            !/[A-Z]/.test(novaSenha) || 
            !/[a-z]/.test(novaSenha) || 
            !/[0-9]/.test(novaSenha)) {
            showMessage('A senha não atende aos requisitos de segurança!', 'error');
            return;
        }

        // Enviar para o PHP via AJAX
        const formData = new FormData();
        formData.append('senha_nova', novaSenha);

        fetch('redefinirsenha.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log('Resposta do servidor:', data);
            
            if(data.includes('Senha alterada com sucesso')) {
                showMessage('Senha redefinida com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = 'admin.php';
                }, 2000);
            } else {
                showMessage('Erro ao redefinir senha!', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showMessage('Erro ao conectar com o servidor!', 'error');
        });
    });
});

// ===========================
// Função para exibir mensagens
// ===========================
function showMessage(message, type) {
    const messageBox = document.getElementById('messageBox');
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    messageBox.innerHTML = `
        <div class="message-box ${type}">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
    `;

    setTimeout(() => {
        messageBox.innerHTML = '';
    }, 5000);
}