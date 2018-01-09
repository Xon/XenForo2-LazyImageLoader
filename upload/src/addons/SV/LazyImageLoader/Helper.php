<?php

namespace SV\LazyImageLoader;

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
            self::$helper = new Helper();
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

    public function getNoScriptBlock($globals, $imgTag)
    {
        if ($this->lazyLoading())
        {
            return $imgTag;
        }
        return '';
    }

    public function getUrl($globals, $url, $width = 0, $height = 0)
    {
        if ($this->lazyLoading())
        {
            $placeholder = '';
            if ($width && $height)
            {
                $placeholder = "data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg' viewBox%3D'0 0 {$width} {$height}'%2F%3E";
            }

            // Insert an SVG with proper aspect ratio to make responsive design work smoothly
            return $placeholder . '" data-src="' . $url;
        }

        return $url;
    }

    public function getCss($globals)
    {
        if ($this->lazyLoading())
        {
            $css = 'lazyload';
            if (!empty($globals['attachment']))
            {
                $attachment = $globals['attachment'];
                if (!empty($globals['full']) && !empty($attachment['width']) && !empty($attachment['height']))
                {
                    $css .= '" style="max-width:' . $attachment['width'] . 'px ';
                }
                else if (!empty($attachment['thumbnail_width']) && !empty($attachment['thumbnail_height']))
                {
                    $css .= '" style="max-width:' . $attachment['thumbnail_width'] . 'px ';
                }
            }
            return $css;// . @$params['extra'] . '<noscript>' . @$params['noscript'] . '</noscript>';
        }

        return '';
    }
}
