document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const emailInput = document.getElementById('newsletterEmail');
            const email = emailInput.value.trim();
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            
            if (!email || !isValidEmail(email)) {
                showNewsletterModal('Hiba történt', 'Kérjük, adjon meg egy érvényes email címet!', false);
                return;
            }
            
            submitButton.disabled = true;
            submitButton.textContent = 'Küldés...';
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                
                const response = await fetch('newsletter.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNewsletterModal('Sikeres feliratkozás!', data.message, true);
                    emailInput.value = '';
                } else {
                    showNewsletterModal('Hiba történt', data.message, false);
                }
            } catch (error) {
                console.error('Hiba:', error);
                showNewsletterModal('Hiba történt', 'Hálózati hiba történt. Kérjük, próbálja újra később!', false);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const newsletterStatus = urlParams.get('newsletter');
    
    if (newsletterStatus === 'success') {
        showNewsletterModal('Sikeres feliratkozás!', 'Köszönjük, hogy feliratkozott hírlevelünkre!', true);
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (newsletterStatus === 'error') {
        showNewsletterModal('Hiba történt', 'Sikertelen feliratkozás. Kérjük, próbálja újra később!', false);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showNewsletterModal(title, message, isSuccess = true) {
    const modal = document.getElementById('newsletterModal');
    const modalTitle = document.getElementById('newsletterModalTitle');
    const modalMessage = document.getElementById('newsletterModalMessage');
    const modalIcon = document.getElementById('newsletterModalIcon');
    
    if (isSuccess) {
        modalTitle.textContent = 'Sikeres feliratkozás!';
        modalIcon.innerHTML = '<span class="material-symbols-outlined" style="color: #4CAF50;">check_circle</span>';
    } else {
        modalTitle.textContent = 'Hiba történt';
        modalIcon.innerHTML = '<span class="material-symbols-outlined" style="color: #f44336;">error</span>';
    }
    
    modalMessage.textContent = message;
    modal.style.display = 'block';
    
    if (isSuccess) {
        setTimeout(closeNewsletterModal, 5000);
    }
}

function closeNewsletterModal() {
    const modal = document.getElementById('newsletterModal');
    modal.style.display = 'none';
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('newsletterModal');
    if (event.target === modal) {
        closeNewsletterModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('newsletterModal');
        if (modal.style.display === 'block') {
            closeNewsletterModal();
        }
    }
});
