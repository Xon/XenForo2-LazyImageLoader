<?php

namespace SV\LazyImageLoader;

use XF\Entity\Attachment;
use XF\Template\Templater;

class Helper
{
    /**  @var null|Helper */
    protected static $helper;

    /**  @var Templater */
    protected $templater;

    /** @var bool */
    protected $hasIncluded = false;

    /**  @var bool */
    protected $forceDisabled = true;

    /** @var bool|null  */
    protected $lazyLoadingEnabled = null;

    /**  @var bool */
    protected $hasLazyLoadingSetup = false;

    /** @var bool */
    protected $nativeLazyLoading = false;

    /** @var bool */
    protected $placeholderUrl = false;

    /** @var string */
    protected $blankSvg = '';

    public static function instance(): self
    {
        if (self::$helper === null)
        {
            self::$helper = \SV\StandardLib\Helper::newExtendedClass(self::class);
        }

        return self::$helper;
    }

    protected function __construct()
    {
        $options = \XF::options();
        $this->nativeLazyLoading = (bool)($options->svLazyLoader_NativeMode ?? $this->nativeLazyLoading);
        $this->placeholderUrl = (bool)($options->svLazyLoader_PlaceholderUrl ?? $this->placeholderUrl);
        $this->blankSvg = (string)($options->svLazyLoader_BlankSvg ?? $this->blankSvg);
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

    public function enqueueJs(): void
    {
        if ($this->hasIncluded || $this->forceDisabled)
        {
            return;
        }
        $this->hasIncluded = true;

        $this->templater->includeJs(
            [
                'addon' => 'SV/LazyImageLoader',
                'prod'  => $this->nativeLazyLoading
                    ? 'sv/lazyimageloader/lazy-compiled.js'
                    : 'sv/lazyimageloader/lazysizes.min.js',
                'dev'   => $this->nativeLazyLoading
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

            if ($this->placeholderUrl && $this->blankSvg !== '')
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

                    if ($width !== 0 && $height !== 0)
                    {
                        $placeholder = strtr($this->blankSvg, [
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
