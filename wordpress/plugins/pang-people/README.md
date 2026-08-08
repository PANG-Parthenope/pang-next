# PANG People 0.6.4

Consolidated People V1 release.

## Overview
- Four profiles per row on desktop.
- Circular 180 px portraits.
- Per-person vertical focal-position control.
- Lightweight grid without card borders/backgrounds.
- Full Name, Academic Position and Institutional Affiliation.
- Category badges omitted from the overview grid.
- Responsive tablet/mobile layout.
- Manual ordering by category.
- Individual profiles retain Biography, Research Interests, ORCID, Google Scholar, Scopus and category badge.

## Data architecture
The permanent plugin no longer bundles a copy of the editorial People CSV.

The authoritative reviewed dataset belongs in the project repository under:

`content/people/`

This avoids duplicate CSV copies diverging between project content and plugin source. Future bulk updates should use temporary reviewed migration/synchronisation tools rather than embedding the master dataset in the permanent plugin.
