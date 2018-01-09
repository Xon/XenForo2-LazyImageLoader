<?php

namespace SV\LazyImageLoader;

use XF\Template\Templater;

class Helper
{
    /**
     * @var bool|null
     */
    protected static $lazyLoadingEnabled = null;

    /**
     * @param bool $enabled
     */
    public static function setLazyLoadingEnabledState($enabled)
    {
        self::$lazyLoadingEnabled = $enabled ? true : false;
    }

    /**
     * @return bool|null
     */
    public static function lazyLoading()
    {
        if (self::$lazyLoadingEnabled === null)
        {
            self::$lazyLoadingEnabled = \XF::options()->SV_LazyLoader_EnableDefault;
        }

        return self::$lazyLoadingEnabled;
    }


    public static function getUrl($globals, $url, $width = 0, $height = 0)
    {
        if (self::lazyLoading())
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

    public static function getCss($globals)
    {
        if (self::lazyLoading())
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

    /**
     * @param string    $content
     * @param array     $params
     * @param Templater $template
     * @return string
     */
    public static function getLazySpinnerCss($content, $params, Templater $template)
    {
        if (self::lazyLoading())
        {
            $css = 'lazyload';
            if (!empty($params['attachment']))
            {
                $attachment = $params['attachment'];
                if (!empty($params['full']) && !empty($attachment['width']) && !empty($attachment['height']))
                {
                    $css .= '" style="max-width:' . $attachment['width'] . 'px ';
                }
                else if (!empty($attachment['thumbnail_width']) && !empty($attachment['thumbnail_height']))
                {
                    $css .= '" style="max-width:' . $attachment['thumbnail_width'] . 'px ';
                }
            }
            return $css . @$params['extra'] . '<noscript>' . @$params['noscript'] . '</noscript>';
        }

        return @$params['extra'];
    }

    /**
     * @param string    $content
     * @param array     $params
     * @param Templater $template
     * @return string
     */
    public static function getLazySpinnerUrl($content, $params, Templater $template)
    {
        $originalUrl = is_array($params) ? $params['url'] : $params;
        if (self::lazyLoading())
        {
            $placeholder = '';
            if (is_array($params))
            {
                $width = $params['width'];
                $height = $params['height'];
                $placeholder = "data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg' viewBox%3D'0 0 {$width} {$height}'%2F%3E";
            }

            // Insert an SVG with proper aspect ratio to make responsive design work smoothly
            return $placeholder . '" data-src="' . $originalUrl;
        }

        return $originalUrl;
    }
}
