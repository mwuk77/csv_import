<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * These two dependencies should be 'autowired' and should be available
 * to App\Importer\Csv in its own scope. Unfortunately here is the only place 
 * the autowiring works, so they're passed to the constructor of App\Importer\Csv. 
 * Not ideal.
 */
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Importer\WrenCsv;

final class ImportCommand extends Command 
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    /**
     * @var ValidatorInterface
     */
    private $validator;    

    /**
     * Inject the dependencies here, as could not get working elsewhere.
     * 
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;

        parent::__construct(null);
    }
    
    /**
     * Configure with an non-valueful Option 'test' and a required Argument 'path'.
     * 
     * Usage: php bin/console import [-t|--test] <path>
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Import a Product Data CSV file.')
            ->setHelp('This command imports a CSV file of Product Data to a Product Data table.')
            ->addArgument('path', InputArgument::REQUIRED, 'File path to the CSV file.')
            ->addOption('test', 't', InputOption::VALUE_NONE, 'Will not write to database.');
    }

    /**
     * Pass option and argument to App\Importer\WrenCsv.
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {       
        $testMode = $input->getOption('test') ?: false;

        $path = $input->getArgument('path');

        $importer = new WrenCsv($path, $testMode, 
                $this->entityManager, $this->validator);
        $importer->import();
        
        //this could be prettified, but conveys the info.
        print_r($importer->getReport());
        
        return 0;//int 0 - terminate successfully.
    }
}