<?php

use Envor\SchemaMacros\SchemaMacros;
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

    $this->containerInstance = DockerContainer::create('mariadb:latest')
        ->setEnvironmentVariable('MARIADB_ROOT_PASSWORD', 'root')
        ->setEnvironmentVariable('MARIADB_DATABASE', 'mariadb_system')
        ->name('mariadb_system')
        ->mapPort(10001, 3306)
        ->start();

    $i = 0;

    while ($i < 50) {
        $process = Process::run('mysql -u root -proot -P 10001 -h 127.0.0.1  mariadb_system -e "show tables;"');
        if ($process->successful()) {
            break;
        }
        sleep(.5);
    }

    config(['database.connections.mariadb_system' => array_merge(config('database.connections.mariadb', config('database.connections.mysql')), [
        'database' => 'mariadb_system',
        'host' => '127.0.0.1',
        'port' => '10001',
        'username' => 'root',
        'password' => 'root',
    ])]);

    $this->connection = 'mariadb_system';
});

afterEach(function () {
    $this->containerInstance->stop();
});

it('can perform the macros on mariadb connection', function () {

    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists('mariadb_system'))->toBeTrue();
    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists('mariadb_system_not_exists'))->toBeFalse();

    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('mariadb_system'))->toBeFalse();
    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('mariadb_system_not_exists'))->toBeTrue();

    $trashedDatabase = DB::connection($this->connection)->getSchemaBuilder()->trashDatabase('mariadb_system');
    expect(DB::connection($this->connection)->getSchemaBuilder()->trashDatabase('does_not_exists'))->toBeFalse();

    expect(DB::connection($this->connection)->select("SHOW DATABASES LIKE '{$trashedDatabase}'"))->toHaveCount(1);
    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists($trashedDatabase))->toBeTrue();

    expect(DB::connection($this->connection)->getSchemaBuilder()->emptyTrash())->toBe(1);

    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(4)->format(SchemaMacros::TRASH_DATE_FORMAT));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(3)->format(SchemaMacros::TRASH_DATE_FORMAT));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(2)->format(SchemaMacros::TRASH_DATE_FORMAT));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->subDays(1)->format(SchemaMacros::TRASH_DATE_FORMAT));
    DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists('trashed_'.now()->format(SchemaMacros::TRASH_DATE_FORMAT));

    expect(DB::connection($this->connection)->getSchemaBuilder()->emptyTrash(2))->toBe(3);
});
