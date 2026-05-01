"use strict";

function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');
    const discoverEventsLink = document.querySelector('.discover-events-link');
    const backgroundImage = document.querySelector('.events-background');
    const dimOverlay = document.getElementById('dim-overlay');

    const images = shuffle([
        '../images/backgrounds/apex-legends-background.png',
        '../images/backgrounds/alan-wake-2-background.jpg',
        '../images/backgrounds/Arma.jpg',
        '../images/backgrounds/Batman.jpg',
        '../images/backgrounds/Batman.webp',
        '../images/backgrounds/Batmanthumbnail.jpg',
        '../images/backgrounds/callofduty.jpg',
        '../images/backgrounds/Destiny.jpg',
        '../images/backgrounds/dune.jpg',
        '../images/backgrounds/EA-FC-25-Premier-League-POTM-.avif',
        '../images/backgrounds/expedition-background.png',
        '../images/backgrounds/fortnite.png',
        '../images/backgrounds/forza-background.jpg',
        '../images/backgrounds/freddy.jpeg',
        '../images/backgrounds/gta-background.webp',
        '../images/backgrounds/King.png',
        '../images/backgrounds/LiarsBar.jpg',
        '../images/backgrounds/mortal-kombat.jpg',
        '../images/backgrounds/NBA2k25.jpg',
        '../images/backgrounds/peak.jpg',
        '../images/backgrounds/rainbow-background.jpg',
        '../images/backgrounds/rust.jpg',
        '../images/backgrounds/stanislav-klabik-final-view-01-final.jpg',
        '../images/backgrounds/tlu-background.jpg',
        '../images/backgrounds/warframe.jpg',
        '../images/backgrounds/zelda.jpg',
    ]);
    let currentIndex = 0;

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('show');
        hamburger.classList.toggle('active');
    });

    discoverEventsLink.addEventListener('click', function (e) {
        e.preventDefault();
        const eventList = document.getElementById('event-list');
        eventList.classList.toggle('show');
        eventList.scrollIntoView({ behavior: 'smooth' });
    });

    // Learn more — uses data-popup-id on each link
    document.querySelectorAll('.learn-more').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            closeAllPopups();
            const popup = document.getElementById(link.dataset.popupId);
            if (popup) {
                popup.classList.add('show');
                document.body.classList.add('popup-active');
                dimOverlay.classList.add('active');
                popup.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // Close popup via the X button (event delegation)
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('close-popup')) {
            closeAllPopups();
        }
    });

    // Preload background images
    images.forEach(src => { new Image().src = src; });

    // Hero background slideshow
    setInterval(() => {
        backgroundImage.style.opacity = 0.4;
        setTimeout(() => {
            currentIndex = (currentIndex + 1) % images.length;
            backgroundImage.src = images[currentIndex];
            backgroundImage.style.opacity = 1;
        }, 1000);
    }, 5000);

    // RSVP buttons inside popups
    setupRSVPButtons();

    // Game name search filtering
    const gameSearchInput = document.getElementById('game-search-input');
    const gameSearchClear = document.getElementById('game-search-clear');
    const noSearchResults = document.getElementById('no-search-results');

    function filterEvents() {
        const q = gameSearchInput.value.trim().toLowerCase();
        let anyVisible = false;
        document.querySelectorAll('.event-card').forEach(card => {
            const match = !q || (card.dataset.gameName || '').includes(q);
            card.style.display = match ? '' : 'none';
            if (match) anyVisible = true;
        });
        if (noSearchResults) noSearchResults.style.display = (!anyVisible && q) ? '' : 'none';
    }

    if (gameSearchInput) {
        gameSearchInput.addEventListener('input', filterEvents);
        gameSearchClear.addEventListener('click', () => {
            gameSearchInput.value = '';
            filterEvents();
        });
    }

    // Favourite event star buttons
    document.querySelectorAll('.fav-btn[data-event-id]').forEach(btn => {
        btn.addEventListener('click', async function () {
            const eventId = this.dataset.eventId;
            try {
                const res = await fetch('./toggle_favourite_event.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'event_id=' + encodeURIComponent(eventId)
                });
                const data = await res.json();
                if (data.favourited) {
                    this.classList.add('favourited');
                    this.setAttribute('aria-label', 'Remove from favourites');
                } else {
                    this.classList.remove('favourited');
                    this.setAttribute('aria-label', 'Add to favourites');
                }
            } catch (err) {
                console.error('Failed to toggle favourite:', err);
            }
        });
    });

    function closeAllPopups() {
        document.querySelectorAll('.popup').forEach(p => p.classList.remove('show'));
        document.body.classList.remove('popup-active');
        dimOverlay.classList.remove('active');
    }
});

/* ---------- RSVP FORM ---------- */
function showRSVPForm(eventDetails) {
    document.querySelectorAll('.popup').forEach(p => p.classList.remove('show'));
    document.querySelectorAll('.rsvp-form-popup').forEach(p => p.remove());

    const dimOverlay = document.getElementById('dim-overlay');
    const popup = document.createElement('div');
    popup.className = 'rsvp-form-popup';
    popup.innerHTML = `
        <div class="rsvp-form-container">
            <button class="close-rsvp-form">X</button>
            <h3>RSVP for ${eventDetails.title}</h3>
            <p class="rsvp-company">${eventDetails.company}</p>
            <form id="rsvpForm">
                <div class="form-group">
                    <label for="rsvp-name">Full Name</label>
                    <input type="text" id="rsvp-name" required>
                </div>
                <div class="form-group">
                    <label for="rsvp-email">Email</label>
                    <input type="email" id="rsvp-email" required>
                </div>
                <div class="form-group">
                    <label for="rsvp-ticket">Ticket Type</label>
                    <select id="rsvp-ticket" required>
                        <option value="General Admission">General Admission</option>
                        <option value="VIP">VIP</option>
                        <option value="Premium">Premium</option>
                    </select>
                </div>
                <button type="submit" class="submit-rsvp">Confirm RSVP</button>
            </form>
        </div>
    `;

    document.body.appendChild(popup);
    dimOverlay.classList.add('active');
    document.body.classList.add('popup-active');
    popup.addEventListener('click', e => e.stopPropagation());

    const form = popup.querySelector('#rsvpForm');
    const submitBtn = form.querySelector('.submit-rsvp');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!form.checkValidity()) { form.reportValidity(); return; }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Confirming...';

        // Save booking to DB before generating ticket
        try {
            const res = await fetch('./save_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'event_id=' + encodeURIComponent(eventDetails.eventId)
            });
            const result = await res.json();
            if (result.error) {
                alert(result.error);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm RSVP';
                return;
            }
        } catch {
            alert('Failed to save registration. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm RSVP';
            return;
        }

        submitBtn.textContent = 'Generating Ticket...';

        const confirmationCode = 'EVT-' + Math.random().toString(36).substr(2, 8).toUpperCase();
        const attendeeName     = form.querySelector('#rsvp-name').value;
        const attendeeEmail    = form.querySelector('#rsvp-email').value;
        const ticketType       = form.querySelector('#rsvp-ticket').value;

        try {
            const qr = qrcode(0, 'L');
            qr.addData(confirmationCode);
            qr.make();
            const qrCode = qr.createDataURL(10, 4);

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(20);
            doc.setTextColor(40, 53, 147);
            doc.text('EVENT RSVP CONFIRMATION', 105, 25, { align: 'center' });

            doc.setFontSize(14);
            doc.setTextColor(0, 0, 0);
            doc.setFont('helvetica', 'bold');
            doc.text('Event Details:', 20, 45);
            doc.setFont('helvetica', 'normal');
            doc.text(`Title: ${eventDetails.title}`, 20, 55);
            doc.text(`Organiser: ${eventDetails.company}`, 20, 65);
            doc.text(`Date: ${eventDetails.date}`, 20, 75);
            doc.text(`Time: ${eventDetails.time}`, 20, 85);
            doc.text(`Location: ${eventDetails.location}`, 20, 95);

            doc.setFont('helvetica', 'bold');
            doc.text('Your Information:', 20, 115);
            doc.setFont('helvetica', 'normal');
            doc.text(`Name: ${attendeeName}`, 20, 125);
            doc.text(`Email: ${attendeeEmail}`, 20, 135);
            doc.text(`Ticket Type: ${ticketType}`, 20, 145);
            doc.text(`Confirmation: ${confirmationCode}`, 20, 155);

            const pageWidth  = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const qrSize     = 100;
            const qrX        = (pageWidth - qrSize) / 2;
            const qrY        = pageHeight - 50 - qrSize;

            doc.addImage(qrCode, 'PNG', qrX, qrY, qrSize, qrSize);
            doc.text('Scan for check-in', pageWidth / 2, qrY + qrSize + 6, { align: 'center' });

            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Thank you for registering!', 105, 280, { align: 'center' });
            doc.text('ArenaSync Events', 105, 285, { align: 'center' });

            doc.save(`ArenaSync_RSVP_${eventDetails.title.replace(/\s+/g, '_')}.pdf`);

            closeRSVPForm(popup);

            // Update popup UI to reflect registered state
            const eventPopup = document.querySelector(`.popup[data-event-id="${eventDetails.eventId}"]`);
            if (eventPopup) {
                const rsvpLink = eventPopup.querySelector('.event-link');
                if (rsvpLink) {
                    const span = document.createElement('span');
                    span.className = 'already-registered';
                    span.textContent = '✓ You’re registered for this event';
                    rsvpLink.replaceWith(span);
                }
            }

            alert('RSVP confirmed! Your ticket has been downloaded.');
        } catch (error) {
            console.error('PDF generation failed:', error);
            alert('Registration saved, but ticket generation failed. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm RSVP';
        }
    });

    popup.querySelector('.close-rsvp-form').addEventListener('click', () => closeRSVPForm(popup));

    function closeRSVPForm(p) {
        if (p && p.parentNode) p.parentNode.removeChild(p);
        document.querySelectorAll('.popup').forEach(pp => pp.classList.remove('show'));
        dimOverlay.classList.remove('active');
        document.body.classList.remove('popup-active');
    }
}

function setupRSVPButtons() {
    document.querySelectorAll('.event-link').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const popup = this.closest('.popup');
            if (!popup) return;

            showRSVPForm({
                eventId:  popup.dataset.eventId,
                title:    popup.dataset.eventTitle  || popup.querySelector('h2')?.textContent || '',
                date:     popup.dataset.eventDate   || '',
                time:     popup.dataset.eventTime   || '',
                company:  popup.dataset.eventCompany || popup.querySelector('.event-company')?.textContent || '',
                location: 'Online Event'
            });
        });
    });
}
