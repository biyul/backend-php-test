<?php

namespace AskNicely\Util;

/**
 * Helper class for adding messages to the flash bag.
 * @package AskNicely\Util
 */
class FlashMessage
{
    public static function success($app, $message) {
        $app['session']->getFlashBag()->add('success', $message);
    }

    public static function danger($app, $message) {
        $app['session']->getFlashBag()->add('danger', $message);
    }
}