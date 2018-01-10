<?php

namespace SV\LazyImageLoader;

use XF\Entity\Attachment;

class Helper
{
    /** @var Helper */
    protected static $helper = null;

    /**
     * @return Helper
     */
    public static function instance()
    {
        if (self::$helper === null)
        {
            $class = \XF::extendClass('SV\LazyImageLoader\Helper');
            self::$helper = new $class();
        }

        return self::$helper;
    }

    /**
     * @var bool|null
     */
    protected $lazyLoadingEnabled = null;

    /**
     * @param bool $enabled
     */
    public function setLazyLoadingEnabledState($enabled)
    {
        $this->lazyLoadingEnabled = $enabled ? true : false;
    }

    /**
     * @return bool|null
     */
    public function lazyLoading()
    {
        if ($this->lazyLoadingEnabled === null)
        {
            $this->lazyLoadingEnabled = \XF::options()->SV_LazyLoader_EnableDefault;
        }

        return $this->lazyLoadingEnabled;
    }

    /**
     * @param array $globals
     * @return bool
     */
    public function isNotScripBlockNeeded(array $globals)
    {
        return !empty($globals['lz_enabled']) || $this->lazyLoading();
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
            $url = htmlspecialchars(strval($url), ENT_QUOTES, 'UTF-8', false);
        }
        if (!empty($globals['lz_enabled']) || $this->lazyLoading())
        {
            $placeholder = '';

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
    public function getCss(array $globals)
    {
        $css = '';

        if (!empty($globals['lz_enabled']) || $this->lazyLoading())
        {
            $css = ' lazyload';
        }

        return $css;
    }
}
