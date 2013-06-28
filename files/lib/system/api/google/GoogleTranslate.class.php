<?php
namespace wcf\system\api\google;
use wcf\system\exception\NamedUserException;
use wcf\system\SingletonFactory;
use wcf\util\JSON;

/**
 * Translation Interface for Google Translate API.
 *
 * @author	Jeffrey Reichardt
 * @copyright	2012-2013 DevLabor UG (haftungsbeschränkt)
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.devlabor.wcf.google.api
 * @subpackage	system.api.google
 */
class GoogleTranslate extends GoogleService {
	/**
	 * @see	wcf\system\api\google\GoogleService::$host
	 */
	protected $host = 'https://www.googleapis.com/language/translate/v2';

	/**
	 * supported languages, multi-dimensional
	 * @var array
	 */
	protected $supportedLanguages = array();

	/**
	 * Returns a list of supported languages by given language code.
	 * 
	 * @param	string	$languageCode	Language Code of Target Language
	 * @return	array
	 */
	public function getSupportedLanguages($languageCode = '') {
		if (empty($this->supportedLanguages) || !isset($this->supportedLanguages[$languageCode])) {
			// gets data by GET method
			$reply = $this->getReply($this->buildURL('/languages', array(
				'key' => GOOGLE_TRANSLATE_API_KEY,
				'target' => $languageCode
			)));
			
			if (!empty($reply['body'])) {
				$jsonData = JSON::decode($reply['body']);
				
				if (isset($jsonData['data']) && isset($jsonData['data']['languages'])) {
					$this->supportedLanguages[$languageCode] = array();
					
					foreach ($jsonData['data']['languages'] as $language) {
						$this->supportedLanguages[$languageCode][] = $language['language'];
					}
				}
			}
		}
		
		return $this->supportedLanguages[$languageCode];
	}

	/**
	 * Detects languages by given string.
	 * 
	 * @param	string	$text
	 * @return	array
	 */
	public function detectLanguages($text) {
		// gets data by GET method
		$reply = $this->getReply($this->buildURL('/detect', array(
			'key' => GOOGLE_TRANSLATE_API_KEY,
			'q' => $text
		)));

		if (!empty($reply['body'])) {
			$jsonData = JSON::decode($reply['body']);

			if (isset($jsonData['data']) && isset($jsonData['data']['detections'])) {
				return $jsonData['data']['detections'];
			}
		}
		
		return array();
	}

	/**
	 * Translates given text into given target language.
	 * 
	 * @param	$text
	 * @param	string	$sourceLanguageCode
	 * @param	string	$targetLanguageCode
	 * @param	string	$format
	 * @return	string
	 * @throws	\wcf\system\exception\NamedUserException
	 */
	public function translate($text, $sourceLanguageCode = '', $targetLanguageCode = '', $format = 'text') {
		if (empty($sourceLanguageCode)) {
			$detectedLanguages = $this->detectLanguages($text);
			
			if (empty($detectedLanguages)) {
				throw new NamedUserException('Source language can\'t be detected.');
			}
			
			$sourceLanguageCode = reset($detectedLanguages);
		}
		
		if (empty($targetLanguageCode)) {
			$targetLanguageCode = WCF::getLanguage()->languageCode;
		}

		if (!$this->isSupported($sourceLanguageCode, $targetLanguageCode)) {
			throw new NamedUserException('Translation between '. $sourceLanguageCode .' and '. $targetLanguageCode .' isn\'t supported.');
		}
		
		$translatedText = '';

		// gets data by GET method
		$reply = $this->getReply($this->buildURL('', array(
			'key' => GOOGLE_TRANSLATE_API_KEY,
			'q' => $text,
			'source' => $sourceLanguageCode,
			'target' => $targetLanguageCode,
			'format' => $format
		)));

		if (!empty($reply['body'])) {
			$jsonData = JSON::decode($reply['body']);

			if (isset($jsonData['data']) && isset($jsonData['data']['translations'])) {
				foreach ($jsonData['data']['translations'] as $translation) {
					$translatedText = $translation['translatedText'];
					break;
				}
			}
		}
		
		return $translatedText;
	}

	/**
	 * Returns true if language translation is supported.
	 * 
	 * @param	string	$sourceLanguageCode
	 * @param	string	$targetLanguageCode
	 * @return	bool
	 */
	public function isSupported($sourceLanguageCode, $targetLanguageCode) {
		$supportedLanguages = $this->getSupportedLanguages($targetLanguageCode);
		
		return (in_array($sourceLanguageCode, $supportedLanguages));
	}
}
