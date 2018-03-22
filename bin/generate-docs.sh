#!/usr/bin/env bash

dir=$(dirname "$0")

cd "$dir/.."

rm -rf docs/docs
./vendor/bin/apigen generate --destination docs/docs -- classes