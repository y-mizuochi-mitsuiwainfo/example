<?php
namespace App\Filament\Resources\TimeRecordResource\Pages;

use App\Filament\Resources\TimeRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimeRecords extends ListRecords
{
    protected static string $resource = TimeRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('登録'),//追加でボタン変更
        ];
    }
    public function getCreateButtonLabel(): string
    {
        return '登録';
    }
}
