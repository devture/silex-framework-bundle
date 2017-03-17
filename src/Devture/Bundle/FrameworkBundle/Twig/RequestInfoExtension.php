<?php
namespace Devture\Bundle\FrameworkBundle\Twig;

use Symfony\Component\HttpFoundation\Request;

class RequestInfoExtension extends \Twig_Extension {

	private $container;

	public function __construct(\Pimple\Container $container) {
		$this->container = $container;
	}

	public function getName() {
		return 'devture_framework_request_info_extension';
	}

	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('is_route', array($this, 'isRoute')),
			new \Twig_SimpleFunction('is_route_prefix', array($this, 'isRoutePrefix')),
		);
	}

	public function isRoute($name) {
		return ($this->getRequest()->attributes->get('_route') === $name);
	}

	public function isRoutePrefix($prefix) {
		return (strpos($this->getRequest()->attributes->get('_route'), $prefix) === 0);
	}

	/**
	 * @throws \LogicException when not in a request context
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	private function getRequest() {
		$request = $this->getRequestStack()->getCurrentRequest();
		if ($request === null) {
			throw new \LogicException('Trying to get request, but not in a request context.');
		}
		return $request;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\RequestStack
	 */
	private function getRequestStack() {
		return $this->container['request_stack'];
	}

}
