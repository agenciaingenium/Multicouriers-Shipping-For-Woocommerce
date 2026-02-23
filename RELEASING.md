# Releasing

## Automatic GitHub releases (semantic-release)

Pushes to `main` run `.github/workflows/release.yml`, which uses `semantic-release` to:

1. Calculate the next version from conventional commits
2. Create a Git tag and GitHub Release
3. Update plugin version metadata and `readme.txt` stable tag
4. Attach a plugin ZIP to the GitHub release

Use conventional commits:

- `fix: ...` -> patch release
- `feat: ...` -> minor release
- `feat!: ...` or `BREAKING CHANGE:` -> major release

## Automatic WordPress.org deployment

When a GitHub Release is published, `.github/workflows/wporg-deploy.yml` deploys to WordPress.org SVN.

Required GitHub repository secrets:

- `WP_ORG_PLUGIN_SLUG` (example: `multicouriers-shipping-for-woocommerce`)
- `WP_ORG_SVN_USERNAME`
- `WP_ORG_SVN_PASSWORD`

The workflow deploys the released tag to:

- `trunk/`
- `tags/<version>/`

## Manual deploy trigger (optional)

You can run the `Deploy to WordPress.org` workflow manually and pass a tag like `v1.0.1`.
