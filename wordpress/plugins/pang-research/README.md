# PANG Research 0.3.0

Clean rebuild of the permanent PANG Research plugin.

## Research page
- `[pang_projects status="ongoing"]` shows all ongoing projects once.
- `[pang_projects status="completed"]` shows all completed projects once.
- Duplicate imported records are collapsed by canonical acronym/project identity.
- Cards use the approved presentation: acronym, full title in italics, description, metadata and Research Area pills.

## Home
- `[pang_selected_projects]` shows only projects with `Selected Project = Yes`.
- Selected status does not affect the Research page.

## Editing
Research Projects can be maintained directly in WordPress via the structured project fields.


## Version 0.3.1

- Fixes duplicate project cards caused by legacy/imported records whose acronym metadata contained the full project title.
- Canonical identity is now derived primarily from the project title prefix (before dash/colon).
- Keeps only the best structured record at render time.
- No data re-import required.


## 0.3.2
- Home Selected Projects cards restyled to match Latest News: white bordered cards, blue top accent, subtle shadow, equal heights and hover treatment.
- Data model and shortcode `[pang_selected_projects]` unchanged.
