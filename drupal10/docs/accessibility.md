# Accessibility basics (WCAG 2.1 AA) for municipal websites

Italian public administration sites must comply with the *Legge Stanca*
(L. 4/2004) and the AgID guidelines, which reference **EN 301 549 → WCAG 2.1
level AA**. Each site publishes an *accessibility statement* (dichiarazione di
accessibilità) on AgID's form every year by 23 September.

Bootstrap Italia components are designed to be accessible, but the markup we
write around them can still break accessibility. What this theme does, and
what to check:

| WCAG principle | Requirement (AA) | In this project |
|---|---|---|
| Perceivable | Text alternatives (1.1.1) | Decorative SVG icons have `aria-hidden="true"`; "Leggi di più" links add a visually hidden title |
| Perceivable | Contrast ≥ 4.5:1 for text, 3:1 for large text/UI (1.4.3, 1.4.11) | Bootstrap Italia palette (#0066CC on white = 5.6:1) |
| Perceivable | Reflow at 320px, text resizable to 200% (1.4.10, 1.4.4) | Responsive grid, rem units |
| Operable | Everything usable with the keyboard, visible focus (2.1.1, 2.4.7) | Native links/buttons only, BI focus styles kept |
| Operable | Skip link to main content (2.4.1) | Drupal "Skip to main content" → `#main-content` |
| Operable | Page titles and meaningful link text (2.4.2, 2.4.4) | Per-page `<title>`; no bare "click here" |
| Understandable | Page language (3.1.1) | `<html lang>` from Drupal |
| Understandable | Form labels and error messages (3.3.1, 3.3.2) | Drupal Form API labels; exposed filter has a `<label>` |
| Robust | Valid semantics, name/role/value (4.1.2) | Landmarks `header`, `nav[aria-label]`, `main`, `footer`; `aria-current="page"` in menu and breadcrumb |

## How to test

- **Automatic**: axe DevTools or Lighthouse in the browser, `pa11y` in CI.
  They find ~30–40% of issues.
- **Keyboard**: unplug the mouse — Tab through the page; the focus must always be visible and in a logical order.
- **Screen reader**: NVDA (Windows) or VoiceOver (macOS): headings list, landmarks, form labels.
- **Zoom** to 200% and a 320px wide window.
- **Content**: editors must write alt text, use real headings (H2/H3) and avoid PDFs that are only scans.
