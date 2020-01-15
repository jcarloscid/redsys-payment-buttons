<?php

// URL de REDSYS con el formulario de procesamiento de pagos (TEST / REAL)
define( 'REDSYS_URL_TPV', 'https://sis-t.redsys.es:25443/sis/realizarPago' );

// Nombre del comercio
define( 'REDSYS_NAME', 'Nombre de tu comercio' );

// Número de comercio (FUC)
define( 'REDSYS_FUC_CODE', '123456789' );

// Clave secreta de encriptación (SHA-256)
define( 'REDSYS_KEYCODE', 'abcdefghijklmnopqrstuvwxyz012345' );

// Número de terminal
define( 'REDSYS_TERMINAL', '001');

// Código de divisa
define( 'REDSYS_CURRENCY', '978');

// URL para la recepción de confirmaciones de pago
define( 'REDSYS_URL_MERCHANT', 'https://misite.com/whereever-installed/payment/return.php');

// URL de vuelta en operaciones de pago con éxito (por defecto)
define( 'REDSYS_URL_OK', 'https://misite.com/whereever-installed//payment/test-ok.php');

// URL de vuelta en operaciones de pago con errores (por defecto)
define( 'REDSYS_URL_KO', 'https://misite.com/whereever-installed//payment/test-ko.php');

// Indica si se debe escribir información en el log sobre las operaciones de confirmación procesadas
define( 'REDSYS_LOG', true );