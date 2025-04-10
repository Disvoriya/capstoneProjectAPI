<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyUser;
use Carbon\Carbon;

class TerminateInactiveUsers extends Command
{
    protected $signature = 'users:terminate';
    protected $description = 'Удаляет пользователей со статусом "Inactive", если истек срок увольнения';

    public function handle()
    {
        $usersToTerminate = CompanyUser::where('status', 'Inactive')
            ->whereNotNull('terminated_at')
            ->where('terminated_at', '<=', Carbon::now())
            ->get();

        foreach ($usersToTerminate as $companyUser) {

            $companyUser->delete();
        }

        $this->info('Уволены пользователи: ' . $usersToTerminate->count());
    }
}
