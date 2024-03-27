<?php

namespace SV\LazyImageLoader;

use XF\Entity\Attachment;
use XF\Template\Templater;

/**
 * Class Helper
 *
 * @package SV\LazyImageLoader
 */
class Helper
{
    /**  @var null|Helper */
    protected static $helper;

    /**  @var Templater */
    protected $templater;

    /**  @var bool */
    protected $forceDisabled = true;

    /** @var bool|null  */
    protected $lazyLoadingEnabled = null;

    /**  @var bool */
    protected $hasLazyLoadingSetup = false;

    public static function instance(): self
    {
        if (self::$helper === null)
        {
            $class = \XF::extendClass(self::class);
            self::$helper = new $class();
        }

        return self::$helper;
    }

    public function allowEnabling(Templater $templater): void
    {
        $this->forceDisabled = false;
        $this->templater = $templater;
    }

    public function hasLazyLoadingSetup(): bool
    {
        return !$this->forceDisabled && $this->hasLazyLoadingSetup;
    }

    public function setLazyLoadingEnabledState(bool $enabled): void
    {
        if ($enabled)
        {
            $this->hasLazyLoadingSetup = true;
        }
        $this->lazyLoadingEnabled = $enabled;
    }

    public function lazyLoading(): bool
    {
        if ($this->lazyLoadingEnabled === null)
        {
            $this->setLazyLoadingEnabledState((bool)(\XF::options()->svLazyLoader_EnableDefault ?? true));
        }

        return !$this->forceDisabled && $this->lazyLoadingEnabled;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function isNotScripBlockNeeded(array $globals): bool
    {
        return $this->lazyLoading();
    }

    /** @var bool */
    protected $hasIncluded = false;

    public function enqueueJs(): void
    {
        if ($this->hasIncluded || $this->forceDisabled)
        {
            return;
        }
        $this->hasIncluded = true;
        $nativeLazyLoading = !empty(\XF::options()->svLazyLoader_NativeMode);

        $this->templater->includeJs(
            [
                'addon' => 'SV/LazyImageLoader',
                'prod'  => $nativeLazyLoading
                    ? 'sv/lazyimageloader/lazy-compiled.js'
                    : 'sv/lazyimageloader/lazysizes.min.js',
                'dev'   => $nativeLazyLoading
                    ? 'sv/lazyimageloader/ls.config.js, sv/lazyimageloader/lazysizes.js, sv/lazyimageloader/ls.native-loading.js'
                    : 'sv/lazyimageloader/lazysizes.js',
                'min'   => false,
            ]
        );
        $this->templater->includeCss('public:svLazyImageLoader.less');
    }

    public function getPlaceholderImage(): string
    {
        return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    }

    public function getUrl(array $globals, string $url, bool $urlNotEscaped = true): string
    {
        if ($urlNotEscaped)
        {
            $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8', false);
        }
        if ($this->lazyLoading())
        {
            $placeholder = $this->getPlaceholderImage();
            $this->enqueueJs();

            $options = \XF::options();
            if (!empty($options->svLazyLoader_PlaceholderUrl))
            {
                $attachment = $globals['attachment'] ?? null;
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

                    if ($width && $height && !empty($options->svLazyLoader_BlankSvg))
                    {
                        $placeholder = strtr($options->svLazyLoader_BlankSvg, [
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

    /** @noinspection PhpUnusedParameterInspection */
    public function getCss(array $globals): string
    {
        $css = '';

        if ($this->lazyLoading())
        {
            $css = ' lazyload';
        }

        return $css;
    }
}
