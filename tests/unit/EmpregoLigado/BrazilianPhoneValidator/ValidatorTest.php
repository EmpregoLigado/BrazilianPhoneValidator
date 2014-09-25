<?php

/*
 * This file is part of BrazilianPhoneValidator.
 *
 * (c) Cardinal Tecnologia Ltda.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EmpregoLigado\Tests\BrazilianPhoneValidator;

use EmpregoLigado\BrazilianPhoneValidator\Validator;

use PHPUnit_Framework_TestCase;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    private $validator;

    public function setUp()
    {
        $dataset = array(
            VALIDATOR::TYPE_SMP => array(
                11 => array(
                    99876 => array('0000-9999'),
                    99877 => array('0000-0999', '1999-2999'),
                    99878 => array('8999-9999'),
                    // 99879 => array(),
                ),
                12 => array(
                    9987 => array('0000-9999'),
                )
                // 10 => array(),
            ),
            VALIDATOR::TYPE_SME => array(
                11 => array(
                    7890 => array('0000-9999'),
                ),
            ),
            VALIDATOR::TYPE_STFC => array(
                11 => array(
                    2340 => array('0000-9999'),
                ),
            ),
        );

        $this->validator = new Validator($dataset);
    }

    public function tearDown()
    {
        unset($this->validator);
    }

    public function testIsValidPhone()
    {
        $this->assertTrue($this->validator->isValid('11998761000'));
        $this->assertTrue($this->validator->isValid('11998772000'));
        $this->assertFalse($this->validator->isValid('11998781000'));
        $this->assertFalse($this->validator->isValid('11998791000'));
        $this->assertFalse($this->validator->isValid('11999990000'));
        $this->assertFalse($this->validator->isValid('10999990000'));
    }

    public function testIsValidTenDigitsPhone()
    {
        $this->assertTrue($this->validator->isValid('1299871000'));
    }

    public function testIsValidFormattedPhone()
    {
        $this->assertTrue($this->validator->isValid('(11) 99876-1000'));
        $this->assertTrue($this->validator->isValid('(11) 9-9876-1000'));
        $this->assertTrue($this->validator->isValid('(11) 998.761.000'));
        $this->assertTrue($this->validator->isValid('(12) 9987-1000'));
    }

    /**
     * @dataProvider invalidPhones
     */
    public function testIsValidPhoneShouldThrowInvalidArgumentExceptionIfPhoneIsAnInvalidArgument()
    {
        try {
            $this->validator->isValid(null);

            $this->fail('Validator::isValid() should throw InvalidArgumentException when the $phone is an invalid argument.');
        } catch (\InvalidArgumentException $e) {
        }
    }

    public function invalidPhones()
    {
        return array(
            array(null),
            array('1198765'),
            array('1198765432a'),
        );
    }

    public function testIsValidSMP()
    {
        $this->assertTrue($this->validator->isValidSMP('11998761000'));
        $this->assertFalse($this->validator->isValidSMP('1123451000'));
    }

    public function testIsValidSME()
    {
        $this->assertTrue($this->validator->isValidSME('1178901000'));
        $this->assertFalse($this->validator->isValidSME('1178911000'));
    }

    public function testIsValidSTFC()
    {
        $this->assertTrue($this->validator->isValidSTFC('1123401000'));
        $this->assertFalse($this->validator->isValidSTFC('1123411000'));
    }

    public function testIsValidCellphone()
    {
        $this->assertTrue($this->validator->isValidCellphone('11998761000'));
        $this->assertTrue($this->validator->isValidCellphone('1178901000'));
        $this->assertFalse($this->validator->isValidCellphone('1123401000'));
    }

    public function testIsValidLandline()
    {
        $this->assertTrue($this->validator->isValidLandline('1123401000'));
        $this->assertFalse($this->validator->isValidLandline('11998761000'));
        $this->assertFalse($this->validator->isValidLandline('1178901000'));
    }
}
