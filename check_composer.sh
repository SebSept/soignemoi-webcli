#!/usr/bin/env bash
version=$(composer --version |  awk '{print $3}')
echo "Composer version extracted : $version"
echo "Querying OSV API for Composer vulnerabilities... (exit code is the result)"
# Ckecking osv response is {}
curl -sd '{"version": "'"$version"'",  "package": {"name": "composer/composer", "ecosystem": "Packagist"}}' "https://api.osv.dev/v1/query" \
| grep "^{}$" -q
