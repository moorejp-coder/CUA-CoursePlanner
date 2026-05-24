# Busch School Course Planning Bot

An AI-powered academic advising chatbot for undergraduate students at the **Tim & Steph Busch School of Business** at The Catholic University of America.

---

## What It Does

The Busch School Course Planning Bot is a conversational AI advisor that helps undergraduate business students plan their degrees without waiting for an appointment. Students log in, upload their Academic Planning Worksheet (a CSV export from Cardinal Station), and immediately receive personalized guidance on:

- Which degree requirements they have completed, are in progress, or still need
- Course sequencing, prerequisites, and scheduling recommendations
- All 12 BSBA specializations and the BSAccounting program
- Business and non-business minors
- How to register for internships, directed studies, and co-ops
- Where to find and submit Busch School administrative forms

No scheduling. No waiting 48 hours for a reply. The bot reads the student's actual academic record and gives specific, actionable advice in seconds — available 24/7 at no cost to the student.

---

## Features

- **Landing Page** — CUA-branded homepage with hero, feature cards, how-it-works steps, and CTA sections using official Catholic University brand colors and fonts
- **Login & Registration** — students register and sign in with their CUA email; auth pages styled to match the university portal aesthetic
- **AI Chat Interface** — clean, responsive conversation UI with full CUA branding (cardinal red, blue, gold; Oswald + Crimson Text fonts); design consistent with landing page
- **APW File Upload** — drag or click to upload a CSV Academic Planning Worksheet exported from Cardinal Station; all 4 APW template versions supported
- **PDF Upload** — upload Cardinal Station graduation audit reports for AI extraction
- **Forms & Requests Panel** — direct links to all Busch School Google Forms (internship approval, directed study, minor declaration, and more)
- **Sidebar Quick-Start Prompts** — one-click buttons for the most common advising questions; New Conversation button to reset the chat
- **Full Curriculum Context** — all 12 BSBA specializations, prerequisites, liberal arts requirements, elective rules, and catalog year differences loaded as AI context
- **Student Academic Profile System** — 6-step onboarding wizard captures degree program, catalog year, specializations, completed courses, GPA, and standing; bot automatically reads profile for personalized advising; read-only Academic Profile page at `/profile/academic`
- **Bot-Driven Profile Updates** — bot can suggest marking a course as completed via a `[PROFILE_UPDATE]` tag; student sees a confirmation banner and clicks Accept to update their record
- **Semester Prompt Banner** — each September and January a gold banner prompts students to report new completions to keep their profile current
- **Consistent Design System** — all pages use official CUA brand colors (`#0a3255`, `#b21f2c`, `#C9A84C`), Google Fonts (Oswald, Roboto, Crimson Text), and matching layout patterns
- **Accessibility** — high-contrast text throughout all dark sections; nav logo inverted to white on dark header; all em dashes removed for screen-reader compatibility

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.5) |
| Database | SQLite |
| Frontend | Blade templates, Tailwind CSS v3, Alpine.js v3 |
| AI | Groq API — `llama-3.3-70b-versatile` |
| PDF parsing | `smalot/pdfparser` |
| Build tool | Vite |
| Auth | Laravel Breeze |
| Hosting | AWS EC2 |

---

## Live URL

**https://buschcourseplanner.dev** *(DNS propagating — use IP below in the meantime)*

**http://44.197.180.244**

The app is deployed on AWS EC2. The custom domain `buschcourseplanner.dev` has been purchased and will be the permanent URL once DNS propagation completes. Register an account and start chatting.

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

# 8. Install Node dependencies and build frontend assets
npm install && npm run build

# 9. Add your Groq API key to .env
#    Open .env and set: GROQ_API_KEY=gsk_your_key_here

# 10. Start the development server
php artisan serve

# 11. Visit the app
open http://127.0.0.1:8000
```

Register an account and start chatting. For live asset rebuilding during development, run `npm run dev` in a separate terminal instead of step 8.

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

## How to Use APW Upload

1. Log in and open the chat
2. Go to **Cardinal Station → Academic Planning → Academic Planning Worksheet**
3. Export your APW as a CSV file (File → Download → CSV)
4. In the chat, click the **paperclip icon**
5. Select your CSV file — the filename appears as a tag
6. Press **Enter** or click **Send**
7. The bot reads your completed, in-progress, and planned courses and gives you a personalized advising summary

PDF uploads (Cardinal Station graduation audit reports) work the same way — just select a `.pdf` file instead.

---

## Project Structure

```
CUA-CoursePlanner/
├── app/
│   └── Http/
│       └── Controllers/
│           ├── ChatController.php           # GET /chat, POST /api/chat → Groq API
│           ├── UploadController.php         # POST /api/upload → PDF/CSV extraction
│           ├── OnboardingController.php     # 6-step academic profile wizard
│           └── AcademicProfileController.php # Profile page + bot update API
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
│       └── specializations.json        # Specialization requirements (pre/post 2024)
├── public/
│   └── build/                          # Vite-compiled CSS and JS (gitignored)
├── deploy.sh                           # AWS EC2 deployment script
├── Procfile                            # Heroku/platform web server config
├── .ebextensions/php.config            # Elastic Beanstalk PHP settings
├── ROADMAP.md                          # Milestone plan and open issues
└── .env                                # Local secrets (gitignored)
```

---

## Contest Info

This project is submitted to the **CUA AI Vibe Coding Competition 2026**. It is being built iteratively with public commits on GitHub to demonstrate a realistic development process from scaffold to production.

See [ROADMAP.md](ROADMAP.md) for the full milestone plan, open issues, and success criteria.

---

## Problem & Solution

**Problem:** Busch School undergraduates lack immediate, reliable access to personalized academic advising. Scheduling an advisor appointment or waiting for an email reply can take 24–72 hours — a real barrier during course registration, add/drop periods, and graduation planning.

**Solution:** An AI advisor that reads the student's actual Academic Planning Worksheet and answers degree-planning questions instantly, accurately, and in plain language — available 24/7 at no cost to the student.
