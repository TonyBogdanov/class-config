#!/usr/bin/env bash

dir=$(dirname "$0")

cd "$dir/.."

./bin/run-tests.sh --coverage-text