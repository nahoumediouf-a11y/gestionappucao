<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=10 : Nombre de sauvegardes à conserver}';

    protected $description = 'Sauvegarde la base SQLite dans storage/app/backups';

    public function handle(): int
    {
        $source = database_path('database.sqlite');

        if (! File::exists($source)) {
            $this->error("Base introuvable : {$source}");

            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $filename = 'database-'.now()->format('Y-m-d_H-i-s').'.sqlite';
        $destination = $backupDir.'/'.$filename;

        File::copy($source, $destination);
        $this->info("Sauvegarde créée : {$destination}");

        $keep = (int) $this->option('keep');
        $backups = collect(File::files($backupDir))
            ->filter(fn ($file) => $file->getExtension() === 'sqlite')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        foreach ($backups->slice($keep) as $old) {
            File::delete($old->getPathname());
            $this->line('Ancienne sauvegarde supprimée : '.$old->getFilename());
        }

        return self::SUCCESS;
    }
}
