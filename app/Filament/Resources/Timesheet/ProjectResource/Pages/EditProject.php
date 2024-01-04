<?php

namespace App\Filament\Resources\Timesheet\ProjectResource\Pages;

use App\Filament\Resources\Timesheet\ProjectResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        $record=$this->getRecord()
;        $project=User::whereHas('jobInfo.team',function($query) use ($record){
    $query->where('id',$record->team_id);
})->get();
     foreach($project as $projects){
        $recipient = User::where('id', $projects->id)->get();
        //   dd($recipient);
       Notification::make()
           ->title('New project is assigned to your team ')
           ->sendToDatabase($recipient);   
        
     }
      
        }
}
