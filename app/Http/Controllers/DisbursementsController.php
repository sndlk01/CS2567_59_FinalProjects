<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disbursements;
use App\Models\Students;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DisbursementsController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function disbursements()
    {
        return view('layouts.ta.disbursements');
    }

    public function uploads(Request $request)
    {
        try {
            $user = Auth::user();
            $student = Students::where('user_id', $user->id)->first();

            if (!$student) {
                return redirect()->back()->with('error', 'ไม่พบข้อมูลนักศึกษา กรุณาติดต่อผู้ดูแลระบบ');
            }

            $request->validate([
                'uploadfile' => 'required|file|mimes:pdf,doc,docx|max:2048',
                'bookbank_id' => 'required|string',
                'bank_name' => 'required|string|not_in:เลือกบัญชีธนาคาร',
                'applicant_type' => 'required|in:0,1',
            ]);

            $disbursement = Disbursements::firstOrNew(['student_id' => $student->id]);
            
            if ($request->hasFile('uploadfile')) {
                $path = $request->file('uploadfile')->store('assets/fileUploads', 'public');
                $disbursement->uploadfile = $path;
            }

            $disbursement->bookbank_id = $request->bookbank_id;
            $disbursement->bank_name = $request->bank_name;
            $disbursement->student_id = $student->id;
            $disbursement->applicant_type = $request->applicant_type;
            
            $disbursement->save();

            return redirect()->back()->with('success', 'อัปโหลดเอกสารเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    
    }
}