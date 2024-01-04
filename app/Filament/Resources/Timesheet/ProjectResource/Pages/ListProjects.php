<?php

namespace App\Filament\Resources\Timesheet\ProjectResource\Pages;

use App\Filament\Resources\Timesheet\ProjectResource;
use App\Models\Employee\Team;
use App\Models\Timesheet\Project;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make() ->visible(function(){
                if(auth()->user()->hasRole('Supervisor')){
                    return true;
                }
            }),
        ];
    }

}
