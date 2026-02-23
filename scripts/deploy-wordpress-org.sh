#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

TAG_NAME="${TAG_NAME:-${1:-}}"
if [[ -z "${TAG_NAME}" ]]; then
  echo "TAG_NAME is required (example: v1.0.1)" >&2
  exit 1
fi

VERSION="${TAG_NAME#v}"
if [[ -z "${VERSION}" || "${VERSION}" == "${TAG_NAME}" ]]; then
  echo "Tag must start with 'v' (received: ${TAG_NAME})" >&2
  exit 1
fi

: "${WP_ORG_PLUGIN_SLUG:?Missing WP_ORG_PLUGIN_SLUG}"
: "${WP_ORG_SVN_USERNAME:?Missing WP_ORG_SVN_USERNAME}"
: "${WP_ORG_SVN_PASSWORD:?Missing WP_ORG_SVN_PASSWORD}"

SVN_URL="https://plugins.svn.wordpress.org/${WP_ORG_PLUGIN_SLUG}"
WORK_DIR="$(mktemp -d)"
SVN_DIR="${WORK_DIR}/svn"
BUILD_DIR="${WORK_DIR}/build/${WP_ORG_PLUGIN_SLUG}"

cleanup() {
  rm -rf "${WORK_DIR}"
}
trap cleanup EXIT

echo "Deploying ${WP_ORG_PLUGIN_SLUG} ${VERSION} to WordPress.org SVN"

svn checkout \
  --non-interactive \
  --username "${WP_ORG_SVN_USERNAME}" \
  --password "${WP_ORG_SVN_PASSWORD}" \
  "${SVN_URL}" "${SVN_DIR}"

mkdir -p "${BUILD_DIR}"

rsync -a \
  --delete \
  --exclude '.git/' \
  --exclude '.github/' \
  --exclude 'node_modules/' \
  --exclude 'dist/' \
  --exclude 'scripts/' \
  --exclude '.releaserc.json' \
  --exclude 'package.json' \
  --exclude 'package-lock.json' \
  --exclude '.DS_Store' \
  --exclude '*.log' \
  "${ROOT_DIR}/" "${BUILD_DIR}/"

mkdir -p "${SVN_DIR}/trunk" "${SVN_DIR}/tags"

rsync -a --delete "${BUILD_DIR}/" "${SVN_DIR}/trunk/"
rm -rf "${SVN_DIR}/tags/${VERSION}"
mkdir -p "${SVN_DIR}/tags/${VERSION}"
rsync -a --delete "${BUILD_DIR}/" "${SVN_DIR}/tags/${VERSION}/"

svn status "${SVN_DIR}" | awk '/^\?/ {print substr($0,9)}' | while IFS= read -r file; do
  [[ -n "${file}" ]] && svn add --parents "${file}"
done

svn status "${SVN_DIR}" | awk '/^\!/ {print substr($0,9)}' | while IFS= read -r file; do
  [[ -n "${file}" ]] && svn rm --force "${file}"
done

if [[ -z "$(svn status "${SVN_DIR}")" ]]; then
  echo "No SVN changes to commit."
  exit 0
fi

svn commit \
  --non-interactive \
  --username "${WP_ORG_SVN_USERNAME}" \
  --password "${WP_ORG_SVN_PASSWORD}" \
  -m "Release ${VERSION}" \
  "${SVN_DIR}"

echo "WordPress.org deploy complete: ${VERSION}"
