<?php

namespace App\Filament\Resources\TimeRecordResource\Pages;

use App\Filament\Resources\TimeRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeRecord extends CreateRecord
{
    protected static string $resource = TimeRecordResource::class;

    public function getTitle(): string
    {
        return '工数 登録';
    }

    protected function getCreateButtonLabel(): string
    {
        return '登録';
    }

    protected function getFormActions(): array //メソッド
    {
        return[
            $this->getCreateFormAction()//オーバーロード(関数)
            ->label('保存'),

            $this->getCancelFormAction()
            ->label('戻る')
        ]; 
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }

}
