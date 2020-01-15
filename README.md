# redsys-payment-buttons
Permite crear botones de pago a través de la pasarela REDSYS a una aplicación genérica en PHP.

*Requiere PHP 7+*

## Instalación y configuración 

Instala el repositorio en un subdirectorio de tu aplicación (eg. `payments`).

Debes revisar y ajustar a tu instalación el fichero `config.php`.

## Cómo crear un botón de pago

```
require_once( 'payments/include/functions.php' );

echo redsys_button(123.45, redsys_ramdom_order() , "Descripción de mi pedido", "Datos-adicionales-de-tu-pedido");
```

## Cómo procesar las confirmaciones de operaciones de pago

Puedes completar el módulo `return.php` incluyendo lo que se deba hacer en tu aplicación, tanto para las operaciones aprobadas como para las denegadas.

```
<?php

/**
 * Procesa la respuesta de un pago de REDSYS.
 * 
 * No debe generar salida HTML pues este resultado no es mostrado a nadie.
 */

require_once( 'include/functions.php' );

$response = redsys_process_response();

if ( $response['correctPayment'] ) {
	// TODO: Operaciones de la aplicación tras un pago correcto.
} else {
	// TODO: Operaciones de la aplicación tras un pago incorrecto.
}
```

No tienes por que usar este módulo, dado que la URL de respuesta hacia la tienda es uno de los parámtros que se configura en `config.php`. Lo que si tienes que hacer es incluir en tu módulo este código:

```
require_once( 'payments/include/functions.php' );

$response = redsys_process_response();
```

para procesar la respuesta y saber si es genuina. El array asociativo `$response` tiene los siguientes campos:

* `genuine`: _true/false_ Indica si la llamada a la tu módulo para procesar una confirmación de pago se considera genuina de REDSYS.
* `correctPayment`: _true/false_ Indica si la llamada a la tu módulo para procesar una confirmación de pago se considera genuina de REDSYS.
* `date`: _string_ Fecha de la operación.
* `hour`: _string_ Hora de la operación. 
* `amount`: _string_ Importe solicitado. 
* `currency`: _string_ Código de moneda.
* `order`: _string_ Código del pedido (único). 
* `merchantCode`: _string_ Código FUC del comercio.
* `terminal`: _string_ Número de terminal TPV. 
* `code`: _string_ Código de respuesta de la autorización. 
* `data`: _string_ Datos adicionales enviados por el comercio. 
* `transactionType`: _string_ Tipo de transacción. 
* `cardCountry`: _string_ Pais de emisión de la tarjeta.
* `autorisation`: _string_ Número de autorización de la operación. 
* `consumerLang`: _string_ Idioma del cliente. 
* `cardNumber`: _string_ Número de tarjeta (ofuscado).
* `secure`: _string_ Indica si la operación ha usado 3D secure. 
* `cardType`: _string_ Tipo de tarjeta (Débito o Crédito).
* `cardBrand`: _string_ Marca de la tarjeta. 

Algunos datos pueden aparecer como `Unknown` o `Not set`, en función de la operación y la configuración del comercio en REDSYS.
