# PANG Next

## 03 – Project Decisions

**Version:** 7.0  
**Status:** Active  
**Last Update:** 2026-08-08

---

## Purpose

This document records the architectural, editorial and technical decisions adopted during the development of **PANG Next**, the new website of the PArthenope Navigation Group (PANG).

Approved decisions constitute the reference for the implementation of the website.

A decision may be modified only when a later decision explicitly supersedes or updates it.

---

# 1. INFORMATION ARCHITECTURE

## D001 – People Structure

**Status:** Approved — Updated

The website contains a single **People** page organised into the following sections:

1. Faculty
2. Researchers
3. Associated Members
4. Students
5. Past Members

All categories are displayed within the same page.

The People section does not use navigation submenus.

The **Students** category provides a common category for students participating in PANG activities, including:

- PhD Students
- Visiting Students
- Master's Students
- Bachelor's Students

The specific academic status of a student is represented through the **Academic Position** field and may be further described in the Biography.

---

## D002 – People Categories

**Status:** Approved — Updated

The definitive People taxonomy is:

- Faculty
- Researchers
- Associated Members
- Students
- Past Members

The previously considered categories:

- Collaborators
- PhD Students
- PhD & Visiting Students

are superseded.

---

## D003 – Main Navigation

**Status:** Approved — Updated

PANG Next adopts a flat navigation model.

The target primary navigation is:

- Home
- About
- Research
- People
- Publications
- Contacts

Dropdown menus are intentionally avoided.

**Projects** is no longer considered a required top-level navigation section.

Research projects are presented as part of the **Research** section because they represent applications and evidence of PANG research activities.

The pages **News** and **Resources** may exist as secondary sections without requiring permanent placement in the primary navigation.

---

## D004 – Associated Members

**Status:** Approved

Members who maintain an active and established scientific relationship with PANG while currently affiliated with another university, research centre or institution are classified as **Associated Members**.

This category is independent of academic position and may therefore include professors, researchers and other research professionals.

Associated Members are considered active members of PANG.

They are **not** classified as Past Members.

Historical relationships with PANG, including participation in the foundation of the group, are described in the individual Biography rather than through a separate category.

---

## D005 – Students

**Status:** Approved

**Students** is the definitive top-level People category for students participating in PANG research activities.

The category may include:

- PhD Students
- Visiting Students
- Master's Students
- Bachelor's Students

Separate top-level categories are not created for each student type.

The specific status of each student is represented through the **Academic Position** field and, when useful, the Biography.

This model is intended to remain stable as the composition of the student community changes over time.

---

## D006 – One Page, One Purpose

**Status:** Approved — Updated

Each principal page has one primary objective.

| Page | Purpose |
|---|---|
| Home | Present the scientific identity of PANG and provide access to key content |
| About | Present the group, its identity and background |
| Research | Present research areas, expertise and related research projects |
| People | Present members of the research group |
| Publications | Present and browse scientific publications |
| News | Present activities, events, awards and announcements |
| Resources | Present software, datasets and other research resources |
| Contacts | Provide contact information |

The Home may contain concise summaries or selected content from other sections when these elements support orientation and navigation.

Detailed content belongs to the corresponding dedicated page.

---

# 2. PEOPLE

## D007 – People Overview Layout

**Status:** Approved — Updated

The People overview uses a compact portrait-based layout rather than large rectangular cards.

The approved Version 1 layout uses:

- four profiles per row on desktop;
- circular portrait photographs;
- approximately 180 px portrait diameter on desktop;
- responsive reduction on tablet and mobile;
- Full Name;
- Academic Position;
- Institutional Affiliation.

Large card backgrounds, borders and shadows are intentionally avoided.

The objective is to provide a compact, academic and easily scannable representation of the research group.

---

## D008 – People Category Badges

**Status:** Approved — Updated

Category badges are **not displayed in the People overview grid** because category membership is already communicated by the corresponding section heading.

Category badges remain available in the individual profile.

This reduces visual redundancy in the People overview.

---

## D009 – People Portraits

**Status:** Approved — Updated

Original People photographs are retained whenever possible.

The overview displays portraits using a circular crop.

The original source image is not destructively modified.

CSS-based cropping uses:

- square aspect ratio;
- `object-fit: cover`;
- per-person vertical focal positioning.

Each Person profile may define a **Photo vertical position (%)** value.

The reference values are:

- `0` = top;
- `50` = centre;
- `100` = bottom.

This allows problematic portraits to be corrected individually without creating new image files.

---

## D010 – People Ordering

**Status:** Approved

People categories are displayed in the following order:

1. Faculty
2. Researchers
3. Associated Members
4. Students
5. Past Members

Members are not automatically ordered alphabetically.

Within each category, ordering is managed manually through the **PANG People** WordPress plugin.

---

## D011 – People Individual Profile

**Status:** Approved — Updated

All individual profiles use the same basic structure.

Each profile may contain:

- Photo
- Full Name
- Academic Position
- Institutional Affiliation
- Category Badge
- Biography
- Research Interests
- ORCID
- Google Scholar
- Scopus

Scientific publications are intentionally not duplicated inside the People data model.

Publications belong to the dedicated **Publications** section.

---

## D012 – Institutional Affiliation

**Status:** Approved

People profiles display the **institutional affiliation only**.

Departments are intentionally omitted from the overview.

This keeps the interface concise and allows the same model to work consistently for members belonging to different institutions.

---

## D013 – Founding Members and Scientific Direction

**Status:** Approved

Being a **founding member of PANG** is treated as biographical information rather than as a People category or structured PANG role.

The information is described directly in the Biography.

For founding members:

> He is a founding member of the PArthenope Navigation Group (PANG).

For the Scientific Director:

> He is a founding member and Scientific Director of the PArthenope Navigation Group (PANG).

No dedicated `PANG Role` field is introduced in Version 1.

---

# 3. PEOPLE DATA AND PLUGIN

## D014 – People Master Dataset

**Status:** Approved — Updated

The authoritative reviewed People dataset is stored in:

```text
content/people/people-review_04.csv
```

This file is the **editorial master dataset**.

It contains reviewed People information including, where available:

- Full Name
- Academic Position
- People Category
- Institutional Affiliation
- Biography
- Research Interests
- ORCID
- Google Scholar
- Scopus
- legacy references and migration information.

Future reviewed datasets may increment the revision number.

---

## D015 – Separation of People Data and Plugin Code

**Status:** Approved — New

The permanent **PANG People** plugin contains application code only.

The editorial People CSV is **not bundled inside the permanent plugin**.

The architecture is:

```text
content/people/
    ↓
reviewed editorial data

wordpress/plugins/pang-people/
    ↓
WordPress application code
```

This avoids maintaining duplicate CSV files that could diverge over time.

Bulk People updates should use temporary reviewed migration or synchronisation tools when required.

---

## D016 – PANG People Plugin

**Status:** Implemented — Updated

The current consolidated People plugin is:

```text
PANG People
Version 0.6.4
```

Permanent source code is stored in:

```text
wordpress/plugins/pang-people/
```

The plugin provides:

- structured Person content;
- People categories;
- Academic Position;
- Institutional Affiliation;
- Biography;
- Research Interests;
- ORCID;
- Google Scholar;
- Scopus;
- Featured Images;
- responsive People grid;
- circular portrait presentation;
- per-person portrait focal positioning;
- individual profiles;
- manual ordering within People categories.

The permanent plugin does not contain the editorial People CSV.

Temporary import and synchronisation plugins are removed after use.

---

# 4. RESEARCH

## D017 – Research Structure

**Status:** Approved — Updated

PANG research is organised into three principal scientific areas:

1. Positioning
2. Navigation
3. Geospatial Technologies

### Positioning

The principal topics are:

- GNSS
- Resilient PNT
- Multi-sensor Positioning

### Navigation

The principal topics are:

- Navigation Systems
- Ship Dynamics & Maritime Operations
- Air Traffic Management

### Geospatial Technologies

The principal topics are:

- Remote Sensing
- Geomatics
- Earth Observation

Resilient Positioning, Navigation and Timing (PNT) is considered a transversal research theme.

Naval Architecture is not presented as an independent top-level research area.

Relevant expertise is represented within **Ship Dynamics & Maritime Operations**.

---

## D018 – Projects Integrated into Research

**Status:** Approved — New

Research projects are not required to form a separate top-level information architecture section.

Projects are considered evidence and applications of PANG research activities and are therefore integrated into the **Research** page.

The intended Research page structure is:

```text
Research

Introduction

Positioning
    Description
    Research topics
    Related projects

Navigation
    Description
    Research topics
    Related projects

Geospatial Technologies
    Description
    Research topics
    Related projects
```

Projects may remain structured internally in WordPress or in project datasets even when no standalone Projects page is exposed in the primary navigation.

---

## D019 – Selected Projects

**Status:** Approved — Updated

The Home highlights a small number of representative research projects.

The initial selection is:

- SMILE
- ARES
- TME

The section is named **Selected Projects**, not Recent Projects.

The purpose is to illustrate representative PANG research activities rather than chronological recency.

The complete project context will be provided within the Research section.

---

# 5. HOME

## D020 – Home Content Philosophy

**Status:** Approved — Updated

The Home is designed as a concise scientific landing page.

It is not intended to reproduce all website sections.

The Home should allow visitors to understand quickly:

- the scientific identity of PANG;
- its main research areas;
- representative research activities;
- recent group activity;
- how to reach People and Publications.

Detailed information belongs to dedicated pages.

---

## D021 – Home Structure

**Status:** Approved — Updated

The target Home structure is:

```text
Hero

Research

Selected Projects | Latest News
```

Dedicated Home sections for **People** and **Publications** have been removed to reduce redundancy.

People and Publications remain directly accessible through the Hero and primary navigation.

---

## D022 – Home Hero

**Status:** Approved — Updated

The Home Hero communicates the scientific positioning of PANG.

The headline is:

> **Advancing Positioning, Navigation and Geospatial Technologies**

The supporting statement describes the group's activities in navigation, resilient PNT, ship dynamics and maritime operations, and geomatics.

The Hero contains two primary calls to action:

- **Meet the Team** → People
- **Explore our Publications** → Publications

The Hero uses a dedicated panoramic scientific image combining visual references to:

- maritime navigation;
- satellite positioning;
- GNSS;
- aviation;
- Earth Observation;
- geospatial technologies.

The image is used as the background of the WordPress Cover block.

Text and buttons remain native WordPress content and are not embedded into the image.

---

## D023 – PANG Visual Identity in Header

**Status:** Approved — New

The new PANG logo is displayed in the website header.

The previous duplication between logo and textual site title is removed.

The header displays:

- PANG logo on the left;
- primary navigation on the right.

The current reference logo height on desktop is approximately **75 px**.

The WordPress site title remains configured internally but is not separately displayed in the header.

---

## D024 – Home Research Section

**Status:** Approved — Updated

The Home Research section presents the three principal PANG research areas:

### Positioning
- GNSS
- Resilient PNT
- Multi-sensor Positioning

### Navigation
- Navigation Systems
- Ship Dynamics & Maritime Operations
- Air Traffic Management

### Geospatial Technologies
- Remote Sensing
- Geomatics
- Earth Observation

Each area uses a dedicated visual icon.

The icons use a consistent PANG-blue visual language:

- satellite/GNSS for Positioning;
- compass/navigation symbol for Navigation;
- globe/Earth Observation symbol for Geospatial Technologies.

The Home Research section does not require an additional `Explore all research` link when Research is already accessible from the primary navigation and clearly represented on the Home.

---

## D025 – Home Selected Projects and Latest News

**Status:** Approved — New

The lower Home section uses a two-column layout:

```text
Selected Projects      Latest News
```

### Selected Projects

The Home displays:

- SMILE
- ARES
- TME

Project descriptions are intentionally compact.

Detailed project information belongs to the Research page.

### Latest News

Latest News is generated dynamically from standard WordPress Posts assigned to the **News** category.

The Home displays the latest three News items.

The intended presentation includes:

- publication date;
- News title.

Author and excerpt are not required in the compact Home presentation.

Featured Images may be introduced when a consistent set of modern News images is available.

---

# 6. NEWS

## D026 – News Content Model

**Status:** Approved — New

News is implemented using standard WordPress Posts.

PANG News posts use the WordPress category:

```text
News
```

The Home Latest News section is dynamically generated from this category.

---

## D027 – Legacy News

**Status:** Implemented — New

Published News from the legacy Drupal website have been imported into WordPress.

The migration preserves:

- title;
- original publication date;
- body content;
- News category;
- legacy Drupal node identifier for duplicate prevention.

Temporary migration tools are removed after verification.

Legacy News may remain without Featured Images when the original image material is unavailable or unnecessary.

---

## D028 – New News Featured Images

**Status:** Approved — New

New PANG News should normally include a **Featured Image**.

Preferred image policy:

1. real photograph of the PANG activity;
2. official event graphic when no suitable PANG photograph exists;
3. avoid generic stock photography.

Recommended Featured Image format:

```text
1200 × 675 px
16:9
```

Additional photographs may be inserted inside the article body when useful.

---

# 7. PUBLICATIONS

## D029 – Publications

**Status:** Approved — Updated

Scientific publications belong to the dedicated **Publications** section.

Publications are not duplicated inside People profiles.

The Publications page is directly accessible from:

- primary navigation;
- Home Hero.

Future publication functionality may include integration with institutional research systems such as IRIS.

Advanced filtering, metrics and automatic synchronisation are not required for the initial public release unless already available and stable.

---

# 8. DESIGN PRINCIPLES

## D030 – Full Width Layout

**Status:** Approved

Main website pages use a full-width layout without sidebars.

The effective content width may be constrained within individual sections to preserve readability.

---

## D031 – Visual Simplicity

**Status:** Approved — Updated

PANG Next prioritises:

- scientific clarity;
- visual consistency;
- maintainability;
- restrained use of decorative elements.

Large card-based interfaces are avoided when a simpler editorial layout communicates information more effectively.

Visual elements should support navigation and scientific identity rather than become decorative ends in themselves.

---

## D032 – Home Section Links

**Status:** Approved — New

Secondary Home calls to action use a common style.

The CSS class:

```text
pang-section-link
```

is used for section-level text links when required.

The intended appearance is:

- PANG/accent blue;
- semibold;
- no permanent underline;
- underline on hover/focus;
- directional arrow where appropriate.

Global link styling is not modified.

---

# 9. DEVELOPMENT AND DEPLOYMENT

## D033 – Editorial Workflow

**Status:** Approved

Legacy content is not copied blindly into WordPress.

The editorial workflow is:

```text
Legacy Drupal
      ↓
Content Extraction
      ↓
Editorial Review
      ↓
Repository
      ↓
WordPress
```

Legacy Drupal is treated as a source of information rather than as the architectural model for the new website.

---

## D034 – Repository Structure

**Status:** Approved — Updated

The repository contains project documentation, reviewed content, branding assets and custom source code.

Reference structure:

```text
pang_next/
├── README.md
├── .gitignore
├── branding/
├── content/
│   └── people/
│       └── people-review_04.csv
├── docs/
├── migration/
├── pages/
└── wordpress/
    ├── plugins/
    │   └── pang-people/
    └── themes/
```

The complete WordPress runtime installation is not versioned.

ZIP installation packages, database dumps and temporary migration plugins are not part of the permanent repository.

---

## D035 – Source Code vs Runtime

**Status:** Approved

GitHub is the authoritative repository for:

- project-owned source code;
- reviewed editorial datasets;
- documentation;
- branding assets.

WordPress databases contain the active published/editorial state.

Therefore:

- WordPress core is not versioned;
- the complete Local runtime is not versioned;
- third-party themes/plugins are not normally versioned;
- custom PANG plugins are versioned;
- reviewed content datasets are versioned;
- project documentation is versioned;
- branding source assets are versioned;
- temporary ZIP packages are not versioned;
- temporary database dumps are not permanently versioned.

---

## D036 – Local, GitHub and Altervista Workflow

**Status:** Approved — New

The current development workflow uses three distinct environments.

### GitHub

GitHub is the authoritative source for:

- custom plugin code;
- documentation;
- reviewed datasets;
- branding assets;
- architectural decisions.

### Local

Local is the development and testing environment for:

- custom WordPress code;
- plugin development;
- migration testing;
- potentially disruptive changes.

### Altervista

Altervista currently acts as the **public staging environment**.

It is used for:

- visual refinement;
- content editing;
- collaborative review;
- public accessibility of the draft website.

During the current development phase, some editorial and Gutenberg layout changes may be performed directly on Altervista.

When Altervista advances beyond Local, the approved changes must subsequently be reflected in:

- project documentation;
- repository source files where applicable;
- Local when necessary for development parity.

---

## D037 – WordPress Plugin Policy

**Status:** Approved

Only plugins with a clear functional purpose should remain permanently installed.

Custom PANG functionality should preferably be implemented through project-owned plugins when this improves maintainability.

Temporary importer and synchronisation plugins must be removed after their task is completed and verified.

---

## D038 – Public-Light / MVP Principle

**Status:** Approved

Version 1 prioritises the information required by external visitors, research partners and proposal reviewers.

A feature belongs in the first public release when it materially improves the visitor's ability to understand:

- who PANG is;
- its scientific expertise;
- who participates in the group;
- representative research activities;
- scientific output;
- recent activity;
- how to contact or verify the group.

Functionality that does not materially support these objectives may be postponed.

Examples include:

- advanced publication metrics;
- interactive collaboration maps;
- decorative animations;
- unnecessary duplication between sections;
- complex dynamic relationships not required for Version 1.

---

# 10. CURRENT IMPLEMENTATION STATUS

| Component | Status |
|---|---|
| Git / GitHub repository | Complete |
| Local WordPress environment | Complete |
| Altervista staging environment | Active |
| Blocksy theme | Active |
| PANG visual identity / logo | Implemented |
| Header V1 | Implemented |
| Main navigation | In transition to simplified architecture |
| Home Hero | Implemented |
| Home Research | Implemented |
| Home Selected Projects | Implemented |
| Home Latest News | In progress |
| People data migration | Complete |
| People profiles | Complete |
| People academic profile links | Partially complete / reviewed |
| PANG People plugin | Version 0.6.4 |
| People compact grid | Complete |
| Portrait focal positioning | Complete |
| Legacy News migration | Complete |
| Research page | Next implementation priority |
| Projects integration into Research | Planned |
| Publications page | In development / integration |
| About page | Planned |
| Contacts page | Planned |
| Resources | Deferred |
| Production deployment | Planned |

---

# 11. NEXT PRIORITIES

The next implementation priorities are:

1. Build the definitive **Research** page.
2. Integrate legacy and current projects into the appropriate Research areas.
3. Complete and refine **Latest News**.
4. Complete the **Publications** integration.
5. Build/refine About and Contacts.
6. Perform responsive and accessibility checks.
7. Consolidate Altervista, Local and GitHub before production release.

---

# Revision History

| Version | Date | Description |
|---|---|---|
| 1.0 | 2026-08-07 | Initial project decisions |
| 2.0 | 2026-08-07 | Consolidated architectural and editorial decisions |
| 3.0 | 2026-08-07 | People V1 implementation and public-light strategy |
| 4.0 | 2026-08-07 | Associated Members and consolidated People V1 decisions |
| 5.0 | 2026-08-07 | Initial Home V1 and Research structure |
| 6.0 | 2026-08-07 | Students category and PANG People 0.5.0 |
| 7.0 | 2026-08-08 | Altervista staging, new Home architecture, Research/Projects integration, News migration, new visual identity and PANG People 0.6.4 |