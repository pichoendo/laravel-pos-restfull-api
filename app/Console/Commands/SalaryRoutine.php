<?php

namespace App\Console\Commands;

use App\Services\SalaryService;
use Illuminate\Console\Command;

class SalaryRoutine extends Command
{

    protected SalaryService $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        parent::__construct();
        $this->salaryService = $salaryService;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:salary-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store salary of employee every month';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $this->info('Start generate the employee salary');
        $this->salaryService->generateSalary();
    }
}
