<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Application;

use App\Recipes\Application\QuantityFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(QuantityFormatter::class)]
final class QuantityFormatterTest extends TestCase
{
    /**
     * @return iterable<string, array{float, string}>
     */
    public static function provideValues(): iterable
    {
        yield 'entier' => [4.0, '4'];
        yield 'entier large' => [500.0, '500'];
        yield 'zéro' => [0.0, '0'];
        yield 'un huitième' => [0.125, '⅛'];
        yield 'un quart' => [0.25, '¼'];
        yield 'un tiers' => [1 / 3, '⅓'];
        yield 'trois huitièmes' => [0.375, '⅜'];
        yield 'un demi' => [0.5, '½'];
        yield 'cinq huitièmes' => [0.625, '⅝'];
        yield 'deux tiers' => [2 / 3, '⅔'];
        yield 'trois quarts' => [0.75, '¾'];
        yield 'sept huitièmes' => [0.875, '⅞'];
        yield 'un et demi' => [1.5, '1 ½'];
        yield 'deux et un quart' => [2.25, '2 ¼'];
        yield 'décimale sans fraction connue' => [1.4, '1,4'];
        yield 'décimale loin de toute fraction' => [0.45, '0,45'];
        yield 'décimale happée par la fraction la plus proche' => [0.123456, '⅛'];
        yield "fractionnaire happée vers l'entier supérieur" => [1.995, '2'];
    }

    #[DataProvider('provideValues')]
    public function testFormat(float $value, string $expected): void
    {
        $this->assertSame($expected, new QuantityFormatter()->format($value));
    }

    public function testTheDecimalSeparatorIsConfigurable(): void
    {
        $this->assertSame('1.4', new QuantityFormatter()->format(1.4, '.'));
    }

    public function testMultiplyingAHalfByThreeGivesOneAndAHalf(): void
    {
        $this->assertSame('1 ½', new QuantityFormatter()->format(0.5 * 3));
    }
}
