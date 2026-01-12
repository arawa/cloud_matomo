<?php

namespace OCA\Matomo\Controller;

use OCA\Matomo\Config;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\IRequest;

class JavaScriptController extends Controller {

	/**
	 * constructor of the controller
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param Config $config
	 */
	public function __construct($appName, IRequest $request, private Config $config) {
		parent::__construct($appName, $request);
	}

	/**
	 * @return Response
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[PublicPage]
	public function tracking() {
		$options = [
			'url' => $this->config->getAppValue('url'),
			'siteId' => $this->config->getAppValue('siteId'),
			'trackDir' => $this->config->getBooleanAppValue('trackDir'),
			'trackUser' => $this->config->getBooleanAppValue('trackUser'),
		];

		$script = "var cloudMatomoOptions = '".json_encode($options)."';";
		$script = file_get_contents(__DIR__ . '/../../js/track.js');
		$script = str_replace('%OPTIONS%', json_encode($options), $script);

		return new DataDownloadResponse($script, 'tracking', 'text/javascript');
	}
}
