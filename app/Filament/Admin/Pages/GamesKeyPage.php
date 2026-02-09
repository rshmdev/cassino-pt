<?php

namespace App\Filament\Admin\Pages;

use App\Models\GamesKey;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\HtmlString;


class GamesKeyPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.games-key-page';

    protected static ?string $title = 'Chaves dos Jogos';

    // protected static ?string $slug = 'chaves-dos-jogos';

    /**
     * @dev @venixplataformas
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }


    public ?array $data = [];
    public ?GamesKey $setting;

    /**
     * @return void
     */
    public function mount(): void
    {
        $gamesKey = GamesKey::first();
        if(!empty($gamesKey)) {
            $this->setting = $gamesKey;
            $this->form->fill($this->setting->toArray());
        }else{
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

                            Section::make('PLAYFIVER API')
                ->description(new HtmlString('
                    <div style="display: flex; align-items: center;">
                        Nossa API fornece diversos jogos de slots e ao vivo. :
                        <a class="dark:text-white" 
                           style="
                                font-size: 14px;
                                font-weight: 600;
                                width: 127px;
                                display: flex;
                                background-color: #f800ff;
                                padding: 10px;
                                border-radius: 11px;
                                justify-content: center;
                                margin-left: 10px;
                           " 
                           href="https://playfiver.app" 
                           target="_blank">
                            PAINEL PLAYFIVER
                        </a>
                        <a class="dark:text-white" 
                           style="
                                font-size: 14px;
                                font-weight: 600;
                                width: 127px;
                                display: flex;
                                background-color: #f800ff;
                                padding: 10px;
                                border-radius: 11px;
                                justify-content: center;
                                margin-left: 10px;
                           " 
                           href="https://t.me/playfiver" 
                           target="_blank">
                            GRUPO TELEGRAM
                        </a>
                    </div>
                    <b>Sua URL de Callback :  ' . url("/playfiver/webhook", [], true) . "</b>"))
                
                ->schema([
                Section::make('CHAVES DE ACESSO PLAYFIVER')
                        ->description('Você pode obter suas chaves de acesso no painel da Playfiver ao criar o seu agente.')
                        ->schema([
                            TextInput::make('playfiver_code')
                                ->label('CÓDIGO DO AGENTE')
                                ->placeholder('Digite aqui o código do agente')
                                ->maxLength(191),

                            TextInput::make('playfiver_token')
                                ->label('AGENTE TOKEN')
                                ->placeholder('Digite aqui o token do agente')
                                ->maxLength(191),
                            TextInput::make('playfiver_secret')
                                ->label('AGENTE SECRETO')
                                ->placeholder('Digite aqui o código secreto do agente')
                                ->maxLength(191),    
                        ])->columns(3),
                ]),
                
                Section::make('Venix Games')
                    ->description('Ajustes de credenciais para a Venix Api, precisa de credito? acesse: https://venix.games ')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        TextInput::make('venixcg_api_url')
                            ->label('Api URL')
                            ->placeholder('Digite aqui a url API')
                            ->readOnly()
                            ->helperText('Recarga https://venix.games')
                            ->maxLength(191)
                            ->columnSpanFull(),
                        TextInput::make('venixcg_agent_code')
                            ->label('Agent Code')
                            ->placeholder('Digite aqui o Agent Code')
                            ->helperText('Recarga https://venix.games')
                            ->maxLength(191),
                        TextInput::make('venixcg_agent_token')
                            ->label('Agent Token')
                            ->placeholder('Digite aqui o Agent Token')
                            ->helperText('Recarga https://venix.games')
                            ->maxLength(191),
                        TextInput::make('venixcg_agent_secret')
                            ->label('Agent Secret')
                            ->placeholder('Digite aqui a Agente Secret')
                            ->helperText('Recarga https://venix.games')
                            ->maxLength(191),
                    ])
                    ->columns(3),

            ])
            ->statePath('data');
    }


    /**
     * @return void
     */
    public function submit(): void
    {
        try {
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title('Atenção')
                    ->body('Você não pode realizar está alteração na versão demo')
                    ->danger()
                    ->send();
                return;
            }

            $setting = GamesKey::first();
            if(!empty($setting)) {
                if($setting->update($this->data)) {
                    Notification::make()
                        ->title('Chaves Alteradas')
                        ->body('Suas chaves foram alteradas com sucesso!')
                        ->success()
                        ->send();
                }
            }else{
                if(GamesKey::create($this->data)) {
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
