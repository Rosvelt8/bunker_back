<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    /**
     * Nom de la commande.
     */
    protected $signature = 'make:service {name}';

    /**
     * Description de la commande.
     */
    protected $description = 'Créer un nouveau service';

    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path("Services/{$name}.php");

        if (File::exists($path)) {
            $this->error("Le service {$name} existe déjà !");
            return 1;
        }

        File::ensureDirectoryExists(app_path('Services'));

        $stub = <<<EOT
<?php

namespace App\Services;

class {$name}
{
    // Ajoutez vos méthodes ici
}
EOT;

        File::put($path, $stub);

        $this->info("Le service {$name} a été créé avec succès !");
        return 0;
    }
}
