PANG NEWS DISPLAY 1.0.15

Permanent plugin.

Shortcodes:
[pang_news_archive]
[pang_news_archive posts_per_page="9"]
[pang_latest_news]
[pang_latest_news limit="3" news_page_url="/news/"]

Expected content model:
- Standard WordPress Posts
- Parent category: News (slug: news)
- Optional child categories under News (Conferences, Awards, etc.)

Suggested setup:
1. Activate plugin.
2. Create/open the WordPress page "News".
3. Insert a Shortcode block containing: [pang_news_archive]
4. In Home, insert a Shortcode block where Latest News should appear: [pang_latest_news limit="3" news_page_url="/news/"]

No featured images are required. The card top area is intentionally image-free and shows publication date + category.


1.0.1
- Typography aligned with the site sans-serif theme; removed monospace appearance from filters, cards and metadata.


1.0.2
- Removed the WordPress font preset variable that resolved to monospace on the staging site.
- Forced the News archive and Home cards to the site-compatible system sans-serif stack.


1.0.15
- Home News section now defaults to 3 cards per row.
- Equal-height cards and aligned title/excerpt/link blocks.
- Responsive: 2 columns on tablet, 1 on mobile.

1.0.15: Home section default title changed to “Latest News”; blue separator retained to match Selected Projects.

1.0.15: Home heading changed from h2 to h3 and resized/aligned to match Selected Projects.

1.0.15: Home Latest News heading vertically aligned with Selected Projects; separator forced against theme overrides.

1.0.15: Latest News raised to align with Selected Projects; real separator element added; All news link color aligned with theme links.

1.0.15:
- Fixed stale CSS cache by updating the internal asset VERSION constant.
- Latest News shifted to the Selected Projects heading baseline.
- Separator rendered as a real HTML element and explicitly forced visible.
- All news link color matched to the Home project link.

1.0.15: Latest News heading resized to match the Selected Projects H3 scale.

1.0.15: Removed previous vertical offset; Latest News heading resized to match Selected Projects and separator spacing normalized.

1.0.15:
- Added show_title and show_link attributes to [pang_latest_news].
- Allows Gutenberg to own the Home heading, separator and All news link for exact visual consistency.
- Removed dependence on vertical offset hacks when title/link are hidden.

1.0.15:
- [pang_latest_news] now renders Home cards only by default.
- Heading, separator and All news link should be created as Gutenberg blocks, mirroring Selected Projects.
- Existing show_title/show_link attributes remain available if ever needed.

1.0.15:
- Removed legacy pang-home-news--aligned class from Home output.
- Reset all historical positioning/transform rules.
- Home News grid now uses the same 18px top / 24px bottom spacing as Selected Projects.

1.2.0:
- Featured Images are rendered in archive News cards when available.
- Featured Images are rendered in compact Latest News Home cards when available.
- Featured Image is prepended to individual News article content for the PANG News category.
- News without a Featured Image keep the existing blue fallback treatment.
- Existing body text and managed galleries remain unchanged.
