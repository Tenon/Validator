<?php
use Tenon\Validator\ValidateRequests;
use PHPUnit\Framework\TestCase;


/**
 * Validator Test
 * Class ValidatorTest
 * @package Bili_Lib\Test\Validator
 */
class ValidatorTest extends TestCase
{
    use ValidateRequests;

    /**
     * test required rule
     */
    public function testRequired()
    {
        list($bPass, $aMessages, $aValidatedParams) = $this->validate([
            'field1' => '',      //should fail
            'field2' => null,    //should fail
            'field3' => true,
            'field4' => false,
            'field5' => 0,
            'field6' => '0',
            'field7' => [],      //should fail
            'field8' => new \StdClass,
        ], [
            'field1,field2,field3,field4,field5,field6,field7,field8' => 'required',
        ],[
            'required' => '{attribute}字段必传',
        ]);
        //期望校验未通过
        $this->assertEquals($bPass, false);
        //期望出错的字段
        $this->assertEquals(array_keys($aMessages), ['field1', 'field2', 'field7']);
        //校验不通过第三个返回参数期望是空数组
        $this->assertEmpty($aValidatedParams);
    }

    /**
     * test json rule
     */
    public function testJson()
    {
        list($bPass, $aMessages, $aValidatedParams) = $this->validate([
            'field1' => "{\"test\":\"123\"}",
            'field2' => "{'test':'123'}",    //should fail
            'field3' => "{\"test\":[1,2,3],\"aaa\":\"{\"a\":\"c\"}\"}", //should fail
        ], [
            'field1,field2,field3' => 'json',
        ]);
        //期望校验未通过
        $this->assertEquals($bPass, false);
        //期望出错的字段
        $this->assertEquals(array_keys($aMessages), ['field2', 'field3']);
        //校验不通过第三个返回参数期望是空数组
        $this->assertEmpty($aValidatedParams);
    }

    /**
     * test ip rule
     */
    public function testIp()
    {
        $params = [
            'field1' => '1.2.3.4',
            'field2' => '10.0.1.20',
            'field3' => '2001:0db8:85a3:08d3:1319:8a2e:0370:7344', //ipv6
        ];
        list($bPass, $aMessages, $aValidatedParams) = $this->validate($params, [
            'field1,field2,field3' => 'ip',
        ]);
        $this->assertEquals($bPass, true);
        $this->assertEmpty($aMessages);
        $this->assertEquals($params, $aValidatedParams);
    }

    /**
     * test same rule
     */
    public function testSame()
    {
        $params = [
            'field1' => '123',
            'field2' => 123,
            'field3' => [],
            'field4' => false,
            'field5' => '',
        ];
        list($bPass, $aMessages, $aValidatedParams) = $this->validate($params, [
            'field2'        => 'same:field1',  //should fail
            'field3,field4' => 'same:field5',  //should fail
            'field4,field5' => 'same:field3',  //should fail
            'field3,field5' => 'same:field4',  //should fail
        ], [
            'same' => '{attribute}字段与{data}不一致'
        ]);
        $this->assertEquals($bPass, false);
        //所有的same校验都没有通过
        $this->assertEquals(array_keys($aMessages), ['field2', 'field3', 'field4', 'field5']);
        $this->assertEmpty($aValidatedParams);
    }

    /**
     * test email rule
     */
    public function testEmail()
    {
        $params = [
            'field1' => 'test-admin@a.b.c.d',
            'field2' => '_-hello.abc@world-shit.cn',
            'field3' => 'hello~2000@126aa.com',
            'field4' => 'hello-_-world@haha+1s.com',  //email's right part cannot contain: ' " ! # $ % ^ & * ( ) + , should fail
        ];
        list($bPass, $aMessages, $aValidatedParams) = $this->validate($params, [
            'field1,field2,field3,field4' => 'email',
        ], [
            'email' => '邮箱格式不正确'
        ]);
        $this->assertEquals($bPass, false);
        $this->assertEquals(array_keys($aMessages), ['field4']);
        $this->assertEmpty($aValidatedParams);
    }

    /**
     * test mobile rule
     */
    public function testMobile()
    {
        $params = [
            'field1' => '14700101234',
            'field2' => '20012345678', //mobile cannot start with 200, should fail
        ];
        list($bPass, $aMessages, $aValidatedParams) = $this->validate($params, [
            'field1,field2' => 'mobile',
        ], [
            'mobile' => '手机格式不正确'
        ]);
        $this->assertEquals($bPass, false);
        $this->assertEquals(array_keys($aMessages), ['field2']);
        $this->assertEmpty($aValidatedParams);
    }

    /**
     * test size rule
     */
    public function testSize()
    {
        $params = [
            'field1' => 123,
            'field2' => '123',
            'field3' => ['a', 'b'],
            'field4' => '测试utf-8',
        ];
        list($bPass, $aMessages, $aValidatedParams) = $this->validate($params, [
            'field1' => 'size:123',
            'field2' => 'size:3',
            'field3' => 'size:2',
            'field4' => 'size:7'
        ]);
        $this->assertEquals($bPass, true);
        $this->assertEmpty($aMessages);
        $this->assertEquals($aValidatedParams, $params);
    }
}
