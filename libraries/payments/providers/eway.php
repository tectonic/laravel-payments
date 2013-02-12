<?php
/**
 * Eway payment gateway provider
 *
 * @author Kirk Bushell
 * @date 4th February 2013
 */
namespace Payments\Providers;

use Payments\Response;
use Laravel\Config;

class Eway extends Provider
{
	/**
	 * Lists the codes that facilitate an approved purchase
	 *
	 * @var array
	 */
	public static $approved_codes = array('00', '08', '10', '11', '16');

	/**
	 * Executes the request against the gateway
	 * 
	 * @return Response
	 */
	public function request() {
		// Validate the data
		$this->validate();

		// Create the request
		$payload = $this->_payload();
		$raw = $this->_request( $payload );

		if (empty($raw)) // possible auth issue
			throw new \Exception('There appears to be a configuration problem with your provider setup. Check to ensure your customer ID is set correctly, and that you have test mode set correctly (true if testing, false otherwise)');

		// Execute and parse the response from Eway
		$xml = $this->_parse_response( $raw );
		
		// Return the response for the transaction
		return $this->response($xml);
	}

	/**
	 * Formulates the respones and returns a Response object
	 *
	 * @param XMLDocument $xml
	 */
	public function response(\SimpleXMLElement $xml) {
		// Success is determined by first looking at the status provided by eWay. We then also
		// check to ensure that the code provided matches the approved codes above.
		$success = $this->_xml_value($xml->ewayTrxnStatus) === 'True' && in_array(substr($xml->ewayTrxnError, 0, 2), self::$approved_codes);
		
		// now we look at the XML object and return a standard Response
		$response = new Response( $this, $success, $this->_xml_value($xml->ewayTrxnStatus), $this->parse_message($this->_xml_value($xml->ewayTrxnError)) );
		$response->reference = $this->_xml_value($xml->ewayTrxnNumber);
		$response->bank_auth_code = $this->_xml_value($xml->ewayAuthCode);

		return $response;
	}

	/**
	 * Returns the string for the response message
	 *
	 * @param string $message
	 * @return string
	 */
	public function parse_message( $message ) {
		return (is_numeric(substr($message, 0, 2))) ? substr($message, 4) : $message;
	}

	/**
	 * Creates the request and returns the raw request XML
	 * 
	 * @return string
	 */
	private function _request( $payload ) {
		$ch = curl_init( $this->gateway() );

		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 240 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

		$response = curl_exec( $ch );

		return $response;
	}

	/**
	 * Generates the XML payload to be sent to the Eway gateway. Relies
	 * heavily on the original Payment object to request information.
	 *
	 * @return string
	 */
	private function _payload() {
		// Create the XML document
		$dom = new \DOMDocument('1.0', 'iso-8859-1');
		$dom->formatOutput = true; // make output nice - allows for better debugging

		// Creat the XML document's root element
		$root = $dom->createElement('ewaygateway');

		$root->appendChild( $dom->createElement('ewayCustomerID', Config::get('payments.customer_id')) );
		$root->appendChild( $dom->createElement('ewayTotalAmount', $this->payment->amount) );
		$root->appendChild( $dom->createElement('ewayCardHoldersName', $this->payment->cc_name) );
		$root->appendChild( $dom->createElement('ewayCardNumber', $this->payment->cc_number) );
		$root->appendChild( $dom->createElement('ewayCardExpiryMonth', $this->payment->cc_expiry_month) );
		$root->appendChild( $dom->createElement('ewayCardExpiryYear', $this->payment->cc_expiry_year) );
		$root->appendChild( $dom->createElement('ewayCVN', $this->payment->cvn) );
		$root->appendChild( $dom->createElement('ewayCustomerFirstName', $this->payment->first_name) );
		$root->appendChild( $dom->createElement('ewayCustomerLastName', $this->payment->last_name) );
		$root->appendChild( $dom->createElement('ewayCustomerEmail', $this->payment->email) );
		$root->appendChild( $dom->createElement('ewayCustomerAddress', $this->payment->address) );
		$root->appendChild( $dom->createElement('ewayCustomerPostcode', $this->payment->postcode) );
		$root->appendChild( $dom->createElement('ewayCustomerInvoiceDescription', $this->payment->description) );
		$root->appendChild( $dom->createElement('ewayCustomerInvoiceRef', $this->payment->invoice_reference) );
		$root->appendChild( $dom->createElement('ewayTrxnNumber', $this->payment->transaction_number) );
		$root->appendChild( $dom->createElement('ewayOption1', $this->payment->option_1) );
		$root->appendChild( $dom->createElement('ewayOption2', $this->payment->option_2) );
		$root->appendChild( $dom->createElement('ewayOption3', $this->payment->option_3) );

		// Now append the main element to the document
		$dom->appendChild( $root );

		return $dom->saveXML();
	}

	/**
	 * Parses the raw response returned from the gateway, and returns an
	 * XML dom object that can be easily queried for data.
	 *
	 * @param string $response
	 * @return XMLDocument
	 */
	private function _parse_response( $response ) {
		return new \SimpleXMLElement( $response );
	}

	/**
	 * Returns the true string value of a SimpleXMLElement
	 *
	 * @param SimpleXMLElement $element
	 * @return string
	 */
	private function _xml_value($element) {
		return sprintf('%s', $element);
	}

	/**
	 * Validates the available data. Will throw an exception if data is not valid.
	 */
	protected function validate() {
		$required = array('amount', 'cc_name', 'cc_number', 'cc_expiry_year', 'cc_expiry_month', 'cvn');

		foreach ($required as $field) {
			$value = $this->payment->$field;
			if ( empty($value))
				throw new \Exception('Payment data must include "'.$field.'"');
		}
	}

	/**
	 * Switches the gateway to be used, based on the test mode
	 *
	 * @return string
	 */
	private function gateway() {
		return $this->test_mode() ? 'https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp': 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp';
	}
}
