<?php

namespace App\Filament\Resources\TimeRecordResource\Pages;

use App\Filament\Resources\TimeRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeRecord extends EditRecord
{
    protected static string $resource = TimeRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        // 編集後にリダイレクトするURL（一覧ページ）
        return TimeRecordResource::getUrl('index');
    }

    public function getTitle(): string
    {
        return '工数 登録';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('保存'),

            $this->getCancelFormAction()
                ->label('戻る'),
        ];
    }
}
