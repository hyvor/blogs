<?php

namespace App\Service\Import\MessageHandler;

use App\Entity\Import;
use App\Service\AppConfig;
use App\Service\Import\Importer\Importer;
use App\Service\Import\Importer\ParserException;
use App\Service\Import\ImportException;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportMessage;
use App\Service\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Service\Import\Sitemap\SitemapParser;
use App\Service\Language\LanguageService;
use App\Service\Media\MediaService;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use App\Service\User\UserService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class ImportMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private ImportService $importService,
        private Connection $connection,
        private LanguageService $languageService,
        private UserService $userService,
        private PostService $postService,
        private MediaService $mediaService,
        private PermalinkService $permalinkService,
        private PostContentService $postContentService,
        private HttpClientInterface $httpClient,
        private AppConfig $appConfig,
    ) {
    }

    public function __invoke(ImportMessage $message): void
    {
        $import = $this->em->find(Import::class, $message->importId);

        if ($import === null) {
            throw new UnrecoverableMessageHandlingException("Import {$message->importId} not found");
        }

        $parser = new SitemapParser(
            $import->getBlog(),
            $message->sitemapUrl,
            PageScraperOptions::fromArray($message->scraperOptions),
            $this->httpClient,
            $this->postContentService,
            $this->appConfig
        );

        $importer = new Importer(
            $import->getBlog(),
            $parser,
            $message->importImages,
            $this->connection,
            $this->languageService,
            $this->userService,
            $this->postService,
            $this->mediaService,
            $this->permalinkService,
            $this->postContentService,
        );

        try {
            $importer->import();
        } catch (ParserException|ImportException $e) {
            $this->importService->markFailed($import, $e->getMessage());
            return;
        }

        $this->importService->markCompleted($import, $importer->postsCount);
    }
}
