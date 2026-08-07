# People migration summary

Records extracted: **26**

## Proposed classification

- Faculty: **9**
- Researchers: **1**
- PhD Students: **4**
- Alumni: **12**

## Data quality notes

- The source uses legacy role labels (`Professor`, `Associate Professor`, `Student`, `Ph.D`, `Technician`, `Past Members`).
- `Past Members` were mapped to `Alumni`.
- `Professor` and `Associate Professor` were mapped to `Faculty`.
- `Student` and `Ph.D` were provisionally mapped to `PhD Students` and require review.
- `Technician` was provisionally mapped to `Researchers` because the current menu has no technical-staff section.
- Photos are referenced by Drupal `public://...` URIs. The actual image files must be copied from the legacy `sites/default/files` tree.
- Blank biographies/contact fields have been preserved as blank rather than invented.

## Next step

Review `people.csv`, correct `proposed_section` and any outdated role/contact information, then use it as the source for the WordPress People import.