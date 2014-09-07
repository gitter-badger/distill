<?php
/**
 * Distill Framework
 * @link http://github.com/pframework
 * @license UNLICENSE http://unlicense.org/UNLICENSE
 * @copyright Public Domain
 * @author Ralph Schindler <ralph@ralphschindler.com>
 */

namespace Distill\Router;

interface RouteInterface
{
    public function getDispatchable();
    public function match(array $source);
    public function assemble($parameters);
}
