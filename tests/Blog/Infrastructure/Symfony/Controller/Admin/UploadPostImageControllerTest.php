<?php

declare(strict_types=1);

namespace App\Tests\Blog\Infrastructure\Symfony\Controller\Admin;

use App\Blog\Infrastructure\Symfony\Controller\Admin\UploadPostImageController;
use App\User\Infrastructure\Foundry\Factory\UserFactory;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(UploadPostImageController::class)]
final class UploadPostImageControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAdminUploadsAnImageAndGetsItsKeyAndDimensions(): void
    {
        $client = $this->createAdminClient();

        $this->upload($client, [
            'slug' => 'my-post',
        ], $this->pngFile());

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame([
            'key' => 'posts-assets/my-post/my-shot.png',
            'width' => 2,
            'height' => 1,
        ], $this->responseData($client));
        $storage = self::getContainer()->get('uploads.storage');
        $this->assertInstanceOf(FilesystemOperator::class, $storage);
        $this->assertTrue($storage->fileExists('posts-assets/my-post/my-shot.png'));
    }

    public function testSameFilenameTwiceGetsASuffix(): void
    {
        $client = $this->createAdminClient();
        $this->upload($client, [
            'slug' => 'my-post',
        ], $this->pngFile());

        $this->upload($client, [
            'slug' => 'my-post',
        ], $this->pngFile());

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('posts-assets/my-post/my-shot-2.png', $this->responseData($client)['key']);
    }

    public function testInvalidSlugIsRejected(): void
    {
        $client = $this->createAdminClient();

        $this->upload($client, [
            'slug' => 'My Post',
        ], $this->pngFile());

        $this->assertResponseStatusCodeSame(400);
        $this->assertArrayHasKey('error', $this->responseData($client));
    }

    public function testNonImageIsRejected(): void
    {
        $client = $this->createAdminClient();
        $path = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($path, 'hello');

        $this->upload($client, [
            'slug' => 'my-post',
        ], new UploadedFile($path, 'My Shot.png', 'image/png', null, true));

        $this->assertResponseStatusCodeSame(415);
    }

    public function testSvgIsRejected(): void
    {
        $client = $this->createAdminClient();
        $path = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg"/>');

        $this->upload($client, [
            'slug' => 'my-post',
        ], new UploadedFile($path, 'logo.svg', 'image/svg+xml', null, true));

        $this->assertResponseStatusCodeSame(415);
    }

    public function testMissingFileIsRejected(): void
    {
        $client = $this->createAdminClient();

        $this->upload($client, [
            'slug' => 'my-post',
        ], null);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $client = self::createClient();

        $this->upload($client, [
            'slug' => 'my-post',
        ], $this->pngFile());

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testRequestWithoutOriginIsRedirectedToLogin(): void
    {
        $client = $this->createAdminClient();

        $this->upload($client, [
            'slug' => 'my-post',
        ], $this->pngFile(), []);

        $this->assertResponseRedirects('/login');
    }

    /**
     * @return array<string, mixed>
     */
    private function responseData(KernelBrowser $client): array
    {
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        return \is_array($data) ? $data : [];
    }

    private function createAdminClient(): KernelBrowser
    {
        $client = self::createClient();
        $client->disableReboot();
        $client->loginUser(UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
        ]));

        return $client;
    }

    private function pngFile(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upload');
        copy(__DIR__ . '/../../../../Fixtures/2x1.png', $path);

        return new UploadedFile($path, 'My Shot.png', 'image/png', null, true);
    }

    /**
     * @param array<string, string> $fields
     * @param array<string, string> $server
     */
    private function upload(KernelBrowser $client, array $fields, ?UploadedFile $file, array $server = [
        'HTTP_ORIGIN' => 'http://localhost',
    ]): void
    {
        $client->request('POST', '/admin/blog/post-images', $fields + [
            '_token' => 'a-stateless-token-of-at-least-24-chars',
        ], $file instanceof UploadedFile ? [
            'file' => $file,
        ] : [], $server);
    }
}
