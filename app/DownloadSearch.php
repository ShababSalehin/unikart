<?php

namespace App;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadSearch implements FromCollection, WithMapping, WithHeadings
{
 
    public function collection()
    {
        $searches = Search::orderBy('count', 'desc')->get();

        return collect($searches);
    }

    public function headings(): array
    {
        return [
            'Seller Name',
            'Number searches',
        ];
    }

    public function map($searches): array
    {

            return [
                $searches->query,
                $searches->count,
            ];
    }
}