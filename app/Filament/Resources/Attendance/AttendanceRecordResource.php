<?php

namespace App\Filament\Resources\Attendance;

use App\Filament\Resources\Attendance\AttendanceRecordResource\Pages;
use App\Filament\Resources\Attendance\AttendanceRecordResource\RelationManagers;
use App\Forms\Components\UserTimezone;
use App\Models\Attendance\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Closure;

use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tapp\FilamentTimezoneField\Forms\Components\TimezoneSelect;

class AttendanceRecordResource extends Resource
{

    protected static ?string $model = AttendanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';



    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Card::make([
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->required()
                        ->disabledOn('edit')
                        ->dehydrated(),
                    Select::make('attendance_type_id')
                        ->relationship('attendanceType', 'name')
                        ->required()
                        ->disabledOn('edit')
                        ->dehydrated(),

                    TextInput::make('reason'),
                    DateTimePicker::make('in')
                    ->seconds(false)
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if($get('out')){
                                $out=Carbon::create($get('out'));
                                if($value){
                                    $in=Carbon::create($value);
                                }
                                if ( !$in->lt($out)) {
                                    $fail("Please select a date that is less than out.");
                                }
                            }


                            },
                        ])
                        ->required(),
                    DateTimePicker::make('out')
                    ->seconds(false)
                        ->suffix(function (Get $get) {
                            $timezone = $get('timezone');
                            return $timezone;
                            // return self::getOffset($timezone);
                        })
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if($get('in')){
                                $in=Carbon::create($get('in'));
                                if($value){
                                    $out=Carbon::create($value);
                                }
                                if ( !$in->lt($out)) {
                                    $fail("Please select a date that is greater than in.");
                                }
                            }

                            },
                        ])


                ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('attendanceType.name'),
                TextColumn::make('status'),
                TextColumn::make('in'),
                TextColumn::make('out'),
                TextColumn::make('total_hours'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('approve')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible( auth()->user()->hasRole('HR') || auth()->user()->hasRole('Super Admin'))
                ->hidden(function(AttendanceRecord $record){

                    if (is_null($record->out)||$record->status == 'approved' || $record->status == 'rejected' ) {
                        return true;
                    }


                    })
                ->action(function (AttendanceRecord $record, array $data): void {
                    AttendanceRecord::find($record->id)->update([
                        'status' => 'approved',

                    ]);
                }),
            Action::make('reject')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible( auth()->user()->hasRole('HR') || auth()->user()->hasRole('Super Admin'))
                ->hidden(function(AttendanceRecord $record){
                    if (is_null($record->out)||$record->status == 'approved' || $record->status == 'rejected' ) {
                        return true;
                    }

                })
                ->action(function (AttendanceRecord $record, array $data): void {
                    AttendanceRecord::find($record->id)->update([
                        'status' => 'rejected',
                    ]);
                })
               ,
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])

            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (auth()->user()->hasRole('HR') || auth()->user()->hasRole('Super Admin')) {

            return $query;
        } else {

            $query->where('user_id', auth()->user()->id);
            return $query;
        }

    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceRecords::route('/'),
            'create' => Pages\AttendanceRecord::route('/create'),
            'edit' => Pages\EditAttendanceRecord::route('/{record}/edit'),
        ];
    }
}
