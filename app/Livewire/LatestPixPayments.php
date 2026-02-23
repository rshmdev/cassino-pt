<?php

namespace App\Livewire;

use App\Models\Deposit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPixPayments extends BaseWidget
{
    protected static ?string $heading = 'Pagamentos Realizados';

    protected static ?int $navigationSort = -1;

    protected int | string | array $columnSpan = 'full';

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(Deposit::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('payment_id')
                    ->label('Pagamento ID'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('BRL')
                    ->label('Valor'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Pendente',
                        '1' => 'Pago',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    /**
     * @return bool
     */
    public static function canView(): bool
    {
        return true;
    }

}
