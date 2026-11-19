<?php

namespace App\Services\Provisioning;

class LampRecipe
{
    public static function supported(string $stack, string $version): bool
    {
        return $stack === 'lamp' && $version === '8.3';
    }

    public static function tasks(): array
    {
        return [
            [
                'id' => 'inspect',
                'name' => 'Inspect Ubuntu and SSH permissions',
            ],
            [
                'id' => 'packages',
                'name' => 'Update package information',
            ],
            [
                'id' => 'apache',
                'name' => 'Install and verify Apache',
            ],
            [
                'id' => 'php',
                'name' => 'Install PHP 8.3, PDO and extensions',
            ],
            [
                'id' => 'database',
                'name' => 'Install MySQL, Composer and Git',
            ],
            [
                'id' => 'verify',
                'name' => 'Verify LAMP dependencies',
            ],
        ];
    }

    public static function packages(): array
    {
        return [
            'apache2',
            'php8.3-cli',
            'php8.3-fpm',
            'php8.3-common',
            'php8.3-mysql',
            'php8.3-mbstring',
            'php8.3-xml',
            'php8.3-curl',
            'php8.3-zip',
            'php8.3-bcmath',
            'php8.3-intl',
            'mysql-server',
            'composer',
            'git',
            'unzip',
            'curl',
            'ca-certificates',
        ];
    }

    public static function initialTasks(): array
    {
        return array_map(
            fn (array $task) => [
                ...$task,
                'status' => 'pending',
                'detail' => 'Waiting to start',
            ],
            self::tasks()
        );
    }
}
