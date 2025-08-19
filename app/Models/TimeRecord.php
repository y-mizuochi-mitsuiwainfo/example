<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_datetime',
        'end_datetime',
        'project_name',
        'task_name',
    ];

    // ← ここをプロパティにする
    protected $casts = [
        'id' => 'integer',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    // （工数アクセサはそのままでOK）
    public function getWorkTimeAttribute(): ?string
    {
        if ($this->start_datetime && $this->end_datetime) {
            $start = $this->start_datetime;
            $end = $this->end_datetime;

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }
            return $start->format('H:i') . ' ～ ' . $end->format('H:i');
        }
        return null;
    }
    // app/Models/TimeRecord.php

public function getWorkDurationAttribute(): ?float
    {
        if ($this->start_datetime && $this->end_datetime) {//thisでstart_datetime参照かつnullチェック
            $start = $this->start_datetime;
            $end = $this->end_datetime;

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }

            $minutes = $start->diffInMinutes($end);//差分
            $hours = round(($minutes / 60) * 2) / 2; // 0.5刻みの四捨五入
 // 小数第2位で四捨五入→第1位まで表示 roundでもok
            if ($hours == 0) {
            $hours = 0.5;
        }

            return $hours;
        }

        return null;//値が無ければ
    }


}
