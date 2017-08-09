<?php
class dokkoMonitorLight {
    function sendAlert($msg, $type = 'warning', $category = 'Generic Report') {
        if (strtolower($type) == 'ok') {
            $type = 0;
        } else {
            if (strtolower($type) == 'warning') {
                $type = 1;
            } else {
                $type = 2;
            }
        }
        @ exec('chmod 755 ../dokkoMonitorLight/monitor');
        @ exec('chmod 755 ../dokkoMonitorLight/ejecutarAccion');
        @ exec('../dokkoMonitorLight/monitor "' . $msg . '" ' . $type . ' "' . $category . '" "on demand" "0 -light-"');
    }
}