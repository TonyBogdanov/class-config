#!/usr/bin/env bash

bin="$(dirname "$0")/../vendor/bin/phpunit"
xml="$(dirname "$0")/../phpunit.xml"

$bin --configuration $xml $*