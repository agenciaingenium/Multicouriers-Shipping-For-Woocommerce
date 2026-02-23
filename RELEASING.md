# Releasing (manual, same flow as `clevers-agent`)

This repository uses a manual release flow via GitHub Actions (not semantic-release).

## Workflows

- `.github/workflows/create_release.yml`
  - Manually triggered (`workflow_dispatch`)
  - Bumps plugin version and `readme.txt` stable tag using `bin/bump-version.php`
  - Commits the version bump
  - Pushes the branch and tag
  - Creates the GitHub Release

- `.github/workflows/release_zip.yml`
  - Runs on GitHub Release publish
  - Builds and attaches the plugin ZIP

- `.github/workflows/deploy_wordpress_org.yml`
  - Runs on GitHub Release publish
  - Deploys to WordPress.org SVN using `10up/action-wordpress-plugin-deploy`

## Required GitHub secrets

For `create_release.yml`:

- `RELEASE_BOT_TOKEN`
  - Personal Access Token with repo write permissions
  - Used so the release creation/tag push can trigger downstream workflows reliably

For `deploy_wordpress_org.yml`:

- `WORDPRESS_ORG_USERNAME`
- `WORDPRESS_ORG_PASSWORD`

## How to create a release

1. Go to **Actions > Create Release**
2. Click **Run workflow**
3. Enter:
   - `version`: e.g. `1.0.1`
   - `target_branch`: usually `main`
   - `draft_release`: optional

The workflows will then:

1. Create/push tag `vX.Y.Z`
2. Create GitHub Release
3. Attach the plugin ZIP
4. Deploy to WordPress.org SVN

## Notes

- `create_release.yml` validates semver format (`X.Y.Z`)
- `release_zip.yml` validates that plugin header `Version` matches the release tag
- `.distignore` controls what is excluded from the ZIP/SVN package
