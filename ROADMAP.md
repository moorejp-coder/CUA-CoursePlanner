# Busch School Course Planning Bot — Project Roadmap

## Project Brief
An AI-powered academic advising chatbot for undergraduate students at the Tim & Steph Busch School of Business at The Catholic University of America. Students complete an onboarding profile and receive instant, personalized guidance on degree requirements, course planning, specializations, minors, and administrative processes.

## Tech Stack
- Backend: Laravel 13 (PHP 8.5) with SQLite
- Frontend: Blade templates, Tailwind CSS, Alpine.js
- AI: Groq API (llama-3.3-70b-versatile)
- Hosting: AWS EC2
- Version Control: GitHub (public repository)

---

## Milestone 1 — Foundation ✅ COMPLETE
**Delivers:** Proof that the full pipeline works end to end.
- Laravel scaffold with authentication (Breeze)
- GitHub repository with public commit history
- AWS Free Tier account with billing alerts and MFA
- Hello-world deployed to AWS Amplify with Cognito login
- PROJECT.md and README.md committed

---

## Milestone 2 — Core Bot ✅ COMPLETE
**Delivers:** A working AI advisor students can talk to.
- Chat interface with CUA branding (cardinal red, blue, gold)
- Busch School logo in header
- Groq API backend connected to llama-3.3-70b-versatile
- Full Busch School curriculum loaded as system prompt
- All 12 specializations, prerequisites, minors, and forms
- Sidebar quick-start buttons
- Forms & requests panel with direct hyperlinks to all Google Forms
- CUA official fonts (Oswald, Crimson Text, Roboto)

---

## Milestone 3 — Conversation Quality ✅ COMPLETE
**Delivers:** A bot that feels professional and trustworthy.
- [x] Strip all markdown asterisks from bot responses
- [x] Bot never asks about schedule constraints unprompted
- [x] Math and language placement only asked when directly relevant
- [x] Bot always recommends consulting a human advisor for final decisions
- [x] Consistent, warm, professional tone across all responses
- [x] Bot handles catalog year differences (pre-2024 vs post-2024)
- [x] Bot handles edge cases: transfer students, double majors, double specializations

---

## Milestone 4 — User Experience Polish ✅ COMPLETE
**Delivers:** An interface students actually enjoy using.
- [x] Loading states and typing indicators work smoothly
- [x] Error messages are friendly and helpful
- [x] New conversation button clears chat cleanly
- [x] Quick-start buttons send message automatically without extra click
- [x] Disclaimer links to correct Academic Services URL
- [x] Accessibility improvements (contrast, font sizes, keyboard navigation)

---

## Milestone 5 — Security & Code Quality ✅ COMPLETE
**Delivers:** A submission that scores well on the code quality rubric.
- [x] Rate limiting on /api/chat (20/min) and /api/upload (10/min)
- [x] Rate limiting on login and register (5/min — brute-force protection)
- [x] Input validation and HTML stripping on all routes
- [x] Message max length reduced to 2,000 characters
- [x] API key stored securely in .env — never in code or logs
- [x] CSRF protection on all POST routes (Laravel default)
- [x] File upload validation: MIME type server-side, 5MB max, double-extension rejection, filename sanitization
- [x] SecurityHeaders middleware: CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
- [x] Session cookies: secure=true, httpOnly=true, sameSite=strict
- [x] HIBP breach check on all password set/change flows (Password::uncompromised())
- [x] Account enumeration fix in password reset (always same flash message)
- [x] Password reset token expiry reduced to 30 minutes; eager token validation before form render
- [x] Session invalidation after password reset and password change
- [x] ValidateSessionBinding middleware: UA change invalidates session, IP change is logged
- [x] Logout everywhere: deletes all DB session rows, rotates remember_token
- [x] DetectAttackPatterns middleware: SQL injection, XSS, null byte, oversized payload blocking
- [x] Honeypot trap on all auth forms
- [x] Dead admin/role code removed (AdminController, EnsureDean, 8 admin views, User::role)
- [x] Seeder credentials moved to env vars
- [x] No PII (student names) in log output
- [x] Code formatted with Laravel Pint
- [x] README security section documents all protections
- [x] Full test suite for security-critical paths (44 tests, all passing)

---

## Milestone 6 — AWS Production Deployment ✅ COMPLETE
**Delivers:** A live, publicly accessible app the judges can visit.
- Laravel app deployed to AWS EC2 (t2.micro, free tier)
- Environment variables configured on server
- GROQ_API_KEY set securely on server
- HTTPS enabled via AWS Certificate Manager or Let's Encrypt
- Public URL: http://44.197.180.244
- Custom domain purchased: buschcourseplanner.dev
- Deploy script automates future deployments
- Billing alerts confirmed active
- App stays live and accessible during judging period

---

## Milestone 7 — Student Academic Profile System ✅ COMPLETE
**Delivers:** Personalized, persistent academic advising that knows every student's exact situation.
- [x] `student_profiles` and `student_courses` database tables
- [x] `StudentProfile` and `StudentCourse` Eloquent models with User relationships
- [x] 6-step onboarding wizard (`/onboarding`) with CUA branding and progress bar
  - Step 1: Basic info (name, admit term, degree, graduation); auto-detects catalog year
  - Step 2: Specialization selection (up to 3) with Alpine.js warnings and MATH 111 notices
  - Step 3: Liberal arts requirements (15 slots with searchable dropdowns)
  - Step 4: Business core (catalog-year-aware) + Transfer Credits section
  - Step 5: Specialization course status (required + electives per chosen spec)
  - Step 6: Credits, GPA, in-progress courses, standing auto-calc, career discernment warnings
- [x] New users redirected to `/onboarding` after registration/email verification
- [x] Academic Profile page (`/profile/academic`) — all 15 LA slots, all core slots, per-spec blocks, completion progress bars, transfer credits
- [x] "Academic Profile" link in chat sidebar
- [x] Bot profile context — every Groq API call prepends the student's full profile summary
- [x] `[PROFILE_UPDATE]` tag — bot suggests course completions; student accepts with one click
- [x] Semester prompt banner — September/January gold banner prompts profile refresh
- [x] `requirements.json` — all degree requirements, business core options, and specialization data loaded dynamically by the wizard
- [x] Onboarding wizard reads all course lists from `requirements.json` (no hardcoded arrays)
- [x] End-to-end test: new user registration → onboarding → chat → profile update flow
- [ ] Deploy updated profile system to production EC2

---

## Milestone 7.5 — Production Security Sprint ✅ COMPLETE (May 2026)
**Delivers:** Defense-in-depth hardening across every layer before live judging.

- [x] HTTPS enforcement + HSTS header (`max-age=31536000; includeSubDomains`) on production
- [x] Session cookie `secure` flag set to `auto` (HTTPS-only in production, HTTP-safe locally)
- [x] `DetectAttackPatterns` middleware — blocks SQL injection, XSS probes, null bytes, oversized payloads
- [x] `ValidateSessionBinding` middleware — UA change invalidates session; IP change logged
- [x] GDPR account deletion — full data wipe (user, profile, courses, sessions, remember tokens)
- [x] HIBP breach check (`Password::uncompromised()`) on all password set/change flows (k-anonymity)
- [x] UUID primary keys on `users` — eliminates sequential ID enumeration
- [x] Alpine.js `x-html` removed from chat UI — bot output is plain text, not rendered HTML
- [x] Global error handler — sanitized JSON error responses; custom error pages; no stack traces to client
- [x] API response field allowlists — model serialization locked down; no accidental field exposure
- [x] `AuthorizesAccess` trait — reusable 401/403 enforcement across controllers
- [x] Self-hosted fonts (Oswald, Roboto, Crimson Text via `@fontsource`) — Google Fonts CDN removed; student IPs no longer sent to Google
- [x] CSP tightened — `font-src 'self'` and `style-src 'self'` only; no external CDN allowances
- [x] `X-XSS-Protection` header removed — retired IE-era header that re-enables certain XSS vectors; CSP covers it instead
- [x] Groq API retry — 2 retries on transient `ConnectionException` (500ms delay)
- [x] SQLite WAL mode + `busy_timeout=5000` — concurrent reads, crash-safe writes
- [x] Heroku Postgres migration path documented in `.env.example` (SQLite is ephemeral on Heroku dynos)
- [x] `AdminUserSeeder` hardened — throws if `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD` env vars are unset; no hardcoded fallbacks in source
- [x] `MAIL_FROM_ADDRESS` default changed from `hello@example.com` to `noreply@buschcourseplanner.dev`
- [x] `.env.example` production defaults: `LOG_STACK=daily`, `LOG_LEVEL=error`, `APP_DEBUG=false`, `APP_URL` set to real domain
- [x] 11 Symfony CVEs patched (`symfony/http-foundation`, `symfony/http-kernel`, `symfony/security-*`, `symfony/polyfill-intl-idn`, etc.) — all patch-level, no breaking changes
- [x] `SecurityHeadersTest` expanded — asserts Google CDN domains are absent from CSP
- [x] All 44+ tests passing after sprint

---

## Milestone 8 — Advanced AI Features ✅ COMPLETE
**Delivers:** Features that impress judges on the AI innovation rubric.
- [x] Bot generates a personalized semester-by-semester 4-year plan (PlannerService + system prompt)
- [x] Bot flags prerequisite conflicts automatically (PrerequisiteService — conflicts, now-eligible, still-blocked)
- [x] Bot calculates exact credits remaining to graduation (course-count-based, not flat estimate)
- [x] Bot identifies the fastest path to graduation (critical chains, semester locks, min semesters)
- [x] Bot suggests which electives complement a student's specialization (eligible now vs. prereqs not met)
- [x] Bot answers questions about double majors and business minors (all 4 degree types in onboarding + context)

---

## Milestone 9 — Demo & Submission
**Delivers:** A polished, winning contest submission.
- Scripted demo against 20 real student scenarios
- Recorded walkthrough of 5 student personas:
  1. Incoming freshman exploring degree requirements
  2. Transfer student checking career discernment exemption
  3. Junior with double specialization planning senior year
  4. Senior checking graduation readiness through their profile
  5. Non-business student exploring a business minor
- One-page project summary (problem, solution, AI usage, key learnings)
- Final README updated with live URL, screenshots, and local setup instructions
- Demo script with timestamps (30-second hook, 5-minute walkthrough, 30-second close)
- Demo rehearsed at least 3 times
- Backup video recording in case live demo fails

---

## Deferred Features
See [FUTUREUPDATES.md](FUTUREUPDATES.md) for full details on features taken offline and planned for future work, including:
- Admin dashboard (student roster, role management, usage statistics)
- Degree requirements editor with chip/pill tag UI
- System prompt editor with version history

---

## Success Criteria
- Bot responds accurately in under 10 seconds
- Profile system correctly personalizes bot responses for each student
- Live public URL accessible with working login and onboarding
- GitHub shows consistent iterative commits across all milestones
- 5 student persona walkthroughs recorded and ready as backup
- One-page summary written and saved as PDF in repository
