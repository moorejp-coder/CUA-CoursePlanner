# Busch School Course Planning Bot

An AI-powered academic advising chatbot for undergraduate students at the **Tim & Steph Busch School of Business** at The Catholic University of America.

---

## What It Does

The Busch School Course Planning Bot is a conversational AI advisor that helps undergraduate business students plan their degrees without waiting for an appointment. Students register, complete a guided onboarding profile, and immediately receive personalized guidance on:

- Which degree requirements they have completed, are in progress, or still need
- Course sequencing, prerequisites, and scheduling recommendations
- All 12 BSBA specializations and the BS Accounting program
- Business and non-business minors
- How to register for internships, directed studies, and co-ops
- Where to find and submit Busch School administrative forms

No scheduling. No waiting 48 hours for a reply. The bot reads the student's saved academic profile and gives specific, actionable advice in seconds — available 24/7 at no cost to the student.

---

## Features

- **Landing Page** — CUA-branded homepage with hero, feature cards, how-it-works steps, and CTA sections using official Catholic University brand colors and fonts
- **Login & Registration** — students register and sign in with their CUA email; restricted to `@cua.edu` addresses
- **AI Chat Interface** — clean, responsive conversation UI with full CUA branding (cardinal red, blue, gold; Oswald + Crimson Text fonts)
- **Forms & Requests Panel** — direct links to all Busch School Google Forms (internship approval, directed study, minor declaration, and more)
- **Sidebar Quick-Start Prompts** — one-click buttons for the most common advising questions; New Conversation button to reset the chat
- **Full Curriculum Context** — all 12 BSBA specializations, prerequisites, liberal arts requirements, elective rules, and catalog year differences loaded as AI context
- **Student Academic Profile System** — 6-step onboarding wizard captures degree program, catalog year, specializations, liberal arts (15 slots), business core (catalog-year-aware), specialization courses, transfer credits, GPA, and standing; bot automatically reads the profile for personalized advising
- **Academic Profile Page** — view at `/profile/academic` shows every required LA slot (15) and core slot including "not yet" rows, per-specialization blocks with elective lists, completion summary cards with progress bars, and transfer credit records
- **Bot-Driven Profile Updates** — bot can suggest marking a course as completed via a `[PROFILE_UPDATE]` tag; student sees a confirmation banner and clicks Accept to update their record
- **Semester Prompt Banner** — each September and January a gold banner prompts students to report new completions to keep their profile current
- **Consistent Design System** — all pages use official CUA brand colors (`#0a3255`, `#b21f2c`, `#C9A84C`), Google Fonts (Oswald, Roboto, Crimson Text), and matching layout patterns
- **Accessibility** — high-contrast text throughout all dark sections; nav logo inverted to white on dark header
- **CUA Email Restriction** — registration and login are restricted to `@cua.edu` addresses; non-CUA emails are rejected with a clear error message
- **Role-Based Access Control** — three roles: `student`, `dean`, `admin`; role infrastructure in place for future use

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.5) |
| Database | SQLite |
| Frontend | Blade templates, Tailwind CSS v3, Alpine.js v3 |
| AI | Groq API — `llama-3.3-70b-versatile` |
| Build tool | Vite |
| Auth | Laravel Breeze |
| Hosting | AWS EC2 |

---

## Live URL

**https://buschcourseplanner.dev**

**http://44.197.180.244**

The app is deployed on AWS EC2. The custom domain `buschcourseplanner.dev` has been purchased and points to the live server. Register an account and start chatting.

---

## How to Run Locally

### Prerequisites

- PHP 8.5+
- Composer
- Node.js 20+ and npm
- A [Groq API key](https://console.groq.com) (free tier works)

### Setup

```bash
# 1. Clone the repository
git clone https://github.com/moorejp-coder/CUA-CoursePlanner.git

# 2. Enter the project directory
cd CUA-CoursePlanner

# 3. Install PHP dependencies
composer install

# 4. Copy the environment file
cp .env.example .env

# 5. Generate the application key
php artisan key:generate

# 6. Create the SQLite database file
touch database/database.sqlite

# 7. Run database migrations
php artisan migrate

# 8. Run the database seeder
php artisan db:seed

# 9. Install Node dependencies and build frontend assets
npm install && npm run build

# 10. Add your Groq API key to .env
#    Open .env and set: GROQ_API_KEY=gsk_your_key_here

# 11. Start the development server
php artisan serve

# 12. Visit the app
open http://127.0.0.1:8000
```

Register an account with a `@cua.edu` email, complete the onboarding wizard, and start chatting. For live asset rebuilding during development, run `npm run dev` in a separate terminal instead of step 9.

---

## Environment Variables

Copy `.env.example` to `.env` and set the following:

```env
GROQ_API_KEY=gsk_your_key_here  # required — get one free at console.groq.com
```

All other variables in `.env.example` can be left at their defaults for local development. **Never commit `.env` to version control.**

---

## Security

The following protections are implemented:

| Layer | Protection |
|-------|-----------|
| HTTP headers | X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy, Permissions-Policy, Content-Security-Policy |
| Session cookies | HTTPS-only (`secure: true`), HttpOnly, SameSite=Strict |
| API rate limiting | 20 req/min on `/api/chat`, 10 req/min on `/api/upload` |
| Auth rate limiting | 5 req/min on login and register (brute-force protection) |
| Input validation | Messages stripped of HTML tags, max 2,000 characters |
| File uploads | 5MB max, MIME type validated server-side, double extensions rejected, filename sanitized |
| Passwords | Min 8 chars, mixed case, at least one number (enforced at registration) |
| Secrets | `GROQ_API_KEY` and `APP_KEY` in `.env` only, never in code or logs |
| CSRF | Laravel CSRF tokens on all POST/PATCH/DELETE routes |

---

## Project Structure

```
CUA-CoursePlanner/
├── app/
│   └── Http/
│       ├── Controllers/
│       │   ├── ChatController.php           # GET /chat, POST /api/chat → Groq API
│       │   ├── OnboardingController.php     # 6-step academic profile wizard
│       │   └── AcademicProfileController.php # Profile page + bot update API
│       └── Middleware/
│           └── EnsureDean.php              # Role-based access guard (future use)
├── resources/
│   └── views/
│       ├── chat.blade.php              # Main chat UI (Alpine.js, Tailwind)
│       ├── onboarding/                 # 6 wizard step views
│       └── profile/academic.blade.php  # Read-only academic profile page
├── routes/
│   └── web.php                         # All application routes
├── storage/
│   └── app/
│       ├── system_prompt.txt           # Full Busch School curriculum context
│       └── requirements.json           # Degree requirements data (per catalog year)
├── public/
│   └── build/                          # Vite-compiled CSS and JS (gitignored)
├── deploy.sh                           # AWS EC2 deployment script
├── ROADMAP.md                          # Milestone plan and open issues
├── FUTUREUPDATES.md                    # Deferred features and re-enable instructions
└── .env                                # Local secrets (gitignored)
```

---

## Contest Info

This project is submitted to the **CUA AI Vibe Coding Competition 2026**. It is being built iteratively with public commits on GitHub to demonstrate a realistic development process from scaffold to production.

See [ROADMAP.md](ROADMAP.md) for the full milestone plan and success criteria.

---

## Problem & Solution

**Problem:** Busch School undergraduates lack immediate, reliable access to personalized academic advising. Scheduling an advisor appointment or waiting for an email reply can take 24–72 hours — a real barrier during course registration, add/drop periods, and graduation planning.

**Solution:** An AI advisor that reads the student's saved academic profile and answers degree-planning questions instantly, accurately, and in plain language — available 24/7 at no cost to the student. Students complete a one-time onboarding wizard, and the bot knows their exact degree, catalog year, specializations, completed courses, GPA, and standing from that point forward.
