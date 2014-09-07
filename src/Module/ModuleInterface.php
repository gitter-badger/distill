<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/9/14
 * Time: 4:37 PM
 */

namespace Distill\Module;

use Distill\Application;

interface ModuleInterface
{
    public function bootstrapModule(Application $application);
}