<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
<link rel="stylesheet" href="css/footer.css">
<footer>
    <div class="container">
        <div class="footer-columns">
            <div class="footer-col">
                <h3>HOTEL SZALKA</h3>
                <div class="footer-stars">★★★★</div>
                <p class="footer-tagline">Mátészalka legújabb szállodája</p>
                <div class="footer-contact">
                    <div class="contact-item">
                        <span class="material-symbols-outlined">location_on</span>
                        <span>4700 Mátészalka, Nagykárolyi út 105</span>
                    </div>
                    <div class="contact-item">
                        <span class="material-symbols-outlined">call</span>
                        <span>+36 70 555 6666</span>
                    </div>
                    <div class="contact-item">
                        <span class="material-symbols-outlined">mail</span>
                        <span>szalka@hotel.com</span>
                    </div>
                </div>
            </div>

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

                <div class="footer-newsletter">
                    <p class="newsletter-text">Szeretne egyéni kedvezményeket kapni?</p>
                    <form method="POST" action="newsletter.php" class="newsletter-form" id="newsletterForm">
                        <input type="email" name="email" placeholder="E-mail cím" required id="newsletterEmail">
                        <button type="submit">Kérem a kedvezményeket</button>
                    </form>
                </div>
            </div>

            <div class="footer-col">
                <h4>INFORMÁCIÓK</h4>
                <ul class="footer-links" id="doc-links">
                    <li><a href="https://drive.google.com/drive/folders/1owGcCeL6zjsVe8SV8cUl6bRLc6JdeyhN?usp=sharing" target="_blank">Google Drive</a></li>
                    <li><a href="https://github.com/Zsombii/szalka_hotel" target= "_blank">Forráskód</a></li>
                    <li><a href="https://docs.google.com/presentation/d/1GwsQob7YaO8N0EFFi6EjSZBfaN55Rnxn/edit?usp=sharing&ouid=103444524099013280860&rtpof=true&sd=true" target="_blank">Prezentáció</a></li>
                    <li><a href="https://docs.google.com/spreadsheets/d/1TzVre6qYbkvlFm3gwImhZpvO_NpAmuG6/edit?usp=sharing&ouid=103444524099013280860&rtpof=true&sd=true" target="_blank">Tevékenységnapló</a></li>
                    <li><a href="https://drive.google.com/file/d/1Z4LKf8WNca7EbbOdFbumby8qXexvGJN5/view?usp=sharing" target="_blank">SQL file</a></li>
                    <li><a href="https://drive.google.com/file/d/1hQT3hfCnDdX9TTMtu8fPsX99uY3fQAoo/view?usp=sharing" target="_blank">ER diagram</a></li>
                    <li><a href="dokumentacio/fejlesztoi.html" target="_blank">Fejlesztői dokumentáció</a></li>
                    <li><a href="dokumentacio/felhasznaloi.html" target="_blank">Felhasználói dokumentáció</a></li>
                    <li><a href="dokumentacio/teszteloi.html" target="_blank">Tesztelői dokumentáció</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-copyright">
            <p>&copy; 2026 Hotel Szalka **** - Minden jog fenntartva.</p>
        </div>
    </div>
</footer>

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

<script src="js/footer.js"></script>