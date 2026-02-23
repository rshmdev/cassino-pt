<?php

namespace App\Filament\Admin\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Livewire\WithFileUploads;

set_time_limit(0);

class AdvancedPage extends Page implements HasForms
{
    use InteractsWithForms, WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.advanced-page';

    protected static ?string $navigationLabel = 'Opções Avançada';

    protected static ?string $modelLabel = 'Opções Avançada';

    protected static ?string $title = 'Opções Avançada';

    protected static ?string $slug = 'advanced-options';

    public ?array $data = [];
    public $output;

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

    }

    /**
     * @return void
     */
    public function upload()
    {

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
                Section::make('Atualização')
                    ->description('Carregue aqui seu arquivo de atualização no formato zip')
                    ->schema([
                        FileUpload::make('update')
                    ])

            ])
            ->statePath('data');
    }

    /**
     * @return void
     */
    public function runMigrate()
    {
        if(env('APP_DEMO')) {
            Notification::make()
                ->title('Atenção')
                ->body('Você não pode realizar está alteração na versão demo')
                ->danger()
                ->send();
            return;
        }

        Artisan::call('migrate');

        $this->output = Artisan::output();
        Notification::make()
            ->title('Sucesso')
            ->body('Migrações carregadas com sucesso')
            ->success()
            ->send();
    }

    /**
     * @return void
     */
    public function runMigrateWithSeed()
    {
        if(env('APP_DEMO')) {
            Notification::make()
                ->title('Atenção')
                ->body('Você não pode realizar está alteração na versão demo')
                ->danger()
                ->send();
            return;
        }

        Artisan::call('migrate --seed');

        $this->output = Artisan::output();
        Notification::make()
            ->title('Sucesso')
            ->body('Migrações carregadas com sucesso')
            ->success()
            ->send();
    }
}
