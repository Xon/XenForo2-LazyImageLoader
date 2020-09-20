<?php

namespace SV\LazyImageLoader\XF\BbCode\Renderer;

use SV\LazyImageLoader\Helper;
use XF\Str\Formatter;
use XF\Template\Templater;

/**
 * Class Html
 *
 * @package SV\LazyImageLoader\XF\BbCode\Renderer
 */
class Html extends XFCP_Html
{
    protected static $updatedPlaceholders = false;
    protected static $lazyLoadImageTemplate2 = '<img src="{PLACEHOLDER}" data-src="%1$s" data-url="%2$s" class="bbImage lazyload %3$s" style="%4$s" alt="%5$s" title="%5$s" /><noscript><img src="%1$s" class="bbImage %3$s"  alt="%5$s" title="%5$s"></noscript>';

    /**
     * @var null|bool
     */
    protected static $lazyLoadingEnabled;

    /**
     * @var null|bool
     */
    protected static $forceSpoilerLazyLoad;

    /**
     * Html constructor.
     *
     * @param Formatter $formatter
     * @param Templater $templater
     *
     * @throws \Exception
     */
    public function __construct(Formatter $formatter, Templater $templater)
    {
        parent::__construct($formatter, $templater);

        self::$lazyLoadingEnabled = Helper::instance()->lazyLoading();
        self::$forceSpoilerLazyLoad = !self::$lazyLoadingEnabled && \XF::options()->sv_forceLazySpoilerTag;

        if (!self::$updatedPlaceholders)
        {
            self::$updatedPlaceholders = true;

            $placeholder = Helper::instance()->getPlaceholderImage();
            self::$lazyLoadImageTemplate2 = \str_replace('{PLACEHOLDER}', $placeholder, self::$lazyLoadImageTemplate2);
        }
    }

    /**
     * XF2.0 - XF2.1 support
     *
     * @param array $children
     * @param mixed $option
     * @param array $tag
     * @param array $options
     * @return null|string|string[]
     * @throws \Exception
     */
    public function renderTagImage(array $children, $option, array $tag, array $options)
    {
        if (\XF::$versionId >= 2020000)
        {
            return parent::renderTagImage($children, $option, $tag, $options);
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $originalImageTemplate = $this->imageTemplate;
        try
        {
            if (self::$lazyLoadingEnabled)
            {
                Helper::instance()->enqueueJs();
                $this->imageTemplate = static::$lazyLoadImageTemplate2;
            }

            return parent::renderTagImage($children, $option, $tag, $options);
        }
        finally
        {
            $this->imageTemplate = $originalImageTemplate;
        }
    }

    /**
     * XF2.2+ support
     *
     * @param string $imageUrl
     * @param string $validUrl
     * @param array  $params
     * @return string|void
     */
    protected function getRenderedImg($imageUrl, $validUrl, array $params = [])
    {
        if (self::$lazyLoadingEnabled)
        {
            Helper::instance()->enqueueJs();
            if (empty($params['alignClass']))
            {
                $params['alignClass'] = '';
            }
            $params['alignClass'] .= ' lazyload';
            $params['src'] = $imageUrl;
        }
        else
        {
            $params['lazyLoading']  = false;
        }

        return parent::getRenderedImg($imageUrl, $validUrl, $params);
    }

    /**
     * @param array $children
     * @param mixed $option
     * @param array $tag
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    public function renderTagSpoiler(array $children, $option, array $tag, array $options)
    {
        if (self::$forceSpoilerLazyLoad)
        {
            self::$lazyLoadingEnabled = true;
            Helper::instance()->setLazyLoadingEnabledState(true);
        }
        try
        {
            return parent::renderTagSpoiler($children, $option, $tag, $options);
        }
        finally
        {
            if (self::$forceSpoilerLazyLoad)
            {
                self::$lazyLoadingEnabled = false;
                Helper::instance()->setLazyLoadingEnabledState(false);
            }
        }
    }
}
