<?php

namespace App\Importer;

class Report
{

    /**
     * Success messages.
     * 
     * @var array
     */
    private $successes = [];

    /**
     * Neutral remarks that are neither 'success' nor 'error'.
     * 
     * @var array
     */
    private $remarks = [];

    /**
     * Error messages.
     * 
     * @var array
     */
    private $errors = [];

    /**
     * Summary messages.
     * 
     * @var array
     */
    private $summary = [];

    /**
     * Add error message to error messages stack.
     * 
     * @param string $message
     * @return void
     */
    public function addErrorMessage(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Add remark to remarks stack.
     * 
     * @param string $message
     * @return void
     */
    public function addRemarkMessage(string $message): void
    {
        $this->remarks[] = $message;
    }

    /**
     * Add success message to success messages stack.
     * 
     * @param string $message
     * @return void
     */
    public function addSuccessMessage(string $message): void
    {
        $this->successes[] = $message;
    }

    /**
     * Add summary message to summary messages stack.
     * 
     * @param string $message
     * @return void
     */
    public function addSummaryMessage(string $message): void
    {
        $this->summary[] = $message;
    }

    /**
     * Get array of all reports.
     * 
     * [successes, remarks, errors, summary]
     * 
     * @return array
     */
    public function getReportMessages(): array
    {
        return [
            'successes' => $this->successes,
            'remarks'   => $this->remarks,
            'errors'    => $this->errors,
            'summary'   => $this->summary
        ];
    }

}
