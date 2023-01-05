<?php
    $hostname = $_GET['hostname'];
    $community = $_GET['community'];
    $snmpver = $_GET['snmpver'];
    try {
        //$device_id = addHost($hostname, $snmpver, $port, $transport, $poller_group, $force_add, $port_assoc_mode, $additional);
        //$link = generate_device_url(array('device_id' => $device_id));
        //print_message("Device added <a href='$link'>$hostname ($device_id)</a>");
        //$command = escapeshellcmd('pwd');
		//$output = shell_exec($command);
		//echo 'SCAN CIDR IP FORMAT ERROR.('.$output.')';
		$command = escapeshellcmd('python ../snmp-scan-custom.py '.$hostname.' -t 32 -c '.$community.' -V '.$snmpver.' -u discovery');
        $output = shell_exec($command);
        if (strpos($output, 'Runtime:') !== false)
        {
            $discovercommand = escapeshellcmd('php ../discovery.php -h new');
            $discoveroutput = shell_exec($discovercommand);
            //$where="hostname in(".substr(strstr($output,"'"),0,strpos(strstr($output,"'"),",@")).")";
            //$datetime= date("Y/m/d H:i:s");
            //$discovery=dbFetchRows("SELECT icon,hostname,sysName,type,version,location_id,os FROM `devices` WHERE disabled = 0 AND $where AND snmp_disable = 0 ORDER BY sysName ASC, device_id DESC");
            echo "Add Device Mass(" . $hostname . ") complete!";
        }
        else{
            echo 'SCAN CIDR IP FORMAT ERROR.('.$command.')';
        }
    } catch (HostUnreachableException $e) {
        echo $e->getMessage();
        foreach ($e->getReasons() as $reason) {
            //print_error($reason);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
?>