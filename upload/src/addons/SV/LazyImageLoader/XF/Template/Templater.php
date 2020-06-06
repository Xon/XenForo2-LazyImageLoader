<?php

namespace SV\LazyImageLoader\XF\Template;

use XF\Mvc\Entity\Entity;

/**
 * Extends \XF\Template\Templater
 */
class Templater extends XFCP_Templater
{
    protected function injectImgLazyAttribute($url)
    {
        if ($url && !empty(\XF::options()->svLazyLoadIcons))
        {
            $url = \str_replace('<img ', '<img loading="lazy" ', $url);
        }

        return $url;
    }

    public function fnAvatar($templater, &$escape, $user, $size, $canonical = false, $attributes = [])
    {
        return $this->injectImgLazyAttribute(parent::fnAvatar($templater, $escape, $user, $size, $canonical, $attributes));
    }

    public function fnThreadmarkIndexIcon(\SV\Threadmarks\XF\Template\Templater $templater, &$escape, Entity $content, \SV\Threadmarks\Entity\ThreadmarkIndex $threadmarkIndex, $size = 'l', array $attributes = [])
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->injectImgLazyAttribute(parent::fnThreadmarkIndexIcon($templater, $escape, $content, $threadmarkIndex, $size, $attributes));
    }

    public function fnResourceIcon($templater, &$escape, \XFRM\Entity\ResourceItem $resource, $size = 'm', $href = '')
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->injectImgLazyAttribute(parent::fnResourceIcon($templater, $escape, $resource, $size, $href));
    }
}