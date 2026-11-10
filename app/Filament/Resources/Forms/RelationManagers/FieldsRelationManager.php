<?php

namespace App\Filament\Resources\Forms\RelationManagers;

use App\Enums\FormFieldType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    protected static ?string $title = 'فیلدهای فرم';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('نوع فیلد')
                ->options(collect(FormFieldType::cases())->mapWithKeys(
                    fn (FormFieldType $type): array => [$type->value => $type->label()]
                ))
                ->required()
                ->live(),
            TextInput::make('label')
                ->label('برچسب')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, $get): void {
                    if (! $get('name')) {
                        $set('name', Str::snake((string) $state));
                    }
                }),
            TextInput::make('name')
                ->label('نام سیستمی')
                ->required()
                ->alphaDash()
                ->maxLength(100),
            TextInput::make('step')
                ->label('مرحله')
                ->numeric()
                ->default(1)
                ->required(),
            TextInput::make('placeholder')
                ->label('Placeholder'),
            Textarea::make('help_text')
                ->label('متن راهنما')
                ->rows(2),
            Toggle::make('is_required')
                ->label('اجباری')
                ->default(false),
            KeyValue::make('options_json')
                ->label('گزینه‌ها (value => label)')
                ->visible(fn (callable $get): bool => in_array($get('type'), [
                    FormFieldType::Select->value,
                    FormFieldType::MultiSelect->value,
                ], true))
                ->keyLabel('value')
                ->valueLabel('label'),
            Textarea::make('visibility_rules_json')
                ->label('قوانین نمایش شرطی (JSON)')
                ->helperText('مثال: {"field":"city","operator":"equals","value":"tehran"}')
                ->rows(3)
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $state)
                ->dehydrateStateUsing(function ($state) {
                    if (! filled($state)) {
                        return null;
                    }
                    if (is_array($state)) {
                        return $state;
                    }
                    $decoded = json_decode((string) $state, true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                }),
            Textarea::make('validation_rules_json')
                ->label('قوانین اعتبارسنجی اضافه (JSON آرایه)')
                ->helperText('مثال: ["max:100","email"]')
                ->rows(2)
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : $state)
                ->dehydrateStateUsing(function ($state) {
                    if (! filled($state)) {
                        return null;
                    }
                    if (is_array($state)) {
                        return $state;
                    }
                    $decoded = json_decode((string) $state, true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                }),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('step')->label('مرحله'),
                TextColumn::make('label')->label('برچسب'),
                TextColumn::make('name')->label('نام'),
                TextColumn::make('type')->label('نوع')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof FormFieldType ? $state->label() : FormFieldType::from((string) $state)->label()),
                IconColumn::make('is_required')->label('اجباری')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var \App\Models\Form $owner */
                        $owner = $this->getOwnerRecord();
                        $data['site_id'] = $owner->site_id;
                        $data['sort_order'] = ((int) $owner->fields()->max('sort_order')) + 1;

                        if (isset($data['options_json']) && is_array($data['options_json'])) {
                            $data['options_json'] = collect($data['options_json'])
                                ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
                                ->values()
                                ->all();
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (isset($data['options_json']) && is_array($data['options_json']) && ! array_is_list($data['options_json'])) {
                            $data['options_json'] = collect($data['options_json'])
                                ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
                                ->values()
                                ->all();
                        }

                        return $data;
                    }),
                DeleteAction::make(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return auth()->user()?->can('update', $this->getOwnerRecord()) ?? false;
    }

    protected function canEdit(Model $record): bool
    {
        return auth()->user()?->can('update', $this->getOwnerRecord()) ?? false;
    }

    protected function canDelete(Model $record): bool
    {
        return auth()->user()?->can('update', $this->getOwnerRecord()) ?? false;
    }
}
