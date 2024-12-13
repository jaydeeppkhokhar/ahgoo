<?php

namespace App\Exports;

use App\Models\Events;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Events::all(['_id', 'event_type', 'event_name', 'event_subtitle', 'event_description', 'event_date', 'event_end_date', 'duration', 'cover_pic', 'location', 'event_category', 'estimated_size', 'name_of_audience', 'age_from', 'age_to', 'gender', 'audience_location', 'web_address', 'per_day_spent', 'total_days', 'total_amount', 'payment_method']); // Add other necessary fields here
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Type',
            'Name',
            'Subtitle',
            'Description',
            'Start Date',
            'End Date',
            'Duration',
            'Cover Pic',
            'Location',
            'Event Category',
            'Estimated_size',
            'Audience Name',
            'Age Form',
            'Age To',
            'Gender',
            'Audience Location',
            'Web Address',
            'Per Day Spent',
            'Total Days',
            'Total Amount'
            // Add other necessary headers here
        ];
    }
}
