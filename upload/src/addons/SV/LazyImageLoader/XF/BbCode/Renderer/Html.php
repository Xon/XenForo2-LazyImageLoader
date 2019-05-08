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
    protected static /** @noinspection HtmlRequiredAltAttribute */ $lazyLoadImageTemplate  = '<img src="{PLACEHOLDER}" data-src="%1$s" class="bbImage lazyload" alt="" data-url="%2$s" /><noscript><img src="%1$s" class="bbImage"></noscript>';

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
            self::$lazyLoadImageTemplate = \str_replace('{PLACEHOLDER}', $placeholder, self::$lazyLoadImageTemplate);
            self::$lazyLoadImageTemplate2 = \str_replace('{PLACEHOLDER}', $placeholder, self::$lazyLoadImageTemplate2);
        }
    }

    /**
     * @param array $children
     * @param mixed $option
     * @param array $tag
     * @param array $options
     * @return null|string|string[]
     * @throws \Exception
     */
    public function renderTagImage(array $children, $option, array $tag, array $options)
    {
        $originalImageTemplate = $this->imageTemplate;
        try
        {
            if (self::$lazyLoadingEnabled)
            {
                Helper::instance()->enqueueJs();
                $this->imageTemplate = \XF::$versionId > 2010000 ? static::$lazyLoadImageTemplate2 : static::$lazyLoadImageTemplate;
            }

            return parent::renderTagImage($children, $option, $tag, $options);
        }
        finally
        {
            $this->imageTemplate = $originalImageTemplate;
        }
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
