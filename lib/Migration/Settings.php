<?php

namespace OCA\Matomo\Migration;

use OCA\Matomo\AppInfo\Application;
use OCP\IAppConfig;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

class Settings implements IRepairStep
{
	public const OLD_APP_ID = 'piwik';

	public function __construct(private IAppConfig $config)
	{
	}

	/**
	 * Returns the step's name
	 */
	public function getName()
	{
		return 'Update Piwik/Matomo settings format';
	}

	/**
	 * @param IOutput $output
	 */
	public function run(IOutput $output)
	{
		$config = $this->config;
		$settings = $config->getKeys(Application::ID);
		if (in_array('url', $settings) || in_array('siteId', $settings)) {
			$output->info("Migration already executed");
			return;
		}
		$settings = $config->getKeys(self::OLD_APP_ID);
		if (in_array('piwik', $settings)) {
			$oldPiwikConfigValue = $config->getValueString(self::OLD_APP_ID, 'piwik');
			$oldPiwikConfig = json_decode($oldPiwikConfigValue);
			$trackDir = $oldPiwikConfig->trackDir;

			$config->setValueString(Application::ID, 'url', $oldPiwikConfig->url);
			$config->setValueString(Application::ID, 'siteId', $oldPiwikConfig->siteId);
			$config->setValueBool(Application::ID, 'trackDir', $trackDir === 'on');

			$config->deleteKey(self::OLD_APP_ID, 'piwik');
		} else {
			if (in_array('url', $settings)) {
				$config->setValueString(Application::ID, 'url', $config->getValueString(self::OLD_APP_ID, 'url'));
			}
			if (in_array('siteId', $settings)) {
				$config->setValueString(Application::ID, 'siteId', $config->getValueString(self::OLD_APP_ID, 'siteId'));
			}
			if (in_array('trackDir', $settings)) {
				$config->setValueBool(Application::ID, 'trackDir', filter_var($config->getValueString(self::OLD_APP_ID, 'trackDir'), FILTER_VALIDATE_BOOLEAN));
			}
			if (in_array('trackUser', $settings)) {
				$config->setValueBool(Application::ID, 'trackUser', filter_var($config->getValueString(self::OLD_APP_ID, 'trackUser'), FILTER_VALIDATE_BOOLEAN));
			}
		}
	}
}
