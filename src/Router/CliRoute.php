<?php
/**
 * Distill Framework
 * @link http://github.com/pframework
 * @license UNLICENSE http://unlicense.org/UNLICENSE
 * @copyright Public Domain
 * @author Ralph Schindler <ralph@ralphschindler.com>
 */

namespace Distill\Router;

class CliRoute implements RouteInterface
{
    const PART_WORD = 'word';
    const PART_PARAMETER = 'parameter';
    const PART_OPTION = 'option';

    protected $specification = null;
    protected $dispatchable = null;
    protected $parameterDefaults = array();
    protected $parameterValidators = array();

    public function __construct($specification, $dispatchable, array $parameterDefaults = array(), array $parameterValidators = array())
    {
        $this->specification = $specification;
        $this->dispatchable = $dispatchable;
        $this->parameterDefaults = $parameterDefaults;
        $this->parameterValidators = $parameterValidators;
    }

    public function getDispatchable()
    {
        return $this->dispatchable;
    }

    public function match(array $source)
    {
        if (!isset($source['argv'])) {
            return false;
        }

        $specificationParts = $this->parseSpecification($this->specification);

        if (count($specificationParts) == 0) {
            return array();
        }

        $argvParts = $source['argv'];
        $curArgvPart = array_shift($argvParts);

        $parameters = array();

        foreach ($specificationParts as $i => $specPart) {

            switch ($specPart[0]) {

                case self::PART_WORD:
                    if ($specPart[1] != $curArgvPart) {
                        return false;
                    } else {
                        $curArgvPart = array_shift($argvParts);
                        continue;
                    }
                    break;

                case self::PART_PARAMETER:

                    $parameterName = ltrim(rtrim($specPart[1], '?'), ':');
                    $parameters[$parameterName] = null;

                    if ($curArgvPart == '' && substr($specPart[1], -1) != '?') {
                        return false;
                    }

                    $parameters[$parameterName] = $curArgvPart;
                    $curArgvPart = array_shift($argvParts);
                    break;

                case self::PART_OPTION:
                    $optionName = substr($specPart[1], 1);
                    $parameters[$optionName] = array();
                    while ($curArgvPart{0} == '-') {
                        $parameters[$optionName][] = $curArgvPart;
                        $curArgvPart = array_shift($argvParts);
                    }
                    break;

            }

        }

        return $parameters;
    }

    public function assemble($parameters)
    {
        // @todo Implement this
        return '';
    }

    protected function parseSpecification($specification)
    {
        if ($specification == '') {
            return;
        }
        $parts = explode(' ', $specification);
        foreach ($parts as $i => $v) {
            $type = self::PART_WORD;
            if ($v{0} == ':') {
                $type = self::PART_PARAMETER;
            } elseif ($v{0} == '-') {
                $type = self::PART_OPTION;
            }
            $parts[$i] = array($type, $v);
        }
        return $parts;
    }
}
