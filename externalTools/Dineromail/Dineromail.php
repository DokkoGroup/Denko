<?php

require_once dirname(dirname(__FILE__)).'/denko/dk.denko.php';

class Dineromail {
	private static $clientName     = '';
	private static $clientLastName = '';
	private static $clientEmail    = '';
	private static $clientAddress  = '';
	private static $clientCity     = '';
	private static $clientCountry  = '';
	private static $clientPhone    = '';

	private static $itemName        = '';
	private static $itemQuantity    = '';
	private static $itemDescription = '';

	private static $subject = '';
	private static $message = '';

	//------------------------------------------------------------------------------------

	public static function plainText($cadena){
		$tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñº";
		$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn ";
		return(strtr($cadena,$tofind,$replac));
        }

	public static function getImageBarcodeURL($amount,
											  $itemName = '', $itemQuantity = 1, $itemDescription = '',
											  $clientName = '', $clientLastName = '', $clientEmail = '', $clientAddress = '', $clientCity = '', $clientCountry = 'Argentina', $clientPhone = '',
											  $subject = '', $message = '') {
		Dineromail::$itemName        = $itemName;
		Dineromail::$itemQuantity    = $itemQuantity;
		Dineromail::$itemDescription = $itemDescription;

		Dineromail::$clientName     = $clientName;
		Dineromail::$clientLastName = $clientLastName;
		Dineromail::$clientEmail    = $clientEmail;
		Dineromail::$clientAddress  = $clientAddress;
		Dineromail::$clientCity     = $clientCity;
		Dineromail::$clientCountry  = $clientCountry;
		Dineromail::$clientPhone    = $clientPhone;

		Dineromail::$subject = $subject;
		Dineromail::$message = $message;

		$result = Dineromail::getPaymentData($amount);

		//Dineromail::printPaymentData($result);exit;
		if ($result->DoPaymentWithReferenceResult->BarcodeImageUrl) {
			return $result->DoPaymentWithReferenceResult->BarcodeImageUrl;
		} else {
			return '';
		}
	}

	//------------------------------------------------------------------------------------
	
	public static function printPaymentData($result) {
		echo "<br/>";
		echo "MerchantTransactionId: " . $result->DoPaymentWithReferenceResult->MerchantTransactionId . "<br/>";
		echo "Status: " . $result->DoPaymentWithReferenceResult->TransactionId . "<br/>";
		echo "Message: " . $result->DoPaymentWithReferenceResult->Message . "<br/>";
		echo "Status: " . $result->DoPaymentWithReferenceResult->Status . "<br/>";
		echo "TransactionId: " . $result->DoPaymentWithReferenceResult->TransactionId . "<br/>";
		echo "BarcodeDigits: " . $result->DoPaymentWithReferenceResult->BarcodeDigits . "<br/>";
		echo "BarcodeImageUrl: " . $result->DoPaymentWithReferenceResult->BarcodeImageUrl . "<br/>";
		echo "VoucherUrl: " . $result->DoPaymentWithReferenceResult->VoucherUrl . "<br/>";
		echo "<br/>";
	}

	//------------------------------------------------------------------------------------

	private static function getPaymentData($Amount) {
		$APIUserName = Denko::getConfig('dineromail_usuario');
		$APIPassword = Denko::getConfig('dineromail_contraseña');
		$Provider    = Denko::getConfig('dineromail_proveedor');
		$Currency    = Denko::getConfig('dineromail_moneda');

		$Crypt = false;
		$MerchantTransactionId = '1';                 // Opcional. Tipo string. Identificador de transacción generado por el comercio requerido para consultas a IPN 3.0.
		$UniqueMessageId = Denko::getConfig('dineromail_unique_message_id');                        // Opcional. Tipo string. Identificador único de mensaje, no puede repetirse en futuras conexiones.
		Denko::setConfig('dineromail_unique_message_id',++$UniqueMessageId);
                $UniqueMessageId.=Denko::getConfig('dineromail_internal_string_id');
		$Subject = Dineromail::plainText(Dineromail::$subject); // Opcional. Tipo string. Concepto o asunto del comprador hacia el vendedor.
		$Message = Dineromail::plainText(Dineromail::$message); // Opcional. Tipo string. Mensaje del comprador hacia el vendedor.

		$Code        = '';                                        // Opcional. Tipo string. Identificador generado por el comercio.
		$Description = Dineromail::plainText(Dineromail::$itemDescription); // Opcional. Tipo string. Descripción del ítem.
		$ItemName    = Dineromail::plainText(Dineromail::$itemName);        // Requerido. Tipo string. Nombre o titulo del ítem.
		$Quantity    = Dineromail::$itemQuantity;                 // Requerido. Tipo string. Cantidad del ítem.

		$Name     = Dineromail::plainText(Dineromail::$clientName);     // Requerido.
		$LastName = Dineromail::plainText(Dineromail::$clientLastName); // Requerido.
		//$Email    = Dineromail::$clientEmail;                 // Requerido.
		$Email	  = 'log+'.str_replace(' ','-',Dineromail::plainText(strtolower($Name))).'+'.Denko::getConfig('dineromail_internal_string_id').'@dokkogroup.com.ar';
		$Address  = Dineromail::plainText(Dineromail::$clientAddress);  // Opcional.
		$City     = Dineromail::plainText(Dineromail::$clientCity);     // Opcional.
		$Country  = Dineromail::plainText(Dineromail::$clientCountry);  // Opcional.
		//$Phone    = Dineromail::$clientPhone;                 // Opcional.
		$Phone    = ''; // No pasamos el telefono pq si es muy largo falla la API

		$ns = 'https://api.dineromail.com/';
		$wsdlPath = 'https://api.dineromail.com/dmapi.asmx?WSDL';

		try {	
			$Items = $Amount.$Code.$Currency.$Description.$ItemName.$Quantity;
			$Buyer = $Name.$LastName.$Email.$Address.$Phone.$Country.$City;
			$Hash = $MerchantTransactionId.$UniqueMessageId.$Items.$Buyer.$Provider.$Subject.$Message.$APIPassword;
			$Hash = MD5($Hash);

			if ($Crypt == true) {
				$MerchantTransactionId = Dineromail::encryptTripleDES($APIPassword, $MerchantTransactionId);
				$UniqueMessageId = Dineromail::encryptTripleDES($APIPassword, $UniqueMessageId);
				$Provider = Dineromail::encryptTripleDES($APIPassword, $Provider);
				$Subject = Dineromail::encryptTripleDES($APIPassword, $Subject);
				$Message = Dineromail::encryptTripleDES($APIPassword, $Message);
				
				$Currency = Dineromail::encryptTripleDES($APIPassword, $Currency);
				$Amount = Dineromail::encryptTripleDES($APIPassword, $Amount);
				$Code = Dineromail::encryptTripleDES($APIPassword, $Code);
				$ItemName = Dineromail::encryptTripleDES($APIPassword, $ItemName);
				$Quantity = Dineromail::encryptTripleDES($APIPassword, $Quantity);
				
				$Address = Dineromail::encryptTripleDES($APIPassword, $Address);
				$City = Dineromail::encryptTripleDES($APIPassword, $City);
				$Country = Dineromail::encryptTripleDES($APIPassword, $Country);
				$Email = Dineromail::encryptTripleDES($APIPassword, $Email);
				$Name = Dineromail::encryptTripleDES($APIPassword, $Name);
				$LastName = Dineromail::encryptTripleDES($APIPassword, $LastName);
				$Phone = Dineromail::encryptTripleDES($APIPassword, $Phone);
			}

			$soap_options = array('trace' => 1, 'exceptions' => 1);	
			$client = new SoapClient($wsdlPath, $soap_options); 	

			$credential = new SOAPVar(array('APIUserName' => $APIUserName,
											'APIPassword' => $APIPassword)
											, SOAP_ENC_OBJECT, 'APICredential', $ns);

			$Item = new SOAPVar(array('Amount' => $Amount
										,'Code' => $Code
										,'Currency' => $Currency
										,'Description' => $Description
										,'Name' => $ItemName
										,'Quantity' => $Quantity)
										, SOAP_ENC_OBJECT, 'Item', $ns);	

			$Items = array($Item);

			$BuyerObject = new SOAPVar(array('Address' => $Address
										,'City' => $City
										,'Country' => $Country
										,'Email' => $Email
										,'LastName' => $LastName
										,'Name' => $Name
										,'Phone' => $Phone)
										, SOAP_ENC_OBJECT, 'Buyer', $ns);

			$request = array('Credential' =>$credential
							,'Crypt' => $Crypt
							,'MerchantTransactionId' => $MerchantTransactionId
							,'UniqueMessageId' => $UniqueMessageId
							,'Provider' => $Provider
							,'Message' => $Message
							,'Subject' => $Subject
							,'Items' => $Items
							,'Buyer' => $BuyerObject
							,'Hash' => $Hash);	
			return $client->DoPaymentWithReference($request);
		} catch (SoapFault $sf) {
			echo "faultstring: ".$sf->faultstring;
			exit;
		}
	}

	//------------------------------------------------------------------------------------

	private static function encryptTripleDES($key, $text) {
		$vector = "uL%&(#(f";

		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');

	    // Complete the key.
//	    if (strlen($key) > 24) { return ''; }
	    $key_add = 24-strlen($key);
	    $key .= substr($key, 0, $key_add);

	    // Padding the text.
	    $text_add = strlen($text)%8;
	    for ($i = $text_add; $i < 8; $i++) {
	        $text .= chr(8-$text_add);
	    }

	    mcrypt_generic_init($td, $key, $vector);
	    $encrypt64 = mcrypt_generic($td, $text);
	    mcrypt_generic_deinit($td);
	    mcrypt_module_close($td);

	    // Return the encrypt text in 64 bits code.
	    return base64_encode($encrypt64);
	}
}
