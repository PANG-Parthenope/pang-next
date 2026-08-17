# PANG Data Export 0.1.1

Permanent administrative export plugin for the PANG website.

## Purpose

Exports the current WordPress content to structured CSV snapshots:

- People
- Projects
- News
- Publications
- Export All (ZIP)

The export page is available at:

`Tools > PANG Data Export`

to users with the WordPress `edit_others_posts` capability, which normally includes:

- Editors
- Administrators

Authors do not receive access.

## Export design

Each CSV contains standard WordPress fields:

- WordPress ID
- post type
- status
- slug
- title
- content
- excerpt
- published/modified dates
- menu order
- featured image URL
- categories
- tags
- export timestamp

It also automatically adds every custom metadata field currently used by that content type.

This makes the exporter resilient to future additions to PANG People, Research and Publications without requiring the exporter to be manually updated for every new field.

## News

When a WordPress category named/slugged `News` exists, only posts in that category are exported.

## Export All

Creates a ZIP containing:

- `people.csv`
- `projects.csv`
- `news.csv`
- `publications.csv`
- `manifest.json`

The ZIP feature requires the standard PHP `ZipArchive` extension. If the server does not provide it, individual CSV exports still work.

## Recommended workflow

1. Lorenzo or another Editor updates content in WordPress.
2. After a meaningful editorial batch, go to `Tools > PANG Data Export`.
3. Click `Export All`.
4. Store the exported CSV snapshots under the corresponding `content/` directories in the GitHub repository.
5. Commit and push.

WordPress remains the operational source; GitHub remains the versioned archive.


## 0.1.1

- Added native export support for PANG Publications 1.1.1.
- Publications are read directly from the WordPress `{prefix}pang_publications` custom table.
- All table columns are exported, including provenance, Scopus metadata and raw JSON.
- Publications are ordered by year descending, then title ascending.


## 0.1.2
- PANG Data Export is now a top-level WordPress admin menu.
- Editors get a dedicated News menu with All News and Add News.
- The generic Posts and Comments menus are hidden for Editors only.
- Administrator menus are unchanged.
- Add News attempts to preselect the News category when that category exists.
