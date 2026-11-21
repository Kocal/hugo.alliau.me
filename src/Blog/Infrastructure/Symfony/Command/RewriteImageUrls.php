<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\Command;

use App\Blog\Application\Markdown\ImageReferenceRewriter;
use App\Blog\Domain\Data\Post;
use App\Shared\Domain\HttpCache\CacheItem;
use App\Shared\Domain\HttpCache\HttpCache;
use App\Shared\Domain\Markdown\MarkdownConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:blog:rewrite-image-urls',
    description: 'Replace legacy cdn.kocal.fr/minio.kocal.fr image URLs by object keys in blog posts',
)]
final class RewriteImageUrls extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageReferenceRewriter $rewriter,
        private readonly HttpCache $httpCache,
        private readonly MarkdownConverter $markdownConverter,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Report the changes without writing them');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $changedPosts = [];
        $missingKeys = [];
        $unreadableKeys = [];
        $unrenderablePosts = [];

        foreach ($this->entityManager->getRepository(Post::class)->findAll() as $post) {
            $result = $this->rewriter->rewrite((string) $post->getContent());

            $missingKeys = [...$missingKeys, ...$result->missingKeys];
            $unreadableKeys = [...$unreadableKeys, ...$result->unreadableKeys];

            if ($result->replacements === []) {
                continue;
            }

            $io->section((string) $post->getTitle());
            $io->listing(array_map(static fn (array $replacement): string => \sprintf('%s -> %s %s', $replacement['from'], $replacement['to'], $replacement['attributes']), $result->replacements));

            try {
                ($this->markdownConverter)($result->content);
            } catch (\Throwable $e) {
                $unrenderablePosts[] = \sprintf('%s: %s', $post->getTitle(), $e->getMessage());

                continue;
            }

            if (! $dryRun) {
                $post->setContent($result->content);
                $changedPosts[] = $post;
            }
        }

        if (! $dryRun && $changedPosts !== []) {
            $this->entityManager->flush();
            $this->httpCache->clearFor(...array_map(CacheItem::fromEntity(...), $changedPosts));
        }

        if ($missingKeys !== []) {
            $io->warning(['Missing objects, URLs kept:', ...array_values(array_unique($missingKeys))]);
        }

        if ($unreadableKeys !== []) {
            $io->warning(['Unreadable dimensions, URLs kept:', ...array_values(array_unique($unreadableKeys))]);
        }

        if ($unrenderablePosts !== []) {
            $io->warning(['Posts whose rewritten content does not render:', ...$unrenderablePosts]);
        }

        if ($dryRun) {
            $io->note('Dry run, nothing written.');
        }

        return Command::SUCCESS;
    }
}
