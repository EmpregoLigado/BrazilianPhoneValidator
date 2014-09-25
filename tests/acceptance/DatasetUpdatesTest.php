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
            array('(11) 99129-6854')
        );
    }
}
