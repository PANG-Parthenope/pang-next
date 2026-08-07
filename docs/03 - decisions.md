# PANG Next

# 03 – Project Decisions

Version: 1.0

Status: Approved

Last Update: 2026-08-07

---

# Purpose

This document records all architectural, editorial and technical decisions adopted during the development of **PANG Next**.

The purpose of this document is to ensure consistency throughout the project and to document the rationale behind every major decision.

Unless explicitly superseded, every approved decision is considered stable.

---

# D001 – People Structure

**Status:** Approved

The website contains a single **People** page.

The page is organised into the following categories:

```
People
├── Faculty
├── Researchers
├── PhD Students
├── Collaborators
└── Past Members
```

The page does not use internal navigation menus.

---

# D002 – Category Badges

**Status:** Approved

Each member card displays a coloured badge identifying the member category.

| Category | Badge Colour |
|-----------|--------------|
| Faculty | Blue |
| Researchers | Green |
| PhD Students | Yellow |
| Collaborators | Purple |
| Past Members | Grey |

The badge is displayed both in the People page and in the individual profile.

---

# D003 – Main Navigation

**Status:** Approved

The website adopts a flat navigation model.

The main navigation contains only the following entries:

- Home
- About
- Research
- People
- Projects
- Publications
- News
- Resources
- Contacts

Navigation does not use dropdown menus.

Subsections are organised inside the corresponding page.

---

# D004 – Collaborators

**Status:** Approved

Researchers belonging to external universities or research institutions and actively collaborating with PANG are classified as **Collaborators**.

Collaborators are **not** considered Past Members.

Past Members include only former members who no longer actively collaborate with the group.

---

# D005 – People Cards

**Status:** Approved

Every member card follows the same layout.

Each card contains:

- Photo
- Full Name
- Academic Position
- Affiliation
- Category Badge
- View Profile button

The following information is intentionally excluded:

- Biography
- Publications
- Contact information
- Research interests

These elements belong to the personal profile.

---

# D006 – Personal Profile

**Status:** Approved

Each member has an individual profile page.

The profile contains only stable information.

Structure:

- Photo
- Full Name
- Academic Position
- Category
- Affiliation

Sections:

- Biography
- Research Interests
- Selected Publications

External Profiles:

- ORCID
- Google Scholar
- Scopus

Dynamic information such as projects or news is intentionally excluded from Version 1.

---

# D007 – One Page, One Purpose

**Status:** Approved

Each page has one primary objective.

| Page | Purpose |
|------|---------|
| Home | Present the research group |
| About | Present PANG |
| Research | Present research activities |
| People | Present team members |
| Projects | Present research projects |
| Publications | Browse scientific publications |
| News | Present recent activities |
| Resources | Software and datasets |
| Contacts | Contact information |

Pages should avoid mixing unrelated content.

---

# D008 – People Ordering

**Status:** Approved

Members are **not** ordered alphabetically.

The page follows the organisational structure of the research group.

Categories appear in the following order:

1. Faculty
2. Researchers
3. PhD Students
4. Collaborators
5. Past Members

Within each category, ordering is manual.

Alphabetical ordering is intentionally avoided because it does not reflect the organisation and scientific leadership of the group.

---

# D009 – Full Width Layout

**Status:** Approved

All main pages use a **full-width layout**.

Sidebars are intentionally excluded.

Reasons:

- cleaner layout
- better readability
- improved mobile experience
- modern appearance
- easier maintenance

---

# D010 – Standard Page Layout

**Status:** Approved

Every page follows the same logical structure.

```
Page Title

↓

Short Introduction

↓

Main Content

↓

Related Content (optional)

↓

Footer
```

This layout guarantees visual consistency across the entire website.

---

# D011 – Development Strategy

**Status:** Approved

Development follows an incremental approach.

Priority:

1. People
2. Home
3. Research
4. Projects
5. Publications
6. News
7. Resources

The objective is to publish Version 1 within one month.

---

# D012 – Editorial Workflow

**Status:** Approved

Content is created before implementation.

Workflow:

```
Legacy Drupal

↓

Extraction

↓

Editorial Review

↓

Repository

↓

WordPress
```

Content is never written directly inside WordPress.

Repository files are considered the authoritative editorial source.

---

# D013 – Repository Structure

**Status:** Approved

The repository contains only project assets developed by the team.

```
pang-next/
│
├── README.md
├── .gitignore
│
├── docs/
├── pages/
├── content/
├── branding/
├── migration/
├── wordpress/
└── assets/
```

The complete WordPress installation is **not** versioned.

Only custom code, documentation and project assets are stored in Git.

---

# D014 – Minimum Viable Product

**Status:** Approved

Version 1 of PANG Next includes only the functionality required for publication.

Features postponed to Version 2 include:

- Automatic IRIS synchronization
- ORCID synchronization
- Dynamic publication filtering
- Research metrics
- Advanced search
- Interactive collaboration map

---

# Revision History

| Version | Date | Description |
|---------|------|-------------|
| 1.0 | 2026-08-07 | Initial approved decisions |