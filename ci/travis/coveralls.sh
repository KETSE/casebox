#!/usr/bin/env bash

php vendor/bin/coveralls \
    --coverage_clover=build/logs/clover-rpc.xml \
    --coverage_clover=build/logs/clover-core.xml