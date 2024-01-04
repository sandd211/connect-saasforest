<?php

namespace App\Filament\Resources\Employee\EmployeeResource\Pages;

use App\Filament\Resources\Employee\EmployeeResource;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;

class ListEmployees extends ListRecords
{

    protected static string $resource = EmployeeResource::class;

    protected static ?string $title = 'Employees';

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Employee'),
        //    ExcelImportAction::make()
        //    ->use(\App\Imports\EmployeeImport::class)
        //     ->color("primary")
        //    ,
           Action::make('import')
           ->hidden(!auth()->user()->hasPermissionTo('Employee Profiles'))
           ->icon('heroicon-o-arrow-down-tray')
           ->color('primary')
           ->url(fn (): string => route('filament.admin.resources.employees.import'))
        ];
    }


    protected function getTableContentGrid(): ?array
{
    return [
        'md' => 2,
        'xl' => 3,
    ];

}
}
