<?php

namespace App\Filament\Resources\Timesheet\ProjectResource\Pages;

use App\Filament\Resources\Timesheet\ProjectResource;
use App\Models\Employee\Team;
use App\Models\User;
// use Filament\Actions\Modal\Actions\Action;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
    
    protected function afterCreate(): void
    {
        $record=$this->getRecord();
        // dd($record);
;        $project=User::whereHas('jobInfo.team',function($query) use ($record){
    $query->where('id',$record->team_id);
   
})->get();
// dd($project);
     foreach($project as $projects){
        $recipient = User::where('id', $projects->id)->get();
        //   dd($recipient);
       Notification::make()
           ->title('New project is assigned to your team ')
           ->actions([
            Action::make('view')
                ->button()->url('/timesheet/projects/'.$record->id.'/edit')
                ->close()
           ])
           ->sendToDatabase($recipient);
           
        
     }
      
        }
   
}
