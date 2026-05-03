// Modal functions
function openModal() {
    document.getElementById('contactModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('contactModal').classList.add('hidden');
    document.body.style.overflow = '';
    resetForm();
}

// Reset form
function resetForm() {
    const form = document.getElementById('contactForm');
    if (form) {
        form.reset();
        const messageDiv = document.getElementById('formMessage');
        messageDiv.classList.add('hidden');
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Отправить заявку';
        submitBtn.classList.remove('bg-green-600', 'bg-red-600');
        submitBtn.classList.add('btn-primary');
    }
}

// Form submission
async function handleSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = document.getElementById('submitBtn');
    const messageDiv = document.getElementById('formMessage');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Validate hCaptcha
    const hcaptchaResponse = document.querySelector('[name="h-captcha-response"]');
    if (!hcaptchaResponse || !hcaptchaResponse.value) {
        showMessage('Пожалуйста, пройдите проверку hCaptcha', 'error');
        return;
    }
    
    // Disable button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Отправка...';
    messageDiv.classList.add('hidden');
    
    try {
        const response = await fetch('api/contact.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showMessage('✓ Заявка успешно отправлена! Я свяжусь с вами в ближайшее время.', 'success');
            form.reset();
            // Reset hCaptcha
            if (window.hcaptcha) {
                hcaptcha.reset();
            }
            setTimeout(() => closeModal(), 3000);
        } else {
            throw new Error(result.error || 'Ошибка при отправке');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('✗ ' + (error.message || 'Произошла ошибка. Попробуйте позже.'), 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Отправить заявку';
    }
}

// Show message
function showMessage(text, type) {
    const messageDiv = document.getElementById('formMessage');
    messageDiv.textContent = text;
    messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
    
    if (type === 'success') {
        messageDiv.classList.add('bg-green-100', 'text-green-800');
    } else {
        messageDiv.classList.add('bg-red-100', 'text-red-800');
    }
}

// Load services
async function loadServices() {
    try {
        const response = await fetch('api/services.php');
        const services = await response.json();
        const grid = document.getElementById('services-grid');
        
        if (grid && services.length > 0) {
            grid.innerHTML = services.slice(0, 3).map((service, index) => `
                <div class="service-card glass-card p-6 rounded-2xl transition duration-300 animate-slide-up" style="animation-delay: ${index * 0.1}s">
                    ${service.image_url ? `
                        <img src="${service.image_url}" alt="${service.title_ru}" class="w-full h-48 object-cover rounded-xl mb-4">
                    ` : ''}
                    <h3 class="text-xl font-bold mb-2 text-neutral-800">${service.title_ru}</h3>
                    <p class="text-neutral-600 mb-4">${service.description_ru || ''}</p>
                    <a href="contacts.html" class="btn-primary text-white px-6 py-2 rounded-lg inline-block font-medium">
                        Заказать
                    </a>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading services:', error);
    }
}

// Language toggle
function toggleLang() {
    alert('Переключение языка будет реализовано в следующей версии');
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('services-grid')) {
        loadServices();
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
});