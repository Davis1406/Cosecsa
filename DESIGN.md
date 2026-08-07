# COSECSA-MIS Design System

Design reference for the COSECSA Management Information System (`cosecsamis.org`).  
Built on **AdminLTE 3** + **Bootstrap 4** with a custom layer (`dist/css/custom.css`) that implements the Stitch design system in light mode and fixes dark-mode contrast.

---

## 1. Brand Colours

| Token | Hex | Usage |
|---|---|---|
| **Maroon / Primary** | `#a02626` | CTA buttons, active nav links, focus rings, brand heading |
| **Maroon Dark** | `#870f0f` | Hover state on primary buttons |
| **Maroon Deep** | `#7f0a12` | Hover state on links |
| **Gold / Accent** | `#FEC503` | Secondary accent, badges, notification dots |
| **Teal** | `#004356` | Tile accent (hospitals, primary stats) |

```
Maroon  ████  #a02626
Gold    ████  #FEC503
Teal    ████  #004356
```

---

## 2. Neutral Palette

### Light Mode

| Token | Hex | Role |
|---|---|---|
| **Ink** | `#141d23` | Body text, headings |
| **Slate** | `#2d3748` | Nav link text, secondary text |
| **Warm Grey** | `#59413f` | Tile labels, muted body text |
| **Mid Grey** | `#6b7280` | Nav icons, section labels |
| **Muted** | `#adb5bd` | Disabled text, pagination disabled |
| **Border** | `#e0e9f2` | Sidebar borders, card dividers |
| **Surface 1** | `#ecf5fe` | Navbar search bg, nav hover bg |
| **Surface 2** | `#e6eff8` | Hover states, subtle fills |
| **Scroll Track** | `#dbe4ed` | Sidebar scrollbar thumb |
| **White** | `#ffffff` | Card bg, sidebar bg, navbar bg |
| **Navbar bg** | `rgba(246,250,255,0.88)` | Frosted-glass navbar (backdrop-filter) |

### Dark Mode

| Token | Hex | Role |
|---|---|---|
| **Page bg** | `#141921` | `content-wrapper` background |
| **Card bg** | `#1e2330` | Card surfaces |
| **Card header** | `#252c3b` | Card header strip |
| **Input bg** | `#2b3040` | Form controls at rest |
| **Input focus bg** | `#323849` | Form controls on focus |
| **Surface** | `#374151` | Input groups, file-upload button |
| **Tile bg** | `#2d3748` | `.stitch-tile` dark variant |
| **Search dropdown** | `#2b2d42` | Global search results panel |
| **Dropdown hover** | `#3a2a3a` | Search result hover |
| **Border subtle** | `#2d3748` | Card borders |
| **Border input** | `#4a5568` | Form control borders |
| **Border divider** | `#3d3f57` | Search section separators |
| **Border disabled** | `#374151` | Disabled input border |

### Dark Mode Text

| Token | Hex | Role |
|---|---|---|
| **Body text** | `#e2e8f0` | Primary readable text, input values |
| **Heading text** | `#f1f5f9` | High-contrast headings, input focus text |
| **Label text** | `#cbd5e0` | Form labels |
| **Muted text** | `#94a3b8` | Subtitles, empty states, disabled |
| **Placeholder** | `#718096` | Input placeholders |
| **Link / accent** | `#f48a8a` | Links and table anchors in dark mode |
| **Link hover** | `#ffb3b3` | Link hover in dark mode |
| **Tile label** | `#9ca3af` | `.stitch-tile-label` in dark mode |
| **Gold text** | `#3a2a00` | Text on gold badge background |

---

## 3. Tile / Stat Card Variants (`.stitch-tile`)

Used on dashboards as drop-in replacements for AdminLTE `.info-box`.

| Class | Border + Fill Colour |
|---|---|
| `.stitch-tile-teal` | `#004356` |
| `.stitch-tile-maroon` | `#a02626` |
| `.stitch-tile-gold` | `#FEC503` |
| `.stitch-tile-green` | `#28a745` |
| `.stitch-tile-blue` | `#2980b9` |

Tile anatomy: `stitch-tile-label` → `stitch-tile-value` → `stitch-tile-trend` → `stitch-tile-bar` / `stitch-tile-fill`.

```html
<div class="stitch-tile stitch-tile-teal">
  <div class="stitch-tile-label">Total Hospitals</div>
  <div class="stitch-tile-value">154</div>
  <div class="stitch-tile-trend">↑ 4%</div>
  <div class="stitch-tile-bar"><div class="stitch-tile-fill" style="width:75%"></div></div>
</div>
```

---

## 4. Typography

| Property | Value |
|---|---|
| **Primary font** | `Source Sans 3` (Google Fonts) |
| **Fallback** | `sans-serif` |
| **Weights loaded** | 300, 400, 500, 600, 700, 900 |
| **Body size** | Browser default (16 px base) |
| **Navbar brand** | `0.92rem`, weight 700, letter-spacing `0.2px` |
| **Nav links** | `0.875rem`, weight 500 |
| **Sub-nav links** | `0.845rem` |
| **Nav icons** | `0.88rem` |
| **Section labels** | `0.6rem`, weight 700, uppercase, letter-spacing `1.4px` |
| **Tile label** | `0.78rem`, weight 600, uppercase |
| **Tile value** | `2rem`, weight 700, letter-spacing `-0.02em` |
| **Tile trend** | `0.75rem`, weight 600 |
| **Badges** | `0.6rem` |

---

## 5. Spacing & Shape

| Token | Value | Usage |
|---|---|---|
| Border radius — small | `4px` | Buttons, small chips |
| Border radius — default | `6px` | Navbar icon buttons |
| Border radius — card | `8px` | Cards, tiles, brand image |
| Border radius — pill | `9999px` | Notification badge |
| Active nav border | `3px solid #a02626` | Left border on active link |
| Tile accent border | `4px solid` | Left border on `.stitch-tile` |
| Sidebar padding | `9px 18px` | Nav link padding |
| Brand padding | `14px 18px` | `.brand-link` |

---

## 6. Shadows

| Use | Value |
|---|---|
| Card / tile default | `0 1px 4px rgba(0,0,0,0.07)` |
| Card / tile hover | `0 4px 12px rgba(0,0,0,0.09)` |
| Tile hover (large) | `0 5px 16px rgba(0,0,0,0.10)` |
| Sidebar | `1px 0 0 #e0e9f2, 2px 0 6px rgba(0,0,0,0.04)` |
| Navbar | `0 1px 0 rgba(0,0,0,0.04)` |
| Dark tile | `0 1px 4px rgba(0,0,0,0.20)` |
| Dark search dropdown | `0 4px 20px rgba(0,0,0,0.40)` |
| Focus ring (primary) | `0 0 0 0.2rem rgba(160,38,38,0.25)` |

---

## 7. Interactive States

### Buttons

| State | Style |
|---|---|
| Primary default | bg `#a02626`, white text |
| Primary hover | bg `#870f0f`, gold text `#FEC503` |
| Secondary default | bg `#6c757d`, white text |
| Disabled | bg `#adb5bd` |

Primary button class: `.btn-custom` (defined in auth pages).  
Standard pages use AdminLTE `.btn-primary` overridden with maroon via `!important`.

### Nav Links

| State | Background | Left border | Text |
|---|---|---|---|
| Default | — | transparent | `#2d3748` |
| Hover | `#ecf5fe` | `rgba(160,38,38,0.35)` | `#141d23` |
| Active (top) | `rgba(160,38,38,0.07)` | `#a02626` 3px | `#a02626`, weight 700 |
| Active (sub) | `rgba(160,38,38,0.06)` | `#a02626` 3px | `#a02626`, weight 600 |

### Form Controls (Dark Mode)

| State | Background | Border | Text |
|---|---|---|---|
| Default | `#2b3040` | `#4a5568` | `#e2e8f0` |
| Focus | `#323849` | `#a02626` | `#f1f5f9` |
| Disabled / readonly | `#1e2330` | `#374151` | `#94a3b8` |
| Placeholder | — | — | `#718096` |

### Pagination (Light Mode)

| State | Text | Background | Border |
|---|---|---|---|
| Default | `#a02626` | — | `#dee2e6` |
| Hover | `#7f0a12` | `#f8d0d0` | `#dee2e6` |
| Active | `#ffffff` | `#a02626` | `#a02626` |
| Disabled | `#adb5bd` | — | — |

---

## 8. Wizard / Multi-Step Form

Defined in `dist/css/wizard.css`.

| Token | Hex | Usage |
|---|---|---|
| Step circle bg | `#eaf0f4` | Inactive step |
| Step circle text | `#a1a7ac` | Inactive step number |
| Step label | `#99a2a8` | Inactive step label |
| Step line | `#d8e1e7` | Connector line |
| Active step | `#a02626` | Active circle bg |
| Active label | `#a02626` | Active step label |
| Form surface | `#f6f9fb` | Wizard fieldset bg |
| Input border | `#5f6771` | Wizard inputs |
| Input text | `#3f4553` | Wizard input values |
| Placeholder | `#405867` → muted | Wizard placeholder |

---

## 9. Component-Specific Notes

### Sidebar
- Light mode: white bg, subtle shadow, Source Sans 3.
- Dark mode: AdminLTE default (dark slate), no custom overrides needed.
- Scrollbar: `5px` thin, thumb `#dbe4ed`.

### Navbar
- Light mode: frosted glass (`rgba(246,250,255,0.88)` + `backdrop-filter: blur(18px)`).
- Dark mode: AdminLTE default.

### DataTables
- Hidden (`opacity: 0`) until JS initialises, then faded in (`transition: opacity 0.38s`).
- Column visibility button: bg `#a02626`, white text.
- IDs managed: `#traineestable`, `#candidatestable`, `#fellowstable`, `#alumnitable`, `#trainerstable`, `#examinerstable`, `#examinerconfirmationtable`, `#memberstable`, `#crstable`, `#hospitalTable`, `#resultstable`, `#adminresultstable`, `#gsresultstable`, `#fcsresultstable`, `#hospitalProgrammesTable`.

### Impersonation Banner
- Bg `#a02626`, white text, `z-index: 1051`.
- Shown when `session('impersonator_id')` is set.

### `.cosecsa-link`
- Light: `#a02626`, hover `#7f0a12`.
- Dark: `#f48a8a`, hover `#ffb3b3`.
- Used on table row links and inline references.

---

## 10. External Dependencies

| Library | Version | Source |
|---|---|---|
| Bootstrap | 4.6.2 | `cdn.jsdelivr.net` |
| AdminLTE | 3.x | local `dist/` |
| Source Sans 3 | latest | `fonts.googleapis.com` |
| FontAwesome Free | 5.x | local `plugins/fontawesome-free/` |
| Select2 | 4.1.0-rc.0 | `cdn.jsdelivr.net` |
| Tom Select | 2.0.0-rc.4 | `cdn.jsdelivr.net` |
| DataTables + plugins | latest | local `plugins/datatables*/` |
| Chart.js | latest | `cdn.jsdelivr.net` |
| chartjs-plugin-datalabels | 2.2.0 | `cdn.jsdelivr.net` |
| FullCalendar | 3.10.2 | `cdnjs.cloudflare.com` |
| Summernote | latest | local `plugins/summernote/` |
| Tempus Dominus | 5.39.0 | `cdnjs.cloudflare.com` |
| Moment.js | local | `plugins/moment/` |
| overlayScrollbars | local | `plugins/overlayScrollbars/` |

---

## 11. File Map

```
public/
  dist/
    css/
      adminlte.min.css   ← AdminLTE base theme
      custom.css         ← Stitch design system overrides (THIS FILE)
      wizard.css         ← Multi-step form wizard styles
    js/
      adminlte.js
      custom.js
      pages/
        dashboard.js
        wizard.js
    img/
      Cosecsa_Logo.png   ← Favicon + brand image
  plugins/
    fontawesome-free/    ← Icons (local, avoids CDN CORS)
    bootstrap/
    jquery*/
    datatables*/
    summernote/
    ...

resources/views/
  layout/
    app.blade.php        ← Master layout (loads all CSS/JS)
    header.blade.php     ← Navbar + sidebar
    footer.blade.php     ← Closing scripts section
  auth/
    login.blade.php
    role-selection.blade.php

app/Http/Middleware/
  SecureHeaders.php      ← Sets CSP, HSTS, X-Frame-Options
  RedirectWww.php        ← 301 www → apex redirect
  TrustProxies.php       ← Trusts all proxies for HTTPS detection
```
