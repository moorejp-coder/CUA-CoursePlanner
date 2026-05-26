# Future Updates — Deferred Features

This file documents features that have been built or designed but are temporarily removed from the live site. Code remains in the repository for future reactivation.

---

## Admin Panel (Deferred — May 2026)

The admin panel was built and functional but has been taken offline pending a full redesign of the requirements editor. It will be re-enabled once the new UI is complete.

### What Was Built

A password-protected panel at `/admin` (requires `dean` or `admin` role) with the following pages:

| Page | Route | Description |
|------|-------|-------------|
| Dashboard | `/admin` | Stat cards (total students, profiles, registered today), recent registrations table, top specializations |
| Students | `/admin/students` | Searchable, paginated student roster; one-click CSV export |
| Student Detail | `/admin/students/{id}` | Full account info, academic profile, and complete course list for any student |
| Requirements Editor | `/admin/requirements` | Edit liberal arts core, business core, and all specialization course lists (backed by `requirements.json`) |
| System Prompt | `/admin/system-prompt` | Live character/token counter, save with auto version archiving, restore any of 10 recent versions |
| Users | `/admin/users` | Change any user's role (student/dean/admin) via AJAX dropdown |
| Statistics | `/admin/stats` | Degree breakdown, specialization breakdown, 6-month bar chart of new registrations |

### Code Location

All admin code is preserved in the repository:

```
app/Http/Controllers/AdminController.php   # All admin controller methods
app/Http/Middleware/EnsureDean.php         # Role-based access guard
resources/views/admin/                     # All admin Blade views
  ├── layout.blade.php                     # Admin shell (sidebar + topbar)
  ├── dashboard.blade.php
  ├── requirements.blade.php
  ├── stats.blade.php
  ├── system-prompt.blade.php
  ├── users.blade.php
  └── students/
      ├── index.blade.php
      └── show.blade.php
storage/app/requirements.json             # Editable degree requirements data
storage/app/system_prompt.txt             # AI system prompt (versioned)
```

### How to Re-enable

Re-add the admin route group to `routes/web.php`:

```php
// ── Admin Routes ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'dean'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::get('/students/export', [AdminController::class, 'exportStudentsCsv'])->name('students.export');
    Route::get('/students/{user}', [AdminController::class, 'studentProfile'])->name('students.show');
    Route::get('/requirements', [AdminController::class, 'requirements'])->name('requirements');
    Route::post('/requirements', [AdminController::class, 'saveRequirements'])->name('requirements.save');
    Route::get('/system-prompt', [AdminController::class, 'systemPrompt'])->name('system-prompt');
    Route::post('/system-prompt', [AdminController::class, 'saveSystemPrompt'])->name('system-prompt.save');
    Route::post('/system-prompt/restore', [AdminController::class, 'restoreSystemPrompt'])->name('system-prompt.restore');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
    Route::get('/stats', [AdminController::class, 'stats'])->name('stats');
});
```

### Planned Redesign — Requirements Editor

The requirements editor redesign was in progress when the admin panel was taken offline. The planned new design includes:

**Chip/Pill Tag Editor for Specialization Courses**
- Accordion cards — one per specialization
- Chip tags for required and elective courses (click × to remove, type to add)
- Editable specialization name field
- Add/remove entire specialization via + button and × button
- Toggle flags: Must Double Specialize, MATH 111 Required
- Notes textarea per specialization

**Business Core Options Chip Editors**
- Info Gateway options — chip editor
- Business Ethics options — chip editor
- Business Law options — label-aware chip editor (code + display label)

**Accounting Courses Row Editor**
- Add/edit/remove rows in the accounting courses table
- Fields per row: Code, Name, Prerequisite, Semester
- Separate chip editor for accounting electives

**Save Behavior**
- Single Save button updates both the structured wizard data AND auto-regenerates the AI narrative `courses` text arrays
- No separate save per section

**Alpine.js Architecture**
- `specEditor(slug, year, data)` — per-accordion Alpine component
- `chipEditor(items)` — reusable chip list component
- `lawEditor(items)` — extended chip editor with code+label pairs
- `acctEditor(rows)` — table row editor component
- Hidden form inputs generated dynamically from Alpine state

**Backend Changes Needed**
- New `saveRequirements` logic to handle structured form: `specs[year][slug][name]`, `specs[year][slug][required][]`, `specs[year][slug][electives][]`, etc.
- Auto-generate `courses` text array from structured data on save
- New routes (optional): `POST /admin/requirements/spec/add`, `POST /admin/requirements/spec/remove`

---

## Other Planned Features

### Milestone 10 — Advanced AI Features
- Bot detects catalog year automatically from uploaded APW
- Bot generates a personalized semester-by-semester 4-year plan
- Bot flags prerequisite conflicts automatically
- Bot calculates exact credits remaining to graduation
- Bot identifies the fastest path to graduation
- Bot suggests which electives complement a student's specialization

### Demo & Submission
- 5 student persona walkthrough recordings
- One-page project summary PDF
- 20-scenario scripted test suite
