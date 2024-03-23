<?php
declare(strict_types=1);

namespace App\Cli\Command;

use App\Domain\Blog\Post;
use App\Domain\Blog\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\s;

#[AsCommand(
    name: 'app:import-raw-posts',
    description: 'Import raw posts from the blog repository',
)]
final class ImportRawPostsCommand extends Command
{
    private array $postsUrls = [
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2020-01-02-generate-pdfs-on-amazon-aws-with-php-and-puppeteer.md',
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2020-04-21-generate-pdfs-on-amazon-aws-with-php-and-puppeteer-the-best-way.md',
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2021-04-26-migration-stack-developpement.md',
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2021-05-04-migration-to-github-native-dependabot-solutions-for-auto-merge-and-action-secrets.md',
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2022-01-07-doctrine-setmaxresults-and-collections-associations-are-on-a-boat.md',
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2023-07-19-how-to-use-php-cs-fixer-ruleset-with-easy-coding-standard.md',
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2023-10-21-blackfire-and-symfony-cli.md',
        'https://raw.githubusercontent.com/Kocal/blog/main/posts/2023-11-12-listen-to-doctrine-events-on-entities-given-a-php-attribute.md'
    ];

    private SymfonyStyle $io;

    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager,
        private PostRepository $postRepository,
        private ValidatorInterface $validator,
        private SluggerInterface $slugger,
    )
    {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->postsUrls as $url) {
            $post = $this->handleRawPostUrl($url);
            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();

        return self::SUCCESS;
    }

    private function handleRawPostUrl(mixed $url): Post
    {
        $this->io->writeln(sprintf("Fetching %s...", $url));

        $response = $this->httpClient->request('GET', $url);
        $rawPost = $response->getContent();

        return $this->handleRawPost($rawPost);
    }

    private function handleRawPost(string $rawPost): Post
    {
        $matches = s($rawPost)->match('/^---\n(?<frontmatter>.*?)\n---\n(?<content>.*)/s');

        $frontmatter = $matches['frontmatter'] ?? throw new \RuntimeException('Unable to extract the "frontmatter" from the raw post.');
        $content = $matches['content'] ?? throw new \RuntimeException('Unable to extract the "content" from the raw post.');

        $frontmatter = Yaml::parse($frontmatter, Yaml::PARSE_DATETIME);

        $post = $this->postRepository->findOneBy(['title' => $frontmatter['title']]) ?? new Post();
        $post->setTitle($frontmatter['title']);
        $post->setTags($frontmatter['tags']);
        $post->setDescription($frontmatter['description']);
        $post->setPublishedAt($frontmatter['date']);
        $post->setSlug($this->slugger->slug($frontmatter['date']->format('Y-m-d') . '-' . $frontmatter['title'])->lower()->toString());

        if (is_array($frontmatter['dependencies'] ?? null)) {
            $post->getSeo()->setDependencies($frontmatter['dependencies']);
        }

        if (is_string($frontmatter['proficiencyLevel'] ?? null)) {
            $post->getSeo()->setProficiencyLevel($frontmatter['proficiencyLevel']);
        }

        $content = s($content)
            ->replace('# {{ $frontmatter.title }}', '')
            ->trim()
            ->toString();

        $post->setContent($content);

        $violations = $this->validator->validate($post);
        if (count($violations) > 0) {
            $this->io->error('Invalid post:');
            foreach ($violations as $violation) {
                $this->io->writeln(sprintf(' - %s: %s', $violation->getPropertyPath(), $violation->getMessage()));
            }
            throw new \RuntimeException('Invalid post.');
        }

        return $post;
    }
}
