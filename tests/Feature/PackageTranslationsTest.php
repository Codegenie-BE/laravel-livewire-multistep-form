<?php

function packageTranslations(string $locale): array
{
    /** @var array<string, string> $translations */
    $translations = require dirname(__DIR__, 2)."/../resources/lang/{$locale}/messages.php";

    return $translations;
}

test('all shipped locales expose the same translation keys', function () {
    $english = packageTranslations('en');
    $dutch = packageTranslations('nl');
    $french = packageTranslations('fr');

    expect(array_keys($dutch))->toBe(array_keys($english))
        ->and(array_keys($french))->toBe(array_keys($english));
});

test('all shipped translation values are non empty strings', function () {
    foreach (['en', 'nl', 'fr'] as $locale) {
        foreach (packageTranslations($locale) as $key => $value) {
            expect($value, "Translation [{$locale}.{$key}] must be a non-empty string.")
                ->toBeString()
                ->not->toBe('');
        }
    }
});
