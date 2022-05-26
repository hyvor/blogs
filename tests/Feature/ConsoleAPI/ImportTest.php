<?php

namespace Tests\Feature\ConsoleAPI;

use Faker\Factory as Faker;
use Illuminate\Http\UploadedFile;

// php artisan test  --filter 'ImportTest'
// working code - https://stackoverflow.com/questions/46408641/testing-file-upload-in-lumen-5-5

it('upload wordpress import file.', function () {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('wordpress-small.xml', 5 * 1000),
    ];
    $platform = [
        'platform' => 'wordpress',
    ];
    $this->call('POST', '/data/import', $platform, [], $files);
    $this->assertJson(200);
});

it('upload medium import file.', function () {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('medium.xml', 5 * 1000),
    ];
    $platform = [
        'platform' => 'medium',
    ];
    $this->call('POST', '/data/import', $platform, [], $files);
    $this->assertJson(200);
});

it('upload ghost import file.', function () {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('ghost.json', 5 * 1000),
    ];
    $platform = [
        'platform' => 'ghost',
    ];
    $this->call('POST', '/data/import', $platform, [], $files);
    $this->assertJson(200);
});




it('upload csv import file.', function () {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('index.csv', 5 * 1000),
    ];
    $this->call('POST', '/data/import', [], [], $files);
    $this->assertJson(200);
});
