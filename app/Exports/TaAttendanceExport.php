<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaAttendanceExport implements FromCollection, WithTitle, ShouldAutoSize, WithStyles, WithColumnWidths, WithEvents
{
    protected $attendances;
    protected $compensation;
    protected $student;
    protected $semester;
    protected $selectedYearMonth;
    protected $teacherName;

    public function __construct($attendances, $compensation, $student, $semester, $selectedYearMonth, $teacherName = '')
    {
        $this->attendances = $attendances;
        $this->compensation = $compensation;
        $this->student = $student;
        $this->semester = $semester;
        $this->selectedYearMonth = $selectedYearMonth;
        $this->teacherName = $teacherName;
    }

    public function collection()
    {
        // สร้าง Collection เปล่าสำหรับส่งออก
        $data = new Collection();
        
        // เพิ่มข้อมูลหัวเรื่อง (จะถูกจัดรูปแบบในส่วนของ styles)
        for ($i = 1; $i <= 6; $i++) {
            $data->push(['']);
        }
        
        // เพิ่มหัวข้อตาราง
        $data->push([
            'ลำดับที่',
            'ชื่อ-สกุล',
            'ระดับ',
            'ว/ด/ป',
            'รหัสวิชา',
            'เวลาสอน',
            'บรรยาย',
            'ปฏิบัติการ',
            'หมายเหตุ'
        ]);
        
        // แปลงข้อมูลการสอนให้เป็นแถวสำหรับตาราง
        $rowNumber = 1;
        $totalLectureHours = 0;
        $totalLabHours = 0;
        
        $flatAttendances = collect();
        foreach ($this->attendances as $section => $attendanceGroup) {
            foreach ($attendanceGroup as $attendance) {
                $flatAttendances->push($attendance);
            }
        }
        
        // เรียงลำดับข้อมูลตามวันที่
        $flatAttendances = $flatAttendances->sortBy(function ($attendance) {
            return $attendance['type'] === 'regular' 
                ? $attendance['data']->start_time 
                : $attendance['data']->start_work;
        });
        
        foreach ($flatAttendances as $attendance) {
            $date = $attendance['type'] === 'regular' 
                ? Carbon::parse($attendance['data']->start_time)->format('d-m-y')
                : Carbon::parse($attendance['data']->start_work)->format('d-m-y');
                
            $time = $attendance['type'] === 'regular' 
                ? Carbon::parse($attendance['data']->start_time)->format('H:i') . '-' . Carbon::parse($attendance['data']->end_time)->format('H:i')
                : Carbon::parse($attendance['data']->start_work)->format('H:i');
                
            $courseId = $attendance['type'] === 'regular' 
                ? $attendance['data']->class->course->subjects->subject_id ?? '-'
                : $attendance['data']->classes->course->subjects->subject_id ?? '-';
                
            $lectureHours = 0;
            $labHours = 0;
            
            if ($attendance['class_type'] === 'LECTURE') {
                $lectureHours = $attendance['hours'];
                $totalLectureHours += $lectureHours;
            } else {
                $labHours = $attendance['hours'];
                $totalLabHours += $labHours;
            }
            
            $note = $attendance['type'] === 'regular' 
                ? ($attendance['data']->attendance->note ?? '-')
                : ($attendance['data']->detail ?? '-');
            
            $data->push([
                $rowNumber,
                $rowNumber === 1 ? $this->student->name : '',
                $rowNumber === 1 ? 'ป.ตรี' : '',
                $date,
                $courseId,
                $time,
                $lectureHours > 0 ? number_format($lectureHours, 2) : '',
                $labHours > 0 ? number_format($labHours, 2) : '',
                $note
            ]);
            
            $rowNumber++;
        }
        
        // เพิ่มแถวสรุป
        $data->push(['', 'รวมเวลา', 'ปริญญาตรี', '', '', '', number_format($totalLectureHours, 2), number_format($totalLabHours, 2), '']);
        $data->push(['', 'ที่สอน', 'ปริญญาโท/เอก', '', '', '', '', '', '']);
        
        // เพิ่มข้อมูลการคำนวณค่าตอบแทน
        $data->push(['']);
        $data->push(['จำนวนเงินที่ขอเบิก']);
        
        $regularTotalHours = $this->compensation['regularLectureHours'] + $this->compensation['regularLabHours'];
        $specialTotalHours = $this->compensation['specialLectureHours'] + $this->compensation['specialLabHours'];
        
        // ภาคปกติ
        $data->push([
            '- ปริญญาตรี (ภาคปกติ)',
            number_format($regularTotalHours, 1),
            'ชั่วโมง',
            'อัตราชั่วโมงละ',
            number_format($this->compensation['rates']['regularLecture'], 2),
            'บาท',
            'เป็นเงิน',
            number_format($this->compensation['regularPay'], 2),
            ''
        ]);
        
        // โครงการพิเศษ
        $data->push([
            '- ปริญญาตรี (โครงการพิเศษ)',
            number_format($specialTotalHours, 1),
            'ชั่วโมง',
            'อัตราชั่วโมงละ',
            number_format($this->compensation['rates']['specialLecture'], 2),
            'บาท',
            'เป็นเงิน',
            $this->compensation['specialPay'] > 0 ? number_format($this->compensation['specialPay'], 2) : '-',
            ''
        ]);
        
        // ปริญญาโท/เอก
        $data->push([
            '- ปริญญาโท/เอก (เหมาจ่าย)',
            '',
            '',
            '',
            '',
            '',
            'เป็นเงิน',
            '',
            ''
        ]);
        
        // สรุปรวม
        $data->push(['']);
        $data->push(['รวมเป็นเงินทั้งสิ้น', number_format($this->compensation['totalPay'], 2), 'บาท', '', '= ' . $this->convertNumberToThaiBaht($this->compensation['totalPay']) . ' =', '', '', '', '']);
        
        // หมายเหตุ
        $data->push(['หมายเหตุ :', 'ขอเบิกจ่ายเพียง', '', 'บาท', '', '', '', '', '']);
        
        // ส่วนลงนาม
        $data->push(['']);
        $data->push(['ผู้ปฏิบัติงาน', '', 'อาจารย์ผู้สอน', '', 'ผู้รับรอง', '', '', '', '']);
        
        // เว้นช่องว่างให้เซ็น
        $data->push(['', '', '', '', '', '', '', '', '']);
        $data->push(['', '', '', '', '', '', '', '', '']);
        
        // ลายเซ็น
        $data->push(['ลงชื่อ.................................', '', 'ลงชื่อ.................................', '', 'ลงชื่อ.................................', '', '', '', '']);
        $data->push(['(.................................)', '', '(.................................)', '', '(.................................)', '', '', '', '']);
        $data->push(['วันที่.....เดือน...............พ.ศ........', '', 'วันที่.....เดือน...............พ.ศ........', '', 'ตำแหน่ง หัวหน้าสาขาวิชาวิทยาการคอมพิวเตอร์', '', '', '', '']);
        $data->push(['', '', '', '', 'วันที่.....เดือน...............พ.ศ........', '', '', '', '']);
        
        return $data;
    }

    public function convertNumberToThaiBaht($number)
    {
        // ในการใช้งานจริง ควรใช้ฟังก์ชันแปลงตัวเลขเป็นคำอ่านเงินบาทที่สมบูรณ์
        // แต่ในตัวอย่างนี้ใช้ตัวอย่างอย่างง่าย
        
        if ($number == 1440) {
            return "หนึ่งพันสี่ร้อยสี่สิบบาทถ้วน";
        }
        
        // ตัวอย่างการใช้ฟังก์ชันที่มีในระบบ (ถ้ามี)
        // return \App\Helpers\ThaiNumberHelper::convertToText($number);
        
        return "จำนวนเงินเป็นตัวอักษร";
    }

    public function title(): string
    {
        $date = Carbon::createFromFormat('Y-m', $this->selectedYearMonth);
        $monthName = $date->locale('th')->monthName;
        $year = $date->year + 543;
        
        return "ใบเบิก " . $this->student->student_id . " " . $monthName . " " . $year;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ลำดับที่
            'B' => 25,  // ชื่อ-สกุล
            'C' => 10,  // ระดับ
            'D' => 12,  // ว/ด/ป
            'E' => 12,  // รหัสวิชา
            'F' => 15,  // เวลาสอน
            'G' => 10,  // บรรยาย
            'H' => 10,  // ปฏิบัติการ
            'I' => 30,  // หมายเหตุ
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // จัดการหลังจากสร้างชีทเสร็จสิ้น
                $sheet = $event->sheet->getDelegate();
                
                // กำหนดช่องที่ต้องการมีเส้นประ (dotted border)
                $rows = $sheet->getHighestRow();
                for ($i = 9; $i <= $rows - 15; $i++) {  // ปรับตามความเหมาะสม
                    $sheet->getStyle('B'.$i.':I'.$i)->getBorders()->getBottom()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED);
                }
                
                // ตั้งค่าการแสดงเส้นกริด
                $sheet->setShowGridlines(false);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // กำหนดสไตล์ของหัวเรื่อง (แบบใบเบิกค่าตอบแทน)
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'แบบใบเบิกค่าตอบแทนผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // ชื่อสถาบัน
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'วิทยาลัยการคอมพิวเตอร์ มหาวิทยาลัยขอนแก่น');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // ภาคการศึกษา
        $semesterText = '';
        $yearText = '';
        
        if ($this->semester) {
            $semesterValue = $this->semester->semesters ?? '';
            if ($semesterValue == '1') {
                $semesterText = "ภาคการศึกษา ( / ) ต้น     (   ) ปลาย     (   ) ฤดูร้อน";
            } elseif ($semesterValue == '2') {
                $semesterText = "ภาคการศึกษา (   ) ต้น     ( / ) ปลาย     (   ) ฤดูร้อน";
            } else {
                $semesterText = "ภาคการศึกษา (   ) ต้น     (   ) ปลาย     ( / ) ฤดูร้อน";
            }
            
            $yearText = "ปีการศึกษา " . ($this->semester->year + 543);
        } else {
            $semesterText = "ภาคการศึกษา (   ) ต้น     (   ) ปลาย     (   ) ฤดูร้อน";
            $yearText = "ปีการศึกษา _______";
        }
        
        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', $semesterText . '     ' . $yearText);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // ประจำเดือน
        $date = Carbon::createFromFormat('Y-m', $this->selectedYearMonth);
        $monthName = $date->locale('th')->monthName;
        $year = $date->year + 543;
        
        $sheet->mergeCells('A4:I4');
        $sheet->setCellValue('A4', 'ประจำเดือน ' . $monthName . ' ' . $year);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // รายวิชาระดับ
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'รายวิชาระดับ');
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        $sheet->mergeCells('D5:F5');
        $sheet->setCellValue('D5', '( / ) ปริญญาตรี');
        
        $sheet->mergeCells('G5:I5');
        $sheet->setCellValue('G5', '(   ) บัณฑิตศึกษา');
        
        // ประเภทโครงการ
        $sheet->mergeCells('A6:C6');
        $sheet->setCellValue('A6', '');
        
        $regularProject = ($this->compensation['regularPay'] > 0) ? '( / )' : '(   )';
        $specialProject = ($this->compensation['specialPay'] > 0) ? '( / )' : '(   )';
        
        $sheet->mergeCells('D6:F6');
        $sheet->setCellValue('D6', $regularProject . ' ภาคปกติ');
        
        $sheet->mergeCells('G6:I6');
        $sheet->setCellValue('G6', $specialProject . ' โครงการพิเศษ');
        
        // กำหนดสไตล์สำหรับหัวตาราง
        $headerRow = 7;
        $sheet->getStyle('A'.$headerRow.':I'.$headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$headerRow.':I'.$headerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$headerRow.':I'.$headerRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        
        // ตีกรอบเนื้อหาตาราง
        $lastDataRow = $sheet->getHighestRow() - 9; // ข้ามส่วนล่าง (ลายเซ็น)
        $sheet->getStyle('A'.$headerRow.':I'.$lastDataRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        
        // จัดรูปแบบแถวรวมเวลา
        $totalRow = $lastDataRow - 17; // ปรับตามโครงสร้างข้อมูล
        $sheet->getStyle('B'.$totalRow.':H'.($totalRow+1))->getFont()->setBold(true);
        
        // จัดรูปแบบแถวขอเบิกเงิน
        $paymentRow = $totalRow + 4;
        $sheet->getStyle('A'.$paymentRow)->getFont()->setBold(true);
        
        // จัดรูปแบบแถวรวมเงินทั้งสิ้น
        $totalAmountRow = $paymentRow + 4;
        $sheet->getStyle('A'.$totalAmountRow.':E'.$totalAmountRow)->getFont()->setBold(true);
        $sheet->mergeCells('E'.$totalAmountRow.':I'.$totalAmountRow);
        
        // แถวลายเซ็น
        $signatureRow = $totalAmountRow + 3;
        $sheet->mergeCells('A'.$signatureRow.':B'.$signatureRow);
        $sheet->mergeCells('C'.$signatureRow.':E'.$signatureRow);
        $sheet->mergeCells('F'.$signatureRow.':I'.$signatureRow);
        $sheet->getStyle('A'.$signatureRow.':I'.$signatureRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$signatureRow.':I'.$signatureRow)->getFont()->setBold(true);
        
        return [
            // กำหนดให้แถวที่เป็นหัวตารางชิดกลาง
            $headerRow => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            
            // จัดรูปแบบคอลัมน์ตัวเลข (บรรยาย/ปฏิบัติการ)
            'G8:H'.$lastDataRow => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }
}