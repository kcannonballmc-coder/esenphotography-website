# Esen Photography website

The website of Esen Photography (wedding, family, newborn and fashion photography in Den Bosch), published for free with **GitHub Pages** at https://esenphotography.com.

## How it works

- `site/data/site.json`: all page text, SEO titles and descriptions, contact details, and which photo goes where.
- `site/data/photos.json` and `site/data/alt.json`: the photo list (with categories and sizes) and each photo's description.
- `site/photos/`: web-sized photos (`lg` 1600 px, `sm` 800 px).
- `site/assets/`: styles (`css/main.css`), scripts (`js/main.js`), logo and favicon.
- `site/build.mjs`: turns the above into the finished website in `_site/`.

Every push to `main` that changes `site/` runs `.github/workflows/pages.yml`, which builds the site and publishes it. Progress shows in the repo's **Actions** tab.

## Preview on your computer

```
node site/build.mjs
node site/serve.mjs
```

Then open http://localhost:4173.

## Adding photos

1. Put the original photo in `photos/<category>/` (this folder is not uploaded to GitHub).
2. Add it to `tools/photo-ids.json` with its category, and a description in `site/data/alt.json`.
3. Run `tools/resize-photos.ps1`, then commit `site/photos` and `site/data`.

## Contact form

Messages are delivered by [Web3Forms](https://web3forms.com) to esen.photonl@gmail.com. The access key is `web3formsKey` in `site/data/site.json` (it is meant to be public). Without a key the Contact page shows an email button instead.

## History

The site previously ran on WordPress at GoDaddy. The custom WordPress theme (`esen/`), the old child theme (`stotage-child/`) and the WordPress scripts in `tools/` are kept for reference only and are no longer used.
