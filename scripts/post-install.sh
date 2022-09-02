#!/bin/bash
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
TRACE_DIR=/../src/tracedTabs
mkdir -p -m 777 $SCRIPT_DIR$TRACE_DIR || true
