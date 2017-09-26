<?php
namespace Tenon\Validator;

trait ValidateRequests
{
    /**
     * @param array $params
     * @param array $rules
     * @param array $translates
     * @return array [pass:bool, messages:array, params:[]]
     */
    public function validate(array $params, array $rules, array $translates = []): array
    {
        $validator = new Validator($params, $rules, $translates);
        $retParams = [];
        $pass = $validator->passes();
        if ($pass) {
            $retParams = array_get($params, array_keys($validator->getExplodedRules()), []);
        }
        return [$pass, $validator->getValidateMessages(), $retParams];
    }
}