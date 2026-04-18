"use strict";

document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('nav-links');

  // Hamburger menu toggle
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
    'Mia Taylor 💬:\nLoved every minute of it. Can’t wait for the next one!',
    'Lucas Young 🕹️:\nThe setup was clean and professional. Gamers paradise!',
    'Isabella Green 🧡:\nFun, safe, and super engaging. Well done!',
    'Ethan Hall 🏆:\nThe finals had me on the edge of my seat. Epic stuff!',
    'Olivia Adams 🤗:\nVery inclusive event, which made it feel welcoming.',
    'James Scott 🎧:\nSound and visuals were next-level. You could tell they cared.',
    'Charlotte Turner 🫶:\nHighly recommend for anyone into gaming or eSports.',
    'Benjamin Phillips 😎:\nI’ve been to a few events, but this one stood out big time.',
    'Amelia White 🎈:\nPerfect mix of fun and competition. Great job everyone!',
    'Henry Mitchell 🧃:\nThere was so much to do! Panels, games, giveaways...',
    'Emily Ross 💻:\nI even learned a few things from the dev talks. Awesome lineup!',
    'Daniel Carter 🎮:\n10/10. Can’t stop talking about it with my friends.',
    'Grace Anderson 🔊:\nThe crowd interaction made it feel like a live concert!',
    'Logan Wright 👏:\nBig respect to the organizers. You nailed it.'
  ];

  reviews.forEach((review, i) => {
    setTimeout(() => {
      const msg = document.createElement('div');
      msg.className = 'floating-message';
      msg.innerText = review;

      // Random horizontal position
      const screenPosition = ["left", "right"];
      const randomPosition = screenPosition[Math.floor(Math.random() * 2)];
      msg.style[randomPosition] = 0 + '%';

      container.appendChild(msg);

      // Remove the message after it floats away
      setTimeout(() => {
        container.removeChild(msg);
      }, 7000); // Should match CSS animation duration
    }, i * 1750); // 2s stagger between each
  });

  const form = document.getElementById("contactForm");
  const statusMsg = document.getElementById("statusMsg");
  const previewContainer = document.getElementById("previewContainer");
  const previewContent = document.getElementById("previewContent");
  const editBtn = document.getElementById("editBtn");
  const deleteBtn = document.getElementById("deleteBtn");

  // Select all elements with the class 'counter'
  const counters = document.querySelectorAll('.counter')

  // Loop through each counter element
  counters.forEach(counter => {
    // Start each counter at 0
    counter.innerText = '0'

    // Function to increment the counter gradually
    const updateCounter = () => {
      // Get the target number from the data-target attribute
      const target = +counter.getAttribute('data-target')
      // Get the current value of the counter (convert from string to number)
      const c = +counter.innerText

      // Calculate how much to increment by (adjust divisor to control speed)
      const increment = target / 200;

      // If the current value is less than the target, update it
      if (c < target) {
        // Increase the counter and round it up
        counter.innerText = `${Math.ceil(c + increment)}`
        // Call updateCounter again after 10 milliseconds
        setTimeout(updateCounter, 10)
      } else {
        // Once the counter reaches the target, set it exactly to the target value
        counter.innerText = target
      }
    }

    // Start updating this counter
    updateCounter()
  })

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const email = document.getElementById("email").value.trim();
    const ticket = document.getElementById("ticket").value;
    const message = document.getElementById("message").value.trim();
    const submitFinalBtn = document.getElementById("submitFinalBtn");
    // Simple email validation
    if (!/^\S+@\S+\.\S+$/.test(email)) {
      statusMsg.style.color = "red";
      statusMsg.textContent = "Please enter a valid email.";
      return;
    }

    // Show preview
    previewContent.innerHTML = `
    <p><strong>First Name:</strong> ${firstName}</p>
    <p><strong>Last Name:</strong> ${lastName}</p>
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
    previewContainer.innerHTML = `
    <h3>Thank you!</h3>
    <p>Your message has been successfully submitted. We'll get back to you soon.</p>
  `;
  });

});