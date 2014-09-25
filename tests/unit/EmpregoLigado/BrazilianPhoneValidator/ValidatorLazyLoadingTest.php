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

class ValidatorLazyLoadingTest extends PHPUnit_Framework_TestCase
{
    public function testShouldLoadDatasetIfNotSetInConstructor()
    {
        // Resets the static property.
        // TODO: why backupStaticAttributes is not working? Maybe: https://github.com/sebastianbergmann/phpunit/issues/1
        $property = new \ReflectionProperty('EmpregoLigado\BrazilianPhoneValidator\Validator', 'dataset');
        $property->setAccessible(true);
        $property->setValue(null);
        $property->setAccessible(false);

        $validator = new Validator();

        $this->assertTrue($validator->isValidSMP('11998761000'));
        $this->assertTrue($validator->isValidSME('1178901000'));
        $this->assertTrue($validator->isValidSTFC('1123401000'));
        $this->assertTrue($validator->isValidCellphone('11998761000'));
        $this->assertTrue($validator->isValidLandline('1123401000'));
    }
}
