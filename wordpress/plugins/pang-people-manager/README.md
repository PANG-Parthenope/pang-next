# PANG People Manager 0.1.0

Simplified editorial interface for adding PANG People profiles.

## Compatibility

Designed specifically for **PANG People 0.6.4**.

It writes directly to the existing:

- `pang_person` custom post type
- `pang_person_category` taxonomy
- `_pang_*` profile metadata
- WordPress Featured Image

No duplicate People database is created.

## Menu

The plugin reuses the existing **People** menu and replaces the standard WordPress **Add New** submenu with:

`People > Add Person`

The existing People list and `Order People` functions remain managed by PANG People.

## Fields

- Full name
- Category dropdown
- Academic position
- Institutional affiliation
- Profile photo
- Photo vertical position
- Biography
- Research interests
- ORCID
- Google Scholar
- Scopus
- Publish / Draft

## Photo guidance

Recommended profile image:

- portrait orientation
- at least 800 × 1000 px
- face centred
- some space above the head

Images smaller than 800 × 800 px are rejected.

The form previews the circular crop used by the People grid and provides the same 0–100 vertical-position control supported by PANG People.

## Access

The form is available to users with the `edit_pages` capability, including Editors and Administrators.
