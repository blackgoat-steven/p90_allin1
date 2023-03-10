<?php

use App\Models\PortsNac;
use LibreNMS\Config;

if (!is_numeric($vars['device'])) {
    $vars['device'] = getidbyname($vars['device']);
}

$permitted_by_port = $vars['tab'] == 'port' && port_permitted($vars['port'], $vars['device']);

if (device_permitted($vars['device']) || $permitted_by_port) {
    if (empty($vars['tab'])) {
        $tab = 'overview';
    } else {
        $tab = str_replace('.', '', $vars['tab']);
    }
    $select = array($tab => 'class="active"');

    $device = device_by_id_cache($vars['device']);
    $attribs = get_dev_attribs($device['device_id']);
    $device['attribs'] = $attribs;
    load_os($device);

    $entity_state = get_dev_entity_state($device['device_id']);

    // print_r($entity_state);
    $pagetitle[] = format_hostname($device, $device['hostname']);

    $component = new LibreNMS\Component();
    $component_count = $component->getComponentCount($device['device_id']);

    $alert_class = '';
    if ($device['disabled'] == '1') {
        $alert_class = 'alert-info';
    } elseif ($device['status'] == '0') {
        $alert_class = 'alert-danger';
    }

    echo '<div class="panel panel-default">';
        echo '<div class="panel-body '.$alert_class.'">';
        require 'includes/html/device-header.inc.php';
        echo '</div>';
    echo '</div>';


    if (device_permitted($device['device_id'])) {
        echo '<ul class="nav nav-tabs">';

        if (Config::get('show_overview_tab')) {
            echo '
                <li role="presentation" '.$select['overview'].'>
                <a href="'.generate_device_url($device, array('tab' => 'overview')).'">
		<i class="fa fa-lightbulb-o fa-lg icon-theme" aria-hidden="true"></i> Overview
                </a>
                </li>';
        }

        echo '<li role="presentation" '.$select['graphs'].'>
            <a href="'.generate_device_url($device, array('tab' => 'graphs')).'">
            <i class="fa fa-area-chart fa-lg icon-theme" aria-hidden="true"></i> Graphs
            </a>
            </li>';

        $health =  dbFetchCell("SELECT COUNT(*) FROM storage WHERE device_id = ?", array($device['device_id'])) +
                   dbFetchCell("SELECT COUNT(*) FROM sensors WHERE device_id = ?", array($device['device_id'])) +
                   dbFetchCell("SELECT COUNT(*) FROM mempools WHERE device_id = ?", array($device['device_id'])) +
                   dbFetchCell("SELECT COUNT(*) FROM processors WHERE device_id = ?", array($device['device_id'])) +
                   count_mib_health($device);

        if ($health) {
            echo '<li role="presentation" '.$select['health'].'>
                <a href="'.generate_device_url($device, array('tab' => 'health')).'">
                <i class="fa fa-heartbeat fa-lg icon-theme" aria-hidden="true"></i> Health
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM applications WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['apps'].'>
                <a href="'.generate_device_url($device, array('tab' => 'apps')).'">
                <i class="fa fa-cubes fa-lg icon-theme" aria-hidden="true"></i> Apps
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM processes WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['processes'].'>
                <a href="'.generate_device_url($device, array('tab' => 'processes')).'">
                <i class="fa fa-microchip fa-lg icon-theme" aria-hidden="true"></i> Processes
                </a>
                </li>';
        }

        if (Config::has('collectd_dir') && is_dir(Config::get('collectd_dir') . '/' . $device['hostname'] . '/')) {
            echo '<li role="presentation" '.$select['collectd'].'>
                <a href="'.generate_device_url($device, array('tab' => 'collectd')).'">
                <i class="fa fa-pie-chart fa-lg icon-theme" aria-hidden="true"></i> CollectD
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM munin_plugins WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['munin'].'>
                <a href="'.generate_device_url($device, array('tab' => 'munin')).'">
                <i class="fa fa-pie-chart fa-lg icon-theme" aria-hidden="true"></i> Munin
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM ports WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['ports'].$select['port'].'">
                <a href="'.generate_device_url($device, array('tab' => 'ports')).'">
                <i class="fa fa-link fa-lg icon-theme" aria-hidden="true"></i> Ports
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM slas WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['slas'].$select['sla'].'>
                <a href="'.generate_device_url($device, array('tab' => 'slas')).'">
                <i class="fa fa-flag fa-lg icon-theme" aria-hidden="true"></i> SLAs
                </a>
                </li>';
        }

        if (dbFetchCell('SELECT 1 FROM `wireless_sensors` WHERE `device_id`=?', array($device['device_id']))) {
            echo '<li role="presentation" '.$select['wireless'].'>
                <a href="'.generate_device_url($device, array('tab' => 'wireless')).'">
                <i class="fa fa-wifi fa-lg icon-theme"  aria-hidden="true"></i> Wireless
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM access_points WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['accesspoints'].'>
                <a href="'.generate_device_url($device, array('tab' => 'accesspoints')).'">
                <i class="fa fa-wifi fa-lg icon-theme"  aria-hidden="true"></i> Access Points
                </a>
                </li>';
        }

        $smokeping_files = get_smokeping_files($device);

        if (count($smokeping_files['in'][$device['hostname']]) || count($smokeping_files['out'][$device['hostname']])) {
            echo '<li role="presentation" '.$select['latency'].'>
                <a href="'.generate_device_url($device, array('tab' => 'latency')).'">
                <i class="fa fa-crosshairs fa-lg icon-theme"  aria-hidden="true"></i> Ping
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM vlans WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['vlans'].'>
                <a href="'.generate_device_url($device, array('tab' => 'vlans')).'">
                <i class="fa fa-tasks fa-lg icon-theme"  aria-hidden="true"></i> VLANs
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM vminfo WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['vm'].'>
                <a href="'.generate_device_url($device, array('tab' => 'vm')).'">
                <i class="fa fa-cog fa-lg icon-theme"  aria-hidden="true"></i> Virtual Machines
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM mefinfo WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['mef'].'>
                <a href="'.generate_device_url($device, array('tab' => 'mef')).'">
                <i class="fa fa-link fa-lg icon-theme"  aria-hidden="true"></i> Metro Ethernet
                </a>
                </li>';
        }

        if ($device['os'] == 'coriant') {
            if (dbFetchCell("SELECT 1 FROM tnmsneinfo WHERE device_id = ?", array($device['device_id']))) {
                echo '<li class="'.$select['tnmsne'].'">
                    <a href="'.generate_device_url($device, array('tab' => 'tnmsne')).'">
                    <i class="fa fa-link fa-lg icon-theme"  aria-hidden="true"></i> Hardware
                    </a>
                    </li>';
            }
        }

        // $loadbalancer_tabs is used in device/loadbalancer/ to build the submenu. we do it here to save queries
        if ($device['os'] == 'netscaler') {
            // Netscaler
            $device_loadbalancer_count['netscaler_vsvr'] = dbFetchCell('SELECT COUNT(*) FROM `netscaler_vservers` WHERE `device_id` = ?', array($device['device_id']));
            if ($device_loadbalancer_count['netscaler_vsvr']) {
                $loadbalancer_tabs[] = 'netscaler_vsvr';
            }
        }

        if ($device['os'] == 'acsw') {
            // Cisco ACE
            $device_loadbalancer_count['loadbalancer_vservers'] = dbFetchCell('SELECT COUNT(*) FROM `loadbalancer_vservers` WHERE `device_id` = ?', array($device['device_id']));
            if ($device_loadbalancer_count['loadbalancer_vservers']) {
                $loadbalancer_tabs[] = 'loadbalancer_vservers';
            }
        }

        // F5 LTM
        if (isset($component_count['f5-ltm-vs'])) {
            $device_loadbalancer_count['ltm_vs'] = $component_count['f5-ltm-vs'];
            $loadbalancer_tabs[] = 'ltm_vs';
        }
        if (isset($component_count['f5-ltm-pool'])) {
            $device_loadbalancer_count['ltm_pool'] = $component_count['f5-ltm-pool'];
            $loadbalancer_tabs[] = 'ltm_pool';
        }
        if (isset($component_count['f5-gtm-wide'])) {
            $device_loadbalancer_count['gtm_wide'] = $component_count['f5-gtm-wide'];
            $loadbalancer_tabs[] = 'gtm_wide';
        }
        if (isset($component_count['f5-gtm-pool'])) {
            $device_loadbalancer_count['gtm_pool'] = $component_count['f5-gtm-pool'];
            $loadbalancer_tabs[] = 'gtm_pool';
        }

        if (is_array($loadbalancer_tabs)) {
            echo '<li role="presentation" '.$select['loadbalancer'].'>
                <a href="'.generate_device_url($device, array('tab' => 'loadbalancer')).'">
                <i class="fa fa-balance-scale fa-lg icon-theme"  aria-hidden="true"></i> Load Balancer
                </a>
                </li>';
        }

        // $routing_tabs is used in device/routing/ to build the tabs menu. we built it here to save some queries
        $device_routing_count['loadbalancer_rservers'] = dbFetchCell('SELECT COUNT(*) FROM `loadbalancer_rservers` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['loadbalancer_rservers']) {
            $routing_tabs[] = 'loadbalancer_rservers';
        }

        $device_routing_count['ipsec_tunnels'] = dbFetchCell('SELECT COUNT(*) FROM `ipsec_tunnels` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['ipsec_tunnels']) {
            $routing_tabs[] = 'ipsec_tunnels';
        }

        $device_routing_count['bgp'] = dbFetchCell('SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['bgp']) {
            $routing_tabs[] = 'bgp';
        }

        $device_routing_count['ospf'] = dbFetchCell("SELECT COUNT(*) FROM `ospf_instances` WHERE `ospfAdminStat` = 'enabled' AND `device_id` = ?", array($device['device_id']));
        if ($device_routing_count['ospf']) {
            $routing_tabs[] = 'ospf';
        }

        $device_routing_count['cef'] = dbFetchCell('SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['cef']) {
            $routing_tabs[] = 'cef';
        }

        $device_routing_count['vrf'] = @dbFetchCell('SELECT COUNT(*) FROM `vrfs` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['vrf']) {
            $routing_tabs[] = 'vrf';
        }

        $device_routing_count['mpls'] = @dbFetchCell('SELECT COUNT(*) FROM `mpls_lsps` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['mpls']) {
            $routing_tabs[] = 'mpls';
        }

        $device_routing_count['cisco-otv'] = $component_count['Cisco-OTV'];
        if ($device_routing_count['cisco-otv'] > 0) {
            $routing_tabs[] = 'cisco-otv';
        }

        if (is_array($routing_tabs)) {
            echo '<li role="presentation" '.$select['routing'].'>
                <a href="'.generate_device_url($device, array('tab' => 'routing')).'">
                <i class="fa fa-random fa-lg icon-theme"  aria-hidden="true"></i> Routing
                </a>
                </li>';
        }

        if (dbFetchCell('SELECT 1 FROM `pseudowires` WHERE `device_id` = ?', array($device['device_id']))) {
            echo '<li role="presentation" '.$select['pseudowires'].'>
                <a href="'.generate_device_url($device, array('tab' => 'pseudowires')).'">
                <i class="fa fa-arrows-alt fa-lg icon-theme"  aria-hidden="true"></i> Pseudowires
                </a>
                </li>';
        }
        if (dbFetchCell("SELECT 1 FROM `links` where `local_device_id`=?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['neighbours'].'>
                    <a href="'.generate_device_url($device, array('tab' => 'neighbours')).'">
                      <i class="fa fa-sitemap fa-lg icon-theme"  aria-hidden="true"></i> Neighbours
                    </a>
                  </li>';
        }
        if (dbFetchCell("SELECT 1 FROM stp WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['stp'].'>
                <a href="'.generate_device_url($device, array('tab' => 'stp')).'">
                <i class="fa fa-sitemap fa-lg icon-theme"  aria-hidden="true"></i> STP
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM `packages` WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['packages'].'>
                <a href="'.generate_device_url($device, array('tab' => 'packages')).'">
                <i class="fa fa-folder fa-lg icon-theme"  aria-hidden="true"></i> Pkgs
                </a>
                </li>';
        }

        if (Config::get('enable_inventory')) {
            if (dbFetchCell("SELECT 1 FROM `entPhysical` WHERE device_id = ?", array($device['device_id']))) {
                echo '<li role="presentation" ' . $select['entphysical'] . '>
                    <a href="' . generate_device_url($device, array('tab' => 'entphysical')) . '">
                    <i class="fa fa-cube fa-lg icon-theme"  aria-hidden="true"></i> Inventory
                    </a>
                    </li>';
            } elseif (@dbFetchCell("SELECT 1 FROM `hrDevice` WHERE device_id = ?", array($device['device_id']))) {
                echo '<li role="presentation" ' . $select['hrdevice'] . '>
                    <a href="' . generate_device_url($device, array('tab' => 'hrdevice')) . '">
                    <i class="fa fa-cube fa-lg icon-theme"  aria-hidden="true"></i> Inventory
                    </a>
                    </li>';
            }
        }

        if (Config::get('show_services')) {
            echo '<li role="presentation" '.$select['services'].'>
                <a href="'.generate_device_url($device, array('tab' => 'services')).'">
                <i class="fa fa-cogs fa-lg icon-theme"  aria-hidden="true"></i> Services
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT 1 FROM toner WHERE device_id = ?", array($device['device_id']))) {
            echo '<li role="presentation" '.$select['toner'].'>
                <a href="'.generate_device_url($device, array('tab' => 'toner')).'">
                <i class="fa fa-print fa-lg icon-theme"  aria-hidden="true"></i> Toner
                </a>
                </li>';
        }

        echo '<li role="presentation" '.$select['logs'].'>
            <a href="'.generate_device_url($device, array('tab' => 'logs')).'">
            <i class="fa fa-sticky-note fa-lg icon-theme"  aria-hidden="true"></i> Logs
            </a>
            </li>';

        echo '<li role="presentation" '.$select['alerts'].'>
            <a href="'.generate_device_url($device, array('tab' => 'alerts')).'">
            <i class="fa fa-exclamation-circle fa-lg icon-theme"  aria-hidden="true"></i> Alerts
            </a>
            </li>';
/*
        echo '<li role="presentation" '.$select['alert-stats'].'>
            <a href="'.generate_device_url($device, array('tab' => 'alert-stats')).'">
            <i class="fa fa-bar-chart fa-lg icon-theme"  aria-hidden="true"></i> Alert Stats
            </a>
            </li>';
*/
        if (Auth::user()->hasGlobalAdmin()) {
            foreach ((array)Config::get('rancid_configs', []) as $configs) {
                if ($configs[(strlen($configs) - 1)] != '/') {
                    $configs .= '/';
                }

                if (is_file($configs.$device['hostname'])) {
                    $device_config_file = $configs.$device['hostname'];
                } elseif (is_file($configs.strtok($device['hostname'], '.'))) { // Strip domain
                    $device_config_file = $configs.strtok($device['hostname'], '.');
                } else {
                    if (!empty(Config::get('mydomain'))) { // Try with domain name if set
                        if (is_file($configs . $device['hostname'] . '.' . Config::get('mydomain'))) {
                            $device_config_file = $configs . $device['hostname'] . '.' . Config::get('mydomain');
                        }
                    }
                } // end if
            }

            if (Config::get('oxidized.enabled') === true && !in_array($device['type'], Config::get('oxidized.ignore_types')) && Config::has('oxidized.url')) {
                $device_config_file = true;
            }
        }

        if ($device_config_file) {
            if (!get_dev_attrib($device, 'override_Oxidized_disable', 'true')) {
                echo '<li class="'.$select['showconfig'].'">
                    <a href="'.generate_device_url($device, array('tab' => 'showconfig')).'">
                    <i class="fa fa-align-justify fa-lg icon-theme"  aria-hidden="true"></i> Config
                    </a>
                    </li>';
            }
        }

        if (Config::get('nfsen_enable')) {
            foreach ((array)Config::get('nfsen_rrds', []) as $nfsenrrds) {
                if ($nfsenrrds[(strlen($nfsenrrds) - 1)] != '/') {
                    $nfsenrrds .= '/';
                }

                $nfsensuffix = Config::get('nfsen_suffix', '');

                if (Config::get('nfsen_split_char')) {
                    $basefilename_underscored = preg_replace('/\./', Config::get('nfsen_split_char'), $device['hostname']);
                } else {
                    $basefilename_underscored = $device['hostname'];
                }

                $nfsen_filename           = preg_replace('/'.$nfsensuffix.'/', '', $basefilename_underscored);
                if (is_file($nfsenrrds.$nfsen_filename.'.rrd')) {
                    $nfsen_rrd_file = $nfsenrrds.$nfsen_filename.'.rrd';
                }
            }
        }//end if

        if ($nfsen_rrd_file) {
            echo '<li role="presentation" '.$select['nfsen'].'>
                <a href="'.generate_device_url($device, array('tab' => 'nfsen')).'">
                <i class="fa fa-tint fa-lg icon-theme"  aria-hidden="true"></i> Netflow
                </a>
                </li>';
        }
/*
        if (can_ping_device($attribs) === true) {
            echo '<li role="presentation" '.$select['performance'].'>
                <a href="'.generate_device_url($device, array('tab' => 'performance')).'">
                <i class="fa fa-line-chart fa-lg icon-theme"  aria-hidden="true"></i> Performance
                </a>
                </li>';
        }
*/
        if (PortsNac::where('device_id', $device['device_id'])->exists()) {
            echo '<li role="presentation" '.$select['nac'].'>
                <a href="'.generate_device_url($device, array('tab' => 'nac')).'">
                <i class="fa fa-lock fa-lg icon-theme"  aria-hidden="true"></i> NAC
                </a>
                </li>';
        }

        echo '<li role="presentation" '.$select['notes'].'>
            <a href="'.generate_device_url($device, array('tab' => 'notes')).'">
            <i class="fa fa-file-text-o fa-lg icon-theme"  aria-hidden="true"></i> Notes
            </a>
            </li>';

        if (is_mib_poller_enabled($device)) {
            echo '<li role="presentation" '.$select['mib'].'>
                <a href="'.generate_device_url($device, array('tab' => 'mib')).'">
                <i class="fa fa-file-text-o fa-lg icon-theme"  aria-hidden="true"></i> MIB
                </a>
                </li>';
        }

            echo '<div class="dropdown pull-right">
                  <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-cog fa-lg icon-theme"  aria-hidden="true"></i>
                  <span class="caret"></span></button>
                  <ul class="dropdown-menu">
                  <!--
                    <li><a href="https://'.$device['hostname'].'" onclick="http_fallback(this); return false;" target="_blank" rel="noopener"><i class="fa fa-globe fa-lg icon-theme"  aria-hidden="true"></i> Web</a></li>-->';
/*
        foreach (Config::get('html.device.links') as $links) {
            $html_link = view(['template' => $links['url']], ['device' => $device])->__toString();
            echo '<li><a href="'.$html_link.'" onclick="http_fallback(this); return false;" target="_blank" rel="noopener"><i class="fa fa-globe fa-lg icon-theme" aria-hidden="true"></i> '.$links['title'].'</a></li>';
        }

        if (Config::has('gateone.server')) {
            if (Config::get('gateone.use_librenms_user') == true) {
                echo '<li><a href="' . Config::get('gateone.server') . '?ssh=ssh://' . Auth::user()->username . '@' . $device['hostname'] . '&location=' . $device['hostname'] . '" target="_blank" rel="noopener"><i class="fa fa-lock fa-lg icon-theme" aria-hidden="true"></i> SSH</a></li>';
            } else {
                echo '<li><a href="' . Config::get('gateone.server') . '?ssh=ssh://' . $device['hostname'] . '&location=' . $device['hostname'] . '" target="_blank" rel="noopener"><i class="fa fa-lock fa-lg icon-theme" aria-hidden="true"></i> SSH</a></li>';
            }
        } else {
            echo '<li><a href="ssh://'.$device['hostname'].'" target="_blank" rel="noopener"><i class="fa fa-lock fa-lg icon-theme"  aria-hidden="true"></i> SSH</a></li>
            ';
        }
            echo '<li><a href="telnet://'.$device['hostname'].'" target="_blank" rel="noopener"><i class="fa fa-terminal fa-lg icon-theme"  aria-hidden="true"></i> Telnet</a></li>';
*/
        if (Auth::user()->hasGlobalAdmin()) {
            echo '<li>
                <a href="'.generate_device_url($device, array('tab' => 'edit')).'">
                <i class="fa fa-pencil fa-lg icon-theme"  aria-hidden="true"></i> Edit </a>
                </li>';
/*
            echo '<li><a href="'.generate_device_url($device, array('tab' => 'capture')).'">
                <i class="fa fa-bug fa-lg icon-theme"  aria-hidden="true"></i> Capture
                </a></li>';
*/
        }
              echo '</ul>
            </div>';
        echo '</ul>';
    }//end if device_permitted


    // include the tabcontent
    echo '<div class="tabcontent">';
    require 'includes/html/pages/device/'.filter_var(basename($tab), FILTER_SANITIZE_URL).'.inc.php';
    echo '</div>';
} else {
    // no device permissions
    require 'includes/html/error-no-perm.inc.php';
}
