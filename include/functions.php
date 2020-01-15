<?php

require_once( __DIR__ . '/../config.php' );
require_once( __DIR__ . '/apiRedsys7.php' );

/**
 * Genera una cadena de caracteres de forma aleatoria.
 * NO HAY GARANTIA DE QUE LAS CADENAS SEAN ÚNICAS.
 * 
 * @return string Cadena alfanumérica generada.
 */ 
function redsys_ramdom_order() {
	$length = 12;
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = '';
    for ($i = 0 ; $i < $length; $i += 5) {
        $str .= substr(str_shuffle($chars), 0, 5);
    }
    return substr($str, 0, $length);
}

/**
 * Pinta un botón para realizar un pago a través de REDSYS.
 *
 * @param  float  $price 		Cantidad a pagar.
 * @param  string $orderNumber	Número de pedido (debe ser único para cada petición a REDSYS).
 * @param  string $description	Descripción del pedido/producto (aparece en la pantalla de pago).
 * @param  string $data			Datos adicionales de la operación (no son mostrados, pero se pueden recuperar tras la confirmación del pago).
 * @param  string $urlOK		URL a la que enviar la petición si el pago es exitoso.
 * @param  string $urlKO		URL a la que enviar la petición si hay errores en el pago.
 * @param  string $btnText		Texto del botón de pago.
 * @param  string $btnClass		Clase(s) CSS para el botón.
 * @param  string $formName		Nombre y también ID del formulario (necesario si se pintan varios botones). 
 * @return string               Fragmento de código HTML para pintar el botón.
 */
function redsys_button($price, $orderNumber, $description, $data = '', $urlOK = REDSYS_URL_OK, $urlKO = REDSYS_URL_KO, $btnText = 'Realizar pago', $btnClass = 'btn btn-primary', $formName = 'from') {

    $apiObj = new RedsysAPI;
    
    $version         = "HMAC_SHA256_V1"; 
    $url_tpv         = REDSYS_URL_TPV;
    $key             = REDSYS_KEYCODE;
    $name            = REDSYS_NAME;
    $code            = REDSYS_FUC_CODE;
    $terminal        = REDSYS_TERMINAL;
    $amount          = floatval($price) * 100.0;
    $currency        = REDSYS_CURRENCY;
    $consumerlng     = '001';
    $transactionType = '0';
    $urlMerchant     = REDSYS_URL_MERCHANT;

    $apiObj->setParameter("DS_MERCHANT_ORDER",              $orderNumber);
    $apiObj->setParameter("DS_MERCHANT_AMOUNT",             $amount);
    $apiObj->setParameter("DS_MERCHANT_CURRENCY",           $currency);
    $apiObj->setParameter("DS_MERCHANT_MERCHANTCODE",       $code);
    $apiObj->setParameter("DS_MERCHANT_TERMINAL",           $terminal);
    $apiObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE",    $transactionType);
    $apiObj->setParameter("DS_MERCHANT_PRODUCTDESCRIPTION", $description);    
    $apiObj->setParameter("DS_MERCHANT_MERCHANTURL",        $urlMerchant);
    $apiObj->setParameter("DS_MERCHANT_URLOK",              $urlOK);      
    $apiObj->setParameter("DS_MERCHANT_URLKO",              $urlKO);
    $apiObj->setParameter("DS_MERCHANT_MERCHANTNAME",       $name); 
    $apiObj->setParameter("DS_MERCHANT_CONSUMERLANGUAGE",   $consumerlng);    
	$apiObj->setParameter("DS_MERCHANT_MERCHANTDATA", 		$data);    

    $params    = $apiObj->createMerchantParameters();
    $signature = $apiObj->createMerchantSignature($key);

    return '<form name="'.$formName.'" id="'.$formName.'" action="'.$url_tpv.'" method="POST">
              <input type="hidden" name="Ds_SignatureVersion" value="'.$version.'">
              <input type="hidden" name="Ds_MerchantParameters" value="'.$params.'">
              <input type="hidden" name="Ds_Signature" value="'.$signature.'">
              <input class="'.$btnClass.'" type="submit" value="'.$btnText.'" />
            </form>';
}

/**
 * Procesa una respuesta de confirmación de operación de pago (exitosa o no).
 *
 * Se decodifican los parámetros enviados y se valida la firma, para validar que la
 * petición es genuina de REDSYS.
 *
 * @return array Resultado de la operación, al menos tendrá los campos:
 * - genuine (boolean): Indica si la invocación es genuina de REDSYS.
 * - correctPayment (boolean): Indica si la operación de pago se considera correcta (aprobada).
 */
function redsys_process_response() {
	
	$response['genuine']        = false;
	$response['correctPayment'] = false;

	/*
	 * Cuando es invocado por REDSYS, se nos pasan estos datos.
	 */
	$version = $_POST['Ds_SignatureVersion'];
	$params = $_POST['Ds_MerchantParameters'];
	$receivedSignature = $_POST['Ds_Signature'];

	/* Solo si hemos sido invocados por REDSYS */
	if ( !empty($version) && !empty($params) && !empty($receivedSignature) ) {

		$apiObj = new RedsysAPI;

		/*
		 * Calcular de forma independiente la firma de los datos
		 * y decodificar los parámetros enviados.
		 */
		$computedSignature = $apiObj->createMerchantSignatureNotif(REDSYS_KEYCODE, $params);
		$decodec           = $apiObj->decodeMerchantParameters($params);	
		
		/*
		 * Extrae cada uno de los parámetros posibles en la respuesta.
		 */
		$response['date']            = $apiObj->getParameter('Ds_Date');
		$response['hour']            = $apiObj->getParameter('Ds_Hour');
		$response['amount']          = $apiObj->getParameter('Ds_Amount');
		$response['currency']        = $apiObj->getParameter('Ds_Currency');
		$response['order']           = $apiObj->getParameter('Ds_Order');
		$response['merchantCode']    = $apiObj->getParameter('Ds_MerchantCode');		
		$response['terminal']        = $apiObj->getParameter('Ds_Terminal');
		$response['code']            = $apiObj->getParameter('Ds_Response');
		$response['data']            = $apiObj->getParameter('Ds_MerchantData');
		$response['transactionType'] = $apiObj->getParameter('Ds_TransactionType');
		$response['cardCountry']     = $apiObj->getParameter('Ds_Card_Country');
		$response['autorisation']    = $apiObj->getParameter('Ds_AuthorisationCode');
		$response['consumerLang']    = $apiObj->getParameter('Ds_ConsumerLanguage');
		$response['cardNumber']      = $apiObj->getParameter('Ds_Card_Number');
		
		if ( empty($response['cardCountry'])   ) $response['cardCountry']  = 'Not set';
		if ( empty($response['autorisation'])  ) $response['autorisation'] = 'Not set';
		if ( empty($response['consumerLang']) || 
			 $response['consumerLang'] == '0'  ) $response['consumerLang'] = 'Not set';
		if ( empty($response['cardNumber'])    ) $response['cardNumber']  = 'Not set';

		switch ($apiObj->getParameter('Ds_SecurePayment')) {
			case '0': 
				$response['secure'] = 'Not secure';
				break;
			case '1': 
				$response['secure'] = 'Secure';
				break;
			default:
				$response['secure'] = 'Unknown';		
		}
		
		if ( !empty($apiObj->getParameter('Ds_Card_Type')) ) {
			switch ($apiObj->getParameter('Ds_Card_Type')) {
				case 'C':
					$response['cardType'] = 'Credit';
					break;
				case 'D':
					$response['cardType'] = 'Debit';
					break;
				default:
					$response['cardType'] = 'Unknown';
			}
		} else {
			$response['cardType'] = 'Not set';
		}
				
		if ( !empty($apiObj->getParameter('Ds_Card_Brand')) ) {
			switch ($apiObj->getParameter('DS_Card_Brand')) {
				case '1':
					$response['cardBrand'] = 'VISA';
					break;
				case '2':
					$response['cardBrand'] = 'MASTERCARD';
					break;
				case '6':
					$response['cardBrand'] = 'DINERS';
					break;
				case '7':
					$response['cardBrand'] = 'PRIVADA';
					break;
				case '8':
					$response['cardBrand'] = 'AMEX';
					break;
				case '9':
					$response['cardBrand'] = 'JCB';
					break;
				case '22':
					$response['cardBrand'] = 'UPI';
					break;
				default:
					$response['cardBrand'] = 'Unknown';
			}
		} else {
			$response['cardBrand'] = 'Not set';
		}
			
		/*
		 * Comparamos la firma enviada y la firma generada para 
		 * validar que el mensaje sea genuino.
		 */
		if ($receivedSignature === $computedSignature) {			
			
			/* 
			 * Es genuino
			 */
			$response['genuine'] = true;
			
			/* Valida el código de respuesta para saber si es una operación aceptada */
			if ( strlen($response['code']) === 3 && substr($response['code'], 0, 1) === '0') 
				$response['correctPayment'] = true;
			elseif ( strlen($response['code']) === 4 && substr($response['code'], 0, 2) === '00') 
				$response['correctPayment'] = true;
							
			/* 
			 * Si el log está activado, en función de si el pago ha sido aprobado o no
			 * genera un mensaje en el log con los datos de la operación.
			 */
			if ( REDSYS_LOG ) {
				if ( $response['correctPayment'] ) {				
					error_log("REDSYS Approved Payment notification VALID Signature Signature=[{$receivedSignature}] for " . json_encode($response));
				} else {
					error_log("REDSYS Denied Payment notification VALID Signature Signature=[{$receivedSignature}] for " . json_encode($response));
				}	
			}
		} else {
			/* 
			 * ¡¡¡NO es genuino!!!
			 */
			if ( REDSYS_LOG ) {
				error_log("REDSYS Invalid Payment notification INVALID Signature Signature=[{$receivedSignature}] for " . json_encode($response));
			}
		}
	}
	
	return $response;
}