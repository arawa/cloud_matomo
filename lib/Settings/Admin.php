<?php

namespace OCA\Matomo\Settings;

use OCA\Matomo\Config;
use OCA\Matomo\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	/**
	 * Admin constructor.
	 *
	 * @param Config $config
	 */
	public function __construct(private Config $config) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		$parameters = [
			'url' => $this->config->getAppValue('url'),
			'siteId' => $this->config->getAppValue('siteId'),
			'trackDir' => $this->config->getBooleanAppValue('trackDir'),
			'trackUser' => $this->config->getBooleanAppValue('trackUser'),
		];

		return new TemplateResponse(Application::ID, 'settings/admin', $parameters);
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return 'additional';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 */
	public function getPriority() {
		return 50;
	}
}
