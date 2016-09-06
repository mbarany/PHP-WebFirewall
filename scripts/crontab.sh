#!/usr/bin/env bash

pushd `dirname $0` > /dev/null
SCRIPTPATH=`pwd`
popd > /dev/null

DATE=`/bin/date`
flag_path="$SCRIPTPATH/../dist/flag.reload"
iptables_rules="$SCRIPTPATH/../dist/iptables-rules"

if [ -f "$flag_path" ]; then
    echo "[$DATE] Reloading Rules..."
    $iptables_rules
    unlink $flag_path
fi
