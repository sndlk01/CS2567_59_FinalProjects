<?php

namespace App\Http\Controllers;

use App\Models\CompensationRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompensationRateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $rates = CompensationRate::orderBy('teaching_type')
            ->orderBy('class_type')
            ->get();

        return view('layouts.admin.compensation-rates.index', compact('rates'));
    }

    public function create()
    {
        return view('layouts.admin.compensation-rates.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'teaching_type' => 'required|in:regular,special',
                'class_type' => 'required|in:LECTURE,LAB',
                'rate_per_hour' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive'
            ]);

            CompensationRate::create($request->all());

            return redirect()->route('admin.compensation-rates.index')
                ->with('success', 'อัตราค่าตอบแทนถูกเพิ่มเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            Log::error('Error creating compensation rate: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มอัตราค่าตอบแทน: ' . $e->getMessage());
        }
    }

    public function edit(CompensationRate $rate)
    {
        return view('layouts.admin.compensation-rates.edit', compact('rate'));
    }

    public function update(Request $request, CompensationRate $rate)
    {
        try {
            $request->validate([
                'rate_per_hour' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive'
            ]);

            $rate->update([
                'rate_per_hour' => $request->rate_per_hour,
                'status' => $request->status
            ]);

            return redirect()->route('admin.compensation-rates.index')
                ->with('success', 'อัตราค่าตอบแทนถูกอัปเดตเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            Log::error('Error updating compensation rate: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาดในการอัปเดตอัตราค่าตอบแทน: ' . $e->getMessage());
        }
    }
}