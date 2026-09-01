<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\EasyAdmin\Controller;

use App\Recipes\Infrastructure\EasyAdmin\Controller\RecipeCrudController;
use App\User\Infrastructure\Foundry\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Couvre bout en bout le contrat HTML de {@see RecipeCrudController}: le champ d'édition
 * du contenu (RecipeContentField, le form theme et le pont Stimulus/Vue) n'a aucun autre test.
 */
#[CoversNothing]
final class RecipeCrudControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testTheNewPageExposesTheRecipeEditorContract(): void
    {
        $client = self::createClient();

        $admin = UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
        ]);

        $client->loginUser($admin);

        $crawler = $client->request(Request::METHOD_GET, '/admin/recipe/new');

        self::assertResponseIsSuccessful();

        $editor = $crawler->filter('[data-controller="recipe-editor"]');
        $this->assertCount(1, $editor);

        $contentValue = $editor->attr('data-recipe-editor-content-value');
        $this->assertNotNull($contentValue);

        $decoded = json_decode($contentValue, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('roots', $decoded);

        $this->assertCount(1, $crawler->filter('[data-recipe-editor-target="field"]'));
        $this->assertCount(1, $crawler->filter('[data-recipe-editor-target="mount"]'));
    }
}
