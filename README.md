# ArenaSync

ArenaSync is a full-stack gaming event platform built by students at Griffith College Cork. It lets attendees discover and book gaming events, follow organisers, and manage their profile — while giving organisers a space to list tournaments and showcases.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 (procedural, `mysqli`) |
| Database | MariaDB 10.4 via XAMPP |
| Frontend | HTML5 · CSS3 (custom properties) · Vanilla JS |
| Auth | PHP sessions + bcrypt (`password_hash`) + Remember Me cookies |
| Dev server | Apache via XAMPP |

---

## Features

### Attendee
- **Sign up / Log in** — email + password auth with Remember Me (persistent cookie)
- **Events** — browse all upcoming gaming events with game details and organiser info
- **Organisers** — search and discover event organisers by company name
- **MyArena** — personal dashboard with three tabs:
  - *Personal Details* — view and update name, email, and password
  - *My Events* — see all events you have booked
  - *Favourites* — saved events and followed organisers

### Organiser
- Dedicated sign-up and login flow
- Listed on the public Organisers page with their hosted events

### Admin
- Separate admin login; pre-seeded accounts for Emmanuel, Ahmad, and Miguel

---

## Database Schema

See `erd.mermaid` for the full entity-relationship diagram. Tables:

| Table | Purpose |
|---|---|
| `users` | All accounts — role `ENUM('admin','organiser','attendee')` |
| `games` | Game catalogue (name, category, description) |
| `events` | Scheduled instances of a game, linked to an organiser |
| `bookings` | Composite-PK join: attendee ↔ event |
| `favourite_events` | Attendee's starred events |
| `favourite_organizers` | Attendee's followed organisers |

---

## Project Structure

```
ArenaSync/
├── index.php                   # Homepage (globe, about, socials)
├── db_config.php               # mysqli connection (shared via require_once)
├── arenasync.sql               # Full DB dump (schema + seed data)
├── arenasync_config.sql        # Original schema reference
├── populate_events.sql         # Event seed data
├── erd.mermaid                 # Entity-relationship diagram
├── README.md
│
├── php/                        # Application pages
│   ├── login.php               # Attendee login
│   ├── signup.php              # Attendee registration
│   ├── organizer-login.php     # Organiser login
│   ├── organizer-signup.php    # Organiser registration
│   ├── events.php              # Events listing
│   ├── organizers.php          # Organiser search + cards
│   ├── my_arena.php            # Attendee dashboard (Personal / My Events / Favourites)
│   ├── remember-me.php         # Remember Me cookie helper
│   ├── logout.php              # Session teardown
│   └── support.php             # Support page
│
├── css/
│   ├── main.css                # Design tokens + nav + footer + responsive
│   ├── home.css                # Homepage-specific styles
│   ├── events.css              # Events page styles
│   ├── organizers.css          # Organiser grid + card styles
│   ├── my_arena.css            # Dashboard layout + sidebar + form styles
│   ├── login.css               # Auth form styles (floating labels, checkbox)
│   ├── chatbot.css             # Chatbot widget styles
│   └── support.css             # Support page styles
│
├── js/
│   ├── main.js                 # Shared hamburger nav toggle
│   ├── home.js                 # Globe, scroll animations, counters
│   ├── events.js               # Event popups, RSVP, QR / PDF ticket
│   ├── organizers.js           # (reserved)
│   ├── my_arena.js             # Dashboard tab switching + password toggle
│   ├── login.js                # Password visibility toggle
│   ├── chatbot.js              # Chatbot widget logic
│   └── support.js              # Support page scripts
│
├── images/                     # Game banners, backgrounds, logo
└── icons/                      # Social media icons (SVG/PNG)
```

---

## Setup

### Prerequisites
- XAMPP (Apache + MariaDB)
- Any modern browser

### Steps

1. Clone / copy the `ArenaSync` folder into `C:\xampp\htdocs\`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Open **phpMyAdmin** → create a database named `arenasync`
4. Import `arenasync.sql` — this creates all tables and seeds users, games, and events
5. Navigate to `http://localhost/ArenaSync/`

### Default Accounts

| Role | Email | Password |
|---|---|---|
| Admin | `emmanuel@arenasync.com` | `admin_Emmanuel` |
| Admin | `ahmad@arenasync.com` | `admin_Ahmad` |
| Admin | `miguel@arenasync.com` | `admin_Miguel` |
| Organiser | `alphabet@company.org` | *(set at signup)* |

---

## Team

**Emmanuel Ayobanjo · Ahmad Assante · Miguel Cofre**
Griffith College Cork — Server-Side Web Development, 2026
