<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Config;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!Auth::check()) {
    die('Unauthorized');
}

set_debug(strpos($_SERVER['PATH_INFO'], 'debug'));
$db_name = dbFetchCell('SELECT DATABASE()');
$report = basename($vars['report']);
$cmd = "rm -rf html/download/$report.xlsx";
shell_exec($cmd);
#echo $report;
$cmd = '/usr/bin/python /usr/local/saas/' . $db_name . '/data_export.py ' . $db_name . ' ' . $report;
#echo $cmd;
$status =  shell_exec($cmd);
#echo $status;
//exit(0);
if ( file_exists("html/download/$report.xlsx")) {

header('Content-type:application/force-download');
//header('Content-Type: text/csv');
header('Content-Transfer-Encoding: Binary');
header('Content-Disposition: attachment; filename='.$report.'.xlsx');
//echo 'Hello PHP';
readfile('html/download/'. $report . '.xlsx');

}else{
echo 'Report not found!!';
}
?>
