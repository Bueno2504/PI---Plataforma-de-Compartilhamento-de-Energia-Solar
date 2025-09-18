// parte do menu mobile
document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobile_btn');
    const mobileMenu = document.getElementById('mobile_menu');
    const icon = mobileBtn.querySelector('i');

    mobileBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('active');
        icon.classList.toggle('fa-x'); 
    });

    // navegação suave (Inicio e Avaliações)
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            // fechar menu mobile depois do clique
            if (mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
                icon.classList.remove('fa-x');
            }
        });
    });

    // avaliacoes (expandir/recolher)
    const button = document.getElementById("verMais");
    const reviews = document.querySelectorAll(".depoimento-card");

    let expanded = false;
    if (button) {
        button.addEventListener("click", () => {
            if (!expanded) {
                reviews.forEach(review => {
                    review.classList.remove("hidden");
                    review.classList.add("show");
                });
                button.textContent = "Recolher avaliações";
                expanded = true;
            } else {
                reviews.forEach((review, index) => {
                    if (index === 0) {
                        review.classList.remove("hidden");
                        review.classList.add("show");
                    } else {
                        review.classList.remove("show");
                        review.classList.add("hidden");
                    }
                });
                button.textContent = "Ver mais avaliações";
                expanded = false;
                // rola de volta pro topo da seção de depoimentos
                document.querySelector("#depoimentos").scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
});

// botao que faz voltar pro topo
const backToTopBtn = document.getElementById('backToTopBtn');
window.addEventListener('scroll', () => {
    backToTopBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
});
backToTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
