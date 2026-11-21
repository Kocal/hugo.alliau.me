<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\Controller\Admin;

use App\Blog\Application\Image\PostImageKeyGenerator;
use App\Blog\Domain\Data\Route as RouteBlog;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UploadPostImageController extends AbstractController
{
    #[Route('/admin/blog/post-images', name: RouteBlog::ADMIN_POST_IMAGE_UPLOAD->value, methods: ['POST'])]
    #[IsCsrfTokenValid('upload_post_image')]
    public function __invoke(
        Request $request,
        ValidatorInterface $validator,
        FilesystemOperator $uploadsStorage,
        PostImageKeyGenerator $keyGenerator,
    ): JsonResponse {
        $slug = (string) $request->request->get('slug', '');
        $slugViolations = $validator->validate($slug, [new Assert\NotBlank(), new Assert\Regex('/^[a-z0-9-]+$/D')]);

        if (\count($slugViolations) > 0) {
            return new JsonResponse([
                'error' => $slugViolations->get(0)->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $file = $request->files->get('file');

        if (! $file instanceof UploadedFile) {
            return new JsonResponse([
                'error' => 'No file uploaded.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $fileViolations = $validator->validate($file, new Assert\Image(
            maxSize: '10M',
            mimeTypes: ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/avif'],
        ));

        if (\count($fileViolations) > 0) {
            $violation = $fileViolations->get(0);

            return new JsonResponse([
                'error' => $violation->getMessage(),
            ], match ($violation->getCode()) {
                File::TOO_LARGE_ERROR => Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                File::INVALID_MIME_TYPE_ERROR => Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
                default => 400,
            });
        }

        $key = $keyGenerator->generate(
            $slug,
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            $file->guessExtension() ?? 'bin',
            $uploadsStorage->fileExists(...),
        );

        $stream = fopen($file->getPathname(), 'r');

        try {
            $uploadsStorage->writeStream($key, $stream);
        } catch (FilesystemException) {
            return new JsonResponse([
                'error' => 'The image could not be stored.',
            ], Response::HTTP_BAD_GATEWAY);
        } finally {
            if (\is_resource($stream)) {
                fclose($stream);
            }
        }

        $size = @getimagesize($file->getPathname());

        return new JsonResponse([
            'key' => $key,
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
        ], Response::HTTP_CREATED);
    }
}
