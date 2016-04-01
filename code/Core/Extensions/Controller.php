<?php namespace Milkyway\SS\Core\Extensions;

/**
 * Milkyway Multimedia
 * Controller.php
 *
 * @package milkyway-multimedia/ss-mwm-core
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Extension;

class Controller extends Extension
{
    /**
     * Find a back link for current controller
     * @param string $fallback
     * @return string
     */
    public function getBackLink($fallback = '')
    {
        $url = '';

        if ($this->owner->Request) {
            if ($this->owner->Request->requestVar('BackURL')) {
                $url = $this->owner->Request->requestVar('BackURL');
            } else {
                if ($this->owner->Request->isAjax() && $this->owner->Request->getHeader('X-Backurl')) {
                    $url = $this->owner->Request->getHeader('X-Backurl');
                } else {
                    if ($this->owner->Request->getHeader('Referer')) {
                        $url = $this->owner->Request->getHeader('Referer');
                    }
                }
            }
        }

        if (!$url) {
            $url = $fallback ? $fallback : singleton('director')->baseURL();
        }

        return $url;
    }

    /**
     * Display a controller using a Page view
     *
     * @param null $controller
     * @param string $url
     * @param string $action
     * @return @Controller
     */
    public function displayNiceView($controller = null, $url = '', $action = '')
    {
        if (!$controller) {
            $controller = $this->owner;
        }

        return singleton('director')->create_view($controller, $url, $action);
    }

    /**
     * Respond to a form view ajax or redirect
     * @param array $params
     * @param \Form $form
     * @param string $redirect
     * @return \SS_HTTPResponse|null
     */
    public function respondToFormAppropriately(array $params, $form = null, $redirect = '')
    {
        if ($redirect && !isset($params['redirect'])) {
            $params['redirect'] = $redirect;
        }

        if ($this->owner->Request->isAjax()) {
            if (!isset($params['code'])) {
                $params['code'] = 200;
            }
            if (!isset($params['code'])) {
                $params['status'] = 'success';
            }

            return singleton('director')->ajax_response($params, $params['code'], $params['status']);
        } else {
            if (isset($params['redirect'])) {
                $this->owner->redirect($params['redirect']);
            }

            if ($form && isset($params['message'])) {
                $form->sessionMessage($params['message'], 'good');
            }

            if (!$this->owner->redirectedTo()) {
                $this->owner->redirectBack();
            }
        }
    }
} 