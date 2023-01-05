<?php
        $db_name = dbFetchCell('SELECT DATABASE()');
        //echo $db_name;
echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Traffic</h3>
            </div>';
echo '<div class="panel-body">';
$port = preg_replace("/\s/",'\\\\\\ ', $port['ifName']);
echo ' <iframe src="/grafana/dashboard/script/device_port_graphs.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&port=' . $port . '&theme=light&kiosk=tv" height="400" frameborder="0"></iframe>';
//echo '/grafana/dashboard/script/device_port_graphs.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&port=' . $port . '';
echo '</div></div>';


echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Packets</h3>
            </div>';
echo '<div class="panel-body">';
echo ' <iframe src="/grafana/dashboard/script/device_port_upkts.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&port=' . $port . '&theme=light&kiosk=tv" height="400" frameborder="0"></iframe>';
echo '</div></div>';

echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Non Unicast</h3>
            </div>';
echo '<div class="panel-body">';
echo ' <iframe src="/grafana/dashboard/script/device_port_nupkts.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&port=' . $port . '&theme=light&kiosk=tv" height="400" frameborder="0"></iframe>';
echo '</div></div>';

echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Errors</h3>
            </div>';
echo '<div class="panel-body">';
echo ' <iframe src="/grafana/dashboard/script/device_port_errors.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&port=' . $port . '&theme=light&kiosk=tv" height="400" frameborder="0"></iframe>';
echo '</div></div>';

/*
if (rrdtool_check_rrd_exists(get_port_rrdfile_path($device['hostname'], $port['port_id']))) {
    $iid = $id;
    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Traffic</h3>
            </div>';
    $graph_type = 'port_bits';

    echo '<div class="panel-body">';
        include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Packets</h3>
            </div>';
    $graph_type = 'port_upkts';

    echo '<div class="panel-body">';
        include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Non Unicast</h3>
            </div>';

    $graph_type = 'port_nupkts';
    echo '<div class="panel-body">';
        include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Errors</h3>
            </div>';

    $graph_type = 'port_errors';

    echo '<div class="panel-body">';
        include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    if (rrdtool_check_rrd_exists(get_port_rrdfile_path($device['hostname'], $port['port_id'], 'poe'))) {
        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">PoE</h3>
            </div>';
        $graph_type = 'port_poe';
        
        echo '<div class="panel-body">';
            include 'includes/html/print-interface-graphs.inc.php';
        echo '</div></div>';
    }

    if (rrdtool_check_rrd_exists(get_port_rrdfile_path($device['hostname'], $port['port_id'], 'dot3'))) {
        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Ethernet Errors</h3>
            </div>';
        $graph_type = 'port_etherlike';
        
        echo '<div class="panel-body">';
            include 'includes/html/print-interface-graphs.inc.php';
        echo '</div></div>';
    }
}
*/
