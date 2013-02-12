Laravel payments
===================
Laravel payments is a library to easily add behind-the-scenes payment functionality to Laravel projects.

Installation
------------
1. Download and copy the files into a folder called "payments" within your bundles directory.
2. Alter your bundles.php file in your application/ folder and add the following:
    
    'payments' => array('auto' => true)

3. Copy the payments/config/payments.php file (configuration file) to application/config/payments.php and configure it as needed. Each provider has it's own configuration options, so be sure to read the provider's README.md file as well.

Usage
-----
Usage uses a common API, but is slightly different for each provider (make sure you read each provider's documentation). However, most will expect calls like:

    $amount  = 5 * 100;  // $5.00
    $data = array(
    	'cc_number' => '...'
    	'cc_name' => '...',
    	'cc_expiry_month' => '01',
    	'cc_expiry_year' => '02'
    );
    
    $payment = \Payments\Payment::create( $amount, $data );

    var_dump($payment->response()); // Debug the returned response