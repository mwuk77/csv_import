<?php

namespace App\Importer;

use App\Importer\Report;

/**
 * Abstract class knows how to read a CSV, and persist it to a Doctrine Entity, 
 * validating it with the Entity's annotated constraints.
 * 
 * It does not know the properties of the entity, nor the fields in the CSV,
 * nor how to map the two.
 * 
 * Child class WrenCsv has the missing info needed to import a Wren CSV.
 */
abstract class Csv
{

    /**
     * @var string Path to CSV file.
     */
    protected $path;

    /**
     * True - Don't persist data.
     * False - Persist data.
     * 
     * @var bool Whether in Test Mode or not.
     */
    protected $testMode = false;

    /**
     * @var int Row count in the CSV to start reading from.
     */
    protected $startRow = 0;

    /**
     * Number of items on each side must match, this should be the subset of 
     * fields which exist both in File and Entity.
     * 
     * @var array Map of [Int CSV Field Index => String EntityColumn].
     */
    protected $mapping;

    /**
     * @var string Pointing to instance of Doctrine Entity in /src/Entity in 
     * App\Entity namespace.
     */
    protected $entity;

    /**
     * @var App\Importer\Report
     */
    protected $reporter;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * Initialise Importer.
     * 
     * Ideally Symfony would inject the dependencies here, but could not get
     * working in time.
     * 
     * @param string $path
     * @param bool $testMode
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @throws \InvalidArgumentException
     */
    public final function __construct(string $path, bool $testMode = false, \Doctrine\ORM\EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator)
    {
        if (empty($path) || $path === null)
        {
            throw new \InvalidArgumentException('No value set for $path in constructor.');
        }

        $this->path = $path;

        if (!is_bool($testMode) || $testMode === null)
        {
            throw new \InvalidArgumentException('No value set for $testMode in constructor.');
        }

        $this->testMode = $testMode;

        $this->setMapping();
        if (!is_array($this->mapping) || $this->mapping === [])
        {
            throw new \InvalidArgumentException('No value set for $this->mapping. Have you defined setMapping():void in extending class ?');
        }

        $this->setEntity();
        if (!is_string($this->entity) || $this->entity === '')
        {
            throw new \InvalidArgumentException('No value set for $this->entity. Have you defined setEntity():void in extending class ?');
        }

        $this->reporter = new Report();

        $this->entityManager = $entityManager;

        $this->validator = $validator;
    }

    /**
     * Run the CSV import process.
     * 
     * Report array available with getReport() afterwards.
     * 
     * @return void
     * @throws \Exception CSV file not found.
     */
    public function import(): void
    {
        $notMappable  = 0;
        $rulesIgnored = 0;
        $imported     = 0;

        $handle = fopen($this->path, "r");
        if ($handle === false)
        {
            throw new \Exception('CSV file not found.');
        }

        for ($index = 0; $row = fgetcsv($handle); $index++)
        {
            if ($index < $this->startRow)
            {
                continue;
            }

            if ($this->rowIsMappable($row, $index) !== true)
            {
                $notMappable++;
                continue;
            }

            $mapping = $this->mapFieldsToValues($row);

            $mapping = $this->rowTranslations($mapping, $index);

            if ($this->ignoreRow($mapping, $index) === true)
            {
                $rulesIgnored++;
                continue;
            }

            if ($this->importRow($mapping, $index) === true)
            {
                $imported++;
            }
        }

        fclose($handle);

        $this->reporter->addSummaryMessage
            ($imported . ' Row(s) were successfully imported. ' .
                (($index - 1) - $imported) . ' Row(s) were not imported.');
        $this->reporter->addSummaryMessage($notMappable . ' Row(s) could not be imported because of mismatched column counts in the CSV.');
        $this->reporter->addSummaryMessage($rulesIgnored . ' Row(s) could not be imported as they matched Ignore Rules.');

        if ($this->testMode === true)
        {
            $this->reporter->addSummaryMessage('App was run in Test Mode. No records were persisted.');
        }
    }

    /**
     * Public accessor for report.
     * 
     * @return array Report messages
     */
    public function getReport(): array
    {
        return $this->reporter->getReportMessages();
    }

    /**
     * Child method should set $this->mapping.
     * 
     * @return void
     */
    protected abstract function setMapping(): void;

    /**
     * Child method should set $this->entity.
     * 
     * @return void
     */
    protected abstract function setEntity(): void;

    /**
     * Take array of mapped row data and say whether row should
     * be ignored.
     * 
     * Child method should return bool true if row should be ignored.
     * 
     * If there are no rules, define and return false.
     * 
     * @param array $mapping
     * @param int $index 
     * @return boolean
     */
    protected abstract function ignoreRow(array $mapping, int $index): bool;

    /**
     * Take array of mapped row data and make any desired substitutions.
     * 
     * Child method should return array of mapped row data in same structure
     * received.
     * 
     * If there are no substitutions, define and return unmodified $mapping.
     * 
     * @param array $mapping
     * @param int $index 
     * @return array Mapped row data.
     */
    protected abstract function rowTranslations(array $mapping, int $index): array;

    /**
     * Create a mapped array of [entityColumn => fileElement] values
     * 
     * @param array $row File data
     * @return array Mapped row data.
     */
    private function mapFieldsToValues(array $row): array
    {
        $mapping = [];
        foreach ($this->mapping as $fileElement => $entityColumn)
        {
            $mapping[$row[$fileElement]] = $entityColumn;
        }

        return array_flip($mapping);
    }

    /**
     * Instantiate and populate Doctrine entity, validate and save.
     * 
     * @param array $mapping
     * @param int $index
     * @return boolean
     */
    private function importRow(array $mapping, int $index): bool
    {
        /**
         * Here we're tightly coupled to Symfony and Doctrine for populating the 
         * model and validating it.
         * 
         * If DI were working, a Model layer would be preferable with a class
         * providing generic methods of save and validate which itself could
         * contain the coupling to Symfony/Doctrine. It could implement a 
         * ModelInterface which declares those two methods.
         * 
         * As DI not working, adding those would mean passing them their
         * dependencies on Symfony Validation and the Doctrine Entity Manager, 
         * from this class.
         */
        $use    = 'App\\Entity\\' . $this->entity;
        $entity = new $use;


        foreach ($mapping as $entityColumn => $fileValue)
        {
            $method = "set$entityColumn";
            $entity->{$method}($fileValue);
        }

        if ($this->validateEntity($entity, $index))
        {
            if ($this->testMode === false)
            {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }

            $this->reporter->addSuccessMessage('Row ' . $index . ': Successfully imported.');
            return true;
        }

        return false;
    }

    /**
     * Run the Symfony Validator against the instantiated & populated entity.
     * 
     * @param type $entity
     * @param type $index
     * @return boolean
     */
    private function validateEntity($entity, $index): bool
    {
        $violations = $this->validator->validate($entity);
        if ($violations->count() > 0)
        {
            $violationsText = 'Row ' . $index . ': Entity Validation error - ';
            for ($i = 0; $i < $violations->count(); $i++)
            {
                $violation      = $violations->get($i);
                $violationsText .= $violation->getPropertyPath() . ' - ' . $violation->getMessage();
            }

            $this->reporter->addErrorMessage($violationsText);

            return false;
        }

        return true;
    }

    /**
     * Take a row in array list form and check it can be mapped.
     * 
     * @param array $row
     * @param int $index
     * @return boolean
     */
    private function rowIsMappable(array $row, int $index): bool
    {
        if (count($row) !== count($this->mapping))
        {
            $this->reporter->addErrorMessage('Row ' . $index . ': CSV Error - Column count mismatch. Row not imported.');
            return false;
        }

        return true;
    }

}
