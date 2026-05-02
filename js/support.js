"use strict";

document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('nav-links');

  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('show');
    hamburger.classList.toggle('active');
  });

  const container = document.getElementById('message-container');

  const reviews = [
    'Katie Sparks ✨:\nAbsolutely loved the event! The energy, the games, everything was on point!',
    'Liam Brooks 🎮:\nFirst time attending and it exceeded all my expectations. Great job!',
    'Ava Martin 💥:\nThe tournaments were intense and super fun to watch!',
    'Noah Rivera 🔥:\nMet some amazing people and had a blast. Definitely coming again!',
    'Sophia Bennett 🎉:\nThe vibes were unreal. Such a cool atmosphere all around.',
    'Jackson Lee 🤩:\nIt was organized really well. Shoutout to the team!',
    'Mia Taylor 💬:\nLoved every minute of it. Can\'t wait for the next one!',
    'Lucas Young 🕹️:\nThe setup was clean and professional. Gamers paradise!',
    'Isabella Green 🧡:\nFun, safe, and super engaging. Well done!',
    'Ethan Hall 🏆:\nThe finals had me on the edge of my seat. Epic stuff!',
    'Olivia Adams 🤗:\nVery inclusive event, which made it feel welcoming.',
    'James Scott 🎧:\nSound and visuals were next-level. You could tell they cared.',
    'Charlotte Turner 🫶:\nHighly recommend for anyone into gaming or eSports.',
    'Benjamin Phillips 😎:\nI\'ve been to a few events, but this one stood out big time.',
    'Amelia White 🎈:\nPerfect mix of fun and competition. Great job everyone!',
    'Henry Mitchell 🧃:\nThere was so much to do! Panels, games, giveaways...',
    'Emily Ross 💻:\nI even learned a few things from the dev talks. Awesome lineup!',
    'Daniel Carter 🎮:\n10/10. Can\'t stop talking about it with my friends.',
    'Grace Anderson 🔊:\nThe crowd interaction made it feel like a live concert!',
    'Logan Wright 👏:\nBig respect to the organizers. You nailed it.'
  ];

  reviews.forEach((review, i) => {
    setTimeout(() => {
      const msg = document.createElement('div');
      msg.className = 'floating-message';
      msg.innerText = review;

      const screenPosition = ["left", "right"];
      const randomPosition = screenPosition[Math.floor(Math.random() * 2)];
      msg.style[randomPosition] = 0 + '%';

      container.appendChild(msg);
      setTimeout(() => container.removeChild(msg), 7000);
    }, i * 1750);
  });

  const counters = document.querySelectorAll('.counter');
  counters.forEach(counter => {
    counter.innerText = '0';
    const updateCounter = () => {
      const target    = +counter.getAttribute('data-target');
      const c         = +counter.innerText;
      const increment = target / 200;
      if (c < target) {
        counter.innerText = `${Math.ceil(c + increment)}`;
        setTimeout(updateCounter, 10);
      } else {
        counter.innerText = target;
      }
    };
    updateCounter();
  });

  const form             = document.getElementById("contactForm");
  const statusMsg        = document.getElementById("statusMsg");
  const previewContainer = document.getElementById("previewContainer");
  const previewContent   = document.getElementById("previewContent");
  const editBtn          = document.getElementById("editBtn");
  const deleteBtn        = document.getElementById("deleteBtn");
  const submitFinalBtn   = document.getElementById("submitFinalBtn");

  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const role        = form.dataset.role;
    const firstName   = document.getElementById("firstName").value.trim();
    const lastNameEl  = document.getElementById("lastName");
    const lastName    = lastNameEl ? lastNameEl.value.trim() : "";
    const email       = document.getElementById("email").value.trim();
    const ticket      = document.getElementById("ticket").value;
    const message     = document.getElementById("message").value.trim();

    if (!ticket) {
      statusMsg.style.color = "red";
      statusMsg.textContent = "Please select a ticket type.";
      return;
    }
    if (!message) {
      statusMsg.style.color = "red";
      statusMsg.textContent = "Please enter a message.";
      return;
    }

    let nameHtml;
    if (role === 'organizer') {
      nameHtml = `<p><strong>Organization:</strong> ${firstName}</p>`;
    } else {
      nameHtml = `<p><strong>First Name:</strong> ${firstName}</p>
                  <p><strong>Last Name:</strong> ${lastName}</p>`;
    }

    previewContent.innerHTML = `
      ${nameHtml}
      <p><strong>Email:</strong> ${email}</p>
      <p><strong>Ticket Enquiry:</strong> ${ticket}</p>
      <p><strong>Message:</strong> ${message}</p>
    `;

    previewContainer.classList.remove("hidden");
    form.classList.add("hidden");
    statusMsg.textContent = "";
  });

  editBtn.addEventListener("click", () => {
    previewContainer.classList.add("hidden");
    form.classList.remove("hidden");
  });

  deleteBtn.addEventListener("click", () => {
    previewContainer.classList.add("hidden");
    form.reset();
    form.classList.remove("hidden");
    statusMsg.style.color = "red";
    statusMsg.textContent = "Submission cancelled. You can fill the form again.";
  });

  submitFinalBtn.addEventListener("click", () => {
    form.submit();
  });
});
