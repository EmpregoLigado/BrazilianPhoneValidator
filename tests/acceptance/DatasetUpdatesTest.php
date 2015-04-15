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
            array('(96) 9129-2344'), // AP
            array('(96) 9129-2344'), // AP
            array('(92) 9129-2344'), // AM
            array('(97) 3412-1234'), // AM
            array('(98) 9129-2344'), // MA
            array('(99) 9129-2344'), // MA
            array('(91) 9129-2344'), // PA
            array('(93) 9129-2344'), // PA
            array('(94) 9129-2344'), // PA
            array('(95) 9129-2344'), // RO
        );
    }
}
