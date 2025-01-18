// Loader
window.addEventListener('load', function() {
    const loader = document.querySelector('.loader-wrapper');
    loader.classList.add('fade-out');
    
    setTimeout(() => {
        loader.style.display = 'none';
    }, 500);
});

// Menu Mobile
const menuBtn = document.querySelector('.menu-btn');
const navLinks = document.querySelector('.nav-links');

menuBtn.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});

// Fechar menu ao clicar em um link
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('active');
    });
});

// Fechar menu ao clicar fora
document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-links') && !e.target.closest('.menu-btn')) {
        navLinks.classList.remove('active');
    }
});

// Botão Voltar ao Topo
const backToTopButton = document.getElementById('backToTop');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        backToTopButton.classList.add('visible');
    } else {
        backToTopButton.classList.remove('visible');
    }
});

backToTopButton.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Animações de Scroll
function handleScrollAnimations() {
    const elements = document.querySelectorAll('.scroll-animation');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementTop < windowHeight * 0.85) {
            element.classList.add('visible');
        }
    });
}

window.addEventListener('scroll', handleScrollAnimations);
window.addEventListener('load', handleScrollAnimations);

// Smooth Scroll para links internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Formulário de Contato com Validação
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validação básica
        const name = this.querySelector('#name').value.trim();
        const email = this.querySelector('#email').value.trim();
        const message = this.querySelector('#message').value.trim();
        
        if (!name || !email || !message) {
            showAlert('Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            showAlert('Veuillez entrer une adresse email valide', 'error');
            return;
        }
        
        // Simulação de envio
        const submitBtn = this.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
        
        setTimeout(() => {
            showAlert('Message envoyé avec succès!', 'success');
            this.reset();
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Envoyer';
        }, 1500);
    });
}

// Validação de email
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Sistema de Alertas
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <div class="alert-content">
            <span>${message}</span>
            <button class="alert-close">&times;</button>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.classList.add('show');
    }, 10);
    
    const closeBtn = alertDiv.querySelector('.alert-close');
    closeBtn.addEventListener('click', () => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            alertDiv.remove();
        }, 300);
    });
    
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            alertDiv.remove();
        }, 300);
    }, 5000);
}

// Adicionar classes de animação aos elementos
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar aos títulos
    document.querySelectorAll('h1, h2').forEach(element => {
        element.classList.add('scroll-animation');
    });
    
    // Adicionar aos cards de serviços
    document.querySelectorAll('.service-card, .feature-item').forEach(element => {
        element.classList.add('scroll-animation');
    });
    
    // Adicionar aos membros da equipe
    document.querySelectorAll('.team-member').forEach(element => {
        element.classList.add('scroll-animation');
    });
});

// Navbar Transparente com Scroll
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll <= 0) {
        navbar.classList.remove('scroll-up');
        return;
    }
    
    if (currentScroll > lastScroll && !navbar.classList.contains('scroll-down')) {
        navbar.classList.remove('scroll-up');
        navbar.classList.add('scroll-down');
    } else if (currentScroll < lastScroll && navbar.classList.contains('scroll-down')) {
        navbar.classList.remove('scroll-down');
        navbar.classList.add('scroll-up');
    }
    
    lastScroll = currentScroll;
});

// Prevenção de spam no formulário
let lastSubmitTime = 0;
const SUBMIT_DELAY = 5000; // 5 segundos

function canSubmit() {
    const now = Date.now();
    if (now - lastSubmitTime < SUBMIT_DELAY) {
        showAlert('Veuillez patienter quelques secondes avant de réessayer', 'error');
        return false;
    }
    lastSubmitTime = now;
    return true;
}

// Adicionar ao event listener do formulário
if (contactForm) {
    const originalSubmitHandler = contactForm.onsubmit;
    contactForm.onsubmit = function(e) {
        if (!canSubmit()) {
            e.preventDefault();
            return false;
        }
        return originalSubmitHandler ? originalSubmitHandler.call(this, e) : true;
    };
}
