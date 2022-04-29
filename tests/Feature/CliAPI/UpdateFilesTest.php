<?php
namespace Tests\Feature\CliAPI;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\LocalDev\LocalDevRepository;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateFilesTest extends TestCase
{

    use RefreshDatabase;

    public $localDev;

    protected function setUp() : void {
        parent::setUp();
        $this->localDev = LocalDevRepository::createNewDev();
    }

    public function test_create_files_success() {

        // then add a few files file
        $content = Str::random();
        $response = $this->callCliAPI('patch', "/dev/{$this->localDev->uuid}/files", [
            'files' => [
                '/templates/index.twig' => base64_encode($content),
                'config.yaml' => 'name'
            ]
        ]);

        $response->assertOk();

        $indexTwig = ThemeFilesRepository::getFile($this->localDev, 'index.twig', ThemeFileFolderEnum::TEMPLATES);

        $this->assertEquals($indexTwig->content, $content);
    }

    // this must be run after create
    public function test_update_files_success() 
    {

        $response = $this->callCliAPI('patch', "/dev/{$this->localDev->uuid}/files", [
            'files' => [
                '/templates/index.twig' => base64_encode('New string')
            ]
        ]);

        $response->assertOk();

        $indexTwig = ThemeFilesRepository::getFile($this->localDev, 'index.twig', ThemeFileFolderEnum::TEMPLATES);
        $this->assertEquals($indexTwig->content, 'New string');

    }

}
