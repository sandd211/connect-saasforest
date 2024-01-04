<?php

namespace App\Filament\Resources\Attendance\AttendanceRecordResource\Pages;

use App\Filament\Resources\Attendance\AttendanceRecordResource;
use App\Models\Attendance\AttendanceRecord;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceRecord extends EditRecord
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['in'] && $data['out']) {
            $in = Carbon::parse($data['in']);
            $out = Carbon::parse($data['out']);
            $data['total_hours'] = $in->diffInHours($out);

        }

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
