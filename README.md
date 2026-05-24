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

- **Login & Registration** — students register and sign in with their CUA email
- **AI Chat Interface** — clean, responsive conversation UI with full CUA branding (cardinal red, blue, gold; Oswald + Crimson Text fonts)
- **APW File Upload** — drag or click to upload a CSV Academic Planning Worksheet exported from Cardinal Station; all 4 APW template versions supported
- **PDF Upload** — upload Cardinal Station graduation audit reports for AI extraction
- **Forms & Requests Panel** — direct links to all Busch School Google Forms (internship approval, directed study, minor declaration, and more)
- **Sidebar Quick-Start Prompts** — one-click buttons for the most common advising questions
- **Full Curriculum Context** — all 12 BSBA specializations, prerequisites, liberal arts requirements, elective rules, and catalog year differences loaded as AI context

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

**http://44.197.180.244**

The app is live on AWS EC2. Register an account and start chatting.

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
│           ├── ChatController.php      # GET /chat, POST /api/chat → Groq API
│           └── UploadController.php    # POST /api/upload → PDF/CSV extraction
├── resources/
│   └── views/
│       └── chat.blade.php              # Main chat UI (Alpine.js, Tailwind)
├── routes/
│   └── web.php                         # All application routes
├── storage/
│   └── app/
│       └── system_prompt.txt           # Full Busch School curriculum context
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
