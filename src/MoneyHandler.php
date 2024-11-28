<?php 

namespace GlpiPlugin\Tender;

require_once __DIR__ . '/../vendor/autoload.php';

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlLocalizedDecimalFormatter;
use Money\Parser\IntlLocalizedDecimalParser;
use Money\Money;

class MoneyHandler {

    public static function formatToString(int|Money $value) {

        global $CFG_GLPI;

        switch($CFG_GLPI['number_format']) {
            case 2:
                $locale = 'de_DE';
                break;
            default:
                $locale = 'en_GB';
        }
 
        $value = gettype($value) == 'integer' ? new Money($value, new Currency('EUR')) : $value;
        $currencies = new ISOCurrencies();

        $numberFormatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $moneyFormatter = new IntlLocalizedDecimalFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($value);

    }

    public static function parseFromFloat(string $value) {

        $locale = 'en_GB';
 
        $currencies = new ISOCurrencies();

        $numberFormatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $moneyParser = new IntlLocalizedDecimalParser($numberFormatter, $currencies);

        return $moneyParser->parse($value, new Currency('EUR'));

    }

    public static function parseFromString(string $value) {

        global $CFG_GLPI;

        switch($CFG_GLPI['number_format']) {
            case 2:
                $locale = 'de_DE';
                break;
            default:
                $locale = 'en_GB';
        }
 
        $currencies = new ISOCurrencies();

        $numberFormatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $moneyParser = new IntlLocalizedDecimalParser($numberFormatter, $currencies);

        return $moneyParser->parse($value, new Currency('EUR'));

    }

    public static function multiply(int|Money $value, int|float $factor) {

        $value = gettype($value) == 'integer' ? new Money($value, new Currency('EUR')) : $value;

        return $value->multiply((string) $factor);

    }

    public static function add(int|Money $value, int|Money $valueToAdd) {

        $value      = gettype($value) == 'integer' ? new Money($value, new Currency('EUR')) : $value;
        $valueToAdd = gettype($valueToAdd) == 'integer' ? new Money($valueToAdd, new Currency('EUR')) : $valueToAdd;

        return $value->add($valueToAdd);
        
    }

    public static function sum(array $values) {

        $sum = new Money(0, new Currency('EUR'));

        foreach ($values as $value) {
            $value = gettype($value) == 'integer' ? new Money($value, new Currency('EUR')) : $value;
            $sum = $sum->add($value);
        }

        return $sum;
        
    }

    public static function getTax(int $value, int $tax) {
        
        $value = new Money($value, new Currency('EUR'));
        $tax = $tax > 0 ? $tax / 100 : 1;

        return $value->multiply((string) $tax);
    }

}