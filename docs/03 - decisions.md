# PANG Next

# 03 – Project Decisions

**Version:** 2.0

**Status:** Approved

**Last Update:** 2026-08-07

---

# Purpose

This document records the architectural, editorial and technical decisions adopted during the development of **PANG Next**.

Approved decisions are considered stable and constitute the reference for the implementation of the website.

---

# D001 – People Structure

**Status:** Approved

The website contains a single **People** page organised into the following sections.

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

| Category | Colour |
|----------|--------|
| Faculty | Blue |
| Researchers | Green |
| PhD Students | Yellow |
| Collaborators | Purple |
| Past Members | Grey |

---

# D003 – Main Navigation

**Status:** Approved

The website adopts a flat navigation model.

The main navigation contains only:

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

---

# D004 – Collaborators

**Status:** Approved

Researchers belonging to external universities or research institutions and actively collaborating with PANG are classified as **Collaborators**.

Collaborators are **not** considered Past Members.

Past Members include only former members who no longer actively collaborate with the group.

---

# D005 – People Cards

**Status:** Approved

All member cards follow the same layout.

Each card contains:

- Photo
- Full Name
- Academic Position
- Affiliation
- Category Badge
- View Profile

The following information is intentionally excluded:

- Biography
- Publications
- Contact information
- Research interests

---

# D006 – Personal Profile

**Status:** Approved

Each member has an individual profile page.

The profile contains:

- Photo
- Full Name
- Academic Position
- Affiliation
- Category

Sections:

- Biography
- Research Interests
- Selected Publications

External Profiles:

- ORCID
- Google Scholar
- Scopus

Projects, news and other dynamic information are intentionally excluded from Version 1.

---

# D007 – One Page, One Purpose

**Status:** Approved

Each page has a single objective.

| Page | Purpose |
|------|---------|
| Home | Present the research group |
| About | Present PANG |
| Research | Present research activities |
| People | Present team members |
| Projects | Present research projects |
| Publications | Browse scientific publications |
| News | Present recent activities |
| Resources | Present software and datasets |
| Contacts | Contact information |

---

# D008 – People Ordering

**Status:** Approved

Members are not ordered alphabetically.

Categories appear in the following order:

1. Faculty
2. Researchers
3. PhD Students
4. Collaborators
5. Past Members

Members inside each category are ordered manually.

---

# D009 – Full Width Layout

**Status:** Approved

All main pages use a full-width layout.

Sidebars are intentionally excluded.

Reasons:

- Better readability
- Cleaner layout
- Improved mobile experience
- Simpler maintenance
- Modern appearance

---

# D010 – Standard Page Layout

**Status:** Approved

Every page follows the same structure.

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

This guarantees consistency across the website.

---

# D011 – Affiliation

**Status:** Approved

Each personal profile displays only the institutional affiliation.

Examples:

- University of Naples Parthenope
- University of Salerno
- National Research Council (CNR)
- Delft University of Technology

Departments are intentionally omitted.

---

# D012 – Version 1 Scope

**Status:** Approved

Version 1 adopts the same profile structure for every category.

No exceptions are made for Faculty, Researchers, PhD Students, Collaborators or Past Members.

More advanced sections may be introduced in future releases.

---

# D013 – Research Structure

**Status:** Approved

The Research page is organised into three main areas.

- Positioning
- Navigation
- Remote Sensing

Navigation includes:

- Maritime Navigation
- Air Navigation
- Integrated Navigation

---

# D014 – Official PANG Statement

**Status:** Approved

The official description of the research group is:

> The PArthenope Navigation Group (PANG) brings together faculty members, researchers, PhD students and collaborators conducting research in positioning, maritime and air navigation, geospatial technologies, and resilient Positioning, Navigation and Timing (PNT).

This statement is used throughout the website and in the project documentation.

---

# D015 – Editorial Workflow

**Status:** Approved

Content is created before implementation.

Workflow:

```
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

Repository files are considered the editorial source.

WordPress is the publishing platform.

---

# D016 – Repository Structure

**Status:** Approved

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

Only project documentation, custom code and editorial assets are versioned.

The complete WordPress installation is intentionally excluded.

---

# D017 – Development Strategy

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

# D018 – MVP Principle

**Status:** Approved

Every implementation decision must answer the following question:

> Does this help us publish Version 1 within one month?

If the answer is **yes**, the feature is included.

Otherwise, it is postponed to Version 2.

---

# Revision History

| Version | Date | Description |
|----------|------------|------------------------------|
| 1.0 | 2026-08-07 | Initial version |
| 2.0 | 2026-08-07 | Consolidated architectural decisions |