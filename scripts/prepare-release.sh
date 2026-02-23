#!/usr/bin/env bash
set -euo pipefail

VERSION="${1:-}"

if [[ -z "$VERSION" ]]; then
  echo "Usage: $0 <version>" >&2
  exit 1
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_FILE="$ROOT_DIR/multicouriers-shipping-for-woocommerce.php"
README_FILE="$ROOT_DIR/readme.txt"
DIST_DIR="$ROOT_DIR/dist"
STAGE_DIR="$DIST_DIR/package"
PLUGIN_SLUG="multicouriers-shipping-for-woocommerce"
ZIP_FILE="$DIST_DIR/${PLUGIN_SLUG}-${VERSION}.zip"

update_file_versions() {
  perl -0pi -e "s/\\* Version:\\s+[0-9]+\\.[0-9]+\\.[0-9]+/* Version: ${VERSION}/g" "$PLUGIN_FILE"
  perl -0pi -e "s/define\\('MCWS_VERSION',\\s*'[0-9]+\\.[0-9]+\\.[0-9]+'\\)/define('MCWS_VERSION', '${VERSION}')/g" "$PLUGIN_FILE"
  perl -0pi -e "s/^Stable tag:\\s+[0-9]+\\.[0-9]+\\.[0-9]+/Stable tag: ${VERSION}/m" "$README_FILE"
}

build_zip() {
  rm -rf "$STAGE_DIR" "$ZIP_FILE"
  mkdir -p "$STAGE_DIR/$PLUGIN_SLUG" "$DIST_DIR"

  rsync -a \
    --exclude '.git/' \
    --exclude '.github/' \
    --exclude 'node_modules/' \
    --exclude 'dist/' \
    --exclude '.releaserc.json' \
    --exclude 'package.json' \
    --exclude 'package-lock.json' \
    --exclude 'scripts/' \
    "$ROOT_DIR/" "$STAGE_DIR/$PLUGIN_SLUG/"

  (
    cd "$STAGE_DIR"
    zip -rq "$ZIP_FILE" "$PLUGIN_SLUG"
  )
}

update_file_versions
build_zip

echo "Prepared release ${VERSION}"
echo "ZIP: ${ZIP_FILE}"
