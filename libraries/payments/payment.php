<?php
/**
 * Provides a common interface for managing payments across any
 * number of gateways utilised across the internet.
 *
 * @author Kirk Bushell
 * @date 4th February 2013
 */
namespace Payments;

use Laravel\Config;

class Payment
{
	/**
	 * Manages the provider. 
	 *
	 * @var Provider
	 */
	protected $provider;

	/**
	 * Stores the response for the transaction
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Stores the information necessary to pass along to the
	 * providers. This could be anything, including amounts, user name/info,
	 * address details.etc.
	 *
	 * @var array
	 */
	protected $payment_data;

	/**
	 * Stores the providers available to the Payments library.
	 *
	 * @var array
	 */
	private static $providers = array();

	/**
	 * Constructor. Sets up the provider and default payment data.
	 *
	 * @param string $provider
	 */
	public function __construct( $provider ) {
		if (!isset(self::$providers[$provider]))
			throw new \Exception('Payment provider "'.$provider.'" not recognized.');

		$class = self::$providers[$provider];
		$this->provider = new $class( $this );
		$this->payment_data = array();
	}

	/**
	 * Easiest way to create a new payment. Instantiates the payment class,
	 * sets some values based on the information provided, and returns
	 * the object.
	 * 
	 * @param string $provider
	 * @return Payment
	 */
	public static function create( $amount, $data = array() ) {
		// We look into the configuration to determine what provider to use
		$config_provider = Config::get('payments.provider');

		// Instantiate a new payment, set some configuration
		$payment = new Payment( $config_provider );
		$payment->set_data( $data );
		$payment->charge( $amount ); // Try and charge the user

		// If everything has worked, return the Payment object
		return $payment;
	}

	/**
	 * Once everything is setup, charge the user, and pass along any extra
	 * information that may be required for the purchase.
	 * 
	 * @param array $data
	 * @return Response
	 */
	public function charge( $amount ) {
		$this->set_amount( $amount );
		$this->response = $this->provider->request();

		return $this;
	}

	/**
	 * Returns the provider object that was created for the payment
	 *
	 * @return Provider
	 */
	public function provider() {
		return $this->provider;
	}

	/**
	 * Returns the response object for the Payment
	 *
	 * @return Response
	 */
	public function response() {
		return $this->response;
	}

	/**
	 * This is a separate method as we want to validate the amount (should be in cents)
	 *
	 * @param integer $amount
	 */
	public function set_amount( $amount ) {
		if (!is_integer($amount)) throw new \Exception('Amount must be provided in cents.');
		
		$this->payment_data['amount'] = $amount;
	}

	/**
	 * Sets the payment data in one fell swoop
	 * 
	 * @param array $data
	 */
	public function set_data( $data ) {
		$this->payment_data = $data;
	}

	/**
	 * Used to set payment data
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		$this->payment_data[$key] = $value;
	}

	/**
	 * Returns the value found at the $key for payment data
	 *
	 * @param string $key
	 */
	public function __get( $key ) {
		if (isset($this->payment_data[$key]))
			return $this->payment_data[$key];
	}

	/**
	 * Registers a new provider for the Payments library
	 *
	 * @param string $provider Should be the name of the class, including the namespace
	 */
	public static function register( $provider ) {
		$namespace_path = explode('\\', $provider);
		$class = array_pop($namespace_path);

		static::$providers[$class] = $provider;
	}
}