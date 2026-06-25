<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Paleta vermelha padrão do tema (cores que estavam aplicadas no site).
     * Usada tanto para atualizar a linha existente quanto como DEFAULT das colunas.
     */
    private array $palette = [
        'primary_color'         => '#D61F26',
        'primary_opacity_color' => '#d61f261a',
        'secundary_color'       => '#B81A20',
        'gray_dark_color'       => '#3b3b3b',
        'gray_light_color'      => '#c9c9c9',
        'gray_medium_color'     => '#676767',
        'gray_over_color'       => '#191A1E',
        'title_color'           => '#ffffff',
        'text_color'            => '#98A7B5',
        'placeholder_color'     => '#4D565E',
        'background_color'      => '#24262B',
        'background_base'       => '#e8e8e8',
        'background_base_dark'  => '#24262B',
        'carousel_banners'      => '#bdbdbd',
        'carousel_banners_dark' => '#1E2024',
        'sidebar_color'         => '#ffffff',
        'sidebar_color_dark'    => '#191A1E',
        'navtop_color'          => '#d8d8de',
        'navtop_color_dark'     => '#24262B',
        'side_menu'             => '#828282',
        'side_menu_dark'        => '#24262B',
        'input_primary'         => '#dedede',
        'input_primary_dark'    => '#1E2024',
        'footer_color'          => '#919191',
        'footer_color_dark'     => '#1E2024',
        'card_color'            => '#ababab',
        'card_color_dark'       => '#1E2024',
        'border_radius'         => '.25rem',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('custom_layouts')) {
            return;
        }

        // 1) Aplica a paleta vermelha na linha existente.
        if (DB::table('custom_layouts')->exists()) {
            DB::table('custom_layouts')->update($this->palette + ['updated_at' => now()]);
        }

        // 2) Torna a paleta o DEFAULT das colunas (instalações novas já nascem com ela).
        foreach ($this->palette as $column => $value) {
            if (Schema::hasColumn('custom_layouts', $column)) {
                DB::statement("ALTER TABLE `custom_layouts` ALTER COLUMN `{$column}` SET DEFAULT '{$value}'");
            }
        }

        // 3) Remove a coluna não utilizada (sub_text_color não é consumida em lugar nenhum do front).
        if (Schema::hasColumn('custom_layouts', 'sub_text_color')) {
            Schema::table('custom_layouts', function (Blueprint $table) {
                $table->dropColumn('sub_text_color');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('custom_layouts')) {
            return;
        }

        // Recria a coluna removida com o default original.
        if (!Schema::hasColumn('custom_layouts', 'sub_text_color')) {
            DB::statement("ALTER TABLE `custom_layouts` ADD `sub_text_color` varchar(20) NOT NULL DEFAULT '#656E78' AFTER `text_color`");
        }

        // Reverte os DEFAULT das colunas para os valores da migration baseline.
        $original = [
            'primary_color'     => '#0073D2',
            'secundary_color'   => '#084375',
            'gray_over_color'   => '#1A1C20',
            'background_base'   => '#ECEFF1',
            'carousel_banners'  => '#1E2024',
        ];
        foreach ($original as $column => $value) {
            DB::statement("ALTER TABLE `custom_layouts` ALTER COLUMN `{$column}` SET DEFAULT '{$value}'");
        }
    }
};
