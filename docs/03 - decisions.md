# PANG Next

## 03 – Project Decisions

**Version:** 6.0  
**Status:** Active  
**Last Update:** 2026-08-07

---

## Purpose

This document records the architectural, editorial and technical decisions adopted during the development of **PANG Next**, the new website of the PArthenope Navigation Group (PANG).

Approved decisions constitute the reference for the implementation of the website.

A decision may be modified only when a later decision explicitly supersedes or updates it.

---

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

The specific academic status of a student may be described through the Academic Position and Biography fields.

---

## D002 – Category Badges

**Status:** Approved — Updated

Each member card displays a coloured badge identifying the member category.

| Category | Badge Colour |
|---|---|
| Faculty | Blue |
| Researchers | Green |
| Associated Members | Purple |
| Students | Yellow |
| Past Members | Grey |

The category badge is also displayed in the individual profile.

Badge colours provide visual identification without becoming a dominant graphic element.

---

## D003 – Main Navigation

**Status:** Approved — Updated

PANG Next adopts a flat navigation model.

The Version 1 primary navigation contains:

- Home
- About
- Research
- People
- Projects
- Publications
- Contacts

Dropdown menus are intentionally avoided.

The pages **News** and **Resources** remain part of the planned information architecture but are excluded from the Version 1 primary navigation until their content is sufficiently developed.

Subsections are organised within their corresponding main pages rather than exposed through navigation submenus.

---

## D004 – Associated Members

**Status:** Approved — Updated

Members who maintain an active and established scientific relationship with PANG while currently affiliated with another university, research centre or institution are classified as **Associated Members**.

This category is independent of academic position and may therefore include professors, researchers and other research professionals.

Associated Members are considered active members of PANG.

They are **not** classified as Past Members.

The term **Associated Members** replaces the previously adopted category **Collaborators**, which could incorrectly suggest that these members are external to the group.

Historical relationships with PANG, including participation in the foundation of the group, are described in the individual Biography and are not represented through a separate category or structured PANG role.

---

## D005 – People Cards

**Status:** Approved

All People cards use the same basic layout.

Each card contains:

- Photo
- Full Name
- Academic Position
- Institutional Affiliation
- Category Badge

The card links directly to the individual profile.

The following information is intentionally excluded from the card:

- Biography
- Research Interests
- Publications
- Contact information
- External academic profiles

The objective is to keep the People overview clean, consistent and easy to browse.

---

## D006 – Personal Profile

**Status:** Approved — Updated

All individual profiles use the same structure in Version 1.

Each profile contains:

- Photo
- Full Name
- Academic Position
- Institutional Affiliation
- Category Badge

The profile contains the following sections.

### Biography

A concise description of the member's academic and scientific activity.

Relevant historical relationships with PANG, such as being a founding member, may be described here.

### Research Interests

A concise list of the member's main scientific interests.

### External Academic Profiles

When available:

- ORCID
- Google Scholar
- Scopus

Publications are intentionally **not duplicated** inside the People data model.

Scientific publications belong to the dedicated **Publications** section of the website.

Projects, news and other dynamic relationships are excluded from the Version 1 personal profile.

---

## D007 – One Page, One Purpose

**Status:** Approved

Each main page has one primary objective.

| Page | Purpose |
|---|---|
| Home | Present PANG and provide access to its main activities |
| About | Present the group, its identity and background |
| Research | Present research activities and scientific expertise |
| People | Present members of the research group |
| Projects | Present research projects |
| Publications | Present and browse scientific publications |
| News | Present recent activities and announcements |
| Resources | Present software and datasets |
| Contacts | Provide contact information |

Pages should avoid unnecessary duplication of information belonging to another section.

The Home page may contain concise summaries of other sections when these summaries serve as navigation and orientation.

---

## D008 – People Ordering

**Status:** Approved — Updated

People categories are displayed in the following order:

1. Faculty
2. Researchers
3. Associated Members
4. Students
5. Past Members

Members are not automatically ordered alphabetically.

Within each category, ordering is managed manually through the **PANG People** WordPress plugin.

This allows the displayed order to reflect the organisation and scientific structure of the research group rather than an arbitrary alphabetical sequence.

---

## D009 – Full Width Layout

**Status:** Approved

Main website pages use a full-width layout without sidebars.

Reasons include:

- better readability;
- cleaner visual hierarchy;
- improved responsive behaviour;
- simpler maintenance;
- more contemporary presentation.

The effective content width may still be constrained within individual sections to preserve readability.

---

## D010 – Standard Page Layout

**Status:** Approved

Main pages follow a common logical structure:

```text
Page Title

Short Introduction

Main Content

Related Content (optional)

Footer
```

Individual pages may adapt this structure when required by their specific purpose.

The Home page follows a dedicated landing-page structure defined separately.

---

## D011 – Institutional Affiliation

**Status:** Approved

People profiles display the **institutional affiliation only**.

Examples include:

- University of Naples Parthenope
- University of Messina
- Joint Research Centre
- École Nationale de l'Aviation Civile

Departments are intentionally omitted from the People interface.

This keeps cards concise and allows the same model to work consistently for members belonging to different institutions.

---

## D012 – Version 1 People Scope

**Status:** Approved — Updated

Version 1 uses the same profile structure for:

- Faculty
- Researchers
- Associated Members
- Students
- Past Members

No category-specific profile variants are introduced in Version 1.

The **Students** category may include PhD, Visiting, Master's and Bachelor's Students without requiring separate top-level People categories.

This keeps the interface consistent and reduces implementation and maintenance complexity.

---

## D013 – Research Structure

**Status:** Approved — Updated

The Research section represents the multidisciplinary expertise of PANG through three principal scientific areas:

1. Positioning
2. Navigation
3. Geospatial Technologies

### Positioning

The main topics presented in Version 1 are:

- GNSS
- Resilient PNT
- Multi-sensor Positioning

### Navigation

The main topics presented in Version 1 are:

- Navigation Systems
- Ship Dynamics & Maritime Operations
- Air Traffic Management

The Navigation area therefore includes expertise spanning navigation systems, maritime operations and the aeronautical domain.

Naval Architecture is not presented as an independent top-level research area.

Relevant expertise is represented within **Ship Dynamics & Maritime Operations**.

### Geospatial Technologies

The main topics presented in Version 1 are:

- Remote Sensing
- Geomatics
- Earth Observation

This structure allows Remote Sensing expertise to be represented explicitly while maintaining a concise top-level information architecture.

Resilient Positioning, Navigation and Timing (PNT) is considered a transversal research theme.

---

## D014 – Official PANG Statement

**Status:** Approved

The official concise description of the research group is:

> The PArthenope Navigation Group (PANG) brings together faculty members, researchers, PhD students and collaborators conducting research in positioning, maritime and air navigation, geospatial technologies, and resilient Positioning, Navigation and Timing (PNT).

The word **collaborators** in this general descriptive statement refers broadly to scientific collaboration and does not correspond to a formal People category.

The formal People category for active members currently affiliated with other institutions is **Associated Members**.

The statement may be reused, with minor contextual adaptations, in:

- Home
- About
- People
- project documentation
- institutional communication

---

## D015 – Editorial Workflow

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

Legacy Drupal is treated as a source of information rather than as a model for the new website architecture.

The repository contains reviewed editorial source material.

WordPress is the publishing platform.

---

## D016 – Repository Structure

**Status:** Approved

The repository contains project documentation, reviewed content, assets and custom source code.

The reference structure is:

```text
pang-next/
├── README.md
├── .gitignore
├── branding/
├── content/
├── docs/
├── migration/
├── pages/
└── wordpress/
    ├── plugins/
    └── themes/
```

The complete WordPress runtime installation managed by Local is not versioned.

Installation packages, ZIP archives, database dumps and temporary migration tools are not part of the permanent repository.

Custom source code developed specifically for PANG Next is versioned.

---

## D017 – Development Strategy

**Status:** Approved — Updated

Development follows an incremental and release-oriented approach.

The implementation priority is:

1. People
2. Home
3. Research
4. Projects
5. Publications
6. About
7. Contacts
8. News
9. Resources
10. Final deployment checks

The immediate objective is to publish a credible public version of PANG as quickly as possible.

The first public release does not need to contain every planned feature.

---

## D018 – Public-Light / MVP Principle

**Status:** Approved

Version 1 prioritises the information required by external visitors, research partners and proposal reviewers.

A feature belongs in the first public release when it materially improves the visitor's ability to understand:

- who PANG is;
- who participates in the group;
- the group's scientific expertise;
- its research projects;
- its scientific output;
- its institutional context;
- how to contact or verify the group.

Functionality that does not materially support these objectives is postponed.

Examples of postponed functionality include:

- automatic IRIS synchronisation;
- automatic ORCID synchronisation;
- research metrics;
- advanced publication filtering;
- interactive collaboration maps;
- decorative animations;
- advanced dynamic relationships between People, Projects and Publications.

---

## D019 – PANG People Implementation

**Status:** Implemented — Updated

The People section is implemented through the custom WordPress plugin:

```text
PANG People
Version 0.5.0
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
- category badges;
- responsive People grids;
- individual profiles;
- manual ordering within People categories.

Version 0.4.1 replaced the previous **Collaborators** category with **Associated Members**.

Version 0.5.0 introduces the definitive **Students** category.

During upgrade, the plugin automatically migrates members previously assigned to:

- PhD Students
- PhD & Visiting Students

into:

- Students

The Students category is intended to include:

- PhD Students
- Visiting Students
- Master's Students
- Bachelor's Students

Temporary importer and synchronisation plugins are removed after use and are not part of the permanent project.

---

## D020 – People Master Dataset

**Status:** Approved — Updated

The reviewed CSV stored under:

```text
content/people/
```

is the editorial master dataset for the initial People migration.

The dataset records information including:

- Full Name
- Academic Position
- People Category
- Institutional Affiliation
- Biography
- Legacy references

No dedicated `PANG Role` field is used.

Historical and organisational roles are described directly in the Biography when relevant.

The latest reviewed dataset supersedes previous intermediate versions.

---

## D021 – People Photographs

**Status:** Approved

Original People photographs are retained whenever possible.

Photographs are stored in:

```text
content/people/images/
```

Automatically generated crops are not considered authoritative source images.

Different source image dimensions are acceptable in Version 1.

Visual consistency is primarily handled through the website layout and CSS rather than through destructive modification of the original photographs.

Photographs may be progressively replaced by higher-quality and more consistent portraits in future releases.

---

## D022 – Founding Members and Scientific Direction

**Status:** Approved

Being a **founding member of PANG** is treated as biographical information rather than as a People category or structured PANG role.

The information is therefore described directly in the Biography.

For founding members, an appropriate formulation is:

> He is a founding member of the PArthenope Navigation Group (PANG).

For the Scientific Director:

> He is a founding member and Scientific Director of the PArthenope Navigation Group (PANG).

No dedicated `PANG Role` field is introduced in Version 1.

---

## D023 – WordPress Plugin Policy

**Status:** Approved

Only plugins with a clear functional purpose should be installed.

Custom functionality specific to PANG should preferably be implemented through project-owned plugins when this improves maintainability and avoids unnecessary third-party dependencies.

Temporary migration plugins must be removed after their task is completed.

The permanent plugin set should remain intentionally minimal.

---

## D024 – Source Code vs Runtime

**Status:** Approved

The Local WordPress installation is the development runtime.

GitHub is the authoritative repository for project-owned source code, reviewed content and documentation.

Therefore:

- WordPress core is not versioned;
- the complete Local runtime is not versioned;
- third-party themes and plugins are not versioned unless explicitly required;
- custom PANG plugins are versioned;
- reviewed content datasets are versioned;
- project documentation is versioned;
- source images required by the project are versioned;
- temporary ZIP installation packages are not versioned;
- temporary database dumps are not part of the permanent repository.

---

## D025 – Home V1 Structure

**Status:** Approved

The Version 1 Home page is organised into five principal sections:

1. Hero
2. Research
3. People
4. Selected Projects
5. Publications

The Home page provides a concise overview of PANG and directs visitors toward the corresponding dedicated sections of the website.

The Home intentionally avoids unnecessary duplication of detailed content already available elsewhere.

---

## D026 – Home Hero

**Status:** Approved

The Home Hero communicates the scientific positioning of PANG rather than repeating the group name already displayed in the site header.

The Hero headline is:

> **Advancing Positioning, Navigation and Geospatial Technologies**

The supporting statement is:

> **Research in navigation, resilient PNT, ship dynamics and maritime operations, and geomatics.**

The Hero contains two calls to action:

- **Explore our Research** → Research
- **Meet the Team** → People

The primary call to action uses a light filled button.

The secondary call to action uses an outline treatment.

The Version 1 Hero uses a dark PANG blue background without requiring a photographic background.

A photographic or more advanced visual Hero may be evaluated in a future design iteration.

---

## D027 – Home Research Section

**Status:** Approved

The Home Research section presents the three principal PANG research areas.

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

The section uses a simple three-column layout rather than graphical cards in Version 1.

The introductory text is:

> PANG develops methods, technologies and applications for positioning, navigation and geospatial technologies, with a focus on resilient PNT, maritime systems and operations, air traffic management, remote sensing and geomatics.

The section links to the complete Research page through:

> **Explore all research →**

---

## D028 – Home People Section

**Status:** Approved — Updated

The Home People section provides a concise description of the composition of PANG without duplicating individual member cards.

The text is:

> PANG brings together faculty members, researchers, students and associated members, combining complementary expertise across positioning, navigation and geospatial technologies.

The generic term **students** is deliberately used on the Home page.

The detailed People section may include PhD, Visiting, Master's and Bachelor's Students within the Students category.

The Home does not display numerical counts of People categories because membership may change over time.

The section links to the complete People page through:

> **Meet the team →**

---

## D029 – Selected Projects on Home

**Status:** Approved

The Home page contains a **Selected Projects** section.

The section is intentionally named **Selected Projects** rather than **Recent Projects**, because the purpose is to highlight representative PANG research activities rather than imply chronological recency.

The initial selected projects are:

1. SMILE
2. ARES
3. TME

These projects were selected from the legacy PANG Drupal archive.

### SMILE

**Satellite Multicostellation Identification Techniques for Liable Enhanced Applications**

Home summary:

> Development of multi-constellation satellite positioning technologies for smart and sustainable mobility applications.

### ARES

**Robotica autonoma per la nave estesa**

Home summary:

> Development of an integrated ship-and-robotics ecosystem combining onboard systems with cooperative underwater and surface autonomous vehicles.

### TME

**Processo Automatico per l'Implementazione di Tecnologie per la Mobilità Efficiente Navale**

Home summary:

> Development of automated technologies and processes for vessel retrofitting, integrating dual-fuel propulsion, hydrofoils, sensing and navigation-performance assessment.

The section links to the complete Projects page through:

> **Explore all projects →**

---

## D030 – Home Publications Section

**Status:** Approved

The Home page contains a concise Publications section.

The Home does not display individual publications, publication counts or bibliometric indicators in Version 1.

The section text is:

> Explore PANG's scientific publications across positioning, navigation, maritime systems and geospatial technologies.

The section links to the Publications page through:

> **View publications →**

Detailed publication management will be addressed when implementing the dedicated Publications section.

---

## D031 – Home Content Philosophy

**Status:** Approved

The Home page is designed as an orientation and credibility layer rather than as a complete repository of PANG content.

The page should allow an external visitor to understand within a short time:

- the scientific identity of PANG;
- the main research areas;
- the composition of the group;
- representative research projects;
- where to find scientific publications.

Detailed information belongs to the corresponding dedicated pages.

The Version 1 Home therefore prioritises clarity, scientific credibility and maintainability over decorative complexity.

---

## D032 – Version 1 Header

**Status:** Approved

The Version 1 header displays the site identity:

> **PANG — PArthenope Navigation Group**

The primary navigation is flat and contains:

- Home
- About
- Research
- People
- Projects
- Publications
- Contacts

Dropdown menus and a generic **More** menu are not used.

The header remains visually distinct from the Home Hero:

- the header identifies the group;
- the Hero communicates its scientific positioning.

---

## D033 – Students Category

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

The decision supersedes the previously considered categories:

- PhD Students
- PhD & Visiting Students

---

## Current Implementation Status

At the time of this revision:

| Component | Status |
|---|---|
| Local development environment | Complete |
| Git / GitHub repository | Complete |
| WordPress installation | Complete |
| Blocksy theme configuration | In progress |
| Main navigation V1 | Complete |
| People data migration | Complete |
| People profiles | Complete |
| Associated Members migration | Complete |
| Students category | Complete |
| Manual People ordering | Complete |
| PANG People plugin | Version 0.5.0 |
| Home Hero | Complete |
| Home Research | Complete |
| Home People | Complete |
| Home Selected Projects | Complete |
| Home Publications | Complete |
| Home V1 | Complete |
| Research page | Planned |
| Projects page | Planned |
| Publications page | Planned |
| About page | Planned |
| Contacts page | Planned |
| News | Deferred |
| Resources | Deferred |
| Production deployment | Planned |

---

## Revision History

| Version | Date | Description |
|---|---|---|
| 1.0 | 2026-08-07 | Initial project decisions |
| 2.0 | 2026-08-07 | Consolidated architectural and editorial decisions |
| 3.0 | 2026-08-07 | People V1 implementation and public-light strategy |
| 4.0 | 2026-08-07 | Associated Members and consolidated People V1 decisions |
| 5.0 | 2026-08-07 | Home V1, Research structure, Selected Projects and V1 navigation |
| 6.0 | 2026-08-07 | Definitive Students category and PANG People 0.5.0 |