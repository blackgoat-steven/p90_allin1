#/bin/bash
echo "remove ip in db" $1
db=$1
iplist=$2
mysql --user=root --password=k7Pdg_vX $db -e 'select hostname from devices;'|grep $2 > /tmp/hostlist

while read line
do

/usr/local/saas/$db/delhost.php $line

done < /tmp/hostlist
