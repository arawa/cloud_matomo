<?php

namespace OCA\Matomo\Controller;

use OCA\Matomo\Config;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SettingsController extends Controller
{
	public function __construct(string $appName, IRequest $request, private Config $config)
	{
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	#[PublicPage]
	#[FrontpageRoute(verb: 'GET', url: '/settings')]
	public function index(): DataResponse
	{
		return new DataResponse([
			'result' => 'success',
			'data' => [
				'url' => $this->config->getAppValue('url'),
				'siteId' => $this->config->getAppValue('siteId'),
				'trackDir' => $this->config->getBooleanAppValue('trackDir')
			],
		]);
	}

	#[FrontpageRoute(verb: 'PUT', url: '/settings/{key}')]
	public function update(string $key): DataResponse
	{
		if (!in_array($key, ['url', 'siteId', 'trackDir', 'trackUser'])) {
			return new DataResponse([
				'status' => 'error',
				'message' => 'Tried to update not allowed param.',
			], Http::STATUS_BAD_REQUEST);
		}

		$this->config->setAppValue($key, $this->getTrimParam('value'));

		return new DataResponse([
			'status' => 'success',
		]);
	}

	private function getTrimParam($key)
	{
		return trim($this->request->getParam($key));
	}

	private function getCheckboxParam($key)
	{
		return $this->getCheckboxValue($this->request->getParam($key));
	}

	private function getCheckboxValue($var)
	{
		return (isset($var)) ? $var : 'false';
	}
}
