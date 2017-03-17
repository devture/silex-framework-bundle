<?php
namespace Devture\Bundle\FrameworkBundle;

use Devture\Component\Form\Twig\FormExtension;
use Devture\Component\Form\Twig\TokenExtension;
use Devture\Component\Form\Token\TemporaryTokenManager;

class ServicesProvider implements \Pimple\ServiceProviderInterface, \Silex\Api\BootableProviderInterface {

	private $config;

	public function __construct(array $config) {
		$config = array_merge(array(
			'token.validity_time' => 7200,
			'token.hash_function' => 'sha256',
		), $config);

		if (!isset($config['token.secret'])) {
			throw new \InvalidArgumentException('The token.secret configuration parameter is required.');
		}

		$this->config = $config;
	}

	public function register(\Pimple\Container $container) {
		$config = $this->config;

		$container->register(new \Silex\Provider\ServiceControllerServiceProvider());

		$container['devture_framework.csrf_token_manager'] = function () use ($config) {
			return new TemporaryTokenManager($config['token.validity_time'], $config['token.secret'], $config['token.hash_function']);
		};

		$container['devture_framework.twig.token_extension'] = function ($container) {
			return new TokenExtension($container['devture_framework.csrf_token_manager']);
		};

		$container['devture_framework.twig.form_extension'] = function ($container) {
			return new FormExtension($container);
		};

		$container['devture_framework.twig.request_info_extension'] = function ($container) {
			return new Twig\RequestInfoExtension($container);
		};
	}

	public function boot(\Silex\Application $app) {
		if (!isset($app['twig.loader.filesystem'])) {
			throw new \RuntimeException('Silex\Provider\TwigServiceProvider not registered. Cannot initialize properly.');
		}

		$class = new \ReflectionClass('\Devture\Component\Form\Twig\FormExtension');
		$app['twig.loader.filesystem']->addPath(dirname(dirname($class->getFileName())) . '/Resources/views/');

		$app['twig']->addExtension($app['devture_framework.twig.form_extension']);
		$app['twig']->addExtension($app['devture_framework.twig.token_extension']);
		$app['twig']->addExtension($app['devture_framework.twig.request_info_extension']);
	}

}
