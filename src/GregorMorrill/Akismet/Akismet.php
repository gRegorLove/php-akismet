<?php

namespace GregorMorrill\Akismet;

class Akismet
{
	/**
	 * @var string $api_key Akismet API key
	 * @access private
	 */
	private $api_key;


	/**
	 * @var string $endpoint Akismet base endpoint
	 * @access private
	 */
	private $endpoint;


	/**
	 * Constructor method
	 * @param string $api_key
	 * @access public
	 * @return bool
	 */
	public function __construct($api_key = NULL)
	{

		if ( $api_key )
		{
			$this->api_key = $api_key;
			$this->endpoint = sprintf('http://%s.rest.akismet.com/1.1/', $api_key);
		}
		else
		{
			throw new Exception('Akismet API key not supplied.');
		}

	} # end method __construct()


	/**
	 * This method handles checking content for spam
	 * For more information, see: http://akismet.com/development/api/#comment-check
	 * @param array $data
	 * @access public
	 * @return array
	 */
	public function checkSpam($data)
	{
		# send these server vars to Akismet as well
		$server_keys = array_fill_keys(array(
			'HTTP_HOST', 'HTTP_USER_AGENT', 'HTTP_ACCEPT', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_ACCEPT_ENCODING',
			'HTTP_ACCEPT_CHARSET', 'HTTP_KEEP_ALIVE', 'HTTP_REFERER', 'HTTP_CONNECTION', 'HTTP_FORWARDED',
			'HTTP_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP',
			'REMOTE_ADDR', 'REMOTE_HOST', 'REMOTE_PORT', 'SERVER_PROTOCOL', 'REQUEST_METHOD'),
			0
		);
		$approved_server_keys = array_intersect_key($_SERVER, $server_keys);

		# merge server vars with supplied data
		$data = array_merge($approved_server_keys, $data);

		# post to Akismet
		$response = $this->post($this->endpoint . 'comment-check', $data);

		# if: Akismet result indicates spam
		if ( 'false' != trim(strtolower($response['body'])) )
		{
			$response['spam'] = TRUE;
		}
		# else: not spam
		else
		{
			$response['spam'] = FALSE;
		}

		return $response;
	} # end method checkSpam()


	/**
	 * This method handles submitting spam content
	 * For more information, see: http://akismet.com/development/api/#submit-spam
	 * @param array $data
	 * @access public
	 * @return array
	 */
	public function submitSpam($data)
	{
		return $this->post($this->endpoint . 'submit-spam', $data);
	} # end method submitSpam()


	/**
	 * This method handles submitting ham content
	 * For more information, see: http://akismet.com/development/api/#submit-ham
	 * @param array $data
	 * @access public
	 * @return array
	 */
	public function submitHam($data)
	{
		return $this->post($this->endpoint . 'submit-ham', $data);
	} # end method submitHam()


	/**
	 * This method handles posting data to Akismet
	 * @param string $endpoint
	 * @param array $data
	 * @access private
	 * @return array
	 */
	private function post($endpoint, $data)
	{
		$response = array();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 6);
		curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
		$curl_response = curl_exec($ch);

		if ( FALSE === $curl_response )
		{
			throw new Exception('There was an error sending the Akismet request.');
		}

		$response['info'] = curl_getinfo($ch);
		$response['info']['request_header'] .= http_build_query($data);
		$response['header'] = substr($curl_response, 0, $response['info']['header_size']);
		$response['body'] = substr($curl_response, $response['info']['header_size']);

		curl_close($ch);

		return $response;
	} # end method post()

}
