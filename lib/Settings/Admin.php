<?php

namespace OCA\Matomo\Settings;

use OCA\Matomo\AppInfo\Application;
use OCA\Matomo\Config;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings
{
	/**
	 * Admin constructor.
	 *
	 * @param Config $config
	 */
	public function __construct(private Config $config, private IInitialState $initialState)
	{
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm()
	{
		$this->initialState->provideInitialState(
			'config',
			[
				'matomoUrl' => $this->config->getAppValue('url'),
				'matomoSiteId' => $this->config->getAppValue('siteId'),
				'matomoTrackDir' => $this->config->getBooleanAppValue('trackDir'),
				'matomoTrackUser' => $this->config->getBooleanAppValue('trackUser'),
			]
		);
		Util::addScript('matomo', 'matomo-admin');
		Util::addStyle('matomo', 'matomo-admin');
		return new TemplateResponse(Application::ID, 'settings/admin', [], 'blank');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection()
	{
		return 'additional';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 */
	public function getPriority()
	{
		return 50;
	}
}
