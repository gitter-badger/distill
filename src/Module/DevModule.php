<?php


namespace Distill\Module;

use Distill\Application;

class DevModule implements ModuleInterface
{
    public function bootstrapModule(Application $application)
    {
        // if using the php built-in server, and file exists route to it:
        if (php_sapi_name() == 'cli-server'
            && !in_array($_SERVER['REQUEST_URI'], ['/', 'index.php'])
            && file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
            return false;
        }
    }
}
