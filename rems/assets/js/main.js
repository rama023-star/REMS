// REMS Main JavaScript File

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Confirm delete
function confirmDelete(message, callback) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        callback();
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Toggle element visibility
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.toggle('hidden');
    }
}

// Load content via AJAX
function loadContent(url, targetId) {
    const target = document.getElementById(targetId);
    if (!target) return;
    
    target.innerHTML = '<div class="flex justify-center py-8"><div class="spinner"></div></div>';
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            target.innerHTML = html;
        })
        .catch(error => {
            target.innerHTML = '<div class="text-center text-red-600 py-8">Error loading content</div>';
            console.error('Error:', error);
        });
}

// Submit form via AJAX
function submitFormAjax(formId, successCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Saved successfully');
            if (successCallback) successCallback(data);
        } else {
            showToast(data.message || 'Error saving data', 'error');
        }
    })
    .catch(error => {
        showToast('Error saving data', 'error');
        console.error('Error:', error);
    });
}

// Initialize tooltips
function initTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');
    elements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute bg-gray-900 text-white text-xs px-2 py-1 rounded -top-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-50';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.id = 'tooltip';
            this.style.position = 'relative';
            this.appendChild(tooltip);
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = document.getElementById('tooltip');
            if (tooltip) tooltip.remove();
        });
    });
}

// Initialize dropdowns
function initDropdowns() {
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.querySelector('.dropdown-menu')?.classList.add('hidden');
            }
        });
    });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
function initSearch(inputId, targetSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.addEventListener('input', debounce(function() {
        const query = this.value.toLowerCase();
        const items = document.querySelectorAll(targetSelector);
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    }, 300));
}

// Export functions for global use
window.REMS = {
    showToast,
    confirmDelete,
    formatCurrency,
    formatDate,
    toggleElement,
    loadContent,
    submitFormAjax,
    initTooltips,
    initDropdowns,
    initSearch,
    debounce
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    REMS.initTooltips();
    REMS.initDropdowns();
});
