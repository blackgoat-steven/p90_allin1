<?php
$db_name = dbFetchCell('SELECT DATABASE()');
$print_graph = !(isset($return_data) && $return_data);
//echo $return_data;
//print_r($graph_array);
//print_r($print_graph);
//print_r($device);
//$graph_data = \LibreNMS\Util\Html::graphRow($graph_array, $print_graph);

if ( $graph_array['type'] == accesspoints_numasoclients || $graph_array['type'] == accesspoints_interference || $graph_array['type'] == accesspoints_channel  || $graph_array['type'] == accesspoints_txpow || $graph_array['type'] == accesspoints_radioutil || $graph_array['type'] == accesspoints_nummonclients || $graph_array['type'] == accesspoints_nummonbssid ){

//echo 'http://g.babygoat.biz/grafana/dashboard/script/' . $graph_array['type'] . '.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&theme=light&kiosk=tv';

echo '<iframe src="/grafana/dashboard/script/' . $graph_array['type'] . '.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&name=' . $ap['name'] . '&theme=light&kiosk=tv" height="400" frameborder="0"></iframe>';

}
else{

echo '<iframe src="/grafana/dashboard/script/' . $graph_array['type'] . '.js?orgId=1&panelId=1&hostname=' . $device['hostname'] . '&dbname=' . $db_name . '&theme=light&kiosk=tv" height="400" frameborder="0"></iframe>';
}

unset($graph_array);
