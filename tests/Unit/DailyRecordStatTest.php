<?php

namespace Tests\Unit;

use App\Filament\Widgets\DailyRecordStat;
use PHPUnit\Framework\TestCase;

class DailyRecordStatTest extends TestCase
{
    public function test_it_returns_cula_name_when_available(): void
    {
        $widget = new DailyRecordStat();

        $this->assertSame('Atas Pagar', $widget->getCulaLabel('VA', ['VA' => 'Atas Pagar']));
    }

    public function test_it_falls_back_to_code_when_name_is_missing(): void
    {
        $widget = new DailyRecordStat();

        $this->assertSame('ZZ', $widget->getCulaLabel('ZZ', []));
    }
}
