# Frontend Design Skill

## When This Skill Activates
Activate for any UI work: HTML, CSS, Tailwind, Blade templates, React, Vue, Alpine.js, visual redesigns, component styling, landing pages, dashboards, chat interfaces, forms, navigation, typography, color, spacing, animations.

## Core Philosophy
Design for humans, not algorithms. Every element must feel intentional, crafted, and purposeful. Professional UI is invisible — users notice the content, not the interface.

## Universal Design Principles

### Spacing
- Use a consistent spacing system: multiples of 4px (4, 8, 12, 16, 24, 32, 48, 64, 96)
- Whitespace is not empty space — it is a design element
- More whitespace = more premium feel
- Consistent padding within components, consistent margins between them

### Typography
- Maximum 3 font sizes per section
- Headlines: bold, high contrast, purposeful size hierarchy
- Body: minimum 16px, line-height 1.6-1.8, never cramped
- Labels/metadata: smaller, lighter weight, muted color
- Never use placeholder fonts — always specify a font stack

### Color
- Every color must have a purpose (primary action, warning, success, neutral)
- Maximum 3-4 colors in any one view
- Always check contrast ratios: 4.5:1 minimum for body text, 3:1 for large text
- Dark backgrounds need light text — never dark on dark
- Light backgrounds need dark text — never light on light

### Components
- Buttons: clear purpose, consistent padding (px-5 py-2.5 minimum), visible on any background
- Cards: consistent border-radius, subtle shadow (shadow-sm or shadow-md only)
- Forms: labels above inputs always, clear focus states, helpful error messages
- Navigation: clear active states, consistent hover behavior
- Avatars/icons: use sparingly, must add meaning not decoration

### What Looks Professional
- Consistent spacing between every similar element
- One clear visual hierarchy per page (what is most important?)
- Limited color palette used with intention
- Typography that guides the eye naturally
- Interactions that feel responsive (hover states, transitions max 200ms)
- Mobile-first responsive layouts

### What Looks Amateurish (avoid always)
- Em dashes in UI copy — use commas or rewrite
- Markdown bold (**text**) in displayed content — strip before rendering
- Too many font sizes competing for attention
- Buttons that blend into backgrounds
- Low contrast text (white on light, dark on dark)
- Left borders on chat bubbles (dated pattern)
- Excessive shadows, gradients, or rounded corners
- Generic hero sections with diagonal CSS patterns overused
- Inconsistent spacing (some gaps 12px, others 20px, others 8px)
- Too many CTAs competing on one screen
- Footer text illegible due to low contrast

## Project-Specific Brand Configuration
When working on a project, check for a brand config at the top of the file or in a brand.md file. If none exists, ask the user for:
- Primary color (main actions, headers)
- Accent color (highlights, CTAs)
- Background color preference (light/dark/neutral)
- Font preferences (serif/sans-serif/mixed)
- Tone (corporate/friendly/academic/startup)

## CUA / Busch School Brand Config (active for this project)
- Primary Blue: #003366
- Cardinal Red: #B41100
- Gold: #C9A84C
- Dark Navy: #071e38
- Limestone: #efebe9
- Fonts: Oswald Bold (headlines), Crimson Text (body), Roboto (UI)
- Tone: Academic, trustworthy, premium university

## Process Before Every UI Change
1. Read the existing file completely — understand what exists
2. Identify specifically what is broken or needs improvement
3. Make the minimum effective change — do not redesign what works
4. Check contrast of all text after changes
5. Run npm run build after any Tailwind changes
6. Test mentally at mobile (375px) and desktop (1280px)
7. Commit with a clear descriptive message
8. Never leave uncommitted changes

## Red Flags — Stop and Ask Before Proceeding
- Changing overall page layout when only copy was requested
- Removing functionality while improving aesthetics
- Adding new dependencies for minor visual effects
- Changing color values outside the brand palette without permission
