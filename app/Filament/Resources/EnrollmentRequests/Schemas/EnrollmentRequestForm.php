<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnrollmentRequests\Schemas;

use CampusOs\Journey\Enums\DocumentRequestStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EnrollmentRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('user_usr_id')
                    ->required(),
                TextInput::make('registration_reg_id'),
                Select::make('erq_kind')
                    ->options([
                        'transcript' => 'Histórico escolar',
                        'enrollment_request' => 'Requerimento de matrícula',
                    ])
                    ->required(),
                TextInput::make('erq_file_path')
                    ->required(),
                TextInput::make('erq_mime')
                    ->required(),
                Select::make('erq_status')
                    ->options(DocumentRequestStatus::class)
                    ->required(),
                // Bruto de propósito: é o que a IA leu antes da conferência do
                // aluno — útil para depurar o extrator, não é editável em
                // produção (a confirmação é quem escreve matrícula de verdade).
                // hydrate/dehydrate em JSON de propósito: a coluna é cast como
                // array no model — sem isso, salvar re-encodaria a string.
                Textarea::make('erq_extraction')
                    ->helperText('O JSON que o extrator devolveu — mesmo formato de ExtractedDocument::toArray().')
                    ->rows(8)
                    ->columnSpanFull()
                    ->afterStateHydrated(function (Textarea $component, mixed $state): void {
                        $component->state(is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state);
                    })
                    ->dehydrateStateUsing(fn (?string $state): ?array => filled($state) ? json_decode($state, true) : null),
                TextInput::make('erq_confidence')
                    ->numeric(),
                TextInput::make('erq_provider'),
                TextInput::make('erq_failure_reason'),
                DateTimePicker::make('erq_parsed_at'),
                DateTimePicker::make('erq_confirmed_at'),
                DateTimePicker::make('erq_created_at'),
                DateTimePicker::make('erq_updated_at'),
            ]);
    }
}
