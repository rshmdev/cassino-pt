<?php

namespace App\Filament\Admin\Pages;

use App\Models\Gateway;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class GatewayPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.gateway-page';

    public ?array $data = [];
    public Gateway $setting;

    /**
     * @dev @venixplataformas
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @return void
     */
    public function mount(): void
    {
        $gateway = Gateway::first();
        if (!empty($gateway)) {
            $this->setting = $gateway;
            $this->form->fill($this->setting->toArray());
        } else {
            $this->form->fill();
        }
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('BlackPearlPay')
                    ->description('Ajustes de credenciais para a BlackPearlPay (Gateway Principal)')
                    ->schema([
                        TextInput::make('blackpearlpay_uri')
                            ->label('Base URI')
                            ->default('https://api.blackpearlpay.com/api/public/cash')
                            ->placeholder('https://api.blackpearlpay.com/api/public/cash')
                            ->maxLength(191)
                            ->columnSpanFull(),
                        TextInput::make('blackpearlpay_api_token')
                            ->label('API Token')
                            ->placeholder('Digite o token da BlackPearlPay')
                            ->maxLength(191)
                            ->columnSpanFull(),
                    ]),
                Section::make('Stripe')
                    ->description('Ajustes de credenciais para Stripe (Checkout Session)')
                    ->schema([
                        TextInput::make('stripe_public_key')
                            ->label('Public Key (pk_...)')
                            ->placeholder('pk_live_...')
                            ->maxLength(191)
                            ->columnSpanFull(),
                        TextInput::make('stripe_secret_key')
                            ->label('Secret Key (sk_...)')
                            ->placeholder('sk_live_...')
                            ->maxLength(191)
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),
                        TextInput::make('stripe_webhook_secret')
                            ->label('Webhook Signing Secret (whsec_...)')
                            ->placeholder('whsec_...')
                            ->maxLength(191)
                            ->columnSpanFull(),
                        TextInput::make('stripe_connected_account_id')
                            ->label('Connected Account ID (acct_...)')
                            ->placeholder('acct_...')
                            ->maxLength(191)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }


    /**
     * @return void
     */
    public function submit(): void
    {
        try {
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title('Atencao')
                    ->body('Voce nao pode realizar esta alteracao na versao demo')
                    ->danger()
                    ->send();
                return;
            }

            $setting = Gateway::first();
            if (!empty($setting)) {
                if ($setting->update($this->data)) {
                    Notification::make()
                        ->title('Chaves Alteradas')
                        ->body('Suas chaves foram alteradas com sucesso!')
                        ->success()
                        ->send();
                }
            } else {
                if (Gateway::create($this->data)) {
                    Notification::make()
                        ->title('Chaves Criadas')
                        ->body('Suas chaves foram criadas com sucesso!')
                        ->success()
                        ->send();
                }
            }


        } catch (Halt $exception) {
            Notification::make()
                ->title('Erro ao alterar dados!')
                ->body('Erro ao alterar dados!')
                ->danger()
                ->send();
        }
    }
}
