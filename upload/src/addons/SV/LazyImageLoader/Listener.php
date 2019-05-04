<?php

namespace SV\LazyImageLoader;

use XF\Mvc\Controller;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View;
use XF\Template\Templater;

/**
 * Class Listener
 *
 * @package SV\LazyImageLoader
 */
class Listener
{
    public static function templaterSetup(/** @noinspection PhpUnusedParameterInspection */ \XF\Container $container, \XF\Template\Templater &$templater)
    {
        $helper = Helper::instance();
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

    /**
     * @param Templater $templater
     * @param string    $type
     * @param string    $template
     * @param array     $params
     *
     * @throws \Exception
     */
    public static function templaterTemplatePreRender(/** @noinspection PhpUnusedParameterInspection */Templater $templater, &$type, &$template, array &$params)
    {
        $params['lzhelper'] = Helper::instance();
        if (!isset($params['__globals']))
        {
            // kinda silly
            $params['__globals'] = $params;
        }
    }

    /**
     * @param Templater $templater
     * @param string    $type
     * @param string    $template
     * @param string    $name
     * @param array     $arguments
     * @param array     $globalVars
     *
     * @throws \Exception
     */
    public static function templaterMacroPreRender(/** @noinspection PhpUnusedParameterInspection */Templater $templater, &$type, &$template, &$name, array &$arguments, array &$globalVars)
    {
        $globalVars['lzhelper'] = Helper::instance();
    }

    /**
     * @param Controller    $controller
     * @param string        $action
     * @param ParameterBag  $params
     * @param AbstractReply $reply
     *
     * @throws \Exception
     */
    public static function conversationControllerPostDispatch(/** @noinspection PhpUnusedParameterInspection */Controller $controller, $action, ParameterBag $params, /** @noinspection ReferencingObjectsInspection */AbstractReply &$reply)
    {
        if ($reply instanceof View)
        {
            Helper::instance()->setLazyLoadingEnabledState((int)\XF::visitor()->hasPermission('conversation', 'sv_lazyload_enable'));
        }
    }

    /**
     * @param Controller    $controller
     * @param string        $action
     * @param ParameterBag  $params
     * @param AbstractReply $reply
     *
     * @throws \Exception
     */
    public static function threadControllerPostDispatch(/** @noinspection PhpUnusedParameterInspection */Controller $controller, $action, ParameterBag $params, /** @noinspection ReferencingObjectsInspection */AbstractReply &$reply)
    {
        if ($reply instanceof View && $thread = $reply->getParam('thread'))
        {
            Helper::instance()->setLazyLoadingEnabledState((int)\XF::visitor()->hasNodePermission($thread->node_id, 'sv_lazyload_enable'));
        }
    }
}
