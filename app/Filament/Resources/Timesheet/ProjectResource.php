<?php

namespace App\Filament\Resources\Timesheet;

use App\Filament\Resources\Timesheet\ProjectResource\Pages;
use App\Filament\Resources\Timesheet\ProjectResource\RelationManagers;
use App\Models\Timesheet\Project;
use App\Models\Timesheet\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Dompdf\FrameDecorator\Text;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                ->schema([
                    TextInput::make('name')
                    ->required()->disabled(function(){
                        if(!auth()->user()->hasRole('Supervisor')){
                            return true;
                        }
                    }),
                    Select::make('team_id')
                    ->relationship('teams', 'name')->required()->disabled(function(){
                        if(!auth()->user()->hasRole('Supervisor')){
                            return true;
                        }
                    }),


                    DatePicker::make('start_date')
                    ->native(false)
                    ->minDate(today())
                    ->suffixIcon('heroicon-m-calendar')
                    ->label('Start date')->required()
                    ->disabled(function(){
                        if(!auth()->user()->hasRole('Supervisor')){
                            return true;
                        }
                    })
                    ,
                    DatePicker::make('end_date')->minDate(function (Get $get) {
                        $StartDate = $get('start_date');
                        return $StartDate ? Carbon::parse($StartDate) : now();
                    })
                    ->native(false)
                    ->suffixIcon('heroicon-m-calendar')
                    ->label('End date')->required()
                    ->disabled(function(){
                        if(!auth()->user()->hasRole('Supervisor')){
                            return true;
                        }
                    })
                        ,

                        Textarea::make('description')
                        ->required()
                        ->columnSpanFull()
                        ->disabled(function(){
                            if(!auth()->user()->hasRole('Supervisor')){
                                return true;
                            }
                        })
                            ,
                ])->columns('2')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

        // ->modifyQueryUsing(fn (Builder $query) => $query->where('team_id',User::where('id', auth()->id())->with('team')->get()[0]->team->id))
          ->modifyQueryUsing(function(){
            if(auth()->user()->hasRole('Supervisor')){
               return ;
            }else{
                return Project::where('team_id',User::where('id', auth()->id())->with('team')->get()[0]->team->id);
            }
          })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->sortable()
                ->searchable()
                ->toggleable(),

                Tables\Columns\TextColumn::make('teams.name')
                ->sortable()
                ->searchable()
                ->toggleable(),

                Tables\Columns\TextColumn::make('start_date')
                ->sortable()
                ->searchable()
                ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                ->sortable()
                ->searchable()
                ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->sortable()
                ->searchable()
                ->toggleable(),
            ])
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(function(){
                    if(auth()->user()->hasRole('Supervisor')){
                        return true;
                    }
                })

                ->after(function(Project $record)
                {

                   $recipient = User::where('id', $record->id)->get();
                    //   dd($recipient);
                   Notification::make()
                       ->title('You have been delete a task')
                       ->sendToDatabase($recipient);



                }),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->visible(function(){
                    if(auth()->user()->hasRole('Supervisor')){
                        return true;
                    }
                })
            ]);
    }






    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
