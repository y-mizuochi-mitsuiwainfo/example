<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeRecordResource\Pages;
use App\Filament\Resources\TimeRecordResource\RelationManagers;
use App\Models\TimeRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\Carbon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;

class TimeRecordResource extends Resource
{
    protected static ?string $label = '工数実績一覧';
    protected static ?string $pluralLabel = '工数実績一覧';

    protected static ?string $model = TimeRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form //工数登録フォーム
{
    $stepMinutes = (int) Config::get('time.work_minutes_step', 10);
    $defaultWorkMinutes = (int) Config::get('time.default_work_minutes', 30);

    $now = now('Asia/Tokyo');
    $minute = (int) $now->format('i');
    $ceilMinute = ceil($minute / $stepMinutes) * $stepMinutes;

    // 60分を超える場合は、次の時間に切り上げ
    if ($ceilMinute >= 60) {
        $now->addHour()->minute(0);
    } else {
        $now->minute($ceilMinute)->second(0);
    }

    $defaultStartTime = $now->format('H:i');
    $defaultEndTime = $now->copy()->addMinutes($defaultWorkMinutes)->format('H:i');

    return $form->schema([
        Section::make()->schema([
            DatePicker::make('work_date')
                ->label('作業日')
                ->required()
                ->default(now())
                ->afterStateUpdated(function (Set $set, Get $get, $state) { //作業日が更新されたときに、start_datetimeを更新
                    $startTime = $get('start_time');
                    $endTime = $get('end_time');
                    if ($startTime) {
                        $set('start_datetime', "{$state} {$startTime}");
                    }
                    if ($endTime) {
                        $set('end_datetime', "{$state} {$endTime}");
                    }
                })
                ->afterStateHydrated(function (Set $set, Get $get) { // 既存データ読み込み時
                    //データベースから値を読み込む際、start_datetime から日付部分を取り出して work_date にセットもし空なら今日の日付をセット
                    $start = $get('start_datetime');
                    if ($start) {
                        $set('work_date', \Carbon\Carbon::parse($start)->toDateString());
                    }else {
                        // start_datetime が空なら、work_date に今日をセット
                        $set('work_date', now()->toDateString()); 
                    }
                }),

            Grid::make(2)->schema([ 
                TimePicker::make('start_time') 
                    ->label('開始時間') 
                    ->seconds(false) 
                    ->default($defaultStartTime) 
                    ->minutesStep($stepMinutes) 
                    ->required() 
                    ->live() 
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $date = $get('work_date');
                        if ($date) {
                            $set('start_datetime', "{$date} {$state}");
                        }
                    })
                    ->afterStateHydrated(function (Set $set, Get $get) {
                        $start = $get('start_datetime');
                        if ($start) {
                            $set('start_time', Carbon::parse($start)->format('H:i'));
                        }
                    }),

                TimePicker::make('end_time')
                    ->label('終了時間')
                    ->seconds(false)
                    ->default(fn (Get $get) => $get('end_time') ?? $defaultEndTime)
                    ->minutesStep($stepMinutes)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $date = $get('work_date');
                        if ($date) {
                            $set('end_datetime', "{$date} {$state}");
                        }
                    })
                    ->afterStateHydrated(function (Set $set, Get $get) {
                        $end = $get('end_datetime');
                        if ($end) {
                            $set('end_time', Carbon::parse($end)->format('H:i'));
                        }
                    }),
                ]),

            TextInput::make('project_name')
                ->label('プロジェクト名')
                ->maxLength(25)
                ->default(Request::get('project_name'))
                ->required(),

            TextInput::make('task_name')
                ->label('タスク名')
                ->default(Request::get('task_name'))
                ->maxLength(25),

            // Hiddenで本来保存される値を定義
            Hidden::make('start_datetime')->dehydrated()->required()->live(),
            Hidden::make('end_datetime')->dehydrated()->required()->live()
            ->rule(function (Get $get) {
                return function (string $attribute, $value, \Closure $fail) use ($get) {
                    $start = $get('start_datetime');
                    $end = $value;

                    try {
                        $startCarbon = \Carbon\Carbon::parse($start);
                        $endCarbon = \Carbon\Carbon::parse($end);
                    } catch (\Exception $e) {
                        $fail('日時の形式が正しくありません。');
                        return;
                    }

                    if ($startCarbon->gte($endCarbon)) {
                        $fail('終了時間は開始時間より後である必要があります。');
                    }
                };
            }),
        ]),
    ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('start_datetime')
            ->label('作業時間')
            ->sortable()
            ->formatStateUsing(function ($state, $record) {
                if (! $record || ! ($record instanceof \Illuminate\Database\Eloquent\Model)) {
                    return '―';
                }

                $start = $record->start_datetime;
                $end = $record->end_datetime;

                if (! $start || ! $end) {
                    return '―';
                }

                // 開始が終了より遅ければ入れ替え
                if ($start->gt($end)) {
                    [$start, $end] = [$end, $start];
                }

                return $start->format('H:i') . ' - ' . $end->format('H:i');
            }),

            Tables\Columns\TextColumn::make('project_name')
                ->label('プロジェクト名')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('task_name')
                ->label('タスク名')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('work_duration')
                ->label('工数')
                ->sortable(),
            ])
            ->filters([
            Tables\Filters\Filter::make('work_date_filter') 
                ->label('作業日')
                ->form([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('開始日')
                        ->default(now()),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('終了日')
                        ->default(now()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['start_date'], fn ($q) => $q->whereDate('start_datetime', '>=', $data['start_date']))
                        ->when($data['end_date'], fn ($q) => $q->whereDate('start_datetime', '<=', $data['end_date']));
                }),
            ])

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('copyToNew')
                        ->label('参照登録')
                        ->icon('heroicon-o-document-duplicate')
                        
                        ->action(function ($record, $livewire) {
                            return redirect()->route('filament.admin.resources.time-records.create', [
                                'project_name' => $record->project_name,
                                'task_name' => $record->task_name,
                            ]);
                        }),
                    Action::make('stopwatchRegister')
                        ->label('ストップウォッチ参照登録')
                        ->icon('heroicon-o-clock') 
                        ->disabled() // 後で～
                    ])
                ->icon('heroicon-o-ellipsis-horizontal'),  
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTimeRecords::route('/'),
            'create' => Pages\CreateTimeRecord::route('/create'),
            'edit' => Pages\EditTimeRecord::route('/{record}/edit'),
        ];
    }
}
