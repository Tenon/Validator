<?php
namespace Tenon\Validator;

use Tenon\Validator\Contracts\ValidatorContract;


class Validator implements ValidatorContract
{
    /**
     * params
     * @var array
     */
    private $params;

    /**
     * rules
     * @var array
     */
    private $rules;

    /**
     * self-define translates
     * @var array
     */
    private $translates;

    /**
     * validated messages
     * @var array
     */
    private $messages = [];

    public function __construct(array $params, array $rules, array $translates = [])
    {
        $this->params     = $params;
        $this->rules      = $this->setRules($rules);
        $this->translates = $translates;
    }

    public function getExplodedRules(): array
    {
        return $this->rules;
    }

    /**
     * init rules
     * @param array $rules
     * @return array
     */
    protected function setRules(array $rules)
    {
        $explodedRules = [];
        foreach ($rules as $attributes => $rule)
        {
            $attributes = explode(',', $attributes);
            $rule = (is_string($rule)) ? explode('|', $rule) : [];
            foreach ($attributes as $attribute) {
                foreach ($rule as $r) {
                    $explodedRules[$attribute][] = new ValidateRule($attribute, $r);
                }
            }
        }
        return $explodedRules;
    }

    /**
     * check pass validation or not
     * @return bool
     */
    public function passes(): bool
    {
        foreach ($this->getExplodedRules() as $attribute => $rules)
        {
            foreach ($rules as $rule) {
                $this->check($rule);
            }
        }
        return count($this->getValidateMessages()) === 0;
    }

    /**
     * return validated result messages
     * @return array
     */
    public function getValidateMessages(): array
    {
        return $this->messages;
    }

    /**
     * check single rule
     * @param $rule
     * @return void
     */
    protected function check(ValidateRule &$rule)
    {
        if (!$rule->rule || !$rule->attribute) return;

        $value = $this->getValue($rule->attribute);

        $method = $this->getMethod($rule->rule);

        $isValidatable = $this->isValidatable($rule->attribute, $rule->rule, $value, $method);

        if ($isValidatable && !$this->$method($rule, $value)) {
            $this->addFailMessage($rule);
        }
    }

    protected function getValue($attribute)
    {
        return array_get($this->params, $attribute, null);
    }

    protected function getMethod($rule)
    {
        return "validate" . ucwords($rule);
    }

    /**
     * if value of one attribute is null and this attribute has no rule as 'required' then ignore this check
     * @param string $attribute
     * @param string $rule
     * @param mixed $value
     * @param string $method
     * @return bool
     */
    protected function isValidatable($attribute, $rule, $value, $method)
    {
        // validate rule not exist, append fail message
        if (!method_exists($this, $method)) {
            $this->appendMessage($attribute, sprintf("%s's validate rule: %s not exist.", $attribute, $rule));
            return false;
        }
        // validate rule exist, check value is empty or not
        return !(is_null($value) && !$this->hasRule($attribute, 'required'));
    }

    protected function addFailMessage(ValidateRule &$rule)
    {
        $message = $this->getTranslatedMessage($rule);
        $this->appendMessage($rule->attribute, $message);
    }

    protected function appendMessage($attribute, $message)
    {
        if ($message) {
            $this->messages[$attribute][] = $message;
        }
    }

    protected function getTranslatedMessage(ValidateRule &$rule)
    {
        $keys = [$rule->rule, $rule->attribute . '.' . $rule->rule];
        $message = '';
        foreach ($keys as $key) {
            $message = $this->translates[$key] ?? $message;
        }
        $message = $message ? $message : "{$rule->attribute}'s value does not match the rule: {$rule->rule}";
        return str_replace(['{attribute}', '{rule}', '{data}'], [$rule->attribute, $rule->rule, $rule->ruleData], $message);
    }

    protected function hasRule($attribute, $rule)
    {
        $rules = $this->rules[$attribute] ?? [];
        foreach ($rules as $r) {
            if ($r->rule == $rule) {
                return true;
            }
        }
        return false;
    }

    protected function replaceTranslate($attribute, $rule, $translate)
    {
        $this->translates[$attribute . '.' . $rule] = $translate;
    }

    /**
     * validate required
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateRequired(ValidateRule &$rule, &$value): bool
    {
        if (is_null($value)) {
            return false;
        }
        elseif (is_string($value) && trim($value) === '') {
            return false;
        }
        elseif (is_array($value) && count($value) < 1) {
            return false;
        }
        return true;
    }

    /**
     * validate is string
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateString(ValidateRule &$rule, &$value): bool
    {
        return is_string($value);
    }

    /**
     * validate is integer
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateInteger(ValidateRule &$rule, &$value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * validate is array
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateArray(ValidateRule &$rule, &$value): bool
    {
        return is_array($value);
    }

    /**
     * validate is float
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateFloat(ValidateRule &$rule, &$value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * validate is boolean
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateBoolean(ValidateRule &$rule, &$value): bool
    {
        return is_bool($value);
    }

    /**
     * validate is json
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateJson(ValidateRule &$rule, &$value): bool
    {
        try {
            json_decode($value);
        } catch (\Exception $e) {
            return false;
        }
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * validate is ip
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateIp(ValidateRule &$rule, &$value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * validate value is same as another attribute's
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateSame(ValidateRule &$rule, &$value): bool
    {
        return $rule->ruleData && ($value === $this->getValue($rule->ruleData));
    }

    /**
     * validate is a valid email format
     * @param Validator $rule
     * @param $value
     * @return bool
     */
    protected function validateEmail(ValidateRule &$rule, &$value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * validate is a valid mobile
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateMobile(ValidateRule &$rule, &$value): bool
    {
        return preg_match('/^1[3-9][0-9]\d{8}$/', $value) > 0;
    }

    /**
     * validate value's size
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateSize(ValidateRule &$rule, &$value): bool
    {
        if (is_null($rule->ruleData)) {
            $this->replaceTranslate($rule->attribute, $rule->rule, "'size' validation must have a suffix value.");
            return false;
        }
        return $this->getSize($rule->attribute, $value) == $rule->ruleData;
    }

    /**
     * validate integer min range
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateMin(ValidateRule &$rule, &$value): bool
    {
        if (is_null($rule->ruleData)) {
            $this->replaceTranslate($rule->attribute, $rule->rule, "'min' validation must have a suffix value.");
            return false;
        }
        return $this->getSize($rule->attribute, $value) >= $rule->ruleData;
    }

    /**
     * validate integer max range
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateMax(ValidateRule &$rule, &$value): bool
    {
        if (is_null($rule->ruleData)) {
            $this->replaceTranslate($rule->attribute, $rule->rule, "'max' validation must have a suffix value.");
            return false;
        }
        return $this->getSize($rule->attribute, $value) <= $rule->ruleData;
    }

    /**
     * validate value is in expected list
     * @param ValidateRule $rule
     * @param $value
     * @return bool
     */
    protected function validateIn(ValidateRule &$rule, &$value): bool
    {
        try {
            $pass = in_array($value, explode(',', $rule->ruleData));
        } catch (\Exception $e) {
            $pass = false;
        }
        return $pass;
    }

    /**
     * if integer, return value self
     * if array,
     * @param $attribute
     * @param $value
     * @return int|mixed
     */
    protected function getSize($attribute, $value)
    {
        if (is_integer($value))
        {
            return array_get($this->params, $attribute);
        }
        elseif (is_array($value))
        {
            return count($value);
        }
        return mb_strlen($value);
    }
}