<x-filament-panels::page>
    <script src=" https://cdn.jsdelivr.net/npm/luxon@3.4.3/build/global/luxon.min.js "></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="flex justify-between ">
        @foreach ($attendanceTypes as $attendanceTypeKey=>$attendanceTypeValue)
        <div class="flex  py-2 px-4 gap-10 border rounded-sm  border-gray-300">
            <p class="pr-2 py-1 capitalize">{{$attendanceTypeValue}}</p>
            <div class="flex gap-2">
                <p class="bg-green-500 text-white px-4 py-1 rounded-md cursor-pointer"
                    wire:click="openAttendanceRecord({{$attendanceTypeKey}})">Check-in</p>
                <p class="bg-red-500 text-white px-4 py-1 rounded-md cursor-pointer" wire:click="openAttendanceRecord({{$attendanceTypeKey}})">Check-out</p>
            </div>
        </div>
        @endforeach

        <x-filament::modal id="createAttendance" width="xl">
            <x-slot name="heading">
               Create Attendance Record
            </x-slot>
            <form wire:submit="create">
                {{ $this->form }}

                <div class="mt-4 flex gap-2">

                    <button type="submit" class="px-3 py-1 bg-[#B39800]  rounded-md text-white">
                        Submit
                       </button>
                       <div class="px-3 py-1 border border-gray-300 rounded-md" @click="$dispatch('close-modal', { id: 'createAttendance' })"> Cancel</div>
                   </div>
            </form>

    </x-filament::modal>
    </div>



</x-filament-panels::page>
