#!/bin/bash
password='k7Pdg_vX'
mysql --user=root --password=$password --database=iimspoc17 -e 'truncate table ipv4_mac;'
