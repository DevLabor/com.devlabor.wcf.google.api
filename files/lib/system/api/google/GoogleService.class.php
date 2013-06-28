<?php
namespace wcf\system\api\google;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\HTTPRequest;

/**
 * Abstract implementation for google API services.
 *
 * @author	Jeffrey Reichardt
 * @copyright	2012-2013 DevLabor UG (haftungsbeschränkt)
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.devlabor.wcf.google.api
 * @subpackage	system.api.google
 */
abstract class GoogleService extends SingletonFactory {
	/**
	 * api host
	 * @var	string
	 */
	protected $host = '';

	/**
	 * Builds url by given parameters.
	 * 
	 * @param	$append	Append given string to api host
	 * @param	array	$options
	 * @return	string
	 * @throws	\wcf\system\exception\SystemException
	 */
	protected function buildURL($append, array $options = array()) {
		if (empty($this->host)) {
			throw new SystemException('Host is not defined for ' . get_class($this));
		}
		
		$url = $this->host . $append;
		
		// add options
		if (!empty($options)) {
			$url .= '?' . http_build_query($options);
		}
		
		return $url;
	}

	/**
	 * Gets reply data from given $url
	 * 
	 * @param	$url
	 * @return	array
	 */
	protected function getReply($url) {
		$request = new HTTPRequest($url);
		$request->execute();
		
		return $request->getReply();
	}
}
