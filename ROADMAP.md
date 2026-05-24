# Busch School Course Planning Bot — Project Roadmap

## Project Brief
An AI-powered academic advising chatbot for undergraduate students at the Tim & Steph Busch School of Business at The Catholic University of America. Students upload their Academic Planning Worksheet and receive instant, personalized guidance on degree requirements, course planning, specializations, minors, and administrative processes.

## Tech Stack
- Backend: Laravel 13 (PHP) with SQLite
- Frontend: Blade templates, Tailwind CSS, Alpine.js
- AI: Groq API (llama-3.3-70b-versatile)
- Hosting: AWS (Amplify for hello-world, EC2/Elastic Beanstalk for full app)
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

## Milestone 3 — APW Intelligence 🔄 IN PROGRESS
**Delivers:** Bot reads and interprets a student's actual academic record.
- CSV file upload for Academic Planning Worksheets
- PDF file upload for Cardinal Station graduation reports
- Parser for all 4 APW versions (BSBA and BSAccounting, pre and post Spring 2024)
- Compact token-efficient APW summary sent to Groq
- Bot accurately reads completed, in-progress, and needed courses
- All 3 specializations detected from uploaded APW
- Accurate interpretation validated against real student APWs

---

## Milestone 4 — Conversation Quality
**Delivers:** A bot that feels professional and trustworthy.
- Strip all markdown asterisks (**bold**) from bot responses
- Bot never asks about schedule constraints
- Math and language placement only asked when directly relevant
- Bot always recommends consulting a human advisor for final decisions
- Consistent, warm, professional tone across all responses
- Bot handles edge cases: transfer students, double majors, non-business minors
- Bot correctly handles pre-2024 vs post-2024 catalog year differences
- Test suite of 20 scripted student questions validated for accuracy

---

## Milestone 5 — User Experience Polish
**Delivers:** An interface students actually enjoy using.
- Mobile responsive design verified on phone and tablet
- Loading states and typing indicators work smoothly
- Error messages are friendly and helpful
- File upload shows clear progress and confirmation
- Chat history persists within the session
- New conversation button clears chat cleanly
- Quick-start buttons send message automatically without extra click
- Disclaimer links to correct Academic Services URL
- Accessibility improvements (contrast, font sizes, keyboard navigation)

---

## Milestone 6 — Security & Code Quality 🔄 IN PROGRESS
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
- [x] Strong password requirements: 8+ chars, mixed case, number
- [x] No PII (student names) in log output
- [x] Code formatted with Laravel Pint
- [x] README security section documents all protections
- [ ] Full test suite for security-critical paths

---

## Milestone 7 — AWS Production Deployment ✅ COMPLETE
**Delivers:** A live, publicly accessible app the judges can visit.
- Laravel app deployed to AWS EC2 (t2.micro, free tier)
- Environment variables configured on server
- GROQ_API_KEY set securely on server
- HTTPS enabled via AWS Certificate Manager or Let's Encrypt
- Public URL with working login and chat — http://44.197.180.244
- Custom domain purchased: buschcourseplanner.dev (DNS propagating as of 2026-05-24)
- Deploy script automates future deployments
- Billing alerts confirmed active
- App stays live and accessible during judging period

---

## Milestone 8 — Database & User Management
**Delivers:** Proper user accounts and conversation history.
- User registration with email verification
- Password reset flow working
- Conversation history saved per user session
- Students can view their past conversations
- Admin can see usage statistics (number of conversations, common questions)
- User data protected — no PII exposed in responses or logs

---

## Milestone 9 — Advanced AI Features
**Delivers:** Features that impress judges on the AI innovation rubric.
- Bot detects catalog year automatically from uploaded APW
- Bot generates a personalized semester-by-semester 4-year plan
- Bot flags prerequisite conflicts automatically
- Bot calculates exact credits remaining to graduation
- Bot identifies the fastest path to graduation
- Bot suggests which electives complement a student's specialization
- Bot answers questions about double majors and business minors for non-business students

---

## Milestone 10 — Demo & Submission
**Delivers:** A polished, winning contest submission.
- Scripted demo against 20 real student scenarios
- Recorded walkthrough of 5 student personas:
  1. Incoming freshman exploring degree requirements
  2. Transfer student checking career discernment exemption
  3. Junior with double specialization planning senior year
  4. Senior verifying graduation readiness via APW upload
  5. Non-business student exploring a business minor
- One-page project summary (problem, solution, AI usage, key learnings)
- Final README updated with live URL, screenshots, and local setup instructions
- Demo script with timestamps (30-second hook, 5-minute walkthrough, 30-second close)
- Demo rehearsed at least 3 times
- Backup video recording in case live demo fails

---

## GitHub Issues to Open
1. Fix APW CSV parser accuracy for all 4 APW versions
2. Add PDF graduation report parsing
3. Strip markdown bold from bot responses
4. Add rate limiting to /api/chat
5. Deploy Laravel app to AWS EC2
6. Add mobile responsive fixes
7. Save conversation history per user
8. Build 4-year plan generator feature
9. Add prerequisite conflict detection
10. Record 5 student persona demo walkthroughs
11. Write one-page project summary
12. Add email verification to registration flow
13. Add admin usage statistics page
14. Validate APW parser against all 4 APW template versions

---

## Riskiest Items (Tackle First)
1. AWS EC2 deployment — most technically complex, do early in Milestone 7
2. APW parser accuracy — core feature judges will test directly
3. Demo reliability — must work live without errors on judging day

---

## Success Criteria
- Bot responds accurately in under 10 seconds
- 90% accuracy on scripted 20-scenario test suite
- Live public URL accessible with working login
- GitHub shows consistent iterative commits across all milestones
- 5 student persona walkthroughs recorded and ready as backup
- One-page summary written and saved as PDF in repository
