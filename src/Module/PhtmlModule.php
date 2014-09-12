<?php


namespace Distill\Module;

use Distill\Application;

class PhtmlModule implements ModuleInterface
{
    protected $namespaces = array();

    public function registerNamespace($namespace, $includePath = null)
    {
        $this->namespaces[$namespace] = $includePath;
        return $this;
    }

    public function bootstrapModule(Application $application)
    {
        $application->on('Application.PostDispatch', [$this, 'handlePhtmlView']);
    }

    public function handlePhtmlView($dispatchable, $return)
    {
        if (is_array($dispatchable) && is_callable($dispatchable)) {
            $r = new \ReflectionObject($dispatchable[0]);
        } else {
            $r = ($dispatchable instanceof \Closure) ? (new \ReflectionFunction($dispatchable)) : (new \ReflectionObject($dispatchable));
        }

        $dispatchableNamespace = $r->getNamespaceName();
        unset($r);

        $match = 0;
        foreach ($this->namespaces as $ns => $includePath) {
            if (strpos($dispatchableNamespace, $ns) === 0) {
                $match = 1;
                break;
            }
        }
        if (!$match || !isset($return['view_script'])) {
            return; // nothing to do
        }

        $oldIncludePath = set_include_path($includePath);
        $script = $return['view_script'];
        unset($return['view_script']);
        $this->render($script, $return);
        set_include_path($oldIncludePath);
    }

    public function render()
    {
        extract(func_get_arg(1));
        include func_get_arg(0);
    }
}

 