#!/usr/bin/env bash

dir=$(dirname "$0")

cd "$dir/.."

rm -rf docs/coverage
./bin/run-tests.sh --coverage-html "docs/coverage"

# MacOS
open "docs/coverage/index.html"

# Linux
xdg-open "docs/coverage/index.html"

# Windows
start "docs/coverage/index.html"