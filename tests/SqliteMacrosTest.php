<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    config(['database.connections.sqlite_system' => array_merge(config('database.connections.sqlite'), [
        'database' => ':memory:',
    ])]);

    $this->connection = 'sqlite_system';
});

afterEach(function () {
    Storage::fake('local');
});

it('can check if a sqlite database exists', function () {
    $path = Storage::path('database.sqlite');

    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists($path))->toBeFalse();

    expect(Schema::connection($this->connection)->databaseExists($path))->toBeFalse();

    Storage::put('database.sqlite', '');

    expect(DB::connection($this->connection)->getSchemaBuilder()->databaseExists($path))->toBeTrue();
    expect(Schema::connection($this->connection)->databaseExists($path))->toBeTrue();
});

it('can create a sqlite database if it does not exist', function () {
    $path = Storage::path('database.sqlite');

    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists($path))->toBeTrue();
    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists($path))->toBeFalse();

    Storage::fake('local');

    expect(Schema::connection($this->connection)->createDatabaseIfNotExists($path))->toBeTrue();
    expect(Schema::connection($this->connection)->createDatabaseIfNotExists($path))->toBeFalse();
});

it('can create a sqlite database if it does not exist recursively', function () {
    $path = Storage::path('/path/to/database.sqlite');

    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists($path))->toBeTrue();
    expect(DB::connection($this->connection)->getSchemaBuilder()->createDatabaseIfNotExists($path))->toBeFalse();

    Storage::fake('local');

    expect(Schema::connection($this->connection)->createDatabaseIfNotExists($path))->toBeTrue();
    expect(Schema::connection($this->connection)->createDatabaseIfNotExists($path))->toBeFalse();
});

it('can trash a sqlite database', function () {
    $path = Storage::path('database.sqlite');

    expect(Storage::disk('local')->files('.trash'))->toHaveCount(0);

    Storage::put('database.sqlite', '');

    $trashfile = DB::connection($this->connection)->getSchemaBuilder()->trashDatabase($path);

    expect(Storage::disk('local')->files('.trash'))->toHaveCount(1);

    expect(Storage::exists('database.sqlite'))->toBeFalse();

    expect(File::exists($trashfile))->toBeTrue();
});

it('can empty the trash', function () {
    $path = Storage::path('database.sqlite');

    Storage::put('database.sqlite', '');

    $trashfile = DB::connection($this->connection)->getSchemaBuilder()->trashDatabase($path);

    expect(Storage::disk('local')->files('.trash'))->toHaveCount(1);

    expect(DB::connection($this->connection)->getSchemaBuilder()->emptyTrash())->toBe(1);

    expect(Storage::disk('local')->files('.trash'))->toHaveCount(0);
});

it('can empty the trash of files older than a certain number of days', function () {
    $path = Storage::path('database.sqlite');

    Storage::put('database.sqlite', '');

    $trashfile = DB::connection($this->connection)->getSchemaBuilder()->trashDatabase($path);

    expect(Storage::disk('local')->files('.trash'))->toHaveCount(1);

    touch($trashfile, now()->subDays(2)->timestamp);

    Storage::put('database2.sqlite', '');

    $trashfile2 = DB::connection($this->connection)->getSchemaBuilder()->trashDatabase(Storage::path('database2.sqlite'));

    expect(Storage::disk('local')->files('.trash'))->toHaveCount(2);

    expect(DB::connection($this->connection)->getSchemaBuilder()->emptyTrash(1))->toBe(1);

    expect(Storage::disk('local')->files('.trash'))->toHaveCount(1);
});
