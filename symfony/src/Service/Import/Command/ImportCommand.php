<?php

namespace App\Service\Import\Command;

use App\Service\Blog\BlogService;
use App\Service\Import\ImportLog;
use App\Service\Import\Importer\Importer;
use App\Service\Import\Importer\ParserException;
use App\Service\Import\Parser\Typepad\TypepadParser;
use App\Service\Import\Parser\WordPressParser;
use App\Service\Language\LanguageService;
use App\Service\Media\MediaService;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use App\Service\User\UserService;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'import',
    description: 'Import a WordPress or Typepad export. --path must point to the local, already-extracted export directory.',
)]
class ImportCommand
{
    public function __construct(
        private BlogService $blogService,
        private PermalinkService $permalinkService,
        private PostContentService $postContentService,
        private Connection $connection,
        private LanguageService $languageService,
        private PostService $postService,
        private MediaService $mediaService,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option('import source: wordpress or typepad')] string $from = '',
        #[Option('local path to the extracted export directory')] string $path = '',
        #[Option('subdomain of the blog to import into')] string $subdomain = '',
        #[Option('parse and report without importing anything')] bool $test = false,
        #[Option('do not import images/media')] bool $noImages = false,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if ($blog === null) {
            $io->error('Blog not found');
            return Command::FAILURE;
        }

        if (!is_dir($path)) {
            $io->error("Directory not found: {$path}");
            return Command::FAILURE;
        }

        $log = new ImportLog($io);

        try {
            $parser = match ($from) {
                'wordpress' => new WordPressParser($blog, $path, $log, $this->permalinkService),
                'typepad' => new TypepadParser($blog, $path, $log, $this->permalinkService),
                default => throw new ParserException('Invalid import source: ' . $from),
            };
        } catch (ParserException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        try {
            if ($test) {
                $io->note('Testing import. Parsing file...');
                $parser->parse();

                $io->note('Checking for missing uploads...');
                $missingUploads = $parser->getMissingUploadsCount();
                if ($missingUploads > 0) {
                    $io->error($missingUploads . ' uploads are missing');
                } else {
                    $io->info('All uploads exist');
                }

                $io->writeln((string)json_encode([
                    'posts' => count($parser->posts),
                    'uploads' => $parser->uploadsCount,
                    'duplicates_ignored' => $parser->duplicateCount,
                ]));

                return Command::SUCCESS;
            }

            $importer = new Importer(
                $blog,
                $parser,
                !$noImages,
                $this->connection,
                $this->languageService,
                $this->postService,
                $this->mediaService,
                $this->permalinkService,
                $this->postContentService,
            );
            $importer->import();

            $io->success("Imported {$importer->postsCount} posts.");

            return Command::SUCCESS;
        } catch (ParserException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
