<?php

#$config['install_dir'] = "/usr/local/collector";
## Have a look in includes/defaults.inc.php for examples of settings you can set here. DO NOT EDIT defaults.inc.php!

### Database config
$config['db_host'] = '192.168.81.132';
$config['db_user'] = 'XPIUA';
$config['db_pass'] = 'XPIUA';
$config['db_name'] = 'realsun';

// This is the user LibreNMS will run as
//Please ensure this user is created and has the correct permissions to your install
$config['user'] = 'librenms';

### This should *only* be set if you want to *force* a particular hostname/port
### It will prevent the web interface being usable form any other hostname
$config['base_url'] = '/realsun/';

### Enable this to use rrdcached. Be sure rrd_dir is within the rrdcached dir
### and that your web server has permission to talk to rrdcached.
#$config['rrdcached']    = "unix:/var/run/rrdcached.sock";

### Default community
$config['snmp']['community'] = array('public');

### Authentication Model
$config['auth_mechanism'] = "mysql"; # default, other options: ldap, http-auth
#$config['http_auth_guest'] = "guest"; # remember to configure this user if you use http-auth

$config['web_mouseover'] = false;
$config['enable_lazy_load'] = false;
#$config['overview_show_sysDescr'] = false;
$config['show_locations']          = 0;  # Enable Locations on menu
$config['show_locations_dropdown'] = 0;  # Enable Locations dropdown on menu
$config['vertical_summary'] = 0;
### List of RFC1918 networks to allow scanning-based discovery
#$config['nets'][] = "10.0.0.0/8";
#$config['nets'][] = "172.16.0.0/12";
#$config['nets'][] = "192.168.0.0/16";
$config['force_ip_to_sysname'] = true;
# Uncomment the next line to disable daily updates
$config['update'] = 0;

$config['influxdb']['enable'] = true;
$config['influxdb']['transport'] = 'http'; # Default, other options: https, udp
$config['influxdb']['host'] = '192.168.81.132';
$config['influxdb']['port'] = '8086';
$config['influxdb']['db'] = 'realsun';
$config['influxdb']['username'] = 'collectwrite';
$config['influxdb']['password'] = 'collectwrite';
$config['influxdb']['timeout'] = 0; # Optional
$config['influxdb']['verifySSL'] = false; # Optional

# Number in days of how long to keep old rrd files. 0 disables this feature
$config['rrd_purge'] = 0;

# Uncomment to submit callback stats via proxy
#$config['callback_proxy'] = "hostname:port";

# Set default port association mode for new devices (default: ifIndex)
#$config['default_port_association_mode'] = 'ifIndex';

# Enable the in-built billing extension
$config['enable_billing'] = 0;

# Enable the in-built services support (Nagios plugins)
$config['geoloc']['latlng'] = false;
$config['show_services'] = 0;
$config['cpu_details_overview'] = false;
$config['geoloc']['latlng'] = false;
$config['show_locations']          = 0;
$config['show_locations_dropdown'] = 0;
$config['show_services']           = 0;
$config['int_customers']           = 0;
$config['summary_errors']          = 0;
$config['int_transit']             = 0;
$config['int_peering']             = 0;
$config['int_core']                = 0;
$config['int_l2tp']                = 0;
$config['bad_if'][] = 'lo';
$config['bad_ifname'][] = 'lo';
$config['title_image'] = "images/realsun_logo.png";
$config['front_page'] = "pages/devices.inc.php";
$config['page_title_suffix'] = 'realsun';
#$config['favicon'] = 'images/custom/p90.ico'
$config['allow_duplicate_sysName'] = true;

