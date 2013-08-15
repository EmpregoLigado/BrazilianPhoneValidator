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

class DatasetProcessor
{
    const INFO_FILENAME = 'info';
    const DATASET_SMP_FILENAME = 'dataset.smp';
    const DATASET_SME_FILENAME = 'dataset.sme';
    const DATASET_STFC_FILENAME = 'dataset.stfc';

    // Attributes
    private $filepath;
    private $filename;
    private $outputDirectory;
    private $datasetFilename;
    private $dataset;
    private $info;

    // Auxiliary Attributes
    private $type;
    private $countProcessedRows;
    private $countCreatedRows;
    private $auxAreaCode;
    private $auxPrefix;
    private $auxFinalRange;
    private $auxInitialRange;

    public function __construct($dataSourceFilePath, $outputDirectory)
    {
        if (!file_exists($dataSourceFilePath)) {
            throw new \InvalidArgumentException(
                sprintf('The data source file path does not exists (%s).', $dataSourceFilePath)
            );
        }

        if (!file_exists($outputDirectory)) {
            throw new \InvalidArgumentException(
                sprintf('The output directory does not exists (%s).', $outputDirectory)
            );
        }

        $this->filepath = $dataSourceFilePath;
        $this->filename = basename($this->filepath);
        $this->outputDirectory = $outputDirectory;

        if (strpos($this->filename, 'SMP') !== false) {
            $this->type = 'smp';
            $this->datasetFilename = self::DATASET_SMP_FILENAME;
        } elseif (strpos($this->filename, 'SME') !== false) {
            $this->type = 'sme';
            $this->datasetFilename = self::DATASET_SME_FILENAME;
        } elseif (strpos($this->filename, 'STFC') !== false) {
            $this->type = 'stfc';
            $this->datasetFilename = self::DATASET_STFC_FILENAME;
        } else {
            throw new \InvalidArgumentException('Data source file is invalid.');
        }

        // Holds information about processed files.
        $this->info = array();

        if ($this->fileExists(self::INFO_FILENAME . '.json')) {
            $content = file_get_contents($this->outputDirectory . DIRECTORY_SEPARATOR . self::INFO_FILENAME . '.json');
            $this->info = json_decode($content, true);
        }

        $this->dataset = array();

        $this->countProcessedRows = 0;
        $this->countCreatedRows = 0;
        $this->auxAreaCode = -1;
        $this->auxPrefix = -1;
        $this->auxFinalRange = -1;
        $this->auxInitialRange = -1;
    }

    public function process()
    {
        if ($this->isNewDataSourceFile()) {
            if ($this->shouldReplaceGeneralDataSourceFile()) {
                unset($this->info[$this->type]);
                unlink($this->outputDirectory . DIRECTORY_SEPARATOR . $this->datasetFilename . ".php");
                unlink($this->outputDirectory . DIRECTORY_SEPARATOR . $this->datasetFilename . ".json");
            }

            $this->parse();
            $this->saveDataset();
            $this->saveInfoFile();

            return true;
        }

        return false;
    }

    public function getResults()
    {
        return array(
            'datasource' => $this->filename,
            'processed' => $this->countProcessedRows,
            'created' => $this->countCreatedRows,
        );
    }

    private function isNewDataSourceFile()
    {
        return !isset($this->info[$this->type]) || !in_array($this->filename, $this->info[$this->type]);
    }

    private function shouldReplaceGeneralDataSourceFile()
    {
        return strpos($this->filename, 'GERAL') !== false && isset($this->info[$this->type]);
    }

    private function parse()
    {
        $handle = fopen ($this->filepath, 'r');

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            if (count($row) <= 1) {
                continue;
            }

            if ($this->countProcessedRows > 0) {
                $areaCode = $row[2];
                $prefix = $row[3];
                $initialRange = $row[4];
                $finalRange = $row[5];

                if (strlen($prefix) < 4) {
                    $this->countProcessedRows++;
                    continue;
                }

                if ($this->auxPrefix == $prefix) {
                    // This range continues the previous range?
                    if ($this->auxFinalRange + 1 == $initialRange) {
                        $this->auxFinalRange = $finalRange;
                    } else {
                        $this->pushToArray($areaCode, $prefix, $initialRange, $finalRange);
                    }
                } else {
                    $this->pushToArray($areaCode, $prefix, $initialRange, $finalRange);
                }
            }

            $this->countProcessedRows++;
        }

        // Persist last instruction
        $this->pushToArray($this->auxAreaCode, $this->auxPrefix, $this->auxInitialRange, $this->auxFinalRange);

        ksort($this->dataset);
    }

    private function pushToArray($areaCode, $prefix, $initialRange, $finalRange)
    {
        // Persist
        if ($this->countProcessedRows > 1) {
            $ir = str_pad($this->auxInitialRange, 4, '0', STR_PAD_LEFT);
            $fr = str_pad($this->auxFinalRange, 4, '0', STR_PAD_LEFT);
            $range = $ir . '-' . $fr;

            if (!isset($this->dataset[$this->auxAreaCode])) {
                $this->dataset[$this->auxAreaCode] = array();
            }

            // Generate dataset with keys based on areaCode and prefix
            if (!array_key_exists($this->auxPrefix, $this->dataset[$this->auxAreaCode])) {
                $this->dataset[$this->auxAreaCode][$this->auxPrefix] = array($range);
            } else {
                $this->dataset[$this->auxAreaCode][$this->auxPrefix][] = $range;
            }

            $this->countCreatedRows++;
        }

        // Update Variables
        $this->auxAreaCode = $areaCode;
        $this->auxPrefix = $prefix;
        $this->auxInitialRange = $initialRange;
        $this->auxFinalRange = $finalRange;
    }

    private function saveDataset()
    {
        if ($this->fileExists($this->datasetFilename . '.php')) {
            $oldDataset= $this->loadDatasetFile();
            $this->dataset = $this->concatenateArrays($oldDataset, $this->dataset);
        }

        $this->saveToPhpFile($this->dataset, $this->datasetFilename);
        $this->saveToJsonFile($this->dataset, $this->datasetFilename, false);
    }

    private function saveInfoFile()
    {
        if (!isset($this->info[$this->type]) || !in_array($this->filename, $this->info[$this->type])) {
            $this->info[$this->type][] = $this->filename;
        } else {
            $this->info = array($this->type => array($this->filename));
        }

        ksort($this->info);

        $this->saveToJsonFile($this->info, self::INFO_FILENAME, true);
    }

    private function concatenateArrays($array1, $array2)
    {
        foreach ($array2 as $k => $v) {
            if (!array_key_exists($k, $array1)) {
                $array1[$k] = $v;
            } else {
                foreach ($v as $k2 => $v2) {
                    $array1[$k][$k2] = $v2;
                }
            }
        }

        ksort($array1);

        return $array1;
    }

    private function saveToPhpFile($data, $filename)
    {
        $template = <<<TEMPLATE
<?php

return %s;

TEMPLATE;

        // Remove spaces and comma before the closing bracket to save filesize.
        $data = var_export($data, true);
        $data = preg_replace('/\s/', '', $data);
        $data = preg_replace('/,\)/', ')', $data);
        $data = sprintf($template, $data);

        file_put_contents($this->outputDirectory . DIRECTORY_SEPARATOR . $filename . '.php', $data);
    }

    private function saveToJsonFile($data, $filename, $prettyPrint = false)
    {
        if ($prettyPrint) {
            $json = $this->jsonEncodeAndFormat($data);
        } else {
            $json = json_encode($data);
        }

        file_put_contents($this->outputDirectory . DIRECTORY_SEPARATOR . $filename . '.json', $json);
    }

    private function loadDatasetFile()
    {
        return include $this->outputDirectory . DIRECTORY_SEPARATOR . $this->datasetFilename . '.php';
    }

    private function fileExists($filename)
    {
        return file_exists($this->outputDirectory . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Formats a JSON string to make it more human-readable.
     * @author Dave Perrett
     * @see http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
     */
    private function jsonEncodeAndFormat($data) {
        if (defined('JSON_PRETTY_PRINT')){
            // PHP 5.4 offers the JSON_PRETTY_PRINT option.
            return json_encode($data, JSON_PRETTY_PRINT);
        }

        $json = json_encode($data);

        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {
            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

                // If this character is the end of an element,
                // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }

}

function help()
{
    $message  = 'This script generates a dataset file using an Anatel dataset as source.' . PHP_EOL;
    $message .= PHP_EOL;
    $message  = 'Usage:   php generate-dataset.php [datasource-file] [destination-path]' . PHP_EOL;
    $message .= 'Example: php generate-dataset.php /path/to/FAIXA_SME_20130803_0832_GERAL.txt ../data/' . PHP_EOL;
    $message .= PHP_EOL;
    $message .= 'Arguments:' . PHP_EOL;
    $message .= 'datasource-file     The path to the datasource file' . PHP_EOL;
    $message .= 'destination-path    Directory to save the generated dataset files (PHP and JSON files)' . PHP_EOL;
    $message .= PHP_EOL;
    $message .= 'Options:' . PHP_EOL;
    $message .= '-h|--help           Display this help message' . PHP_EOL;
    $message .= PHP_EOL;

    print $message;
}

$short_options = 'h';
$long_options = array('help');

$options = getopt($short_options, $long_options);

if (isset($options['h']) || isset($options['help']) || $argc === 1) {
    help();
    exit(0);
}

$dataSourceFilePath = isset($argv[1]) ? $argv[1] : null;
$outputDirectory = isset($argv[2]) ? $argv[2] : null;

try {
    $processor = new DatasetProcessor($dataSourceFilePath, $outputDirectory);

    echo 'Processing file '.$dataSourceFilePath.' ...'.PHP_EOL;

    $processor->process();
    $results = $processor->getResults();

    echo 'Results:'.PHP_EOL;
    echo '- Processed rows: '.$results['processed'].PHP_EOL;
    echo '- Added rows:     '.$results['created'].PHP_EOL.PHP_EOL;

} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;

    exit(1);
}

exit(0);
