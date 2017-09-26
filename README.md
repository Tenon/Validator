### Validator

#### 使用介绍

在controller中引入```Bili_lib\Validator\ValidateRequests```这个trait，可以进行对参数按一定提供的规则进行校验，如下形式使用：

```php

class Controller
{
    use ValidateRequests;

    public function requests(array $aHeader, array $aBody, $aHttp = [])
    {
        $rules = [
            'field1' => 'required|integer',
            'filed2' => 'required|string',
            'filed3' => 'integer|max:1000',
        ];
        $translates = [
            'required'       => '{attribute}不能为空', // message will be: [field1|field2]不能为空
            'field1.integer' => 'field1必须是一个整数',
            'field2.string'  => 'field2必须是一个字符串',
            'field3.max'     => 'field3值过大',
        ];
        list($bPass, $aMessages, $aValidatedParams) = $this->validate($aBody, $rules, $translates);
    }
}


/**

参数eg:
    //传入的参数数组
    $params
    //定义的校验规则
    [
        'field1' => 'required|integer',
        'filed2' => 'required|string',
        'filed3' => 'integer|max:1000',
    ]
    //自定义的校验返回消息
    [
        'required'       => '{attribute}不能为空', // message will be: [field1|field2]不能为空
        'field1.integer' => 'field1必须是一个整数',
        'field2.string'  => 'field2必须是一个字符串',
        'field3.max'     => 'field3值过大',
    ]

return eg:
    //是否校验通过
    $bPass [boolean]
    //校验返回的validation message
    $aMessages [array]: ['field1' => ['xxxx', 'xxx'], 'field2' => ['xxxx']]
    //校验后的参数；校验未通过为空，通过则返回你"关心"的参数，即定义了规则的那些key
    $aValidatedParams [array]: ['field1' => xxx, 'field2' => xxx, 'field3' => xxx]

*/
```

#### 既定规则

    + required

    + string

    + integer

    + array

    + float

    + boolean

    + json

    + ip

    + same
        与某个字段值保持一致
        eg: [
                'field1' => 'required|integer',
                'field2' => 'required|same:field1',
            ]

    + email

    + mobile

    + size
        to integer: return integer self
        to array: return count(array)
        to string: return mb_string length

    + min
        compare with size
        to string: 比较字符串长度
        to integer: 比较数字大小
        to array: 比较数组个数
        eg: [
                'field1' => 'string|min:2',
                'field2' => 'integer|min:2',
            ]

    + max
        compare with size
        to string: 比较字符串长度
        to integer: 比较数字大小
        to array: 比较数组个数
        eg: [
                'field1' => 'string|max:10',
                'field2' => 'integer|max:9999',
            ]

    + in
        判断参数是否出现在in:xx,xx,xx的列表中