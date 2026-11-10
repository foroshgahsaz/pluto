<?php

namespace App\Filament\Resources\Forms\RelationManagers;

use App\Models\Form;
use App\Services\Forms\ExportFormSubmissionsService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'پاسخ‌های فرم';

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id')->label('شناسه'),
            TextEntry::make('created_at')->label('زمان')->dateTime('Y-m-d H:i'),
            TextEntry::make('user.name')->label('کاربر')->placeholder('-'),
            TextEntry::make('payload_json')
                ->label('داده')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string) $state)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('شناسه')->sortable(),
                TextColumn::make('created_at')->label('زمان')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('user.name')->label('کاربر')->placeholder('-'),
                TextColumn::make('ip')->label('IP')->toggleable(),
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label('خروجی CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(function (): bool {
                        /** @var Form $form */
                        $form = $this->getOwnerRecord();

                        return auth()->user()?->can('exportSubmissions', $form) ?? false;
                    })
                    ->action(function () {
                        /** @var Form $form */
                        $form = $this->getOwnerRecord();

                        activity('forms')
                            ->causedBy(auth()->user())
                            ->performedOn($form)
                            ->withProperties(['site_id' => $form->site_id])
                            ->log('form_submissions.exported');

                        return app(ExportFormSubmissionsService::class)->toCsvResponse($form);
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('viewSubmissions', $this->getOwnerRecord()) ?? false),
                DeleteAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('deleteSubmissions', $this->getOwnerRecord()) ?? false),
            ])
            ->defaultSort('id', 'desc');
    }

    protected function canView(Model $record): bool
    {
        return auth()->user()?->can('viewSubmissions', $this->getOwnerRecord()) ?? false;
    }

    protected function canDelete(Model $record): bool
    {
        return auth()->user()?->can('deleteSubmissions', $this->getOwnerRecord()) ?? false;
    }
}
