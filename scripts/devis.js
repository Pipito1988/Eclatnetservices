// scripts/devis.js

// Configuração dos preços base (em euros)
const PRICES = {
    immeuble: {
        basePrice: 2.5, // por m²
        frequency: {
            quotidien: 1,
            hebdomadaire: 0.8,
            mensuel: 0.6
        },
        services: {
            vitrerie: 150,
            sols: 200,
            sanitaires: 100,
            desinfection: 180
        }
    },
    bureau: {
        basePrice: 3, // por m²
        frequency: {
            quotidien: 1,
            hebdomadaire: 0.85,
            mensuel: 0.7
        },
        services: {
            vitrerie: 200,
            sols: 250,
            sanitaires: 150,
            desinfection: 200
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('devisForm');
    const steps = document.querySelectorAll('.form-step');
    const modal = document.getElementById('resultModal');
    
    // Navegação entre etapas
    document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', function() {
            const currentStep = this.closest('.form-step');
            if (validateStep(currentStep)) {
                currentStep.classList.remove('active');
                currentStep.nextElementSibling.classList.add('active');
            }
        });
    });

    document.querySelectorAll('.prev-step').forEach(button => {
        button.addEventListener('click', function() {
            const currentStep = this.closest('.form-step');
            currentStep.classList.remove('active');
            currentStep.previousElementSibling.classList.add('active');
        });
    });

    // Validação do formulário
    function validateStep(step) {
        const inputs = step.querySelectorAll('input[required], select[required]');
        let valid = true;

        inputs.forEach(input => {
            if (!input.value) {
                valid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });

        if (!valid) {
            showAlert('Veuillez remplir tous les champs obligatoires', 'error');
        }

        return valid;
    }

    // Cálculo do orçamento
    function calculateEstimate(formData) {
        const serviceType = formData.get('serviceType');
        const surface = parseFloat(formData.get('surface'));
        const frequency = formData.get('frequency');
        const etages = parseInt(formData.get('etages'));
        
        let total = 0;
        
        // Cálculo base por m²
        const basePrice = PRICES[serviceType].basePrice * surface;
        
        // Multiplicador de frequência
        const frequencyMultiplier = PRICES[serviceType].frequency[frequency];
        
        // Adicional por andar (5% por andar adicional)
        const etageMultiplier = 1 + ((etages - 1) * 0.05);
        
        // Serviços adicionais
        const selectedServices = formData.getAll('services[]');
        const servicesTotal = selectedServices.reduce((acc, service) => {
            return acc + PRICES[serviceType].services[service];
        }, 0);
        
        // Cálculo total
        total = (basePrice * frequencyMultiplier * etageMultiplier) + servicesTotal;
        
        // Adiciona margem de variação (±15%)
        const minTotal = total * 0.85;
        const maxTotal = total * 1.15;
        
        return {
            min: Math.round(minTotal),
            max: Math.round(maxTotal)
        };
    }

    // Função para mostrar alertas
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const formMessage = document.getElementById('formMessage');
        formMessage.innerHTML = '';
        formMessage.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Envio do formulário
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!validateStep(document.querySelector('.form-step.active'))) {
            return;
        }

        const formData = new FormData(this);
        const estimate = calculateEstimate(formData);

        // Preparar dados para o EmailJS
        const templateParams = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            address: formData.get('address'),
            serviceType: formData.get('serviceType'),
            surface: formData.get('surface'),
            frequency: formData.get('frequency'),
            etages: formData.get('etages'),
            services: formData.getAll('services[]').join(', '),
            estimateMin: estimate.min,
            estimateMax: estimate.max
        };

        try {
            // Desabilitar botão durante o envio
            const submitBtn = this.querySelector('.submit-devis');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';

            // Enviar email usando EmailJS
            await emailjs.send(
                'service_0ey1awo', // Substitua pelo seu Service ID do EmailJS
                'template_bmv2wt6', // Substitua pelo seu Template ID do EmailJS
                templateParams
            );

            // Mostrar sucesso
            modal.style.display = 'block';
            form.reset();
            document.querySelector('.form-step.active').classList.remove('active');
            document.querySelector('.form-step').classList.add('active');

        } catch (error) {
            console.error('EmailJS Error:', error);
            showAlert('Une erreur est survenue. Veuillez réessayer.', 'error');
        } finally {
            // Restaurar botão
            const submitBtn = this.querySelector('.submit-devis');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>Obtenir le devis</span><i class="fas fa-calculator"></i>';
        }
    });

    // Fechar modal
    document.querySelector('.close').addEventListener('click', () => {
        modal.style.display = 'none';
    });

    document.querySelector('.modal-close').addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target == modal) {
            modal.style.display = 'none';
        }
    });
});
