<?php
/**
 * Sets up the library. Basically registering payment gateways.
 *
 * @author Kirk Bushell
 * @date 5th February 2013
 */

Autoloader::namespaces(array(
	'Payments' => path('bundle').'payments/libraries/payments'
));

// Now we register the available payment gateways
\Payments\Payment::register("\Payments\Providers\Eway");
