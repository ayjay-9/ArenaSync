'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('nav-links');

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('show');
        hamburger.classList.toggle('active');
    });

    // ── Globe ──────────────────────────────────────────────────────
    const globeContainer = document.getElementById('globe-container');
    if (globeContainer && typeof Globe === 'function') {
        Globe()
            .width(300)
            .height(300)
            .globeImageUrl('//unpkg.com/three-globe/example/img/earth-dark.jpg')
            .pointsData([
                { lat: 51.8894, lng:  -8.4942, label: 'Griffith College Cork' },
                { lat: 53.3498, lng:  -6.2603, label: 'Dublin' },
                { lat:  6.5244, lng:   3.3792, label: 'Lagos' },
                { lat: 35.6895, lng: 139.6917, label: 'Tokyo' },
                { lat: 34.0522, lng:-118.2437, label: 'Los Angeles' },
                { lat: 37.5665, lng: 126.9780, label: 'Seoul' },
                { lat:-23.5505, lng: -46.6333, label: 'São Paulo' },
                { lat:-33.8688, lng: 151.2093, label: 'Sydney' },
                { lat:-77.8419, lng: 166.6863, label: 'McMurdo Station' }
            ])
            .pointAltitude(0.1)
            .pointLabel('label')
            .pointColor(() => '#3CA9E2')
            (globeContainer);
    }

    // ── Scroll animations ──────────────────────────────────────────
    const missionVision  = document.getElementById('about-mission-vision');
    const mission        = document.getElementById('about-mission');
    const vision         = document.getElementById('about-vision');
    const locations      = document.getElementById('locations');
    const description    = document.getElementById('about-description');
    const socialsSection = document.getElementById('socials-section');
    let countersStarted  = false;

    function startCounters() {
        document.querySelectorAll('.home-counter').forEach(counter => {
            counter.innerText = '0';
            const target = +counter.getAttribute('data-target');
            const tick = () => {
                const c = +counter.innerText;
                const increment = target / 200;
                if (c < target) {
                    counter.innerText = `${Math.ceil(c + increment)}`;
                    setTimeout(tick, 10);
                } else {
                    counter.innerText = target;
                }
            };
            tick();
        });
    }

    function checkScroll() {
        if (!description || !locations) return;

        const descRect    = description.getBoundingClientRect();
        const locRect     = locations.getBoundingClientRect();

        if (descRect.bottom < window.innerHeight * 0.75) {
            missionVision?.classList.add('visible');
            mission?.classList.add('visible');
            vision?.classList.add('visible');
        } else {
            missionVision?.classList.remove('visible');
            mission?.classList.remove('visible');
            vision?.classList.remove('visible');
        }

        if (locRect.top < window.innerHeight * 0.8) {
            locations.classList.add('visible');
        } else {
            locations.classList.remove('visible');
        }

        if (socialsSection) {
            const socialsRect = socialsSection.getBoundingClientRect();
            if (socialsRect.top < window.innerHeight * 0.8) {
                socialsSection.classList.add('visible');
                if (!countersStarted) {
                    countersStarted = true;
                    startCounters();
                }
            } else {
                socialsSection.classList.remove('visible');
                countersStarted = false;
            }
        }
    }

    window.addEventListener('scroll', checkScroll);
    checkScroll();
});
