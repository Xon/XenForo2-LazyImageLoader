<?php

namespace SV\LazyImageLoader;

use XF\Mvc\Controller;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View;

class Listener
{
    protected static $lazyLoadPermInit = false;


    public static function conversationControllerPostDispatch(Controller $controller, $action, ParameterBag $params, AbstractReply &$reply)
    {
        if (!self::$lazyLoadPermInit)
        {
            self::$lazyLoadPermInit = true;

            Helper::setLazyLoadingEnabledState(\XF::visitor()->hasPermission('conversation', 'sv_lazyload_enable'));
        }
    }

    public static function threadControllerPostDispatch(Controller $controller, $action, ParameterBag $params, AbstractReply &$reply)
    {
        if (!self::$lazyLoadPermInit && $reply instanceof View && $thread = $reply->getParam('thread'))
        {
            self::$lazyLoadPermInit = true;

            Helper::setLazyLoadingEnabledState(
                \XF::visitor()
                   ->hasNodePermission($thread->node_id, 'sv_lazyload_enable')
            );
        }
    }
}
