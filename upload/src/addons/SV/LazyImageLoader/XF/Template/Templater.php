<?php

namespace SV\LazyImageLoader\XF\Template;

use SV\Threadmarks\Entity\ThreadmarkIndex;
use XF\Mvc\Entity\Entity;
use XFRM\Entity\ResourceItem;
use function str_replace;

/**
 * @Extends \XF\Template\Templater
 */
class Templater extends XFCP_Templater
{
    protected function injectImgLazyAttribute(string $url, bool $hasNativeLazyLoading)
    {
        if ($url !== '' && (\XF::options()->svLazyLoader_Icons ?? false))
        {
            if ($hasNativeLazyLoading)
            {
                $url = str_replace('" loading="lazy"', '"', $url);
            }
            else
            {
                $url = str_replace('<img ', '<img loading="lazy" ', $url);
            }
        }

        return $url;
    }

    /** @noinspection PhpCastIsUnnecessaryInspection */
    public function fnAvatar($templater, &$escape, $user, $size, $canonical = false, $attributes = [])
    {
        return $this->injectImgLazyAttribute((string)parent::fnAvatar($templater, $escape, $user, $size, $canonical, $attributes), \XF::$versionId > 2020000);
    }

    public function fnThreadmarkIndexIcon(\SV\Threadmarks\XF\Template\Templater $templater, &$escape, Entity $content, ThreadmarkIndex $threadmarkIndex, $size = 'l', array $attributes = [])
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->injectImgLazyAttribute((string)parent::fnThreadmarkIndexIcon($templater, $escape, $content, $threadmarkIndex, $size, $attributes), false);
    }

    public function fnResourceIcon($templater, &$escape, ResourceItem $resource, $size = 'm', $href = '')
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->injectImgLazyAttribute((string)parent::fnResourceIcon($templater, $escape, $resource, $size, $href), \XF::$versionId > 2020000);
    }
}