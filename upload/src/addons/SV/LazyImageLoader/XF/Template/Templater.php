<?php

namespace SV\LazyImageLoader\XF\Template;

use XF\Mvc\Entity\Entity;

/**
 * Extends \XF\Template\Templater
 */
class Templater extends XFCP_Templater
{
    protected function injectLazyAttributes(&$attributes)
    {
        if (is_array($attributes) && empty($attributes['loading']) && !empty(\XF::options()->svLazyLoadIcons))
        {
            $attributes['loading'] = 'lazy';
        }

        return $attributes;
    }

    public function fnAvatar($templater, &$escape, $user, $size, $canonical = false, $attributes = [])
    {
        return parent::fnAvatar($templater, $escape, $user, $size, $canonical, $this->injectLazyAttributes($attributes));
    }

    public function fnThreadmarkIndexIcon(\SV\Threadmarks\XF\Template\Templater $templater, &$escape, Entity $content, \SV\Threadmarks\Entity\ThreadmarkIndex $threadmarkIndex, $size = 'l', array $attributes = [])
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return parent::fnThreadmarkIndexIcon($templater, $escape, $content, $threadmarkIndex, $size, $this->injectLazyAttributes($attributes));
    }
}