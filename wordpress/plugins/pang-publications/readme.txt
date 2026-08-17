PANG Publications
=================
Version 1.0.0

Purpose
-------
- Imports the reviewed Drupal historical publications archive (2004-2024).
- Synchronizes Scopus publications from 2025 onward for 9 configured PANG members.
- Deduplicates by DOI, then Scopus EID, then normalized title + year.
- Provides [pang_publications] shortcode with responsive searchable/filterable table.
- Provides Cite export: APA, IEEE, BibTeX and RIS.
- Runs automatic Scopus synchronization weekly via WP-Cron.

Installation
------------
1. Install and activate the ZIP in WordPress.
2. Open PANG Publications in wp-admin.
3. Confirm historical import count.
4. Click Synchronize Scopus now.
5. Create a page Publications and insert [pang_publications].

API key
-------
The plugin reuses the option pang_scopus_api_key already used by PANG Scopus Diagnostic.
For production it can instead be defined in wp-config.php:
  define('PANG_SCOPUS_API_KEY', '...');
  define('PANG_SCOPUS_INSTTOKEN', '...'); // optional

Security
--------
API credentials are never exposed on the public page. Citation exports are generated locally from stored publication metadata.


1.0.1
- Full author list requested explicitly from Scopus Search API.
- Prefer given-name + surname for display in Authors.
- Correct APA/IEEE citation author parsing and full surnames.
- All authors are retained in BibTeX and RIS exports.


1.0.2
- Scopus records are enriched through Abstract Retrieval META_ABS to store the complete ordered author list.
- Full author lists are cached for 30 days.
- Citation parser preserves compound surnames such as Del Pizzo, De Luca, Di ..., Della ..., Van ..., Von ....
- Existing Scopus records are refreshed by running Synchronize Scopus now.


1.0.3
- Scopus Search now uses COMPLETE view to retrieve full author lists.
- Abstract Retrieval remains a fallback.
- Richer author lists are never overwritten by single-author results.
- Sync log tracks full-author vs single-author records.

1.0.4
- Restores Scopus Search to STANDARD view for compatibility with the API entitlement verified on AlterVista.
- Uses Abstract Retrieval META_ABS only as per-record author enrichment.
- Author-enrichment failures no longer stop the entire synchronization.
- Adds detailed last-sync diagnostics (HTTP, counts, full/single-author records, enrichment errors).
- Fixes fallback parsing for author names without commas.


1.0.5
- Crossref REST API fallback by DOI for complete ordered author metadata.
- Scopus remains the primary source for identifying PANG publications.
- Crossref author lists are cached for 30 days.
- Sync diagnostics report Crossref-enriched records and Crossref errors.
- Existing complete author lists are never replaced by shorter incoming lists.


1.1.0
- Final public-page refinement.
- Renamed PANG Author filter to PANG Member.
- Improved desktop and mobile column proportions and readability.
- Added a concise provenance note below the publication table.
- Reorganized Cite into Copy citation and Export groups.
- Refined citation modal and action buttons.
