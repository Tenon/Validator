<?php
namespace Tenon\Validator;

class ValidateRule
{
    /**
     * @var string
     */
    private $attribute;

    /**
     * string
     * @var
     */
    private $rule;

    /**
     * @var mixed(string|integer)
     */
    private $ruleData;

    public function __construct($attribute, $rule)
    {
        $this->attribute = $attribute;

        $this->setRule($rule);
    }

    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            return null;
        }
        return $this->$name;
    }

    protected function setRule($rule)
    {
        if (is_string($rule)) {
            $tmp = explode(':', $rule);
            if (count($tmp) > 1) {
                $this->ruleData = $tmp[1];
            }
            $this->rule = $tmp[0];
        } elseif (is_array($rule)) {
            $this->rule = key($rule);
            $this->ruleData = $rule[$this->rule];
        }
    }
}