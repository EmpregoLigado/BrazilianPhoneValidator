# BrazilianPhoneValidator

This library provides a validator for Brazilian telephone numbers based on the
ranges of telephone numbers from Anatel. It provides validation rules for the
main telephone types:

- Cellphones - [SMP](http://tinyurl.com/anatel-smX), *Serviço Móvel Pessoal*
  (Personal Mobile Service) in the Anatel specifications
- Landline phones - [STFC](http://tinyurl.com/anatel-stfc), *Serviço
  Telefônico Fixo Comutado* (Switched Landline Telephone Service) in the
  Anatel specifications
- Push-to-talk phones - [SME](http://tinyurl.com/anatel-smX), *Serviço Móvel
  Especializado* (Specialized Mobile Service) in Anatel specifications

[![Build Status](https://travis-ci.org/EmpregoLigado/BrazilianPhoneValidator.png)](https://travis-ci.org/EmpregoLigado/BrazilianPhoneValidator)


## What is Anatel?

According to [Wikipedia](http://en.wikipedia.org/wiki/Brazilian_Agency_of_Telecommunications):

> "The National Telecommunications Agency (in Portuguese, Agência Nacional de
> Telecomunicações - Anatel) is a special agency in Brazil created by the
> general telecommunications act (Law 9472, 16/07/1997) in 1997. The agency is
> administratively and financially independent, and not hierarchically
> subordinate to any government agency."


## Requirements

PHP 5.3 or above.


## Installation

The easiest way to install BrazilianPhoneValidator is
[through Composer](http://getcomposer.org/). Just create a `composer.json` file
for your project:

```JSON
{
    "require": {
        "empregoligado/brazilian-phone-validator": "dev-master"
    }
}
```

And then run these commands:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

Now include the Composer-generated autoload to have access to the library:

```PHP
<?php

require 'vendor/autoload.php';

$validator = new EmpregoLigado\BrazilianPhoneValidator\Validator();
```


## Usage

The `EmpregoLigado\BrazilianPhoneValidator\Validator` class provides the
following high-level public API to validate a Brazilian phone number:

- `isValid($phone)` (any type of phone number)
- `isValidCellphone($phone)`
- `isValidLandline($phone)`

And the following lower-level public API to validate a specific Brazilian phone
number type:

- `isValidSME($phone)`
- `isValidSMP($phone)`
- `isValidSTFC($phone)`

Where `$phone` is a "numeric" string with the area code and phone number
(examples: `1149502480` and `6134111200`.)

Usage example:

```php
<?php

$validator = new EmpregoLigado\BrazilianPhoneValidator\Validator();

$phone = '6134111200';

// Checks if it is a valid Brazilian phone number.
if ($validator->isValid($phone)) {
    // success statement
} else {
    // error statement
}
```


### Creating/updating the dataset files

The dataset files available in the `data/` directory are created and updated
by the `bin/dataset-processor.php` script. It generates a JSON and a PHP file
for each processed dataset file.

To process the dataset files correctly, you must download the latest "Geral"
(general) file, process it and then process all (if any) the additional
incremental files which the date is greater than the "Geral" file date.

    $ php bin/dataset-processor.php /path/to/FAIXA_SMP_20130803_0330_GERAL.txt data/

This library ships with the latest possible dataset version. You can download
them independently to use for validation or for other purposes.


#### Anatel dataset files (SME, SMP and STFC)

- [SME](http://sistemas.anatel.gov.br/sapn/ArquivosABR/faixaSME.asp?SISQSmodulo=18098)
- [SMP](http://sistemas.anatel.gov.br/sapn/ArquivosABR/faixaSMP.asp?SISQSmodulo=18099)
- [STFC](http://sistemas.anatel.gov.br/sapn/ArquivosABR/faixaSTFC.asp?SISQSmodulo=18100)


## License

This library is licensed under the MIT license - see the LICENSE file for details.
