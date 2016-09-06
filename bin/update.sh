#!/usr/bin/env bash

# ensure running as root
if [ "$(id -u)" != "0" ]; then
  exec sudo "$0" "$@"
fi

pushd `dirname $0` > /dev/null
SCRIPTPATH=`pwd`
popd > /dev/null

iptables_rules="$SCRIPTPATH/../dist/iptables-rules"

if [ ! -f "$iptables_rules" ]; then
    echo "#!/usr/bin/env bash" > $iptables_rules
    chmod 0777 $iptables_rules
fi

echo "* * * * * root $SCRIPTPATH/../scripts/crontab.sh d> /dev/null 2>&1" > /etc/cron.d/PHP-WebFirewall
