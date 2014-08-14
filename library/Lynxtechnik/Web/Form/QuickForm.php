<?php

namespace Icinga\Module\Lynxtechnik\Web\Form;

use Icinga\Application\Icinga;
use Icinga\Web\Notification;
use Icinga\Web\Request;
use Icinga\Web\Url;
use Zend_Form;

/**
 * QuickForm wants to be a base class for simple forms
 */
abstract class QuickForm extends Zend_Form
{
    const ID = '__FORM_NAME';

    const CSRF = '__FORM_CSRF';

    /**
     * The name of this form
     */
    protected $formName;

    /**
     * Whether the form has been sent
     */
    protected $hasBeenSent;

    /**
     * Whether the form has been sent
     */
    protected $hasBeenSubmitted;

    /**
     * The submit caption, element - still tbd
     */
    // protected $submit;

    /**
     * Our request
     */
    protected $request;

    protected $successUrl;

    protected $successMessage;

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setMethod('post');
        $this->setAction(Url::fromRequest());
        $this->createIdElement();
        $this->regenerateCsrfToken();
        $this->setup();
    }

    protected function createIdElement()
    {
        $this->detectName();
        $this->addHidden(self::ID, $this->getName());
        $this->getElement(self::ID)->setIgnore(true);
    }

    public function regenerateCsrfToken()
    {
        if (! $element = $this->getElement(self::CSRF)) {
            $this->addHidden(self::CSRF);
            $element = $this->getElement(self::CSRF);
        }
        $element->setValue(CsrfToken::generate())->setIgnore(true);
        return $this;
    }

    public function removeCsrfToken()
    {
        $this->removeElement(self::CSRF);
        return $this;
    }

    public function addHidden($name, $value = null)
    {
        $this->addElement('hidden', $name);
        $this->getElement($name)->setDecorators(array('ViewHelper'));
        if ($value !== null) {
            $this->setDefault($name, $value);
        }
        return $this;
    }

    public function setSuccessUrl($url)
    {
        $this->successUrl = $url;
        return $this;
    }

    public function setup()
    {
    }

    public function setAction($action)
    {
        if (! $action instanceof Url) {
            $action = Url::fromPath($action);
        }
        return parent::setAction((string) $action);
    }

    public function hasBeenSubmitted()
    {
        return $this->hasBeenSent();
    }

    public function handleRequest(Request $request = null)
    {
        if ($request !== null) {
            $this->request = $request;
        }

        if ($this->hasBeenSent()) {
            $post = $this->getRequest()->getPost();
            if ($this->hasBeenSubmitted()) {
                if ($this->isValid($post)) {
                    $this->onSuccess();
                } else {
                    $this->onFailure();
                }
            } else {
                if ($this->isValidPartial($post)) {
                    // Nothing to do, just want to see the errors
                }
            }
        } else {
            // Well...
        }

        return $this;
    }

    public function translate($string)
    {
        // TODO: A module should use it's own domain
        return t($string);
    }

    public function onSuccess()
    {
        $this->redirectOnSuccess();
    }

    public function setSuccessMessage($message)
    {
        $this->successMessage = $message;
        return $this;
    }

    public function getSuccessMessage($message = null)
    {
        if ($message !== null) {
            return $message;
        }
        if ($this->successMessage === null) {
            return t('Form has successfully been sent');
        }
        return $this->successMessage;
    }

    public function redirectOnSuccess($message = null)
    {
        $url = $this->successUrl ?: $this->getAction();
        $this->notifySuccess($this->getSuccessMessage($message));
        $this->redirectAndExit($url);
    }

    public function onFailure()
    {
    }

    public function notifySuccess($message = null)
    {
        if ($message === null) {
            $message = t('Form has successfully been sent');
        }
        Notification::success($message);
        return $this;
    }

    public function notifyError($message)
    {
        Notification::error($message);
        return $this;
    }

    protected function redirectAndExit($url)
    {
        Icinga::app()->getFrontController()->getResponse()->redirectAndExit($url);
    }

    public function getRequest()
    {
        if ($this->request === null) {
            $this->request = Icinga::app()->getFrontController()->getRequest();
        }
        return $this->request;
    }

    public function hasBeenSent()
    {
        if ($this->hasBeenSent === null) {
            $req = $this->getRequest();
            if ($req->isPost()) {
                $post = $req->getPost();
                $this->hasBeenSent = array_key_exists(self::ID, $post) &&
                    $post[self::ID] === $this->getName();
            } else {
                $this->hasBeenSent === false;
            }
        }

        return $this->hasBeenSent;
    }

    protected function detectName()
    {
        if ($this->formName !== null) {
            $this->setName($this->formName);
        } else {
            $this->setName(get_class($this));
        }
    }
}
