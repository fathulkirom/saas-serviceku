#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: scripts/release-version.sh <version>"
  echo "Example: scripts/release-version.sh 1.2.0"
  exit 1
fi

version="$1"

if ! [[ "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Invalid version format: $version"
  echo "Expected semantic version (MAJOR.MINOR.PATCH), e.g. 1.2.0"
  exit 1
fi

echo "$version" > VERSION

if grep -q '^APP_VERSION=' .env.example; then
  sed -i.bak "s/^APP_VERSION=.*/APP_VERSION=$version/" .env.example
  rm -f .env.example.bak
else
  awk -v v="$version" 'NR==1{print; print "APP_VERSION="v; next}1' .env.example > .env.example.tmp
  mv .env.example.tmp .env.example
fi

echo "Version updated to $version"
echo "Updated files: VERSION, .env.example"
