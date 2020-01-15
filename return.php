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