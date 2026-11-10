<?php

namespace App\Filament\Resources\Media\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('پیش‌نمایش')
                    ->disk(fn ($record) => $record->isPrivate() ? null : $record->disk)
                    ->visibility('public')
                    ->square()
                    ->defaultImageUrl(null)
                    ->getStateUsing(fn ($record) => $record->isImage() && ! $record->isPrivate() ? $record->path : null),
                TextColumn::make('original_name')->label('نام')->searchable(),
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('folder.name')->label('پوشه'),
                TextColumn::make('category')->label('دسته')->badge(),
                TextColumn::make('visibility')->label('دسترسی')->badge(),
                TextColumn::make('mime_type')->label('نوع')->toggleable(),
                TextColumn::make('size')->label('حجم')->formatStateUsing(fn ($state) => number_format(((int) $state) / 1024, 1).' KB'),
                TextColumn::make('created_at')->label('تاریخ')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_id')->label('سایت')->relationship('site', 'name'),
                SelectFilter::make('category')->label('دسته')->options([
                    'image' => 'تصویر',
                    'document' => 'سند',
                    'attachment' => 'پیوست',
                    'other' => 'سایر',
                ]),
                SelectFilter::make('visibility')->label('دسترسی')->options([
                    'public' => 'عمومی',
                    'private' => 'خصوصی',
                ]),
                SelectFilter::make('folder_id')->label('پوشه')->relationship('folder', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('downloadPrivate')
                    ->label('دانلود خصوصی')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn ($record) => $record->isPrivate())
                    ->url(fn ($record) => route('media.private.download', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
