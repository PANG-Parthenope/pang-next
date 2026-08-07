# PANG Next

## 03 – Project Decisions

**Version:** 4.0  
**Status:** Active  
**Last Update:** 2026-08-07

---

## Purpose

This document records the architectural, editorial and technical decisions adopted during the development of **PANG Next**, the new website of the PArthenope Navigation Group (PANG).

Approved decisions are considered stable and constitute the reference for the implementation of the website.

A decision may be modified only when a later decision explicitly supersedes or updates it.

---

## D001 – People Structure

**Status:** Approved — Updated

The website contains a single **People** page.

The page is organised into the following sections:

1. Faculty
2. Researchers
3. Associated Members
4. PhD Students
5. Past Members

All categories are displayed within the same page.

The People section does not use navigation submenus.

---

## D002 – Category Badges

**Status:** Approved — Updated

Each member card displays a coloured badge identifying the member category.

| Category | Badge Colour |
|---|---|
| Faculty | Blue |
| Researchers | Green |
| Associated Members | Purple |
| PhD Students | Yellow |
| Past Members | Grey |

The category badge is also displayed in the individual profile.

Badge colours are intended to provide visual identification without becoming a dominant graphic element.

---

## D003 – Main Navigation

**Status:** Approved

PANG Next adopts a flat navigation model.

The main navigation contains:

- Home
- About
- Research
- People
- Projects
- Publications
- News
- Resources
- Contacts

Dropdown menus are intentionally avoided.

Subsections are organised inside their corresponding main pages rather than exposed through navigation submenus.

---

## D004 – Associated Members

**Status:** Approved — Updated

Members who maintain an active and established scientific relationship with PANG while currently affiliated with another university, research centre or institution are classified as **Associated Members**.

This category is independent of academic position and may therefore include professors, researchers and other research professionals.

Associated Members are considered active members of PANG.

They are **not** classified as Past Members.

The term **Associated Members** replaces the previously adopted category **Collaborators**, which could incorrectly suggest that these members are external to the group.

Historical relationships with PANG, including participation in the foundation of the group, are described in the individual biography and are not represented through a separate category or structured PANG role.

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

The profile contains the following sections:

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

Projects, news, academic roles and other dynamic information are excluded from the Version 1 personal profile.

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

---

## D008 – People Ordering

**Status:** Approved — Updated

People categories are displayed in the following order:

1. Faculty
2. Researchers
3. Associated Members
4. PhD Students
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

The objective is to maintain visual and navigational consistency throughout PANG Next.

---

## D011 – Institutional Affiliation

**Status:** Approved

People profiles display the **institutional affiliation only**.

Examples:

- University of Naples Parthenope
- University of Messina
- Joint Research Centre
- École Nationale de l'Aviation Civile

Departments are intentionally omitted from the People interface.

This keeps cards concise and allows the same model to work consistently for members belonging to different institutions.

---

## D012 – Version 1 People Scope

**Status:** Approved

Version 1 uses the same profile structure for:

- Faculty
- Researchers
- Associated Members
- PhD Students
- Past Members

No category-specific profile variants are introduced in Version 1.

This keeps the interface consistent and reduces implementation and maintenance complexity.

---

## D013 – Research Structure

**Status:** Approved — Updated

The Research section must represent the multidisciplinary expertise of PANG.

The principal scientific themes are organised around:

- Positioning
- Navigation
- Geospatial Technologies

Navigation explicitly includes expertise in:

- Maritime Navigation
- Air Navigation
- Integrated Navigation

**Geospatial Technologies** provides an umbrella for relevant expertise including:

- Remote Sensing
- Geomatics
- Earth Observation

Naval architecture is not presented as a separate top-level research area.

Relevant expertise and applications are integrated into the maritime navigation domain.

Resilient Positioning, Navigation and Timing (PNT) is considered a transversal research theme.

---

## D014 – Official PANG Statement

**Status:** Approved

The official concise description of the research group is:

> The PArthenope Navigation Group (PANG) brings together faculty members, researchers, PhD students and collaborators conducting research in positioning, maritime and air navigation, geospatial technologies, and resilient Positioning, Navigation and Timing (PNT).

The word **collaborators** in this general descriptive statement refers broadly to scientific collaboration and does not correspond to a People category.

The formal People category is **Associated Members**.

This statement may be reused, with minor contextual adaptations, in:

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

Current priority is:

1. People
2. Home
3. Research
4. Projects
5. Publications
6. News
7. Resources
8. Contacts and final deployment checks

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
Version 0.4.1
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

Version 0.4.1 replaces the previous **Collaborators** category with **Associated Members** and migrates existing members to the new category.

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

Intermediate migration CSV files may be retained locally during development but should not be treated as authoritative.

The latest reviewed dataset supersedes previous versions.

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

## D022 – Founding Members

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

At the current stage, the permanent plugin set is intentionally minimal.

---

## D024 – Source Code vs Runtime

**Status:** Approved

The Local WordPress installation is the development runtime.

GitHub is the authoritative repository for project-owned source code and documentation.

Therefore:

- WordPress core is not versioned;
- third-party themes and plugins are not versioned unless explicitly required;
- custom PANG plugins are versioned;
- reviewed content datasets are versioned;
- project documentation is versioned;
- temporary ZIP installation packages are not versioned.

---

## Current Implementation Status

At the time of this revision:

| Component | Status |
|---|---|
| Local development environment | Complete |
| Git / GitHub repository | Complete |
| WordPress installation | Complete |
| Blocksy theme | Complete |
| Main navigation | Complete |
| People data migration | Complete |
| People profiles | Complete |
| Associated Members migration | Complete |
| Manual People ordering | Complete |
| Home | In progress |
| Research | Planned |
| Projects | Planned |
| Publications | Planned |
| News | Planned |
| Resources | Planned |
| Production deployment | Planned |

---

## Revision History

| Version | Date | Description |
|---|---|---|
| 1.0 | 2026-08-07 | Initial project decisions |
| 2.0 | 2026-08-07 | Consolidated architectural and editorial decisions |
| 3.0 | 2026-08-07 | People V1 implementation and public-light strategy |
| 4.0 | 2026-08-07 | Associated Members, People 0.4.1 and consolidated People V1 decisions |