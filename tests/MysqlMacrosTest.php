<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Spatie\Docker\DockerContainer;

beforeEach(function () {
    if (! `which mysql`) {
        $this->fail('MySQL client is not installed');
    }

    if (! `which docker`) {
        $this->fail('Docker is not installed');
    }

    $this->containerInstance = DockerContainer::create('mysql:latest')
        ->setEnvironmentVariable('MYSQL_ROOT_PASSWORD', 'root')
        ->setEnvironmentVariable('MYSQL_DATABASE', 'mysql_system')
        ->name('mysql_system')
        ->mapPort(10002, 3306)
        ->start();

    $i = 0;

    while ($i < 50) {
        $process = Process::run('mysql -u root -proot -P 10002 -h 127.0.0.1  mysql_system -e "show tables;"');
        if ($process->successful()) {
            break;
        }
        sleep(.5);
    }

    config(['database.connections.mysql_system' => array_merge(config('database.connections.mysql'), [
        'database' => 'mysql_system',
        'host' => '127.0.0.1',
        'port' => '10002',
        'username' => 'root',
        'password' => 'root',
    ])]);

    $this->time = now();

    $this->connection = 'mysql_system';
});

afterEach(function () {
    $this->containerInstance->stop();
});

it('can perform the macros on mysql builder', function () {

    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists('mysql_system'))->toBeTrue();
    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists('mysql_system_not_exists'))->toBeFalse();

    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('mysql_system'))->toBeFalse();
    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('mysql_system_not_exists'))->toBeTrue();

    $trashedDatabase = DB::connection($this->connection)->getSchemaBuilder()->trashDatabase('mysql_system');
    expect(DB::connection($this->connection)->getSchemaBuilder()->trashDatabase('does_not_exists'))->toBeFalse();

    expect(DB::connection($this->connection)->select("SHOW DATABASES LIKE '{$trashedDatabase}'"))->toHaveCount(1);
    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists($trashedDatabase))->toBeTrue();

    expect(DB::connection($this->connection)->getSchemaBuilder()->emptyTrash())->toBe(1);

    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(4)->format(config('schema-macros.trash-time-format')));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(3)->format(config('schema-macros.trash-time-format')));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(2)->format(config('schema-macros.trash-time-format')));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(1)->format(config('schema-macros.trash-time-format')));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->format(config('schema-macros.trash-time-format')));

    expect(DB::connection($this->connection)->getSchemaBuilder()->emptyTrash(2))->toBe(3);
});
