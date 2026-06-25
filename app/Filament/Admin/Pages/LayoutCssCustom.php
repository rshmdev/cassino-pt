<?php

namespace App\Filament\Admin\Pages;

use App\Models\CustomLayout;
use Creagia\FilamentCodeField\CodeField;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Cache;

class LayoutCssCustom extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.layout-css-custom';

    protected static ?string $navigationLabel = 'Customização Layout';

    protected static ?string $modelLabel = 'Customização Layout';

    protected static ?string $title = 'Customização Layout';

    protected static ?string $slug = 'custom-layout';

    public ?array $data = [];
    public CustomLayout $custom;

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
        $this->custom = CustomLayout::first();
        $this->form->fill($this->custom->toArray());
    }

    /**
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {

        return $data;
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tema')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([

                        // ----------------------------------------------------------------
                        Tabs\Tab::make('Marca & Texto')
                            ->icon('heroicon-o-swatch')
                            ->columns(2)
                            ->schema([
                                ColorPicker::make('primary_color')
                                    ->label('Cor Principal')
                                    ->helperText('Cor da marca: botões, links, ícones ativos e destaques em todo o site.')
                                    ->required(),
                                ColorPicker::make('primary_opacity_color')
                                    ->label('Cor Principal (translúcida)')
                                    ->helperText('Versão suave/transparente da cor principal (fundos de destaque, brilhos).')
                                    ->required(),
                                ColorPicker::make('secundary_color')
                                    ->label('Cor Secundária')
                                    ->helperText('Cor de apoio usada em alguns destaques secundários.')
                                    ->required(),
                                ColorPicker::make('title_color')
                                    ->label('Cor dos Títulos')
                                    ->helperText('Cor dos títulos e cabeçalhos das seções.')
                                    ->required(),
                                ColorPicker::make('text_color')
                                    ->label('Cor do Texto')
                                    ->helperText('Cor do texto comum (parágrafos, descrições, labels).')
                                    ->required(),
                                ColorPicker::make('placeholder_color')
                                    ->label('Cor do Placeholder')
                                    ->helperText('Cor do texto de exemplo dentro dos campos (ex.: "Procurar...").')
                                    ->required(),
                                TextInput::make('border_radius')
                                    ->label('Arredondamento das bordas')
                                    ->helperText('Raio das bordas dos elementos. Ex.: .25rem (suave), .5rem, 9999px (redondo).')
                                    ->required(),
                            ]),

                        // ----------------------------------------------------------------
                        Tabs\Tab::make('Cinzas & Base')
                            ->icon('heroicon-o-squares-2x2')
                            ->columns(2)
                            ->schema([
                                ColorPicker::make('gray_dark_color')
                                    ->label('Cinza Escuro')
                                    ->helperText('Tom de cinza escuro para detalhes e elementos auxiliares.')
                                    ->required(),
                                ColorPicker::make('gray_medium_color')
                                    ->label('Cinza Médio')
                                    ->helperText('Cinza médio para ícones e textos auxiliares.')
                                    ->required(),
                                ColorPicker::make('gray_light_color')
                                    ->label('Cinza Claro')
                                    ->helperText('Cinza claro para bordas e detalhes sutis.')
                                    ->required(),
                                ColorPicker::make('gray_over_color')
                                    ->label('Cinza de Realce (hover)')
                                    ->helperText('Cinza usado em realces e ao passar o mouse (hover).')
                                    ->required(),
                                ColorPicker::make('background_color')
                                    ->label('Cor de Fundo (base)')
                                    ->helperText('Cor de fundo base usada por vários componentes.')
                                    ->required(),
                            ]),

                        // ----------------------------------------------------------------
                        Tabs\Tab::make('Modo Claro')
                            ->icon('heroicon-o-sun')
                            ->columns(2)
                            ->schema([
                                ColorPicker::make('background_base')
                                    ->label('Fundo Principal')
                                    ->helperText('Fundo geral das páginas no modo claro.')
                                    ->required(),
                                ColorPicker::make('carousel_banners')
                                    ->label('Fundo do Carrossel / Banners')
                                    ->helperText('Fundo da área de banners no topo da home.')
                                    ->required(),
                                ColorPicker::make('sidebar_color')
                                    ->label('Menu Lateral (sidebar)')
                                    ->helperText('Fundo do menu lateral esquerdo.')
                                    ->required(),
                                ColorPicker::make('navtop_color')
                                    ->label('Barra Superior (navbar)')
                                    ->helperText('Fundo da barra do topo do site.')
                                    ->required(),
                                ColorPicker::make('side_menu')
                                    ->label('Itens do Menu Lateral')
                                    ->helperText('Fundo das caixinhas/itens dentro do menu lateral.')
                                    ->required(),
                                ColorPicker::make('footer_color')
                                    ->label('Rodapé')
                                    ->helperText('Fundo do rodapé.')
                                    ->required(),
                                ColorPicker::make('input_primary')
                                    ->label('Campos / Inputs')
                                    ->helperText('Fundo dos campos de formulário (inputs).')
                                    ->required(),
                                ColorPicker::make('card_color')
                                    ->label('Cards de Jogos')
                                    ->helperText('Fundo dos cards/quadros de jogos.')
                                    ->required(),
                            ]),

                        // ----------------------------------------------------------------
                        Tabs\Tab::make('Modo Escuro')
                            ->icon('heroicon-o-moon')
                            ->columns(2)
                            ->schema([
                                ColorPicker::make('background_base_dark')
                                    ->label('Fundo Principal')
                                    ->helperText('Fundo geral das páginas no modo escuro (é o modo padrão do site).')
                                    ->required(),
                                ColorPicker::make('carousel_banners_dark')
                                    ->label('Fundo do Carrossel / Banners')
                                    ->helperText('Fundo da área de banners no topo da home (escuro).')
                                    ->required(),
                                ColorPicker::make('sidebar_color_dark')
                                    ->label('Menu Lateral (sidebar)')
                                    ->helperText('Fundo do menu lateral esquerdo (escuro).')
                                    ->required(),
                                ColorPicker::make('navtop_color_dark')
                                    ->label('Barra Superior (navbar)')
                                    ->helperText('Fundo da barra do topo do site (escuro).')
                                    ->required(),
                                ColorPicker::make('side_menu_dark')
                                    ->label('Itens do Menu Lateral')
                                    ->helperText('Fundo das caixinhas/itens do menu lateral (escuro).')
                                    ->required(),
                                ColorPicker::make('footer_color_dark')
                                    ->label('Rodapé')
                                    ->helperText('Fundo do rodapé (escuro).')
                                    ->required(),
                                ColorPicker::make('input_primary_dark')
                                    ->label('Campos / Inputs')
                                    ->helperText('Fundo dos campos de formulário (escuro).')
                                    ->required(),
                                ColorPicker::make('card_color_dark')
                                    ->label('Cards de Jogos')
                                    ->helperText('Fundo dos cards/quadros de jogos (escuro).')
                                    ->required(),
                            ]),

                        // ----------------------------------------------------------------
                        Tabs\Tab::make('Código (Avançado)')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                CodeField::make('custom_css')
                                    ->label('CSS personalizado')
                                    ->setLanguage(CodeField::CSS)
                                    ->withLineNumbers()
                                    ->minHeight(300),
                                CodeField::make('custom_js')
                                    ->label('JS personalizado')
                                    ->setLanguage(CodeField::JS)
                                    ->withLineNumbers()
                                    ->minHeight(300),
                                CodeField::make('custom_header')
                                    ->label('HTML no <head>')
                                    ->setLanguage(CodeField::HTML)
                                    ->withLineNumbers()
                                    ->minHeight(300),
                                CodeField::make('custom_body')
                                    ->label('HTML no <body>')
                                    ->setLanguage(CodeField::HTML)
                                    ->withLineNumbers()
                                    ->minHeight(300),
                            ]),

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
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title('Atenção')
                    ->body('Você não pode realizar está alteração na versão demo')
                    ->danger()
                    ->send();
                return;
            }

            $custom = CustomLayout::first();

            if(!empty($custom)) {
                if($custom->update($this->data)) {

                    Cache::put('custom', $custom);

                    Notification::make()
                        ->title('Dados alterados')
                        ->body('Dados alterados com sucesso!')
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
