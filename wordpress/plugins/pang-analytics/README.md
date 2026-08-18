# PANG Analytics 0.1.1

Cloudflare Web Analytics integration for the PANG website.

## Production domain
- `panglab.eu`
- `www.panglab.eu`

## Cloudflare token
`289780d708e646cd8b091353badbdd66`

## Behaviour
- injects the official Cloudflare Web Analytics beacon in the footer;
- runs only on the production domain;
- does not run in wp-admin;
- does not track logged-in WordPress users, so internal editorial traffic does not pollute public analytics.

## Installation
1. WordPress -> Plugins -> Add New Plugin -> Upload Plugin.
2. Upload `pang-analytics-0.1.1.zip`.
3. Activate **PANG Analytics**.
4. Open `https://www.panglab.eu/` in a private/incognito window.
5. Return to Cloudflare Web Analytics and click **Next**.

## Repository
Store the unpacked source under:

`wordpress/plugins/pang-analytics/`
