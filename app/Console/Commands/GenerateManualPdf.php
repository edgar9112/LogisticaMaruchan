<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateManualPdf extends Command
{
    protected $signature = 'manual:pdf';

    protected $description = 'Genera el manual de usuario en PDF dentro de public/docs';

    public function handle(): int
    {
        $pdf = Pdf::loadView('manual.pdf');
        $path = public_path('docs/manual-usuario-logistica-maruchan.pdf');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        $this->info('Manual generado: '.$path);

        return self::SUCCESS;
    }
}