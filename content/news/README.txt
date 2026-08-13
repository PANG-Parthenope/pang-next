PANG News images ready package

Canonical news database: news-review_06.csv
Image manifest: news-images-manifest_03.csv

Images present:
- 24 image files
- 11 News with at least one image
- 11 Featured Images
- 13 Gallery images

Import rule:
- image_role=featured -> WordPress Featured Image
- image_role=gallery -> News gallery
- filename is relative to content/news/images/

Folders without images are intentionally retained with .gitkeep where present.
