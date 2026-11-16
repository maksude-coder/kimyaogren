<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\PeriodicSymbol;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PeriodicSymbolTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function names(): array
    {
        return [
            'two words' => ['Asitler, Bazlar ve Tuzlar', 'Ab'],
            'single word' => ['Karışımlar', 'Ka'],
            'turkish dotted i upper' => ['iyonik Bağlar', 'İb'],
            'turkish dotless I lower' => ['Kimya IŞIK', 'Kı'],
            'leading digits ignored' => ['3. Ünite: Gazlar', 'Üg'],
            'no letters' => ['123', ''],
        ];
    }

    #[DataProvider('names')]
    public function test_symbol_for_name(string $name, string $expected): void
    {
        $this->assertSame($expected, PeriodicSymbol::for($name));
    }
}
