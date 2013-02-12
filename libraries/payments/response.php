<?php
/**
 * The Response class is responsible for simply maintaining the status
 * of a given payment gateway response, as well as the message returned. It
 * also contains a reference to the provider object that initiated the request.
 */
namespace Payments;

use Payments\Providers\Provider;

class Response
{
	/**
	 * Provider object
	 */
	public $provider;

	/**
	 * Whether or not the transaction was a success
	 *
	 * @var string
	 */
	public $success;

	/**
	 * Status of the response
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Message of the response
	 *
	 * @var string
	 */
	public $message;

	/**
	 * Response transaction reference (if applicable)
	 *
	 * @var string
	 */
	public $reference;

	/**
	 * Response bank auth code (if applicable)
	 *
	 * @var string
	 */
	public $bank_auth_code;

	/**
	 * Constructor
	 * 
	 * @param Provider $provider
	 * @param boolean $success
	 * @param string $status
	 * @param string $message
	 */
	public function __construct( Provider $provider, $success, $status, $message ) {
		$this->provider = $provider;
		$this->success = $success;
		$this->status  = $status;
		$this->message = $message;
	}

	/**
	 * Returns true if the transaction was valid
	 *
	 * @return boolean
	 */
	public function valid() {
		return $this->success;
	}

	/**
	 * Returns true if the transaction was invalid
	 *
	 * @return boolean
	 */
	public function invalid() {
		return !$this->valid();
	}
}