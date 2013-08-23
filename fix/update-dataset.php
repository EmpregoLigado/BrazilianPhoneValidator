<?php
require_once __DIR__.'/../src/EmpregoLigado/BrazilianPhoneValidator/Validator.php';

class FixDataset
{
    const DATASET_SMP_FILENAME = 'dataset.smp';

    private $filepath;
    private $dataset;

    public function __construct($datasetDirectory)
    {
        $filepath = $datasetDirectory . DIRECTORY_SEPARATOR . self::DATASET_SMP_FILENAME;

        if (!file_exists($filepath . ".json")) {
            throw new \InvalidArgumentException('The ' . self::DATASET_SMP_FILENAME . ".json" . ' does not exists');
        }

        $this->filepath = $filepath;

    }

    public function fix($array) {
        $content = file_get_contents($this->filepath . ".json");
        $this->dataset = json_decode($content, true);

        foreach ($array as $areaCode)
        {
            foreach ($this->dataset[$areaCode] as $prefix => $range)
            {
                //echo $prefix . PHP_EOL;
                //echo $prefix . "<br>";
                if (strlen($prefix) == 4) {
                    $newPrefix = "9" . $prefix;
                    $this->dataset[$areaCode][$newPrefix] = $range;
                    unset($this->dataset[$areaCode][$prefix]);
                }
            }
        }

        $this->saveDataset();
    }

    private function saveDataset()
    {
        $this->saveToPhpFile($this->dataset);
        $this->saveToJsonFile($this->dataset);
    }

    private function saveToPhpFile($data)
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

        file_put_contents($this->filepath . '.php', $data);
    }

    private function saveToJsonFile($data)
    {
        file_put_contents($this->filepath . '.json', json_encode($data));
    }
}

$array = array(12, 13, 14, 15, 16, 17, 18, 19);
$fixDataset = new FixDataset("data");
$fixDataset->fix($array);