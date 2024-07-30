<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Contact::select('first_name', 'last_name', 'phone', 'email')
            ->where('organization_id', session()->get('current_organization'))
            ->whereNull('deleted_at')
            ->get();
    }

    public function headings(): array
    {
        // Define your headers here
        return [
            'first_name',
            'last_name',
            'phone',
            'email',
        ];
    }
}
