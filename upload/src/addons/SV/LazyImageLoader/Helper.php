<?php

namespace SV\LazyImageLoader;

use XF\Template\Templater;

class Helper
{
    protected static $lazyLoadingEnabled = null;

    public static function setLazyLoadingEnabledState($enabled)
    {
        self::$lazyLoadingEnabled = $enabled ? true : false;
    }

    public static function lazyLoadingEnabled()
    {
        if (self::$lazyLoadingEnabled === null)
        {
            self::$lazyLoadingEnabled = \XF::options()->SV_LazyLoader_EnableDefault;
        }

        return self::$lazyLoadingEnabled;
    }

    public static function getLazySpinnerUrl($content, $params, Templater $template)
    {
        $originalUrl = is_array($params) ? $params['url'] : $params;
        if (self::$lazyLoadingEnabled)
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
