<!-- FOOTER -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
<footer>
    <div class="container">
        <!-- INFORMÁCIÓS OSZLOPOK -->
        <div class="footer-columns">
            <!-- HOTEL INFÓ -->
            <div class="footer-col">
                <h3>HOTEL SZALKA</h3>
                <div class="footer-stars">★★★★</div>
                <p class="footer-tagline">Mátészalka legújabb szállodája</p>
                <div class="footer-contact">
                    <div class="contact-item">
                        <span class="material-symbols-outlined">location_on</span>
                        <span>4700 Mátészalka, Nagykárolyi út 67</span>
                    </div>
                    <div class="contact-item">
                        <span class="material-symbols-outlined">call</span>
                        <span>+36 70 123 4567</span>
                    </div>
                    <div class="contact-item">
                        <span class="material-symbols-outlined">mail</span>
                        <span>szalka@hotel.com</span>
                    </div>
                </div>
            </div>

            <!-- NYITVATARTÁS ÉS HÍRLEVÉL -->
            <div class="footer-col">
                <h4>NYITVATARTÁS</h4>
                <div class="footer-opening-hours">
                    <div class="footer-hours-item">
                        <span class="footer-hours-day">Hétfő - Csütörtök:</span>
                        <span class="footer-hours-time">08:00 - 21:00</span>
                    </div>
                    <div class="footer-hours-item">
                        <span class="footer-hours-day">Péntek - Szombat:</span>
                        <span class="footer-hours-time">08:00 - 22:00</span>
                    </div>
                    <div class="footer-hours-item">
                        <span class="footer-hours-day">Vasárnap:</span>
                        <span class="footer-hours-time">08:00 - 20:00</span>
                    </div>
                </div>

                <!-- HÍRLEVÉL (nyitvatartás alatt) -->
                <div class="footer-newsletter">
                    <p class="newsletter-text">Szeretne egyéni kedvezményeket kapni?</p>
                    <form method="POST" action="newsletter.php" class="newsletter-form" id="newsletterForm">
                        <input type="email" name="email" placeholder="E-mail cím" required id="newsletterEmail">
                        <button type="submit">Kérem a kedvezményeket</button>
                    </form>
                </div>
            </div>

            <!-- JOGI INFORMÁCIÓK -->
            <div class="footer-col">
                <h4>INFORMÁCIÓK</h4>
                <ul class="footer-links">
                    <li><a href="#">Adatvédelmi tájékoztató</a></li>
                    <li><a href="#">Általános Szerződési Feltételek</a></li>
                    <li><a href="#">Cookie szabályzat</a></li>
                    <li><a href="#">Kapcsolat</a></li>
                </ul>
            </div>
        </div>

        <!-- COPYRIGHT -->
        <div class="footer-copyright">
            <p>&copy; 2026 Hotel Szalka Mátészalka **** - Minden jog fenntartva.</p>
        </div>
    </div>
</footer>

<!-- HÍRLEVÉL FELUGRÓ ABLAK (MODAL) - ÁTNEVEZVE, HOGY NE ÜTKÖZZÖN A SZOBA MODALLAL -->
<div id="newsletterModal" class="newsletter-modal">
    <div class="newsletter-modal-content">
        <div class="newsletter-modal-icon" id="newsletterModalIcon">
            <span class="material-symbols-outlined">mark_email_read</span>
        </div>
        <h3 id="newsletterModalTitle">Köszönjük a feliratkozást!</h3>
        <p id="newsletterModalMessage">Sikeresen feliratkozott hírlevelünkre.</p>
        <button class="newsletter-modal-button" onclick="closeNewsletterModal()">Rendben</button>
    </div>
</div>

<style>
/* ========== FOOTER STÍLUSOK ========== */
footer {
    background: var(--dark-blue);
    color: var(--white);
    padding: 40px 0 20px;
    font-family: 'Montserrat', sans-serif;
}

/* Oszlopok */
.footer-columns {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
    padding: 30px 0;
    border-top: 1px solid rgba(197, 160, 89, 0.2);
    border-bottom: 1px solid rgba(197, 160, 89, 0.2);
}

.footer-col h3 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    color: var(--gold);
    margin-bottom: 10px;
    letter-spacing: 2px;
}

.footer-col h4 {
    font-size: 18px;
    color: var(--gold);
    margin-bottom: 20px;
    letter-spacing: 1px;
    font-weight: 600;
}

.footer-stars {
    color: var(--gold);
    font-size: 16px;
    letter-spacing: 4px;
    margin-bottom: 5px;
}

.footer-tagline {
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    margin-bottom: 20px;
    font-style: italic;
}

/* Kontakt */
.footer-contact {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: rgba(255,255,255,0.8);
}

.contact-item .material-symbols-outlined {
    color: var(--gold);
    font-size: 18px;
}

/* Nyitvatartás - footerben (CSAK ITT!) */
.footer-col .footer-opening-hours {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 25px;
    background: transparent;
    padding: 0;
}

.footer-col .footer-hours-item {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    padding: 4px 0;
    border-bottom: 1px dashed rgba(197, 160, 89, 0.2);
    background: transparent;
}

.footer-col .footer-hours-item:last-child {
    border-bottom: none;
}

.footer-col .footer-hours-day {
    color: rgba(255,255,255,0.7);
}

.footer-col .footer-hours-time {
    color: var(--gold);
    font-weight: 600;
}

/* Hírlevél */
.footer-newsletter {
    margin-top: 10px;
}

.newsletter-text {
    font-size: 16px;
    color: var(--gold);
    margin-bottom: 12px;
    line-height: 1.4;
    font-weight: bold;
}

.newsletter-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.newsletter-form input {
    width: 100%;
    padding: 10px 12px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(197, 160, 89, 0.3);
    color: var(--white);
    font-size: 13px;
    box-sizing: border-box;
}

.newsletter-form input:focus {
    outline: none;
    border-color: var(--gold);
}

.newsletter-form input::placeholder {
    color: rgba(255,255,255,0.5);
}

.newsletter-form button {
    width: 100%;
    padding: 10px;
    background: var(--gold);
    color: var(--dark-blue);
    border: 2px solid var(--gold);
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-size: 12px;
    cursor: pointer;
    transition: 0.3s;
}

.newsletter-form button:hover {
    background: transparent;
    color: var(--gold);
}

/* Linkek */
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 13px;
    transition: 0.3s;
    display: inline-block;
}

.footer-links a:hover {
    color: var(--gold);
    transform: translateX(5px);
}

/* Copyright */
.footer-copyright {
    text-align: center;
    padding-top: 25px;
}

.footer-copyright p {
    font-size: 12px;
    color: rgba(255,255,255,0.4);
    margin: 0;
}

/* ========== NEWSLETTER MODAL (FELUGRÓ ABLAK) - KÜLÖN OSZTÁLYOKKAL ========== */
.newsletter-modal {
    display: none;
    position: fixed;
    z-index: 10000; /* Magasabb z-index, mint a szoba modal (9999) */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    animation: newsletterFadeIn 0.3s;
}

.newsletter-modal-content {
    background: linear-gradient(135deg, var(--dark-blue) 0%, #1a2b3c 100%);
    margin: 15% auto;
    padding: 30px;
    border: 2px solid var(--gold);
    width: 90%;
    max-width: 400px;
    border-radius: 8px;
    position: relative;
    text-align: center;
    color: var(--white);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    animation: newsletterSlideIn 0.3s;
}

.newsletter-close {
    color: var(--gold);
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.newsletter-close:hover {
    color: var(--white);
    transform: scale(1.1);
}

.newsletter-modal-icon {
    margin-bottom: 20px;
}

.newsletter-modal-icon .material-symbols-outlined {
    font-size: 60px;
    color: var(--gold);
}

.newsletter-modal-content h3 {
    font-size: 24px;
    color: var(--gold);
    margin-bottom: 15px;
    font-family: 'Playfair Display', serif;
}

.newsletter-modal-content p {
    font-size: 16px;
    line-height: 1.5;
    margin-bottom: 25px;
    color: rgba(255,255,255,0.9);
}

.newsletter-modal-button {
    background: var(--gold);
    color: var(--dark-blue);
    border: 2px solid var(--gold);
    padding: 10px 30px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: 0.3s;
    border-radius: 4px;
}

.newsletter-modal-button:hover {
    background: transparent;
    color: var(--gold);
}

/* Animációk */
@keyframes newsletterFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes newsletterSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Reszponzív */
@media (max-width: 768px) {
    .footer-columns {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .footer-col {
        text-align: center;
    }
    
    .contact-item {
        justify-content: center;
    }
    
    .footer-col .hours-item {
        flex-direction: column;
        align-items: center;
        gap: 5px;
        border-bottom: none;
    }
    
    .footer-links a:hover {
        transform: translateX(0) scale(1.05);
    }
    
    .newsletter-form {
        max-width: 300px;
        margin: 0 auto;
    }
    
    .newsletter-modal-content {
        margin: 30% auto;
        width: 95%;
        padding: 20px;
    }
}
</style>

<script>
// Hírlevél form AJAX kezelés
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async function(e) {
            e.preventDefault(); // Megakadályozzuk a hagyományos form küldést
            
            const emailInput = document.getElementById('newsletterEmail');
            const email = emailInput.value.trim();
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            
            // Email validáció
            if (!email || !isValidEmail(email)) {
                showNewsletterModal('Hiba történt', 'Kérjük, adjon meg egy érvényes email címet!', false);
                return;
            }
            
            // Submit button letiltása (dupla küldés elkerülése)
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
                    emailInput.value = ''; // Input mező ürítése
                } else {
                    showNewsletterModal('Hiba történt', data.message, false);
                }
            } catch (error) {
                console.error('Hiba:', error);
                showNewsletterModal('Hiba történt', 'Hálózati hiba történt. Kérjük, próbálja újra később!', false);
            } finally {
                // Submit button visszaállítása
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });
    }
    
    // URL paraméterek ellenőrzése (hagyományos form küldés után)
    const urlParams = new URLSearchParams(window.location.search);
    const newsletterStatus = urlParams.get('newsletter');
    
    if (newsletterStatus === 'success') {
        showNewsletterModal('Sikeres feliratkozás!', 'Köszönjük, hogy feliratkozott hírlevelünkre! Hamarosan küldjük az első ajánlatainkat.', true);
        // Tisztítjuk az URL-t (eltávolítjuk a paramétert)
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (newsletterStatus === 'error') {
        showNewsletterModal('Hiba történt', 'Sikertelen feliratkozás. Kérjük, próbálja újra később!', false);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Email cím validálása
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Newsletter modal megjelenítése
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
    
    // Automatikus bezárás 5 másodperc után (csak siker esetén)
    if (isSuccess) {
        setTimeout(closeNewsletterModal, 5000);
    }
}

// Newsletter modal bezárása
function closeNewsletterModal() {
    const modal = document.getElementById('newsletterModal');
    modal.style.display = 'none';
}

// Newsletter modal bezárása külső kattintásra
window.addEventListener('click', function(event) {
    const modal = document.getElementById('newsletterModal');
    if (event.target === modal) {
        closeNewsletterModal();
    }
});

// Newsletter modal bezárása ESC billentyűre
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('newsletterModal');
        if (modal.style.display === 'block') {
            closeNewsletterModal();
        }
    }
});

// Fontos: Ne akadályozza a szoba modal működését!
// Az eredeti window.onclick-et nem írjuk felül, mert az a rooms.php-ban van
</script>