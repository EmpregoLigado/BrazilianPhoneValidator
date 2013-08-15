<?php

/*
 * This file is part of BrazilianPhoneValidator.
 *
 * (c) Cardinal Tecnologia Ltda.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require_once __DIR__.'/../src/EmpregoLigado/BrazilianPhoneValidator/Validator.php';

function create_tick($yes = true)
{
    return '[' . ($yes ? 'X' : ' ') . ']';
}

try {
    $phoneValidator = new EmpregoLigado\BrazilianPhoneValidator\Validator();

    if ($argc > 1) {
        $phone = $argv[1];
    } else {
        echo 'Enter the phone number you want to validate: ';
        $phone = chop(fgets(STDIN));
    }

    $result = $phoneValidator->isValid($phone);
    echo 'Results:' . PHP_EOL . PHP_EOL;
    echo create_tick($result) . ' Is a valid Brazilian phone? ' . PHP_EOL;

    $result = $phoneValidator->isValidLandline($phone);
    echo create_tick($result) . ' Is a valid landline phone?  ' . PHP_EOL;

    $result = $phoneValidator->isValidCellphone($phone);
    echo create_tick($result) . ' Is a valid cellphone?       ' . PHP_EOL;

    $result = $phoneValidator->isValidSMP($phone);
    echo create_tick($result) . ' Is a valid SMP?             ' . PHP_EOL;

    $result = $phoneValidator->isValidSME($phone);
    echo create_tick($result) . ' Is a valid SME?             ' . PHP_EOL;

    $result = $phoneValidator->isValidSTFC($phone);
    echo create_tick($result) . ' Is a valid STFC?            ' . PHP_EOL;

    echo PHP_EOL;

    exit(0);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
