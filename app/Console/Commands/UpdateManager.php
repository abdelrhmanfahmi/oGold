<?php

namespace App\Console\Commands;

use App\Models\MatchData;
use App\Services\MatchService;
use Illuminate\Console\Command;

class UpdateManager extends Command
{
    public function __construct(private MatchService $matchService)
    {
        parent::__construct();
    }

    protected $signature = 'update_manager:cron';

    protected $description = 'Command description';

    public function handle()
    {
        //logic here
        $dataLog = $this->matchService->loginAsManager();
        $match = MatchData::first();
        $match->update(['manager_id' => env('MANAGER_ID') , 'manager_password' => env('MANAGER_PASSWORD') , 'manager_token' => $dataLog->token]);
        \Log::info('work fine login manager');
    }
}
