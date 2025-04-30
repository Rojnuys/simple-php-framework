<?php

namespace App\Core\Core;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CliCore extends BaseCore
{
    protected array $commands = [
        'migrate' => 'migrate',
        'migrate:reset' => 'migrateReset',
        'migrate:refresh' => 'migrateRefresh',
        'make:migration' => 'makeMigration',
        'cache:clear' => 'cacheClear',
    ];
    protected string $cachedContainerClassName = 'CliCachedContainer';

    public function getMigrationDirectoryPath(): string
    {
        return $this->getProjectDirectoryPath() . DIRECTORY_SEPARATOR . 'db' .  DIRECTORY_SEPARATOR . 'migrations';
    }

    public function run(): void
    {
        $this->loadContainer();
        $this->runModules();

        global $argc, $argv;

        if ($argc === 1 || $argv[1] === 'help') {
            echo "Available commands:\n  - migrate\n  - migrate:reset\n  - migrate:refresh\n";
            exit();
        }

        if (!isset($this->commands[$argv[1]])) {
            echo "Unknown command: {$argv[1]}\n";
            exit();
        }

        $this->{$this->commands[$argv[1]]}();

        $this->stopModules();
    }

    protected function migrate(): void
    {
        $fileNames = scandir($this->getMigrationDirectoryPath());
        foreach ($fileNames as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }
            $migration = include $this->getMigrationDirectoryPath() . DIRECTORY_SEPARATOR . $fileName;
            $migration->up();
        }
    }

    protected function migrateReset(): void
    {
        $fileNames = scandir($this->getMigrationDirectoryPath());
        foreach ($fileNames as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }
            $migration = include $this->getMigrationDirectoryPath() . DIRECTORY_SEPARATOR . $fileName;
            $migration->down();
        }
    }

    protected function migrateRefresh(): void
    {
        $this->migrateReset();
        $this->migrate();
    }

    protected function makeMigration(): void
    {
        global $argv;

        if (!isset($argv[2])) {
            echo "Command {$argv[1]} must have the second 'name' parameter\n";
            exit();
        }

        $fileName = date("d_m_Y_H_i_s") . '_' . $argv[2] . '.php';
        file_put_contents(
            $this->getMigrationDirectoryPath() . DIRECTORY_SEPARATOR . $fileName,
            "<?php\n\nuse App\Core\Database\Interfaces\IMigration;\nuse Illuminate\Database\Capsule\Manager;\n\nreturn new class implements IMigration {\n\tpublic function up(): void\n\t{\n\t}\n\n\tpublic function down(): void\n\t{\n\t}\n};"
        );
    }

    protected function cacheClear(): void
    {
        if (!is_dir($this->getCacheDirectoryPath())) {
            echo "Cache doesn't exist";
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->getCacheDirectoryPath(), FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $fileInfo) {
            $filePath = $fileInfo->getRealPath();

            if ($fileInfo->isDir()) {
                rmdir($filePath);
            } else {
                unlink($filePath);
            }
        }

        echo "Cache was cleared successfully\n";
    }
}