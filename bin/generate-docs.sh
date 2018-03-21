#!/usr/bin/env bash

dir=$(dirname "$0")

cd "$dir/.."

./vendor/bin/apigen generate --destination docs -- classes