<?php

use EmpregoLigado\BrazilianPhoneValidator\Validator;

class DatasetUpdatesTest extends \PHPUnit_Framework_TestCase
{
    private $validator;

    public function setUp()
    {
        $this->validator = new Validator;
    }

    /**
     * @dataProvider provideValidNumbers
     */
    public function testValidPhoneNumber($validNumber)
    {
        $this->assertTrue(
            $this->validator->isValid($validNumber),
            sprintf('Number "%s" should be valid.', $validNumber)
        );
    }

    public static function provideValidNumbers()
    {
        return array(
            array('(11) 99129-2344'),
            array('(11) 93013-6165'),
            array('(11) 94567-9158'),
            array('(96) 99129-2344'), // AP
            array('(92) 99129-2344'), // AM
            array('(97) 3412-1234'), // AM
            array('(98) 99129-2344'), // MA
            array('(99) 99129-2344'), // MA
            array('(91) 99129-2344'), // PA
            array('(93) 99129-2344'), // PA
            array('(94) 99129-2344'), // PA
            array('(95) 99129-2344'), // RO
        );
    }
}
