<?php
/**
 * Distill Framework
 * @link http://github.com/pframework
 * @license UNLICENSE http://unlicense.org/UNLICENSE
 * @copyright Public Domain
 * @author Ralph Schindler <ralph@ralphschindler.com>
 */

namespace Distill\Callback;

class CallbackCollection extends \SplPriorityQueue
{
    /** @var \Distill\Callback\CallbackContext */
    protected $callbackContext;

    public function __construct(CallbackContext $callbackContext = null)
    {
        $this->callbackContext = ($callbackContext) ?: new CallbackContext();
    }

    public function getCallbackContext()
    {
        return $this->callbackContext;
    }

    public function setCallbackContext($context)
    {
        $this->callbackContext = $context;
        return $this;
    }

    public function remove($callback)
    {
        $os = array();
        $flags = $this->setExtractFlags(self::EXTR_BOTH);
        foreach ($this as $o) {
            if ($callback === $o['data']) {
                continue;
            }
            $os[] = $o;
        }
        foreach ($os as $o) {
            $this->insert($o['data'], $o['priority']);
        }
        $this->setExtractFlags($flags);
    }

}
