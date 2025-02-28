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
        try {
            $user = Auth::user();
            $student = Students::where('user_id', $user->id)->first();
            $disbursement = null;

            if ($student) {
                $disbursement = Disbursements::where('student_id', $student->id)->first();
            }

            return view('layouts.ta.disbursements', compact('disbursement'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
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

            if ($disbursement->uploadfile && Storage::disk('public')->exists($disbursement->uploadfile)) {
                Storage::disk('public')->delete($disbursement->uploadfile);
            }

            if ($request->hasFile('uploadfile')) {
                $file = $request->file('uploadfile');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('assets/fileUploads', $fileName, 'public');
                $disbursement->uploadfile = $path;
            }

            $disbursement->bookbank_id = $request->bookbank_id;
            $disbursement->bank_name = $request->bank_name;
            $disbursement->student_id = $student->id;
            $disbursement->applicant_type = $request->applicant_type;

            $disbursement->save();

            return redirect()->route('layout.ta.disbursements')->with('success', 'อัปโหลดเอกสารเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function downloadDocument($id)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Please log in to access this page.');
            }
    
            $user = Auth::user();
    
            $student = Students::where('user_id', $user->id)->first();
            if (!$student) {
                return redirect()->back()->with('error', 'Student record not found.');
            }
    
            $disbursement = Disbursements::findOrFail($id);
    
            if ($disbursement->student_id !== $student->id) {
                return redirect()->back()->with('error', 'You do not have permission to access this document.');
            }
    
            if (!Storage::disk('public')->exists($disbursement->uploadfile)) {
                return back()->with('error', 'File not found.');
            }
    
            return Storage::disk('public')->download($disbursement->uploadfile);
    
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while downloading the document.');
        }
    }
}