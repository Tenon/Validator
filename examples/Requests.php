<?php
require('init.php');
use Tenon\Validator\ValidateRequests;

class Requests
{
    use ValidateRequests;

    public function register(array $params)
    {
        list($bPass, $aMessages, $aValidatedParams) = $this->validate($params, [
            'username,password' => 'required|string',
            'email'             => 'required|email',
            'confirm_password'  => 'required|same:password',
            'mobile'            => 'required|mobile',
            'invite_code'       => 'integer|max:9999',
            'vcode'             => 'in:1,2,3,4',
        ], [
            'required'              => '{attribute}不能为空',  //通用定义校验结果，如果没有这里的定义，会返回更通用的诸如xxx is required.
            'mobile.required'       => '手机号必填',  //对某字段定制校验结果
            'mobile.mobile'         => '手机号格式不正确',
            'email.email'           => '邮箱格式不正确',
            'confirm_password.same' => '两次密码不一致',
            'invite_code.max'       => '邀请码不正确',
            'vcode.in'              => 'vcode不正确',
        ]);
        var_dump($bPass, $aMessages, $aValidatedParams);
        // do somethings...
    }
}

$request = new Requests();

$request->register([
   'username' => 'bilibili',
    'email' => '2233@bilibili.com',
    'password' => 'bilibili2233',
    'confirm_password' => 'bili2233',
    'mobile' => '13122333322',
    'invite_code' => 22333,
    'vcode' => 5,
]);

