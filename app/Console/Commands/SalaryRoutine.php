<?php

namespace App\Console\Commands;

use App\Services\SalaryService;
use Illuminate\Console\Command;

class SalaryRoutine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Script to calculate and store salary of employee every month';

    /**
     * Execute the console command.
     */

    protected SalaryService $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        parent::__construct();
        $this->salaryService = $salaryService;
    }

    public function handle()
    {
        $this->salaryService->generateSalary();
    }
}
