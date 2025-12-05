<?php
/**
 * Created            16/02/2017 22:59
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

/**
 * Class Email
 * An email address. Broken into two parts (id and domain) to help prevent email harvesting.
 * @package phpGPX\Models
 */
class Email implements Summarizable
{

	/**
	 * Id half of email address (jakub.dubec)
	 * @var string
	 */
	public $id;

	/** Domain half of email address (gmail.com)
	 * @var string
	 */
	public $domain;


	/**
  * Serialize object to array
  */
 public function toArray(): array
	{
		return [
			'id' => (string) $this->id,
			'domain' => (string) $this->domain
		];
	}
}
