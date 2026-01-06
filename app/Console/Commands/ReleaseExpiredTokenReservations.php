<?php

namespace App\Console\Commands;

use App\Models\SurveyToken;
use Illuminate\Console\Command;

class ReleaseExpiredTokenReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Liberar tokens reservados que han expirado (más de 5 minutos sin votar)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Liberando tokens reservados expirados...');

        $count = SurveyToken::releaseExpiredReservations();

        if ($count > 0) {
            $this->info("Se liberaron {$count} tokens que estaban reservados y expiraron.");
        } else {
            $this->info('No hay tokens reservados expirados para liberar.');
        }

        return Command::SUCCESS;
    }
}
