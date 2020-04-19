<?php
/**
 * Almacena un email en la base de datos
 * @param PHPMailer $mail
 * @param Boolean $fueEnviado
 */
function guardarEmail(&$mail,$fueEnviado){
    $daoEmail = Denko :: daoFactory ('Email');
    $daoEmail instanceof DataObjects_Email;
    $daoEmail->fromemail    = $mail->From;
    $daoEmail->fromname     = $mail->FromName;
    $daoEmail->emailsubject = $mail->Subject;
    $daoEmail->message      = $mail->Body;
    $daoEmail->destination  = null;
    foreach($mail->to as $address){
        $daoEmail->destination.= ($daoEmail->destination===null?'':',').$address[0];
    }
    $daoEmail->sendtries = 1;
    $daoEmail->sent = $fueEnviado?'1':'0';
    $daoEmail->insert();
}

###############################################################################
/*
    Función para enviar emails.

    Parámetros:
      - sender:   [STRING] dirección de email del remitente.
      - to:       [STRING] dirección de email del destino.
      - subject:  [STRING] asunto del email.
      - message:  [STRING] mensaje.

    Parámetros opcionales:
      - replyTo:  [ARRAY|STRING] Direccion de email y nombre a quien responder
        el email.
        Arreglo con las claves "address" y "name".
        En caso de ser solo una string asume que es "address".
        DEFAULT: null.
      - report: [BOOL] Indica si el email va a ser reportado (o sea, se grabará en la DB)
      - fromName: [STRING] setea el "From name" del mensaje
      - sendBeforeReport: [BOOL] indica si se debe enviar el mail antes de almacenarlo en la DB.
*/
function sendMail($sender,$to,$subject,$message,$replyTo=null,$report=true,$fromName=null,$sendBeforeReport=false){
    require_once '../phpmailer/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->From    = $sender;
    $mail->Subject = $subject;
    $mail->Body    = $message;
    if($replyTo != null){
        if(is_array($replyTo)){
            $mail->AddReplyTo($replyTo['address'],isset($replyTo['name'])?$replyTo['name']:'');
        }else{
            $mail->AddReplyTo($replyTo);
        }
    }
    $mail->IsHTML (true);
    $mail->IsSMTP();
    $mail->Port     = 25;
    $mail->SMTPAuth = true;
    $mail->Host     = getConfig('SMTPMAIL_HOST');
    $mail->Username = getConfig('SMTPMAIL_USERNAME');
    $mail->Password = getConfig('SMTPMAIL_PASSWORD');
    if($fromName == null){
        $mail->FromName = getConfig('SMTPMAIL_FROMNAME');
    }
    $arrTo = explode(',',$to);
    foreach($arrTo as $auxTo){
        $trimmed = trim($auxTo);
        if($trimmed == '') continue;
        $mail->AddAddress($trimmed);
    }

    if($report === true){
        $res=false;
        if($sendBeforeReport){
            $res=$mail->Send();
        }
        guardarEmail($mail,$res);
        return $res;
    }else{
        return $mail->Send();
    }
}