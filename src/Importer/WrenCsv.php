<?php
namespace App\Importer;

class WrenCsv extends Csv
{
    /**
     * @var int Row count in the CSV to start reading from.
     */
    protected $startRow = 1; //skip header row.

    /**
     * Set string pointing to instance of Doctrine Entity in /src/Entity in 
     * App\Entity namespace.
     * 
     * @return void
     */
    protected function setEntity() : void
    {
        $this->entity = 'ProductData';
    }
        
    /**
     * Map of [CSV Field Index => EntityColumn].
     * 
     * @return void
     */
    protected function setMapping() : void
    {
        $this->mapping = [
            0 => 'ProductCode',
            1 => 'ProductName',
            2 => 'ProductDescription',
            3 => 'StockLevel',
            4 => 'PriceGBP',
            5 => 'Discontinued'
        ];
    }
    
    /**
     * Go through mapped row of data and make substitutions.
     * 
     * @param array $mapping
     * @param int $index
     * @return array
     */
    protected function rowTranslations(array $mapping, int $index): array 
    {
        //is discontinued - Discontinued date = now.
        if($mapping['Discontinued'] === 'yes')
        {
            $mapping['Discontinued'] = new \DateTime();
            $this->reporter->addRemarkMessage('Row ' .  $index.  ': Discontinued datetime substituted for this row.');
        }
        else
        {
            $mapping['Discontinued'] = null;
        }
     
        //coerce missing StockLevels to int 0.
        if(isset($mapping['StockLevel']) === false
            || $mapping['StockLevel'] === null
            || empty($mapping['StockLevel']))
        {
            $mapping['StockLevel'] = 0;
            $this->reporter->addRemarkMessage('Row ' .  $index.  ': Missing StockLevel coerced to int 0.');            
        }
        
        //remove any alpha chars from Price field.
        $mapping['PriceGBP'] = filter_var($mapping['PriceGBP'], 
                FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        
        //populate Added with import time. Unclear from requirements whether
        //this is needed.
        $mapping['Added'] =  new \DateTime();
        
        return $mapping;
    }    

    /**
     * Rules which cause rows to be ignored.
     * 
     * @param array $mapping
     * @param int $index
     * @return bool
     */
    protected function ignoreRow(array $mapping, int $index) : bool
    {
        //less than £5 and less than 10 in stock.
        //bccomp to compare with right precision, floats have too many edge cases.
        if(bccomp($mapping['PriceGBP'], '5', 2) === -1 
                && (int) $mapping['StockLevel'] < 10)
        {
            $this->reporter->addErrorMessage('Row ' . $index . ': Ignore Rule enforced - "Less than £5 and less than 10 in stock". Row not imported.');
            return true;
        }
        
        //over £1000.
        if(bccomp($mapping['PriceGBP'], '1000', 2) === 1)
        {
            $this->reporter->addErrorMessage('Row ' . $index . ': Ignore Rule enforced - "Over £1000". Row not imported.');
            return true;            
        }
                        
        return false;
    }
}