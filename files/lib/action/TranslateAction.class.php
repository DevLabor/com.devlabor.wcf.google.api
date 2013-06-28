<?php
namespace wcf\action;
use wcf\system\api\google\GoogleTranslate;
use wcf\system\exception\AJAXException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Translates given text from sourceLanguage to targetLanguage. 
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2012-2013 DevLabor UG (haftungsbeschränkt)
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.devlabor.wcf.google.api
 * @subpackage	action
 */
class TranslateAction extends AbstractAjaxAction {
	/**
	 * @see	wcf\action\Action::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	wcf\action\Action::$neededModules
	 */
	public $neededModules = array('MODULE_GOOGLE_SERVICES', 'GOOGLE_TRANSLATE_ENABLED', 'GOOGLE_TRANSLATE_API_KEY');
	
	/**
	 * @see	wcf\action\Action::$neededPermissions
	 */
	public $neededPermissions = array('user.system.google.translate.canTranslate');
	
	/**
	 * ISO-Code of Source Language
	 * @var string
	 */
	protected $sourceLanguage = '';

	/**
	 * * ISO-Code of Target Language
	 * @var string
	 */
	protected $targetLanguage = '';

	/**
	 * Text for Translation
	 * @var string
	 */
	protected $text = '';
	
	/**
	 * @see	wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['sourceLanguage'])) $this->sourceLanguage = StringUtil::trim($_REQUEST['sourceLanguage']);
		if (isset($_REQUEST['targetLanguage'])) $this->targetLanguage = StringUtil::trim($_REQUEST['targetLanguage']);
		if (isset($_REQUEST['text'])) $this->text = StringUtil::trim($_REQUEST['text']);
	}
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
				
		try {
			$translatedText = GoogleTranslate::getInstance()->translate($this->text, $this->sourceLanguage, $this->targetLanguage);
		}
		catch(Exception $e) {
			throw new AJAXException($e->getMessage());
		}

		if (empty($translatedText)) {
			throw new AJAXException('No translation available.');
		}
		
		$this->executed();
		
		$this->sendJsonResponse(array(
			'sourceLanguage' => $this->sourceLanguage,
			'targetLanguage' => $this->targetLanguage,
			'translatedText' => $translatedText
		));
	}
}
