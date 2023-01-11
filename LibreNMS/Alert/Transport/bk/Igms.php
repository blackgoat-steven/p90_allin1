<?php
/**
* transport-telegram.inc.php
*
* LibreNMS Telegram alerting transport
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @link       http://librenms.org
* @copyright  2017 Neil Lathwood
* @author     Neil Lathwood <neil@lathwood.co.uk>
*/
namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Igms extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $igms_opts['igms-target-name'] = $this->config['igms-target-name'];
        $igms_opts['url'] = $this->config['igms-url'];
        print_r($igms_opts);
        $file = fopen('/tmp/Igms.log', "a");
        fwrite($file, 'igms-target-name=' . $igms_opts['igms-target-name'] ."\n");
        fclose($file);
        return $this->contactIgms($obj, $igms_opts);
    }

    public static function contactIgms($obj, $data)
    {
        $db_name = dbFetchCell('SELECT DATABASE()');
        #$cmd = 'python /usr/local/librenms/createTicket-p90.py ' . $obj['hostname'] . ' ' . $obj['rule'] . ' ' . $obj['state'] . ' ' . $obj['msg'] ;
        $cmd = 'python createTicket-p90.py ' . '\'' . $obj['sysName'] . '\' \'' . $obj['name'] . '\' \'' . $obj['state'] . '\' HARD \'' . $obj['title'] . '\' ' . '\'' . $data['igms-target-name'] . '\'';
        $file = fopen('/tmp/Igms.log', "a");
        //fwrite($file, "measurement=" . $measurement . "tags=" . print_r($tmp_tags, true) . "filed=" . print_r($tmp_fields, true) . "\n");
        fwrite($file, 'obj=' . json_encode($obj) . 'cmd=' . $cmd ."\n");
        fclose($file);
        print_r($cmd);
        print_r($obj);
        shell_exec($cmd);
        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'target name',
                    'name' => 'igms-target-name',
                    'descr' => 'IGMS custom name',
                    'type' => 'text',
                ],
                [
                    'title' => 'IGMS url',
                    'name' => 'igms-url',
                    'descr' => 'IGMS API url',
                    'type' => 'text',
                ]
            ],
            'validation' => [
                'igms-target-name' => 'required|string',
                'igms-url' => 'required|string'
            ]
        ];
    }
}
