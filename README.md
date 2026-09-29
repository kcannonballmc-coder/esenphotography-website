# Esen Photography website

Code for the WordPress site hosted on GoDaddy (Managed WordPress), using the **Stotage** theme.

## What lives here

- `stotage-child/`: the child theme. Custom CSS goes in `style.css`, custom PHP in `functions.php`.
- `.github/workflows/deploy.yml`: uploads `stotage-child/` to GoDaddy over SFTP on every push to `main`.

Page content, layouts (Elementor), photos and settings live in the WordPress database. Edit those in WP Admin, not here.

## Workflow

1. Edit files in `stotage-child/`.
2. Commit and push to `main`.
3. GitHub Actions deploys automatically. Check progress in the repo's **Actions** tab.

## Required GitHub secrets (Settings → Secrets and variables → Actions)

| Secret          | Value                              |
|-----------------|------------------------------------|
| `SFTP_HOST`     | GoDaddy SFTP host                  |
| `SFTP_USERNAME` | GoDaddy SFTP username              |
| `SFTP_PASSWORD` | GoDaddy SFTP password              |
| `SFTP_PORT`     | Optional, defaults to `22`         |

The deploy finds the themes folder on the server automatically. To force a path, set the repository **variable** `REMOTE_THEMES_DIR` (e.g. `html/wp-content/themes`).
