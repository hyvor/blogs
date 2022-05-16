<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Http\UploadedFile;
use Faker\Factory as Faker;

// php artisan test  --filter 'ImportTest'
// working code - https://stackoverflow.com/questions/46408641/testing-file-upload-in-lumen-5-5

it('upload xml import file.', function() {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('wordpress-small.xml', 5*1000)
    ];
    $this->call('POST', '/data/import/upload', [], [], $files);
    $this->assertJson(200);
});

it('upload json import file.', function() {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('ghost.json', 5*1000)
    ];
    $this->call('POST', '/data/import/upload', [], [], $files);
    $this->assertJson(200);
});

it('upload html import file.', function() {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('index.html', 5*1000)
    ];
    $this->call('POST', '/data/import/upload', [], [], $files);
    $this->assertJson(200);
});

it('upload csv import file.', function() {
    Faker::create();
    $files = [
        'file' => UploadedFile::fake()->create('index.csv', 5*1000)
    ];
    $this->call('POST', '/data/import/upload', [], [], $files);
    $this->assertJson(200);
});