<?php
/**
 * Provides a base implementation for all providers. The abstract
 * methods need to be implemented in order for the Payment
 * class to do what it needs to.
 * 
 * @author Kirk Bushell
 * @date 4th February 2013
 */
namespace Payments\Providers;

use Payments\Payment;
use Laravel\Config;

abstract class Provider
{
	/**
	 * Stores the amount to be used for the transaction
	 * 
	 * @var mixed $amount
	 */
	protected $amount;

	/**
	 * Stores the payment object required for various data queries
	 *
	 * @var Payment
	 */
	protected $payment;

	/**
	 * Constructs the provider
	 *
	 * @param Payment $payment
	 */
	public function __construct( Payment $payment ) {
		$this->payment = $payment;
	}

	/**
	 * Stores the amount to be used for the transaction
	 * 
	 * @param mixed $amount
	 */
	public function set_amount( $amount ) {
		$this->amount = $amount;
	}

	/**
	 * Returns the Payment's test mode setting
	 *
	 * @return boolean
	 */
	protected function test_mode() {
		return Config::get('payments.test_mode', true);
	}

	/**
	 * Overloadable method - all providers should implement data validation and execute
	 * the method when it makes sense to do so.
	 */
	abstract protected function validate();
	
	/**
	 * Classes that extend, should implement this method that best
	 * suits the API for the payment gateway.
	 */
	abstract public function request();
}