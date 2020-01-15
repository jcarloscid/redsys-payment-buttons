# redsys-payment-buttons
Permite crear a una aplicación genérica en PHP botones de pago a través de la pasarela REDSYS.

**Requiere PHP 7+**

## Instalación y configuración 

Instala el repositorio en un subdirectorio de tu aplicación (eg. `redsys-payment-buttons`)

Debes revisar y ajustar a tu instalación el fichero `config.php`

## Cómo crear un botón de pago

```
require_once( 'redsys-payment-buttons/include/functions.php' );

$orderNumber =  redsys_ramdom_order();
echo redsys_button(123.45, $orderNumber, "Descripción de mi pedido", "datos-adicionales-de-mi-pedido");
```

El uso de `redsys_ramdom_order()` no es obligatorio, pero si es necesario que generes un número de pedido único en cada petición de pago.

Consulta la documentación de `redsys_button()` para explorar los parámetros adicionales que puedes usar en esta función.

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

Aunque no tienes por que usar este módulo, dado que la URL de respuesta hacia la tienda es uno de los parámtros que se configura en `config.php`. Lo que si tienes que hacer es incluir en tu módulo este código:

```
require_once( 'redsys-payment-buttons/include/functions.php' );

$response = redsys_process_response();
```

para procesar la respuesta y saber si es genuina. 

El array asociativo `$response` tiene los siguientes campos:

* `genuine`: _true/false_ Indica si la llamada a tu módulo para procesar una confirmación de pago se considera genuina de REDSYS.
* `correctPayment`: _true/false_ Indica si la operación de pago ha sido correcta (aprobada).
* `code`: _string_ Código de respuesta de la autorización. Detalla el motivo por el que se aprueba o deniega la operación. 
* `date`: _string_ Fecha de la operación.
* `hour`: _string_ Hora de la operación. 
* `amount`: _string_ Importe solicitado. 
* `currency`: _string_ Código de moneda.
* `order`: _string_ Código del pedido (único). 
* `merchantCode`: _string_ Código FUC del comercio.
* `terminal`: _string_ Número de terminal TPV. 
* `transactionType`: _string_ Tipo de transacción. 
* `autorisation`: _string_ Número de autorización de la operación. 
* `secure`: _string_ Indica si la operación ha usado 3D secure. 
* `data`: _string_ Datos adicionales enviados por el comercio. 
* `consumerLang`: _string_ Idioma del cliente. 
* `cardCountry`: _string_ Pais de emisión de la tarjeta.
* `cardNumber`: _string_ Número de tarjeta (ofuscado).
* `cardType`: _string_ Tipo de tarjeta (Débito o Crédito).
* `cardBrand`: _string_ Marca de la tarjeta. 

Algunos datos pueden aparecer como `Unknown` o `Not set`, en función de la operación y la configuración del comercio en REDSYS.

Consultar la documentación de REDSYS y del banco o la entidad de pagos para detalles sobre los códigos de respuesta, divisas, paises, ...  

## Cómo gestionar la vuelta de la pasarela de pagos

A la vuelta de una operación de pago, la aplicación volverá a recibir el control en una de las dos URLs configuradas en el botón de pago:

* `$urlOK`: Si el pago ha sido considerado correcto.
* `$urlKO`: Si el pago ha sido considerado erróneo.

El propósito de estas URLS es simplemente continuar la navegación del usuario y es posible que ambas se dirijan hacia el mismo destino. Dependiendo de cómo esté configurada la pasarela estas URLS podrían recibir por parámetros el resultado de la operación, pero se desaconseja trabajar de esta manera, y en su lugar procesar la respuesta en la URL configurada en `REDSYS_URL_TPV` (donde no se permite mostrar salida HTML).

Si estos parámetros no se indican en la creación del botón, se usaran los valores por defecto configurados en `config.php` para `REDSYS_URL_OK` y `REDSYS_URL_KO` respectivamente.
