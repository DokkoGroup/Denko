<?php
require_once '../web/common.php';
###############################################################################
// Busco los mails que no pudieron ser enviados:
$daoEmail = Denko :: daoFactory ('Email');
$daoEmail instanceof DataObjects_Email;
$daoEmail->whereAdd('sent = \'0\'');
$count = $daoEmail->find();
//////////////////////////////////////////////////////////////////////////////
if($count > 0){
    $sendCount = 0;
    $iniServerUp = true;
    $endServerUp = true;
    while($daoEmail->fetch()){
        // Veo si puedo mandar el mail:
        if(sendMail($daoEmail->fromemail,$daoEmail->destination,$daoEmail->emailsubject,$daoEmail->message,$daoEmail->fromname,false)){
            // En caso que pueda, actualizo el DAO a 'sent' (enviado):
            $daoEmail->sendtries++;
            $daoEmail->sent = '1';
            $daoEmail->update();
            $sendCount++;
            $endServerUp = true;
        }else{
            // En caso de no poder enviar el mail:
            if($sendCount == 0){
                // indico que el mailserver está caído desde el inicio:
                $iniServerUp = false;
            }
            // Incremento el contador de intentos:
            $daoEmail->sendtries++;
            $daoEmail->update();
            // Marco que el mailserver se cayó en medio del bucle:
            $endServerUp = false;
        }
    }
    // Muestro un reporte:
    echo '
    - Cantidad de emails por enviar: '.$count.'
    - Cantidad de emails enviados: '.$sendCount.'
    - Estado del mailserver al inicio: '.($iniServerUp?'UP':'DOWN').'
    - Estado del mailserver al final: '.($endServerUp?'UP':'DOWN').'
';
    exit(0);
}else{
    echo '
    - No hay emails por enviar...
    ';
    exit(1);
}
