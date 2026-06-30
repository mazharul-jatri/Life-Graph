# Life Graph Engine — Project Specification

> A personal life-trajectory visualization and decision-simulation platform.
> Built for personal use first, structured for future SaaS release.

---

## 1. Vision

Visualize a person's entire life journey (0–70+ years) across multiple dimensions
(career, finance, health, relationships), benchmark it against population averages,
and simulate future "what-if" decision branches (the butterfly effect) to project
where different choices lead by end-of-life.

**Core value prop:** Decision-clarity tool, not a fortune-teller. The simulation
engine should make tradeoffs visible and reasoned, not predict exact numbers.

---

## 1.1 What Makes This Worth Paying For

A graph of someone's life is a novelty for one viewing. For people to pay,
the product has to deliver an outcome, not just a visual. Keep these
principles in mind through every build phase:

- **Clarity over prediction.** The product's real value is forcing the user
  to articulate decisions and tradeoffs explicitly (writing down "quit
  smoking at 30" with a defined modifier), not claiming to predict the
  future accurately. Market it as a decision-clarity tool.
- **Actionability.** Each insight the dashboard surfaces should suggest a
  next action, not just a static chart. E.g. "Your finance curve is below
  benchmark for your age — common decision points that helped others in
  this range: [list from decision_templates]."
- **Recurring value, not one-time.** A user who logs events once and never
  returns won't pay monthly. Build in reasons to come back: periodic
  check-ins, milestone reminders, "update your trajectory" nudges.
- **Comparison is the hook, but honesty builds trust.** Be transparent
  about `confidence` levels on benchmark data (section 4.4) — overstating
  certainty will damage credibility once users realize career/finance
  projections are estimates, not facts.
- **Personal-use-first is the right call.** Build this for yourself,
  validate it changes how you think about your own decisions, before
  investing in onboarding/payments for others — consistent with your
  validate-before-build approach on other projects.

---

## 2. Core Features (MVP Scope)

Based on product decisions made:

1. **Past + Future Decision Simulation** — log real past events, simulate future
   branches
2. **"What-if" Decision Simulator** — branch points where user picks Path A vs
   Path B, each with projected outcomes
3. **Comparison Engine** — user's curve vs population average curve, by age
4. **Multi-dimensional tracking** — Career, Finance, Health, Relationships
   (separate curves)
5. **Composite Life Index** — single weighted score blending all 4 dimensions
6. **Personal-use first** — single-user mode in MVP, multi-tenant architecture
   prepared for future SaaS release

### Out of scope for MVP (future phases)
- Multi-user / public SaaS onboarding
- Payment/subscription system
- Social sharing of life graphs
- Native mobile app — responsive web only, must function correctly on all
  screen sizes; no separate mobile build planned
- AI-generated personalized predictions (start with rule-based modifiers)

---

## 2.1 Admin Control Panel (Core Requirement, Not an Add-on)

No content or configuration should be hardcoded in the codebase. Every
visible/configurable element must be manageable from a control panel,
accessible only to the super admin (single user — you) in MVP, with the
schema designed so role-based multi-admin access can be added later without
rework.

**Manageable from the control panel:**

- **Branding** — app name, logo upload, favicon, color theme (primary/
  secondary/accent colors, applied via CSS variables so the whole UI
  re-themes from one config change)
- **Dimensions** — labels, colors, default weights for the composite Life
  Index (the `Dimension` table from section 3.1, fully CRUD-able)
- **Benchmark data** — view/edit/add `BenchmarkDataset` rows manually,
  in addition to the scheduled import job (manual override matters when
  imported data is wrong or missing for a region)
- **Decision templates** — manage preset decision modifiers (e.g. "quit
  smoking", "career switch") so new ones can be added without code changes
- **General settings** — site metadata, default country/locale, units
  (currency, measurement) used across the app

**Architectural implication:** introduce a `Setting` key-value table
(`settings: key, value, type, group`) for simple config (app name, colors,
locale), separate from the structured tables (`Dimension`,
`BenchmarkDataset`, decision templates) which already support CRUD via
their own admin screens. Frontend should read branding/theme settings on
load and apply them globally (e.g. inject CSS variables), not hardcode
colors/labels in components.

---

## 3. Domain Model

### 3.1 Core Entities

**User**
- Single user in MVP, but schema supports multi-tenancy from day one
  (`users` table + `user_id` FK everywhere)

**LifeEvent** (the past — actual things that happened)
```
id
user_id
title                 // "Got married", "Started smoking", "Promoted to Senior"
description
event_date
dimension             // enum: career | finance | health | relationship
impact_score          // -10 to +10, magnitude of impact
tags                  // json array, e.g. ["marriage", "milestone"]
created_at / updated_at
```

**Dimension** (lookup/config table)
```
id
key                   // career | finance | health | relationship
label
weight                // default weight in composite Life Index calc (user-adjustable)
color                 // for chart rendering
```

**DecisionNode** (branch points — past OR future)
```
id
user_id
title                 // "Quit smoking at 30"
decision_age          // age at which decision applies
status                // enum: taken | hypothetical | rejected
parent_decision_id     // nullable, for chained decisions
dimension_modifiers    // json: { career: +2, health: +8, finance: -1 }
notes
created_at / updated_at
```

**DecisionPath** (a simulated branch — collection of decisions + resulting projection)
```
id
user_id
name                  // "Path A: Continue smoking", "Path B: Quit at 30"
root_decision_id
is_baseline           // bool, marks the "current trajectory" path
projected_outcomes    // json, cached calculated curve (see 4.2)
created_at / updated_at
```

**Setting** (control panel key-value config — branding, theme, locale)
```
id
key                   // e.g. "app_name", "logo_url", "color_primary"
value
type                  // string | color | image | number | boolean | json
group                 // branding | theme | locale | general
updated_at
```

**BenchmarkDataset** (population averages — reference data, not user-specific)
```
id
country_code          // ISO 3166-1 alpha-2, e.g. "BD", "US"; nullable = global default
region_code            // optional state/province code, nullable for most countries
dimension
age
average_value          // normalized score or real metric (net worth, etc.)
unit                    // e.g. "USD", "score_0_100", "years"
source                  // citation/reference for the data
confidence              // enum: high | medium | low — see section 9.1
fetched_at              // when this row was last imported from source
```

**Lookup priority when resolving a benchmark value for a user:**
`region_code match → country_code match → global default`
This way the schema supports state-level data wherever it exists, without
blocking the app when it doesn't.

**LifeSnapshot** (cached/computed yearly rollup per user, for fast chart rendering)
```
id
user_id
age
career_score
finance_score
health_score
relationship_score
life_index            // composite
is_projected          // bool — false for past (from real events), true for future (simulated)
path_id               // nullable FK to DecisionPath, only for projected data
```

### 3.2 Relationships
```
User 1---N LifeEvent
User 1---N DecisionNode
User 1---N DecisionPath
DecisionPath 1---N LifeSnapshot (projected)
User 1---N LifeSnapshot (actual, path_id null)
DecisionNode N---1 DecisionNode (self-referential, parent_decision_id)
```

---

## 4. Calculation Engine (The Core Logic)

This is the most important architectural piece — keep it as an isolated,
testable service layer, NOT mixed into controllers.

### 4.1 Past Curve Calculation
For each year of the user's life:
1. Pull all `LifeEvent` records up to that age
2. Apply each event's `impact_score` to its `dimension`, with a decay/persistence
   function (an event's impact doesn't stay at full strength forever — model as
   exponential decay or step-function plateau, your choice)
3. Sum into a per-dimension score per age → store in `LifeSnapshot`
4. Calculate composite `life_index` = weighted sum of 4 dimension scores

### 4.2 Future Simulation (Decision Branching)
1. Start from current age's snapshot (baseline state)
2. For each `DecisionPath`, walk its chain of `DecisionNode`s in age order
3. At each decision node, apply `dimension_modifiers` to the running projection
4. Apply baseline "natural drift" per dimension (e.g. health naturally declines
   after 40 without intervention — define these as configurable base curves)
5. Cache the resulting yearly snapshots in `LifeSnapshot` with `path_id` set
6. Multiple paths can be computed and compared side-by-side on the same chart

### 4.3 Benchmark Comparison
Simple lookup: for the user's current age and dimension, fetch
`BenchmarkDataset` row and overlay as a reference line on the chart.

**Recommendation on data sourcing:** Be transparent in the UI about which
benchmark numbers are well-sourced (e.g. life expectancy impact of smoking —
solid public health research) vs. rough estimates (e.g. "career growth by age" —
highly contextual). Tag each `BenchmarkDataset` row with a `confidence` level.

### 4.4 Benchmark Data Sourcing Strategy

**Pattern: scheduled import, never live API calls during user requests.**

```
Public Data Sources → Artisan import command (scheduled monthly/quarterly)
                     → BenchmarkDataset table
                     → App reads from local DB only at runtime
```

This data changes slowly (yearly at most) and live-fetching it on every
request would add latency, break offline use, and risk rate limits once
multi-user. Treat external sources as a periodic ETL input, not a runtime
dependency.

**Recommended sources by dimension:**

| Dimension | Source | Granularity |
|---|---|---|
| Health / life expectancy | WHO Global Health Observatory API, World Bank Open Data API | Country-level, reliable |
| Finance / income | National statistics bureaus (e.g. BBS for Bangladesh), World Inequality Database | Country-level mostly; state-level only for a few countries (US, India) |
| Lifestyle impact modifiers (smoking, exercise, etc.) | WHO / CDC published research | Global, not country-specific — apply as universal modifiers |
| Career growth | No reliable public dataset exists | Approximate manually or crowdsource later; flag as `confidence: low` |

**Geographic granularity decision: country-level for MVP, schema-ready for
state-level.** Build `BenchmarkDataset` with an optional `region_code` field
(see section 3.1) so state/province data can be added later, but only
populate country-level rows initially. State-level public data is only
clean and available for a handful of countries (US, India, a few others) —
for most countries, including Bangladesh, reliable district/state-level
data doesn't exist in usable public form. Chasing that now would stall
MVP progress without a real payoff.

**Fallback chain when no data exists for a user's country:** use a
`country_code = NULL` "global average" row as last resort, and clearly
label it in the UI as a global estimate rather than presenting it as
localized data — this matters for trust/accuracy with international users
later.

### 4.5 Important Design Principle

Keep modifier rules **data-driven, not hardcoded**. Store decision modifier
templates in a `decision_templates` table (e.g. "smoking cessation", "marriage",
"career switch") so users (and you, building this for others later) can pick
from realistic presets instead of guessing numbers from scratch.

---

## 5. Tech Stack

### Backend — Laravel
- **Laravel 11.x** (matches your existing experience)
- **Laravel Sanctum** for auth (lightweight, fine for single-user MVP, scales
  to SPA + multi-user later)
- **MySQL 8.x** as primary DB
- **Redis** for caching computed `LifeSnapshot` projections (you already use
  Redis — reuse that experience)
- **Laravel Queues** for recalculating snapshots in the background when a
  user edits historical events (avoid blocking requests on recalculation)

### Frontend — Vue.js
- **Vue 3** + Composition API
- **Pinia** for state management
- **Vue Router**
- **Axios** for API calls

### Charting library — recommendations

| Library | Best for | Notes |
|---|---|---|
| **Apache ECharts (vue-echarts)** | **Top pick.** Branching timelines, multi-series line charts, zoomable timeline, rich tooltips | Most powerful free option, excellent for your branching "path comparison" visual, large community |
| **D3.js** | Full custom control (e.g. custom butterfly-effect branch visuals) | Steeper learning curve, use only if ECharts can't do a specific custom visual you want |
| **Chart.js (vue-chartjs)** | Simpler line/bar charts | Good fallback for simpler dashboard widgets, less suited to branching paths |
| **ApexCharts (vue3-apexcharts)** | Clean default styling, good zoom/pan | Good alternative to ECharts, slightly less flexible for custom branch rendering |

**Recommendation: Apache ECharts.** It natively supports the kind of
multi-line, zoomable, annotated timeline you need (life curve + benchmark line
+ multiple decision-path branches on one chart), and has good Vue 3 bindings
via `vue-echarts`.

### Other useful packages

**Backend (Laravel)**
- `spatie/laravel-data` — clean DTOs for passing structured event/decision data
  between layers (very useful given your complex nested JSON structures)
- `spatie/laravel-query-builder` — for filtering events/timeline by dimension,
  date range, etc.
- `laravel/horizon` — if you want visibility into the queue jobs recalculating
  snapshots
- `barryvdh/laravel-debugbar` — dev only, query/performance debugging

**Frontend (Vue)**
- `vue-echarts` — ECharts Vue 3 wrapper
- `dayjs` — lightweight date handling (age calculations, timeline math)
- `vuetify` or `tailwindcss` — pick one for UI; given you'll want a polished
  dashboard-style UI eventually, Tailwind + a component kit (e.g. shadcn-vue)
  gives more design control than Vuetify
- `vee-validate` + `zod` — form validation for event/decision entry forms

---

## 6. Suggested Architecture (Folder Structure)

```
app/
  Domain/
    LifeGraph/
      Models/
        LifeEvent.php
        DecisionNode.php
        DecisionPath.php
        LifeSnapshot.php
        BenchmarkDataset.php
      Services/
        TimelineCalculatorService.php   // 4.1 logic
        SimulationEngineService.php     // 4.2 logic
        BenchmarkComparisonService.php  // 4.3 logic
        LifeIndexCalculator.php         // composite score logic
      DataTransferObjects/
        EventData.php
        DecisionModifierData.php
      Jobs/
        RecalculateLifeSnapshotsJob.php
  Http/
    Controllers/Api/
      LifeEventController.php
      DecisionNodeController.php
      DecisionPathController.php
      TimelineController.php          // returns chart-ready data
      BenchmarkController.php
    Controllers/Admin/
      SettingController.php           // branding, theme, locale (Setting table)
      DimensionController.php         // manage dimensions/weights
      BenchmarkAdminController.php    // manual edit/add of BenchmarkDataset
      DecisionTemplateController.php  // manage decision presets

resources/js/  (or separate Vue SPA repo)
  components/
    admin/
      BrandingSettings.vue
      ThemeColorPicker.vue
      DimensionManager.vue
      BenchmarkDataManager.vue
      DecisionTemplateManager.vue
    charts/
      LifeTimelineChart.vue
      PathComparisonChart.vue
      DimensionBreakdownChart.vue
    events/
      EventForm.vue
      EventList.vue
    decisions/
      DecisionNodeForm.vue
      DecisionPathBuilder.vue
  stores/
    timeline.store.js
    decisions.store.js
  views/
    Dashboard.vue
    Timeline.vue
    Simulator.vue
```

---

## 7. API Endpoints (MVP)

```
GET    /api/timeline                    // full computed life snapshots (past)
GET    /api/timeline/benchmark          // benchmark comparison overlay

POST   /api/events                      // create life event
GET    /api/events
PUT    /api/events/{id}
DELETE /api/events/{id}

POST   /api/decision-nodes
GET    /api/decision-nodes
PUT    /api/decision-nodes/{id}

POST   /api/decision-paths              // create a "what-if" path
GET    /api/decision-paths
GET    /api/decision-paths/{id}/projection   // computed future curve for this path
POST   /api/decision-paths/compare           // pass multiple path IDs, get combined chart data

GET    /api/dimensions
GET    /api/life-index                  // current composite score

// Admin (super admin only, single user in MVP)
GET/PUT  /api/admin/settings            // branding, theme, locale (key-value)
CRUD     /api/admin/dimensions
CRUD     /api/admin/benchmark-data
CRUD     /api/admin/decision-templates
```

---

## 8. Build Phases

**Phase 1 — Foundation**
- DB schema + migrations
- Auth (single user OK, but build with Sanctum properly)
- CRUD for `LifeEvent`
- `TimelineCalculatorService` (past curve only)
- Basic ECharts line chart rendering past life index

**Phase 2 — Benchmarking**
- Seed `BenchmarkDataset` with researched data (start with finance + health,
  most data available publicly)
- Overlay comparison line on chart

**Phase 3 — Decision Simulation**
- `DecisionNode` + `DecisionPath` CRUD
- `SimulationEngineService`
- Path comparison chart (multiple future branches on one view)

**Phase 4 — Polish for personal use**
- Dashboard summary view
- Export/print life report
- Edit historical events and see recalculation propagate

**Phase 5 — SaaS readiness (future)**
- Multi-tenant hardening
- Onboarding flow for new users (guided event entry)
- Subscription/billing
- Public benchmark dataset expansion (consider crowdsourced anonymized data
  as a differentiator)

---

## 9. Open Questions to Resolve Before Coding

- Decay function for past event impact: exponential decay vs step plateau —
  pick one model to start, can refine later
- Natural baseline drift curves per dimension (e.g. health decline curve
  with age) — needs initial research-backed defaults
- Confidence/sourcing methodology for benchmark data, especially career and
  finance dimensions which are highly context-dependent (country, industry)
- Whether `life expectancy` itself should be a dynamic variable affected by
  health decisions (e.g. smoking reduces projected end-of-life age, not just
  health score) — this would make the "final day" visualization more powerful
  but adds complexity

---

## 10. Notes for AI Coding Agent

- Keep calculation logic entirely in `Services/`, fully unit-tested,
  independent of HTTP layer — this logic is the product's core value
- All chart-ready API responses should return data already shaped for
  ECharts series format to minimize frontend transformation logic
- Avoid hardcoding modifier values in code — pull from
  `decision_templates`/`BenchmarkDataset` tables so the system is adjustable
  without redeployment
- Build for single-user now but do NOT skip `user_id` scoping anywhere —
  retrofitting multi-tenancy later is expensive
