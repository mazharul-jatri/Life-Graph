# Lifecurv — Full Project Architecture & Specification

**Stack:** Laravel 13 (PHP 8.3+) · MySQL / SQLite · Vue 3 · Inertia.js · Tailwind CSS · Apache ECharts
**Core Concept:** Predictive Life Trajectory & Actuarial Life-Simulation Engine (Age 0–120) with Multi-Lane Branching What-If Timelines, Benchmark Comparisons, and Habit/Activity Impact Modeling.

---

## 1. High-Level System Architecture

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│                         Vue 3 SPA Layer (Inertia.js)                             │
│  ┌─────────────────────────┐ ┌───────────────────────────┐ ┌───────────────────┐ │
│  │ Age 0-120 Lifespan Chart│ │ Branching Timeline Canvas │ │ Activity & Habit  │ │
│  │ (Health/Wealth/Career)  │ │ (Decision Forks & Lanes)  │ │ Logger / Modifiers│ │
│  └─────────────────────────┘ └───────────────────────────┘ └───────────────────┘ │
└────────────────────────────────────────┬─────────────────────────────────────────┘
                                         │ Inertia.js Props & Form State
┌────────────────────────────────────────▼─────────────────────────────────────────┐
│                           Laravel 13 Backend (PHP 8.3+)                          │
│                                                                                  │
│   Controllers & Form Requests           Simulation & Analytics Domain            │
│   ├── TimelineController                ├── LifeProjectionEngine                 │
│   ├── ActivityLoggerController          ├── ActuarialReferenceService            │
│   ├── SimulationController              ├── HabitImpactCalculator                │
│   └── BenchmarkController               └── TimelineTreeBuilder                  │
└────────────────────────────────────────┬─────────────────────────────────────────┘
                                         │
               ┌─────────────────────────┼─────────────────────────┐
               ▼                         ▼                         ▼
         MySQL / SQLite                Redis              Actuarial Reference Data
     (User data, activities,     (Cached simulations,       (WHO, CDC, BLS, OECD,
      timelines, milestones)      benchmark datasets)       Life Tables, Habit Meta)
```

---

## 2. Multi-Pillar Life Vector Paradigm

A person's life trajectory is modeled across **four interconnected pillars** across the **Age 0 to 120 spectrum**:

| Pillar | Measured Metric | Key Modifiers & Data Inputs | Reference Benchmark Sources |
|---|---|---|---|
| **Health & Vitality** | Biological Age, Life Expectancy (years), Morbidity Risk (%) | Workout frequency, smoking, sleep, diet, BMI, chronic conditions | WHO Life Tables, CDC National Health Stats, Framingham Risk Model |
| **Wealth & Financials** | Net Worth ($), Compound Investment Growth, Retirement Age | Monthly investment, savings rate, salary, debt, ROI assumption (7-8%) | S&P 500 historical compounding, OECD savings benchmarks |
| **Career & Mastery** | Career Velocity Index, Seniority Level, Skill Capital | Degrees, workshops, certifications, industry switch, promotions | BLS Occupational Outlook, OECD Earnings data |
| **Fulfillment & Life Index** | Aggregate Life Score (0–100) | Weighted synthesis of Health, Wealth, Career, and Personal Milestones | Subjective user weights + longitudinal quality-of-life studies |

---

## 3. Database Schema Design

```
users
├── id, name, email, password, timezone, locale
├── created_at, updated_at

user_profiles
├── id, user_id (FK)
├── birth_date (date)                  -- used to compute exact chronological age
├── current_age (float)                -- e.g. 25.0
├── gender (enum: male, female, other) -- actuarial baseline adjustment
├── country_code (char 2/3)            -- baseline life expectancy / GDP
├── baseline_life_expectancy (float)   -- e.g. 78.5 (derived from WHO data)
├── target_retirement_age (int)        -- default: 60
├── created_at, updated_at

life_timelines
├── id, user_id (FK), name, description
├── is_primary (bool)                  -- true = Actual Path, false = Counterfactual What-If
├── parent_timeline_id (FK, nullable)  -- self-referencing hierarchy
├── branch_at_age (float, nullable)    -- e.g. age 25.0 (exact divergence point)
├── branch_at_x (string, nullable)     -- e.g. "2023-06"
├── created_at, updated_at

life_activities
├── id, user_id (FK), timeline_id (FK)
├── title (string)                     -- e.g. "Daily Cardio & Strength Training"
├── activity_type (string)             -- "workout", "education", "investment", "habit", "smoking"
├── pillar (enum: health, wealth, career, personal)
├── frequency (string)                 -- "daily", "weekly", "monthly", "one_time"
├── intensity_or_amount (decimal)      -- e.g. $500/mo or 45 mins/day or 20 cigs/day
├── start_age (float)                  -- e.g. 24.0
├── end_age (float, nullable)          -- null if currently ongoing
├── impact_coefficients (json)         -- computed delta per pillar
├── is_active (bool)
├── created_at, updated_at

life_events
├── id, timeline_id (FK)
├── title, description
├── category (career, education, health, finance, personal, achievement)
├── event_date (date, nullable)
├── age_at_event (float)               -- e.g. 22.0, 25.0, 48.5
├── is_projected (bool)                -- false = Past Reality, true = Simulated Future
├── impact_score (tinyint, -5 to +5)
├── risk_alert (string, nullable)      -- e.g. "Cardiovascular risk spike"
├── metadata (json)
├── created_at, updated_at

actuarial_benchmarks
├── id, source (WHO, CDC, BLS, OECD, S&P500)
├── category (life_expectancy, mortality_risk, income_trajectory, investment_yield)
├── country_code (char 2/3), gender, age_bracket
├── baseline_value (decimal), standard_deviation (decimal)
├── metadata (json)
├── UNIQUE(source, category, country_code, gender, age_bracket)

habit_impact_rules
├── id, activity_type (string)         -- e.g. "smoking", "daily_exercise", "index_investing"
├── pillar (health, wealth, career)
├── unit (string)                      -- "per_year", "per_dollar", "per_session"
├── delta_life_expectancy (float)      -- e.g. -0.5 years per pack-year, +0.12 yrs per gym-year
├── delta_career_velocity (float)      -- e.g. +1.15 multiplier for skill workshop
├── delta_morbidity_risk_pct (float)   -- e.g. -35% cardiac risk
├── metadata (json)                    -- citations from epidemiological studies
```

---

## 4. The Simulation & Projection Engine

### `LifeProjectionEngine`
Calculates yearly points from `Age 0` to `Age 120` (`interval: 1 year` or `0.5 year`):

1. **Past Reality Segment (Age 0 to Current Age e.g. 25)**:
   - Grounded strictly in logged historical events and activities.
   - Verified milestones appear as solid markers.
2. **Current Age Marker ("You Are Here" at Age 25)**:
   - Dynamic vertical indicator separating historical truth from future simulation.
3. **Projected Future Segment (Age 25 to Age 120)**:
   - Compounding formula for Wealth: $W(t) = W_0 (1 + r)^t + \sum P \cdot (1 + r)^t$
   - Actuarial Health Decay Curve: Gompertz-Makeham mortality model modified by habit coefficients ($\Delta \text{LifeExp} = \sum h_i \cdot c_i$).
   - Career Step-ladder: Promotion probability distribution based on education & workshops.
4. **Counterfactual Forking ("What-If" Branches)**:
   - User clones timeline at `Age X` and alters habits (e.g. *Start Investing $500/mo* or *Quit Smoking*).
   - Generates parallel trajectory curves showing the delta in lifespan, retirement age, and net worth.

---

## 5. ECharts Trajectory Data Contract (0–120 Age Canvas)

The backend (`LifeProjectionEngine` / `TimelineTreeBuilder`) emits a structured JSON contract:

```json
{
  "user_profile": {
    "current_age": 25.0,
    "max_display_age": 120,
    "projected_lifespan": 84.5,
    "projected_retirement_age": 56.0
  },
  "pillars": ["overall", "health", "wealth", "career"],
  "timelines": [
    {
      "id": 1,
      "name": "Current Trajectory (Baseline)",
      "is_primary": true,
      "parent_id": null,
      "branch_at_age": null,
      "color": "#06b6d4",
      "curve_data": [
        { "age": 0, "health": 100, "wealth": 0, "career": 0, "overall": 70 },
        { "age": 25, "health": 95, "wealth": 15000, "career": 60, "overall": 78 },
        { "age": 60, "health": 72, "wealth": 650000, "career": 85, "overall": 82 },
        { "age": 84.5, "health": 10, "wealth": 420000, "career": 0, "overall": 20 }
      ],
      "milestones": [
        { "age": 22, "title": "BSc Degree", "pillar": "education", "impact": 4, "is_projected": false },
        { "age": 23.5, "title": "Senior Engineer", "pillar": "career", "impact": 4, "is_projected": false },
        { "age": 56, "title": "Projected Financial Freedom", "pillar": "wealth", "impact": 5, "is_projected": true }
      ]
    },
    {
      "id": 2,
      "name": "What-If: Heavy Smoking & Zero Exercise",
      "is_primary": false,
      "parent_id": 1,
      "branch_at_age": 25.0,
      "color": "#f43f5e",
      "curve_data": [
        { "age": 25, "health": 95, "wealth": 15000, "career": 60, "overall": 78 },
        { "age": 52, "health": 40, "wealth": 120000, "career": 65, "overall": 48 },
        { "age": 68.2, "health": 0, "wealth": 40000, "career": 0, "overall": 0 }
      ],
      "milestones": [
        { "age": 52, "title": "Cardiovascular Risk Critical Alert", "pillar": "health", "impact": -5, "is_projected": true, "is_risk": true }
      ]
    }
  ]
}
```

---

## 6. Directory Structure & Key Classes

```
app/
├── Models/
│   ├── User.php
│   ├── UserProfile.php
│   ├── LifeTimeline.php
│   ├── LifeActivity.php
│   ├── LifeEvent.php
│   ├── ActuarialBenchmark.php
│   └── HabitImpactRule.php
├── Services/
│   ├── Simulation/
│   │   ├── LifeProjectionEngine.php       -- generates 0-120 curve vectors
│   │   ├── HealthActuarialModel.php       -- biological age, Gompertz mortality
│   │   ├── WealthCompoundingModel.php     -- investment & cashflow projection
│   │   └── CareerVelocityModel.php        -- skill compounding & milestones
│   └── TimelineTreeBuilder.php            -- transforms models into ECharts schema
├── Http/
│   ├── Controllers/
│   │   ├── TimelineController.php
│   │   ├── ActivityLoggerController.php
│   │   └── SimulationController.php
│   └── Requests/
│       ├── StoreActivityRequest.php
│       └── SimulateScenarioRequest.php
database/
├── migrations/
└── seeders/
    ├── ActuarialBenchmarkSeeder.php       -- WHO, CDC, BLS baseline statistics
    ├── HabitImpactRuleSeeder.php          -- impact coefficients for gym, smoking, investments
    └── LifeSimulationDemoSeeder.php       -- 25-year-old realistic sample profile

resources/js/
├── Components/
│   ├── charts/
│   │   ├── LifespanTrajectoryChart.vue    -- ECharts 0-120 interactive canvas
│   │   ├── BranchingTimelineChart.vue     -- Multi-lane decision graph
│   │   └── MetricGaugeCards.vue           -- Bio age, Net worth @ 60, Est. Lifespan
│   ├── simulator/
│   │   ├── ActivityLoggerModal.vue        -- Log workouts, workshops, investments, habits
│   │   ├── WhatIfScenarioDrawer.vue       -- Toggle scenario modifiers in real time
│   │   └── PillarTogglePills.vue          -- Switch Health / Wealth / Career / All
│   └── ui/
└── Pages/
    ├── Timelines/
    │   ├── Show.vue                       -- Combined Trajectory & Milestone Dashboard
    │   └── Simulator.vue                  -- Deep dive sandbox simulator
```

---

## 7. Security, Performance & Scalability

1. **Deterministic Fast Projections**:
   - The simulation math runs in O(N) where N = 120 data points per timeline. Response times are under 15ms.
2. **Redis Caching for Reference Models**:
   - Actuarial tables and habit impact coefficients are cached indefinitely in Redis.
3. **Reactive Client Recalculations**:
   - For fast slider changes in the UI, lightweight delta calculations run client-side in Vue 3 composables, with full server-side sync on commit.