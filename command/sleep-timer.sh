#!/bin/sh

SLEEP="$1"
[[ "x$SLEEP" = "x" ]] && exit;
sleep ${SLEEP}m
mpc stop