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
    protected static $lazyLoadPermInit = false;

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
        $helper = Helper::instance();
        $params['lzhelper'] = $helper;
        if ($helper->lazyLoading())
        {
            $params['lz_enabled'] = true;
            $templater->includeJs(
                [
                    'addon' => 'SV/LazyImageLoader',
                    'prod' => 'sv/lazyimageloader/lazysizes.min.js',
                    'dev' => 'sv/lazyimageloader/lazysizes.js',
                    'min' => false,
                ]
            );
        }
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
        if (!self::$lazyLoadPermInit && $reply instanceof View)
        {
            self::$lazyLoadPermInit = true;

            Helper::instance()->setLazyLoadingEnabledState(\XF::visitor()->hasPermission('conversation', 'sv_lazyload_enable'));
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
        if (!self::$lazyLoadPermInit && $reply instanceof View && $thread = $reply->getParam('thread'))
        {
            self::$lazyLoadPermInit = true;

            Helper::instance()->setLazyLoadingEnabledState(\XF::visitor()->hasNodePermission($thread->node_id, 'sv_lazyload_enable'));
        }
    }
}
