<?php

namespace App\Filament\Resources\Attendance\AttendanceRecordResource\Pages;

use App\Filament\Resources\Attendance\AttendanceRecordResource;
use App\Models\Attendance\AttendanceRecord as AttendanceAttendanceRecord;
use App\Models\Attendance\AttendanceType;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\c;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;

class AttendanceRecord extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AttendanceRecordResource::class;

    protected static ?string $title = 'Attendance';

    protected static string $view = 'filament.resources.attendance.attendance-record-resource.pages.attendance-record';


    public ?array $data = [];
    #[Rule('required|gt:out')]
    public $in;
    #[Rule('required')]
    public $out;
    #[Rule('required')]
    public $attendanceTypeId;
    public $attendanceTypes;
    #[Rule('required')]
    public $status = 'pending';
    public $statuses = [];
    public $reason;
    public $users;
    public function mount(): void
    {
        $this->attendanceTypes = AttendanceType::all()->pluck('name', 'id');
        $this->statuses = [
            'Pending' => 'pending',
            'Approved' => 'approved',
            'Rejected' => 'rejected'
        ];
        $this->users = User::whereNotIn('id', [auth()->id()])->pluck('name', 'id');
        // $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('user_id')
                    ->reactive()

                    ->afterStateUpdated(function (?string $state, ?string $old, Get $get) {
                        //Get the last record
                        $lastRecord = AttendanceAttendanceRecord::where('user_id', $state)->where('attendance_type_id', $this->attendanceTypeId)->orderBy('updated_at', 'desc')->first();
                        if (!is_null($lastRecord)) {

                            if (is_null($lastRecord->out)) {

                                $this->form->fill($lastRecord->toArray());
                            } else {
                                $this->form->fill([
                                    'user_id' => $get('user_id'),
                                    'reason' => $get('reason')
                                ]);
                            }
                        } else {
                            $this->form->fill([
                                'user_id' => $get('user_id'),
                                'reason' => $get('reason')
                            ]);
                        }
                    })
                    ->label('User')
                    ->options(User::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('attendance_type_id')
                    ->options($this->attendanceTypes)
                    ->default($this->attendanceTypeId)
                    ->required()
                    ->hidden()
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('reason'),
                DateTimePicker::make('in')
                ->seconds(false)
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if ($get('out')) {
                                $out = Carbon::create($get('out'));
                                if ($value) {
                                    $in = Carbon::create($value);
                                }
                                if (!$in->lt($out)) {
                                    $fail("Please select a date that is less than out.");
                                }
                            }
                        },
                    ])
                    ->disabled(function(Get $get){
                        $lastRecord = AttendanceAttendanceRecord::where('user_id',  $get('user_id'))->where('attendance_type_id', $this->attendanceTypeId)->orderBy('updated_at', 'desc')->first();
                        if (!is_null($lastRecord)) {
                            if (!is_null($lastRecord->in) && is_null($lastRecord->out)) {
                                return true;
                            }
                        }
                    })
                    ->dehydrated()
                    ->required(),
                DateTimePicker::make('out')
                ->seconds(false)
                    ->required(function (Get $get) {
                        $lastRecord = AttendanceAttendanceRecord::where('user_id',  $get('user_id'))->where('attendance_type_id', $this->attendanceTypeId)->orderBy('updated_at', 'desc')->first();
                        if (!is_null($lastRecord)) {
                            if (is_null($lastRecord->out)) {
                                return true;
                            }
                        }
                    })
                    ->hidden(function (Get $get) {
                        $lastRecord = AttendanceAttendanceRecord::where('user_id',  $get('user_id'))->where('attendance_type_id', $this->attendanceTypeId)->orderBy('updated_at', 'desc')->first();
                        if (!is_null($lastRecord)) {
                            if (!is_null($lastRecord->out)) {
                                return true;
                            }
                        }else{
                            return true;
                        }
                    })
                    // ->dehydrated(function (Get $get) {
                    //     $lastRecord = AttendanceAttendanceRecord::where('user_id',  $get('user_id'))->where('attendance_type_id', $this->attendanceTypeId)->orderBy('updated_at', 'desc')->first();
                    //     if (!is_null($lastRecord)) {
                    //         if (!is_null($lastRecord->out)) {
                    //             return true;
                    //         }
                    //     }
                    // })

                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if ($get('in')) {
                                $in = Carbon::create($get('in'));
                                if ($value) {
                                    $out = Carbon::create($value);
                                }
                                if (!$in->lt($out)) {
                                    $fail("Please select a date that is greater than in.");
                                }
                            }
                            $attendanceTypeRecord=AttendanceType::find($this->attendanceTypeId);
                           if($attendanceTypeRecord->name=='work'){

                            $lastBreakRecord=AttendanceAttendanceRecord::where('user_id',  $get('user_id'))->whereNotIn('attendance_type_id', [$this->attendanceTypeId])->orderBy('updated_at', 'desc')->first();
if($lastBreakRecord){
                            if (!$lastBreakRecord->out) {
                                Notification::make()
                                    ->title("You can't logout without finishing the break.")
                                    ->success()
                                    ->send();
                                $fail("");
                            }}
                        }
                        },
                    ])

                // ...
                ->disabledOn('edit')
            ])
            ->statePath('data');
    }
    #[On('setOutDateTime')]
    public function setOutDateTime($dateTime)
    {
        $this->out = $dateTime;
    }
    #[On('setInDateTime')]

    public function setInDateTime($dateTime)
    {
        $this->in = $dateTime;
    }
    public function create(): void
    {
        $data = $this->form->getState();

        $lastRecord = AttendanceAttendanceRecord::where('user_id', $data['user_id'])->where('attendance_type_id', $this->attendanceTypeId)->orderBy('updated_at', 'desc')->first();
        if (!is_null($lastRecord)) {
            if (is_null($lastRecord->out)) {
                $in = Carbon::parse($data['in']);
                $out = Carbon::parse($data['out']);
                $data['total_hours'] = $in->diffInHours($out);
                // set total hours
                if ($data['in'] && $data['out']) {
                    $in = Carbon::parse($data['in']);
                    $out = Carbon::parse($data['out']);
                    $data['total_hours'] = $in->diffInHours($out);
                }
                AttendanceAttendanceRecord::where('id', $lastRecord->id)->update($data);
            } else {

                $data = $this->form->getState();
                $data['attendance_type_id'] = $this->attendanceTypeId;

                // set total hours
                // if ($data['in'] && $data['out']) {
                //     $in = Carbon::parse($data['in']);
                //     $out = Carbon::parse($data['out']);
                //     $data['total_hours'] = $in->diffInHours($out);
                // }}
                if(auth()->user()->hasRole('HR')){
                    $data['status'] = 'approved';
                   }
            if(auth()->user()->hasRole('Super Admin')){
                $data['status'] = 'approved';
            }
                AttendanceAttendanceRecord::create(
                    $data
                );
            }
            }
         else {

            $data = $this->form->getState();
            $data['attendance_type_id'] = $this->attendanceTypeId;
            // set total hours
            // if ($data['in'] && $data['out']) {
            //     $in = Carbon::parse($data['in']);
            //     $out = Carbon::parse($data['out']);
            //     $data['total_hours'] = $in->diffInHours($out);
            // }
            if(auth()->user()->hasRole('HR')){
                $data['status'] = 'approved';
               }
        if(auth()->user()->hasRole('Super Admin')){
            $data['status'] = 'approved';
        }
            AttendanceAttendanceRecord::create(
                $data
            );
        }


        $this->form->fill();

        $this->dispatch('close-modal', id: 'createAttendance');
    }
    public function openAttendanceRecord($attendanceType)
    {
        $this->attendanceTypeId = $attendanceType;


        $this->form->fill();

        $this->dispatch('open-modal', id: 'createAttendance');
    }
}
