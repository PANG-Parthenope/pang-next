# PANG Next

## 03 – Project Decisions

**Version:** 3.0  
**Status:** Active  
**Last Update:** 2026-08-07

---

## Purpose

This document records the architectural, editorial and technical decisions adopted during the development of **PANG Next**.

Approved decisions are considered stable and constitute the reference for the implementation of the website.

A decision may be changed only when a later decision explicitly supersedes it.

---

## D001 – People Structure

**Status:** Approved

The website contains a single **People** page organised into the following sections:

1. Faculty
2. Researchers
3. Collaborators
4. PhD Students
5. Past Members

The page does not use navigation submenus.

---

## D002 – Category Badges

**Status:** Approved

Each member card displays a coloured badge identifying the member category.

| Category | Badge Colour |
|---|---|
| Faculty | Blue |
| Researchers | Green |
| Collaborators | Purple |
| PhD Students | Yellow |
| Past Members | Grey |

The same category information is also displayed in the individual profile.

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

Subsections are organised inside their corresponding main pages.

---

## D004 – Collaborators

**Status:** Approved

Researchers belonging to external universities, research centres or other institutions and actively collaborating with PANG are classified as **Collaborators**.

Collaborators are not considered Past Members.

**Past Members** includes only people who previously belonged to or collaborated with PANG and are no longer actively involved with the group.

---

## D005 – People Cards

**Status:** Approved

All People cards use the same basic layout.

Each card contains:

- Photo
- Full Name
- Academic Position
- Affiliation
- Category Badge

The card links to the individual profile.

The following information is intentionally excluded from the card:

- Biography
- Research Interests
- Publications
- Contact information
- External academic profiles

---

## D006 – Personal Profile

**Status:** Approved

All individual profiles use the same structure in Version 1.

The profile contains:

- Photo
- Full Name
- Academic Position
- Affiliation
- Category Badge

Profile sections:

- Biography
- Research Interests

External academic profiles, when available:

- ORCID
- Google Scholar
- Scopus

Publications are intentionally not duplicated inside the People data model.

Scientific publications belong to the dedicated **Publications** section of the website.

Projects, news, academic roles and other dynamic information are postponed to future versions.

---

## D007 – One Page, One Purpose

**Status:** Approved

Each main page has one primary purpose.

| Page | Purpose |
|---|---|
| Home | Present PANG and provide access to its main activities |
| About | Present the group and its identity |
| Research | Present research activities and expertise |
| People | Present members and collaborators |
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
3. Collaborators
4. PhD Students
5. Past Members

Members are not automatically ordered alphabetically.

Within each category, the order is managed manually through the **PANG People** WordPress plugin.

This allows the displayed order to reflect the organisation of the research group rather than an arbitrary alphabetical sequence.

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

The objective is to maintain visual and navigational consistency throughout PANG Next.

---

## D011 – Affiliation

**Status:** Approved

People profiles display the institutional affiliation only.

Examples:

- University of Naples Parthenope
- University of Messina
- Joint Research Centre
- École Nationale de l'Aviation Civile

Departments are intentionally omitted from the People interface.

---

## D012 – Version 1 People Scope

**Status:** Approved

Version 1 uses the same profile structure for:

- Faculty
- Researchers
- Collaborators
- PhD Students
- Past Members

No category-specific profile variants are introduced in Version 1.

This keeps the interface consistent and reduces maintenance.

---

## D013 – Research Structure

**Status:** Approved

The Research section must represent the multidisciplinary expertise of PANG.

The principal scientific themes include:

- Positioning
- Navigation
- Geospatial Technologies

Navigation explicitly includes:

- Maritime Navigation
- Air Navigation
- Integrated Navigation

Remote sensing expertise is represented within the broader area of **Geospatial Technologies**.

Naval architecture is not presented as a separate top-level research area; relevant expertise is integrated into the maritime domain.

---

## D014 – Official PANG Statement

**Status:** Approved

The official concise description of the research group is:

> The PArthenope Navigation Group (PANG) brings together faculty members, researchers, PhD students and collaborators conducting research in positioning, maritime and air navigation, geospatial technologies, and resilient Positioning, Navigation and Timing (PNT).

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

The repository contains the reviewed editorial source material.

WordPress is the publishing platform.

---

## D016 – Repository Structure

**Status:** Approved

The repository contains project documentation, reviewed content, assets and custom source code.

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

The complete Local/WordPress runtime installation is not versioned.

Installation ZIP files and temporary migration tools are not part of the permanent repository.

---

## D017 – Development Strategy

**Status:** Approved

Development is incremental and release-oriented.

Current priority:

1. People
2. Home
3. Research
4. Projects
5. Publications
6. News
7. Resources
8. Contacts and final deployment checks

The immediate objective is to make a credible public version of PANG available as quickly as possible.

---

## D018 – MVP / Public-Light Principle

**Status:** Approved

Version 1 prioritises information required by external visitors, research partners and proposal reviewers.

A feature is included in the first public release when it materially improves the ability to understand:

- who PANG is;
- who participates in the group;
- the group's scientific expertise;
- its projects;
- its scientific output;
- how to contact or verify the group.

Non-essential functionality is postponed.

Examples of postponed functionality include:

- automatic IRIS synchronisation;
- automatic ORCID synchronisation;
- research metrics;
- advanced publication filtering;
- interactive collaboration maps;
- decorative animations.

---

## D019 – PANG People Implementation

**Status:** Implemented

The People section is implemented through the custom WordPress plugin:

```text
PANG People
Version 0.4.0
```

Permanent source code is stored in:

```text
wordpress/plugins/pang-people/
```

The plugin provides:

- structured Person content;
- People categories;
- academic position;
- affiliation;
- biography;
- research interests;
- ORCID;
- Google Scholar;
- Scopus;
- featured photographs;
- category badges;
- responsive People grids;
- individual profiles;
- manual ordering by category.

Temporary importer and synchronisation plugins are removed after use and are not part of the permanent project.

---

## D020 – People Master Dataset

**Status:** Approved

The reviewed master dataset for the initial People migration is:

```text
content/people/people-review_02.csv
```

This dataset supersedes earlier intermediate CSV files.

The original People photographs are retained in:

```text
content/people/images/
```

Original photographs are preferred over automatically cropped or regenerated versions.

Image presentation and visual consistency are handled by the website layout whenever possible.

---

## Revision History

| Version | Date | Description |
|---|---|---|
| 1.0 | 2026-08-07 | Initial decisions |
| 2.0 | 2026-08-07 | Consolidated architecture and editorial decisions |
| 3.0 | 2026-08-07 | People V1 implementation and public-light strategy |