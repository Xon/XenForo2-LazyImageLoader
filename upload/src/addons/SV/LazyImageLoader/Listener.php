<?php
/**
 * @noinspection PhpUnusedParameterInspection
 */

namespace SV\LazyImageLoader;

use XF\Container;
use XF\Mvc\Controller;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View;
use XF\Template\Templater;

abstract class Listener
{
    public static function templaterSetup(Container $container, Templater &$templater): void
    {
        $helper = Helper::instance();
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        if ($templater instanceof \XF\Mail\Templater)
        {
            // do not enable lazy loading for emails
        }
        else
        {
            $helper->allowEnabling($templater);
        }

        $templater->addDefaultParam('lzhelper', $helper);
    }

    public static function templaterTemplatePreRender(Templater $templater, string &$type, string &$template, array &$params): void
    {
        $params['lzhelper'] = Helper::instance();
        if (!isset($params['__globals']))
        {
            // kinda silly
            $params['__globals'] = $params;
        }
    }

    public static function templaterMacroPreRender(Templater $templater, string &$type, string &$template, string &$name, array &$arguments, array &$globalVars): void
    {
        $globalVars['lzhelper'] = Helper::instance();
    }

    public static function conversationControllerPostDispatch(Controller $controller, string $action, ParameterBag $params, AbstractReply &$reply): void
    {
        if ($reply instanceof View)
        {
            Helper::instance()->setLazyLoadingEnabledState((bool)\XF::visitor()->hasPermission('conversation', 'sv_lazyload_enable'));
        }
    }

    public static function threadControllerPostDispatch(Controller $controller, string $action, ParameterBag $params, AbstractReply &$reply): void
    {
        if ($reply instanceof View && $thread = $reply->getParam('thread'))
        {
            Helper::instance()->setLazyLoadingEnabledState((bool)\XF::visitor()->hasNodePermission($thread->node_id, 'sv_lazyload_enable'));
        }
    }
}
