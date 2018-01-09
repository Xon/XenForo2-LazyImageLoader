<?php

namespace SV\LazyImageLoader\XF\BbCode\Renderer;

use SV\LazyImageLoader\Helper;
use XF\Str\Formatter;
use XF\Template\Templater;

class Html extends XFCP_Html
{
    protected static $lazyLoadImageTemplate = null;

    protected static $lazyLoadingEnabled   = null;
    protected static $forceSpoilerLazyLoad = null;

    protected $originalImageTemplate = null;

    public function __construct(Formatter $formatter, Templater $templater)
    {
        parent::__construct($formatter, $templater);

        self::$lazyLoadingEnabled = Helper::lazyLoadingEnabled();
        self::$forceSpoilerLazyLoad = !self::$lazyLoadingEnabled && \XF::options()->sv_forceLazySpoilerTag;
    }

    public function renderTagImage(array $children, $option, array $tag, array $options)
    {
        try
        {
            $this->originalImageTemplate = $this->imageTemplate;

            if (self::$lazyLoadingEnabled)
            {
                $this->imageTemplate = self::$lazyLoadImageTemplate;
            }

            return parent::renderTagImage($children, $option, $tag, $options);
        }
        finally
        {
            $this->imageTemplate = $this->originalImageTemplate;
        }
    }

    public function renderTagSpoiler(array $children, $option, array $tag, array $options)
    {
        try
        {
            $this->originalImageTemplate = $this->imageTemplate;

            if (self::$forceSpoilerLazyLoad)
            {
                Helper::setLazyLoadingEnabledState(true);
                $this->imageTemplate = self::$lazyLoadImageTemplate;
            }

            return parent::renderTagSpoiler($children, $option, $tag, $options);
        }
        finally
        {
            $this->imageTemplate = $this->originalImageTemplate;
            Helper::setLazyLoadingEnabledState(false);
        }
    }
}
