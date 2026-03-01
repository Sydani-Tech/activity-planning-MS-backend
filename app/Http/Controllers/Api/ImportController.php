<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ActivitiesImport;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new ActivitiesImport($request->user()->id);
            Excel::import($import, $request->file('file'));

            return response()->json([
                'message' => 'Import completed successfully.',
                'rows_imported' => $import->getRowCount(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
