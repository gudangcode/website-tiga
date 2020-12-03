<?php

/**
 * Automation class.
 *
 * Model for automation elements
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

class AutomationElement
{
    protected $data;

    /**
     * Constructor.
     *
     * @return the associated automation2
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get options.
     *
     * @return string
     */
    public function getOption($name)
    {
        if (isset($this->data) && isset($this->data->options) && isset($this->data->options->$name)) {
            return $this->data->options->$name;
        }

        return;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function get($name)
    {
        if (isset($this->data) && isset($this->data->$name)) {
            return $this->data->$name;
        }

        return;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getName()
    {
        switch ($this->get('type')) {
            case 'ElementTrigger':
                return trans('messages.automation.trigger.title', [
                    'title' => trans('messages.automation.trigger.'.$this->getOption('key')),
                ]);
                break;
            case 'ElementAction':
                $email = Email::findByUid($this->getOption('email_uid'));
                if ($email) {
                    return trans('messages.automation.send_a_email', ['title' => $email->subject]);
                } else {
                    return trans('messages.automation.no_email');
                }

                break;
            case 'ElementWait':
                return trans('messages.automation.wait.delay.'.$this->getOption('time'));
                break;
            case 'ElementCondition':
                if ($this->getOption('type') == 'open') {
                    return trans('messages.automation.action.condition.read_email.title');
                } elseif ($this->getOption('type') == 'click') {
                    return trans('messages.automation.action.condition.click_link.title');
                }
                break;
            default:
        }
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getIcon()
    {
        return self::getIconByType($this->get('type'));
    }

    /**
     * Get value.
     *
     * @return string
     */
    public static function getIconByType($type)
    {
        switch ($type) {
            case 'ElementTrigger':
                return '<i class="lnr lnr-exit-up bg-success"></i>';
                break;
            case 'ElementAction':
                return '<i class="lnr lnr-envelope bg-primary"></i>';
                break;
            case 'ElementWait':
                return '<i class="lnr lnr-clock bg-secondary"></i>';
                break;
            case 'ElementCondition':
                return '<i class="material-icons bg-warning">call_split</i>';
                break;
            default:
        }
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getIconWithoutBg($class = '')
    {
        switch ($this->get('type')) {
            case 'ElementTrigger':
                return '<i class="lnr lnr-exit-up '.$class.'"></i>';
                break;
            case 'ElementAction':
                return '<i class="lnr lnr-envelope '.$class.'"></i>';
                break;
            case 'ElementWait':
                return '<i class="lnr lnr-clock '.$class.'"></i>';
                break;
            case 'ElementCondition':
                return '<i class="material-icons '.$class.'">call_split</i>';
                break;
            default:
        }
    }
}
