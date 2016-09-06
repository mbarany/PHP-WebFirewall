#!/usr/bin/env bash

# iptables rules
#
# http://www.thegeekstuff.com/scripts/iptables-rules

# Clear rules and  delete all non-default Chains
iptables -F
iptables -X

# Set Chain defaults
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT

# Established Sessions
iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT

# Allow local loopback
iptables -A INPUT -i lo -j ACCEPT

# Ping
#iptables -A INPUT -p icmp --icmp-type echo-request -j ACCEPT

# Add Web Firewall Chain
iptables -N WEB-FIREWALL
iptables -t filter -A INPUT -j WEB-FIREWALL

# DNS
iptables -A INPUT -p udp --dport 53 -j ACCEPT
iptables -A INPUT -p tcp --dport 53 -j ACCEPT

# HTTP/HTTPS
iptables -A INPUT -p tcp --dport 80 -m state --state NEW,ESTABLISHED -j ACCEPT
iptables -A INPUT -p tcp --dport 443 -m state --state NEW,ESTABLISHED -j ACCEPT

# Logging - rules should be at bottom
iptables -N LOGGING
iptables -A INPUT -j LOGGING
iptables -A LOGGING -m limit --limit 2/min -j LOG --log-prefix "IPTables Packet Dropped: " --log-level 4
iptables -A LOGGING -j DROP

# Save the rules for server restarts
iptables-save
