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

    /**
     * @param \XF\Template\Templater $templater
     */
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
     * @return bool
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
     * @noinspection PhpUnusedParameterInspection
     */
    public function isNotScripBlockNeeded(array $globals)
    {
        return $this->lazyLoading();
    }

    /** @var bool */
    protected $hasIncluded = false;

    public function enqueueJs()
    {
        if ($this->hasIncluded || $this->forceDisabled)
        {
            return;
        }
        $this->hasIncluded = true;
        $nativeLazyLoading = !empty(\XF::options()->svNativeLazyLoading);

        $this->templater->includeJs(
            [
                'addon' => 'SV/LazyImageLoader',
                'prod'  => $nativeLazyLoading
                    ? 'sv/lazyimageloader/lazy-compiled.js'
                    : 'sv/lazyimageloader/lazysizes.min.js',//, sv/lazyimageloader/ls.config.js',
                'dev'   => $nativeLazyLoading
                    ? 'sv/lazyimageloader/lazysizes.js, sv/lazyimageloader/ls.native-loading.js, sv/lazyimageloader/ls.config.js'
                    : 'sv/lazyimageloader/lazysizes.js',
                'min'   => false,
            ]
        );
        $this->templater->includeCss('public:svLazyImageLoader.less');
    }

    /**
     * @return string
     */
    public function getPlaceholderImage()
    {
        return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
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
            $placeholder = $this->getPlaceholderImage();
            $this->enqueueJs();

            $options = \XF::options();
            if ($options->lazyLoaderPlaceholderUrl)
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
                        $placeholder = strtr($options->svLazyLoaderBlankSvg, [
                            '{width}' => $width,
                            '{height}' => $height,
                        ]);
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
     * @noinspection PhpUnusedParameterInspection
     */
    public function getCss(array $globals)
    {
        $css = '';

        if ($this->lazyLoading())
        {
            $css = ' lazyload';
        }

        return $css;
    }
}
