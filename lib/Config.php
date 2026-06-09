<?php

namespace OCA\Matomo;

use OCA\Matomo\AppInfo\Application;
use OCP\IAppConfig;

class Config
{
	public function __construct(private IAppConfig $config)
	{
	}

	public function getAppValue(string $key, string $default = ''): string
	{
		$value = $this->config->getValueString(Application::ID, $key, $default);
		return (empty($value)) ? $default : $value;
	}

	public function setAppValue(string $key, string $value)
	{
		if (in_array($key, ['trackDir', 'trackUser'])) {
			return $this->config->setValueBool(Application::ID, $key, $this->validateBoolean($value));
		}
		return $this->config->setValueString(Application::ID, $key, $value);
	}

	public function getBooleanAppValue($key)
	{
		return $this->config->getValueBool(Application::ID, $key);
	}

	private function validateBoolean($val)
	{
		return $val === true || $val === 'true' || $val === 1 || $val === '1';
	}

	public function deleteAppValue(string $key)
	{
		return $this->config->deleteKey(Application::ID, $key);
	}
}
