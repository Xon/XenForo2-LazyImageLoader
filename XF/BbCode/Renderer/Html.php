<?php

namespace SV\LazyImageLoader\XF\BbCode\Renderer;

use SV\LazyImageLoader\Helper;

class Html extends XFCP_Html
{
	protected static $lazyLoadTemplate = null;

	protected static $lazyLoadingEnabled = null;
	protected static $forceSpoilerLazyLoad = null;

	public function __construct(\XF\Str\Formatter $formatter, \XF\Template\Templater $templater)
	{
		parent::__construct($formatter, $templater);

		self::$lazyLoadingEnabled = Helper::lazyLoadingEnabled();
		self::$forceSpoilerLazyLoad = !self::$lazyLoadingEnabled && \XF::options()->sv_forceLazySpoilerTag;
	}

	public function renderTagImage(array $children, $option, array $tag, array $options)
	{
		if (self::$lazyLoadingEnabled)
		{
			$this->_imageTemplate = self::$lazyLoadTemplate;
		}

		return parent::renderTagImage($children, $option, $tag, $options);
	}

	public function renderTagSpoiler(array $children, $option, array $tag, array $options)
	{
		$temp = $this->imageTemplate;

		if (self::$forceSpoilerLazyLoad)
		{
			Helper::setLazyLoadingEnabledState(true);
			$this->imageTemplate = self::$lazyLoadTemplate;
		}
		$response = parent::renderTagSpoiler($children, $option, $tag, $options);
		if (self::$forceSpoilerLazyLoad)
		{
			$this->imageTemplate = $temp;
			Helper::setLazyLoadingEnabledState(false);
		}

		return $response;
	}
}