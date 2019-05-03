<?php

namespace SV\LazyImageLoader;

use XF\Entity\Attachment;

/**
 * Class Helper
 *
 * @package SV\LazyImageLoader
 */
class Helper
{
    /**
     * @var null|Helper
     */
    protected static $helper;

    /**
     * @var \XF\Template\Templater
     */
    protected $templater;

    /**
     * @var bool
     */
    protected $forceDisabled = true;

    /**
     * @var bool|null
     */
    protected $lazyLoadingEnabled;

    /**
     * @var bool
     */
    protected $hasLazyLoadingSetup = false;

    /**
     * @return Helper
     * @throws \Exception
     */
    public static function instance()
    {
        if (self::$helper === null)
        {
            /** @noinspection ClassConstantCanBeUsedInspection */
            $class = \XF::extendClass('SV\LazyImageLoader\Helper');
            self::$helper = new $class();
        }

        return self::$helper;
    }

    public function allowEnabling(\XF\Template\Templater $templater)
    {
        $this->forceDisabled = false;
        $this->templater = $templater;
    }

    /**
     * @return bool
     */
    public function hasLazyLoadingSetup()
    {
        return !$this->forceDisabled && $this->hasLazyLoadingSetup;
    }

    /**
     * @param bool $enabled
     */
    public function setLazyLoadingEnabledState($enabled)
    {
        if ($enabled)
        {
            $this->hasLazyLoadingSetup = true;
        }
        $this->lazyLoadingEnabled = $enabled ? true : false;
    }

    /**
     * @return bool|null
     */
    public function lazyLoading()
    {
        if ($this->lazyLoadingEnabled === null)
        {
            $this->setLazyLoadingEnabledState(\XF::options()->SV_LazyLoader_EnableDefault);
        }

        return !$this->forceDisabled && $this->lazyLoadingEnabled;
    }

    /**
     * @param array $globals
     * @return bool
     */
    public function isNotScripBlockNeeded(/** @noinspection PhpUnusedParameterInspection */ array $globals)
    {
        return $this->lazyLoading();
    }

    protected $hasIncluded = false;
    public function enqueueJs()
    {
        if ($this->hasIncluded || $this->forceDisabled)
        {
            return;
        }
        $this->hasIncluded = true;

        $this->templater->includeJs(
            [
                'addon' => 'SV/LazyImageLoader',
                'prod'  => 'sv/lazyimageloader/lazysizes.min.js',
                'dev'   => 'sv/lazyimageloader/lazysizes.js',
                'min'   => false,
            ]
        );

        $this->templater->includeJs(
            [
                'addon' => 'SV/LazyImageLoader',
                'prod'  => 'sv/lazyimageloader/xf/lightbox.min.js',
                'dev'   => 'sv/lazyimageloader/xf/lightbox.js',
                'min'   => false,
            ]
        );
    }

    /**
     * @param array  $globals
     * @param string $url
     * @param bool   $urlNotEscaped
     * @return string
     */
    public function getUrl(array $globals, $url, $urlNotEscaped = true)
    {
        if ($urlNotEscaped)
        {
            $url = htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8', false);
        }
        if ($this->lazyLoading())
        {
            $placeholder = '';
            $this->enqueueJs();

            if (\XF::options()->lazyLoaderPlaceholderUrl)
            {
                $attachment = isset($globals['attachment']) ? $globals['attachment'] : null;
                $full = !empty($globals['full']);
                if ($attachment instanceof Attachment)
                {
                    $width = $height = 0;
                    $attachmentData = $attachment->Data;
                    if ($attachmentData)
                    {
                        if ($full)
                        {
                            $width = $attachmentData->width;
                            $height = $attachmentData->height;
                        }
                        else if ($attachment->has_thumbnail)
                        {
                            $width = $attachmentData->thumbnail_width;
                            $height = $attachmentData->thumbnail_height;
                        }
                    }

                    if ($width && $height)
                    {
                        $placeholder = "data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg' viewBox%3D'0 0 {$width} {$height}'%2F%3E";
                    }
                }
            }

            // Insert an SVG with proper aspect ratio to make responsive design work smoothly
            return $placeholder . '" data-src="' . $url;
        }

        return $url;
    }

    /**
     * @param array $globals
     * @return string
     */
    public function getCss(/** @noinspection PhpUnusedParameterInspection */ array $globals)
    {
        $css = '';

        if ($this->lazyLoading())
        {
            $css = ' lazyload';
        }

        return $css;
    }
}
