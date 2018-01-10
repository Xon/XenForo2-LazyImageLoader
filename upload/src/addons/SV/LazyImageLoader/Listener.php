<?php

namespace SV\LazyImageLoader;

use XF\Mvc\Controller;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View;
use XF\Template\Templater;

class Listener
{
    protected static $lazyLoadPermInit = false;

    public static function templaterTemplatePreRender(Templater $templater, &$type, &$template, array &$params)
    {
        $helper = Helper::instance();
        $params['lzhelper'] = $helper;
        if ($helper->lazyLoading())
        {
            $params['lz_enabled'] = true;
            $templater->includeJs(
                [
                    'addon' => 'SV/LazyImageLoader',
                    'prod'  => 'sv/lazyimageloader/lazysizes.min.js',
                    'dev'   => 'sv/lazyimageloader/lazysizes.js',
                    'min'   => false,
                ]
            );
        }
    }

    public static function templaterMacroPreRender(Templater $templater, &$type, &$template, &$name, array &$arguments, array &$globalVars)
    {
        $globalVars['lzhelper'] = Helper::instance();
    }

    public static function conversationControllerPostDispatch(Controller $controller, $action, ParameterBag $params, AbstractReply &$reply)
    {
        if (!self::$lazyLoadPermInit && $reply instanceof View )
        {
            self::$lazyLoadPermInit = true;

            Helper::instance()->setLazyLoadingEnabledState(\XF::visitor()->hasPermission('conversation', 'sv_lazyload_enable'));
        }
    }

    public static function threadControllerPostDispatch(Controller $controller, $action, ParameterBag $params, AbstractReply &$reply)
    {
        if (!self::$lazyLoadPermInit && $reply instanceof View && $thread = $reply->getParam('thread'))
        {
            self::$lazyLoadPermInit = true;

            Helper::instance()->setLazyLoadingEnabledState(
                \XF::visitor()
                   ->hasNodePermission($thread->node_id, 'sv_lazyload_enable')
            );
        }
    }
}
