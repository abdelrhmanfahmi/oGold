<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MatchService;
use Illuminate\Console\Command;

class UpdateUserCommand extends Command
{
    public function __construct(private MatchService $matchService)
    {
        parent::__construct();
    }

    protected $signature = 'login_user:cron';

    protected $description = 'this cron job for login as user in system every 10 mins and update his data in users table to use it in our system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::where('email' , env('EMAILUPDATEPRICE'))->first();
        $this->matchService->loginAccountForCronJob();
        \Log::info('work fine login user');
    }
}
