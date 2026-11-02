# MedTech Design System Proposal

## Direction

Use a distinct enterprise healthcare visual language: navy (#12304A), action blue (#1769AA), care teal (#159A9C), soft blue surface (#F7FAFC), white cards, and semantic green/amber/red status colors. Existing `medtech-app.css` and `medtech-enterprise.css` establish this direction but currently coexist with legacy stylesheet layers; the React redesign should make one token system canonical.

## Typography and spacing

- Font stack: Inter/system UI for application UI; no decorative display font in portals.
- Type scale: 12, 14, 16, 20, 28, 36px; line-height at least 1.45 for body text.
- 4px spacing base; component spacing 8/12/16/24/32/48px.
- 44px minimum pointer target; visible keyboard focus; error text tied to fields.

## Shared shell

- Desktop: 244px navigation rail, 72px compact header, fluid main workspace.
- Tablet: collapsible rail with persistent context/header.
- Mobile: drawer navigation, sticky compact header, one-column content.
- Header: brand, portal context, notification count, profile menu; no oversized avatar or blank region.

## Components

- Page header: eyebrow, title, concise purpose, primary action.
- KPI cards: label, value, comparison/context, no misleading charts.
- Data table: sticky/clear header, status pill, row actions, responsive scroll with cue.
- Form card: labelled inputs, helper/error text, footer actions, unsaved-state handling.
- Timeline: dates, author, source, immutable/audited events.
- States: intentional empty, skeleton loading, retryable error, permission-denied explanation.

## Accessibility baseline

Target WCAG 2.2 AA: semantic landmarks, keyboard navigation, focus visibility, color-independent status, contrast verification, accessible labels, screen-reader table names, print styles, and motion reduction support. Validate with manual keyboard and screen-reader tests before launch.

## Role variations

Patient: reassuring, task-led; Doctor: patient-context-first; Administrator: operations/exceptions-first. The same shell, tokens, buttons, and tables apply to all roles.

## Revision note — 2026-11-02

Screen-specific desktop/mobile layouts and state requirements are now defined in `MEDTECH_UI_WIREFRAMES.md`; this document remains the shared-token and accessibility specification.
