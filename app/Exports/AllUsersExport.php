<?php

namespace App\Exports;

use App\Models\AllUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllUsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AllUser::select([
            '_id',
            'name',
            'email',
            'phone',
            'country',
            'profile_pic',
            'cover_pic',
            'profile_summary',
            'username',
            'dob',
            'gender',
            'hobby1'
            // Add other necessary fields here
        ])->get();
    }
    public function headings(): array
    {
        return [
            '_id',
            'Name',
            'Email',
            'Phone No',
            'Country',
            'Profile Pic',
            'Cover Pic',
            'Profile Summary',
            'Username',
            'DOB',
            'Gender',
            'Hobby'
            // Add other necessary fields here
        ];
    }
}
