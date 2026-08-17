Sì. Lo aggiornerei trasformandolo da semplice README tecnico in una guida operativa del progetto pang_next. Puoi sostituire il contenuto attuale con questo:

# PANG Next
Source repository for the website of the **PANG – PArthenope Navigation Group**,  
Department of Science and Technology, University of Naples Parthenope.
Website: https://sitopang.altervista.org/
---
## Project structure
```text
pang_next/
├── branding/
│
├── content/
│   ├── home/
│   ├── news/
│   ├── people/
│   ├── projects/
│   └── publications/
│
├── docs/
├── migration/
├── pages/
│
├── wordpress/
│   ├── plugins/
│   ├── tools/
│   │   ├── importers/
│   │   └── diagnostics/
│   └── themes/
│
└── README.md

branding/

PANG visual identity assets, including logos, icons and website graphics.

content/

Reviewed and structured source content used by the website.

* home/ — Home page assets and content
* news/ — News datasets and related assets
* people/ — People master/review datasets
* projects/ — Research Projects datasets
* publications/ — Publications data and supporting files

docs/

Project documentation and technical notes.

migration/

Files and documentation related to the migration from the previous PANG website and legacy data sources.

pages/

Static/editorial material used to build the main WordPress pages.

wordpress/

WordPress-specific source code.

⸻

WordPress architecture

The website uses WordPress with the Blocksy theme and a small set of custom PANG plugins.

The general design principle is:

* WordPress manages day-to-day content;
* custom plugins manage structured and dynamic content;
* CSV datasets are retained as reviewed/master data and for bulk operations;
* GitHub stores the source code, datasets, migration tools and documentation.

⸻

Runtime plugins

The following PANG plugins are part of the production website and should remain installed and active.

PANG People

Manages structured People profiles and the People page.

Current production version:

0.6.4

PANG Research

Manages Research Projects, Research page project grids and Selected Projects on the Home page.

Current production version:

0.3.3

Completed Projects are ordered by year in descending order and alphabetically by acronym when projects have the same year.

PANG Publications

Manages the PANG publications archive, historical records and current publication synchronization.

Current production version:

1.1.1

PANG News Display

Manages the News archive and Latest News displayed on the Home page.

Current production version:

1.2.0

PANG Contacts

Creates and renders the Contacts page, including institutional references, contact information, map and the “G. Simeon” Navigation Laboratory.

Current production version:

0.1.5

PANG Footer Minimal

Provides the custom PANG footer used throughout the website.

Current production version:

0.1.1

⸻

Administrative tools

Importers and diagnostic plugins are not production/runtime plugins.

They are stored under:

wordpress/tools/

and should normally not be installed on the live WordPress website.

Importers

Stored in:

wordpress/tools/importers/

Current archived versions:

* PANG Research Importer 0.2.0
* PANG News CSV Importer 1.1.0
* PANG News Reset Importer 1.1.0
* PANG News Image Importer 1.1.0

These tools were used for reviewed bulk imports and migration operations.

Diagnostics

Stored in:

wordpress/tools/diagnostics/

Current archived diagnostic tool:

* PANG Scopus Diagnostic 0.3.4

Diagnostic tools should only be installed temporarily when required.

⸻

Content management workflow

People

A new individual profile should normally be created directly in WordPress:

People → Add Person

Complete the structured fields, including:

* Full Name
* Category
* Academic Position
* Institutional Affiliation
* Biography
* Research Interests
* ORCID
* Google Scholar
* Scopus
* Featured Image
* Photo vertical position, when required

The main categories include:

* Faculty
* Researchers
* Associated Members
* Students
* Former Members

After publication, update the corresponding reviewed/master dataset under:

content/people/

and commit the change to GitHub.

⸻

Research Projects

New projects should normally be created directly in WordPress:

Research Projects → Add Research Project

Complete the structured fields:

* Acronym
* Title
* Description
* Programme
* Start year
* End year
* Status
* Selected Project
* Responsible Person
* Responsible Role
* Project URL
* Research Areas

Research Areas are:

* Positioning
* Navigation
* Geospatial Technologies

Project status determines where the project is displayed:

ongoing
   ↓
Ongoing Projects
completed
   ↓
Completed Projects

The Selected Project field controls whether a project is displayed in the Selected Projects section of the Home page.

After publication or modification, update the corresponding master dataset under:

content/projects/

and commit the change to GitHub.

⸻

News

New News items should normally be created using standard WordPress posts.

Posts → Add New

Assign the appropriate News category and provide:

* Title
* Content
* Publication date
* Featured Image
* Gallery, when required

PANG News Display automatically manages the News archive and Latest News components.

Bulk News importers should only be used for exceptional migration or reconstruction operations.

⸻

Publications

Publications are managed through PANG Publications.

Historical publication data and current synchronization mechanisms should remain separated from normal editorial WordPress content.

Supporting datasets and publication-related source files are stored under:

content/publications/

⸻

Main website pages

The principal information architecture is:

Home
About
Research
People
Publications
News
Contacts

Home

Provides the main introduction to PANG and includes:

* Hero section
* Research Areas
* Selected Projects
* Latest News

About

Presents the scientific heritage and development of PANG, including:

* group photograph;
* research tradition at the University of Naples Parthenope;
* development of the PArthenope Navigation Group;
* “G. Simeon” Navigation Laboratory;
* research milestones.

Research

Presents:

* Positioning
* Navigation
* Geospatial Technologies
* Ongoing Projects
* Completed Projects

People

Displays structured profiles of current and former PANG members.

Publications

Provides access to the PANG scientific publication archive.

News

Contains research, institutional and dissemination news related to PANG.

Contacts

Provides:

* University of Naples Parthenope location
* “G. Simeon” Navigation Laboratory
* Group email
* Telephone
* University website
* Department of Science and Technology website
* Collaboration information
* Map

⸻

“G. Simeon” Navigation Laboratory

PANG carries out its research, experimental and educational activities at the “G. Simeon” Navigation Laboratory, within the Department of Science and Technology at the University of Naples Parthenope.

The Laboratory is located at:

University of Naples Parthenope
Centro Direzionale di Napoli – Isola C4
4th floor, South Wing
80143 Naples, Italy

Scientific Coordinator:

Prof. Salvatore Gaglione

⸻

Development workflow

The repository should remain aligned with the production WordPress installation.

For custom plugin changes:

modify source
      ↓
increment plugin version
      ↓
install/test on WordPress
      ↓
verify functionality
      ↓
update the same source version in GitHub
      ↓
commit and push

The version stored in:

wordpress/plugins/

should always correspond to the stable version running on WordPress.

Do not keep obsolete plugin versions inside the runtime plugins/ directory.

⸻

Bulk imports

CSV/importer workflows should be reserved for:

* initial migrations;
* bulk data reconstruction;
* reviewed mass updates.

Normal single-record updates should be performed directly in WordPress.

General rule:

NORMAL CONTENT UPDATE
WordPress
   ↓
update reviewed/master dataset
   ↓
GitHub

For bulk migration:

REVIEWED DATASET
   ↓
temporary importer
   ↓
WordPress
   ↓
verification
   ↓
remove importer from WordPress
   ↓
archive importer in wordpress/tools/

⸻

Repository policy

The repository is the long-term source archive for the PANG website.

It should contain:

* current production plugin source code;
* reviewed/master datasets;
* visual identity assets;
* documentation;
* migration material;
* archived administrative tools.

Temporary WordPress plugins should not remain installed on the production website once their task has been completed.

⸻

PANG – PArthenope Navigation Group

Department of Science and Technology
University of Naples Parthenope
Naples, Italy

https://www.uniparthenope.it/
https://www.scienzeetecnologie.uniparthenope.it/

Farei però **una verifica prima del commit**: nel README ho riportato `PANG Contacts 0.1.5`, perché è l'ultima versione che abbiamo identificato come installata. Se in WordPress vedi effettivamente **0.1.5**, il README è allineato.
Come commit userei:
`Update project README with WordPress architecture and maintenance workflow`