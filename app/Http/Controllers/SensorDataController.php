<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;

class SensorDataController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'humidity' => 'required|numeric',
            'temperature' => 'required|numeric',
            'smoke' => 'required|integer',
        ]);

        $sensorData = SensorData::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diterima dan disimpan',
            'data' => $sensorData,
        ]);
    }

    public function index()
    {
        $data = SensorData::orderBy('created_at', 'desc')->limit(1)->get(); // Fetch only the latest record
        return response()->json($data);
    }
    
}