<?php

/*
 * This file is part of BrazilianPhoneValidator.
 *
 * (c) Cardinal Tecnologia Ltda.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EmpregoLigado\BrazilianPhoneValidator;

class Validator
{
    const TYPE_SMP = 'smp';
    const TYPE_SME = 'sme';
    const TYPE_STFC = 'stfc';

    private static $dataset;

    public function __construct(array $dataset = array())
    {
        if (count($dataset)) {
            self::$dataset = $dataset;
        }
    }

    public function isValid($phone)
    {
        return $this->isValidSMP($phone) || $this->isValidSTFC($phone) || $this->isValidSME($phone);
    }

    public function isValidCellphone($phone)
    {
        return $this->isValidSMP($phone) || $this->isValidSME($phone);
    }

    public function isValidLandline($phone)
    {
        return $this->isValidSTFC($phone);
    }

    public function isValidSME($phone)
    {
        return $this->isValidRange($phone, self::TYPE_SME);
    }

    public function isValidSMP($phone)
    {
        return $this->isValidRange($phone, self::TYPE_SMP);
    }

    public function isValidSTFC($phone)
    {
        return $this->isValidRange($phone, self::TYPE_STFC);
    }

    private function isValidRange($phone, $type)
    {
        $phone = trim(preg_replace('/[^0-9]/', '', $phone));
        $phoneLength = strlen($phone);

        if (!is_numeric($phone) || $phoneLength > 11 || $phoneLength < 10) {
            throw new \InvalidArgumentException('The phone value must be a string with 10 or 11 characters length.');
        }

        $prefixEnd = $phoneLength === 10 ? 4 : 5;
        $rangeStart = $phoneLength === 10 ? 6 : 7;

        $areaCode = substr($phone, 0, 2);
        $prefix = substr($phone, 2, $prefixEnd);
        $phoneRange = substr($phone, $rangeStart);

        if (!isset(self::$dataset[$type])) {
            $this->loadDataset($type);
        }

        if (!isset(self::$dataset[$type][$areaCode][$prefix])) {
            return false;
        }

        foreach (self::$dataset[$type][$areaCode][$prefix] as $range) {
            list($initial, $final) = explode('-', $range);

            if ($phoneRange >= $initial && $phoneRange <= $final) {
                return true;
            }
        }

        return false;
    }

    private function loadDataset($type)
    {
        self::$dataset[$type] = require __DIR__.'/../../../data/dataset.'.$type.'.php';
    }
}
