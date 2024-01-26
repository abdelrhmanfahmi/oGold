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

    protected $signature = 'update_user:cron';

    protected $description = 'Command description';

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
