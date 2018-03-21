#!/usr/bin/env bash

tmp=$(mktemp -u)
bin="$(dirname "$0")/run-tests.sh"

$bin --coverage-html "$tmp"

# MacOS
open "$tmp/index.html"

# Linux
xdg-open "$tmp/index.html"

# Windows
start "$tmp/index.html"