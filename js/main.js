// Configuration
const API_BASE = 'api';
let currentLang = 'ru';

// Modal functions
function openModal() {
    document.getElementById('contactModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('contactModal').classList.add('hidden');
}

// Form submission
async function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Отправка...';
    
    try {
        const response = await fetch('api/contact.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            submitBtn.textContent = '✓ Отправлено!';
            submitBtn.classList.remove('bg-accent-blue');
            submitBtn.classList.add('bg-green-600');
            form.reset();
            setTimeout(() => closeModal(), 2000);
        } else {
            throw new Error(result.error || 'Ошибка отправки');
        }
    } catch (error) {
        console.error('Error:', error);
        submitBtn.textContent = '✗ Ошибка';
        submitBtn.classList.remove('bg-accent-blue');
        submitBtn.classList.add('bg-red-600');
    } finally {
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            submitBtn.classList.add('bg-accent-blue');
            submitBtn.classList.remove('bg-green-600', 'bg-red-600');
        }, 2000);
    }
}

// Load services
async function loadServices() {
    try {
        const response = await fetch(`${API_BASE}/services.php`);
        const services = await response.json();
        const grid = document.getElementById('services-grid');
        
        if (grid && services.length > 0) {
            grid.innerHTML = services.map(service => `
                <div class="glass-card p-6 hover:shadow-xl transition-shadow">
                    ${service.image_url ? `<img src="${service.image_url}" alt="${service.title_ru}" class="w-full h-48 object-cover rounded-lg mb-4">` : ''}
                    <h3 class="text-xl font-semibold mb-2">${service.title_ru}</h3>
                    <p class="text-neutral-secondary mb-4">${service.description_ru || ''}</p>
                    <a href="contacts.html" class="btn-primary inline-block">Заказать</a>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading services:', error);
    }
}

// Load categories
async function loadCategories() {
    try {
        const response = await fetch(`${API_BASE}/categories.php?type=service`);
        const categories = await response.json();
        const tabs = document.getElementById('category-tabs');
        
        if (tabs && categories.length > 0) {
            categories.forEach((cat, index) => {
                const btn = document.createElement('button');
                btn.className = `px-6 py-2 rounded-lg ${index === 0 ? 'bg-accent-blue text-white' : 'bg-neutral-border/30 hover:bg-neutral-border/50'}`;
                btn.textContent = cat.title_ru;
                btn.onclick = () => filterServices(cat.id);
                tabs.appendChild(btn);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load posts
async function loadPosts() {
    try {
        const response = await fetch(`${API_BASE}/posts.php`);
        const posts = await response.json();
        const grid = document.getElementById('posts-grid');
        
        if (grid && posts.length > 0) {
            grid.innerHTML = posts.map(post => `
                <article class="glass-card p-6 hover:shadow-xl transition-shadow">
                    ${post.image_url ? `<img src="${post.image_url}" alt="${post.title_ru}" class="w-full h-48 object-cover rounded-lg mb-4">` : ''}
                    <h3 class="text-xl font-semibold mb-2">${post.title_ru}</h3>
                    <p class="text-neutral-secondary mb-4">${post.excerpt_ru || ''}</p>
                    <a href="post.html?slug=${post.slug}" class="text-accent-blue hover:underline">Читать далее →</a>
                </article>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading posts:', error);
    }
}

// Load tech stack
async function loadTechStack() {
    const stack = [
        'JavaScript', 'TypeScript', 'React', 'Next.js', 'Node.js', 
        'PHP', 'MySQL', 'PostgreSQL', 'Docker', 'Git', 'Tailwind CSS',
        'Figma', 'Adobe XD', 'Linux', 'Nginx'
    ];
    
    const container = document.getElementById('tech-stack');
    if (container) {
        container.innerHTML = stack.map(tech => 
            `<span class="px-3 py-1 bg-accent-blue/10 text-accent-blue rounded-full text-sm">${tech}</span>`
        ).join('');
    }
}

// Load timeline
async function loadTimeline() {
    const timeline = [
        { year: '2020-н.в.', title: 'Senior Fullstack Developer', company: 'Tech Company' },
        { year: '2017-2020', title: 'Middle Developer', company: 'Web Studio' },
        { year: '2015-2017', title: 'Junior Developer', company: 'StartUp' }
    ];
    
    const container = document.getElementById('timeline');
    if (container) {
        container.innerHTML = timeline.map(item => `
            <div class="border-l-2 border-accent-blue pl-4 pb-4">
                <div class="text-sm text-accent-blue font-semibold">${item.year}</div>
                <div class="font-semibold">${item.title}</div>
                <div class="text-neutral-secondary">${item.company}</div>
            </div>
        `).join('');
    }
}

// Language toggle
function toggleLang() {
    currentLang = currentLang === 'ru' ? 'en' : 'ru';
    // Simple implementation - in production would load JSON translations
    alert('Language switch: ' + currentLang.toUpperCase() + ' (demo)');
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    // Auto-load based on page
    if (document.getElementById('services-grid') && window.location.pathname.includes('index.html')) {
        loadServices();
    }
});