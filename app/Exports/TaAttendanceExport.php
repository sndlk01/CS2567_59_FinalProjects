<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaAttendanceExport implements WithMultipleSheets
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

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        
        $normalAttendances = collect();
        $specialAttendances = collect();
        
        foreach ($this->attendances as $section => $sectionAttendances) {
            $normalSectionAttendances = collect();
            $specialSectionAttendances = collect();
            
            foreach ($sectionAttendances as $attendance) {
                if ($attendance['teaching_type'] === 'regular') {
                    $normalSectionAttendances->push($attendance);
                } else {
                    $specialSectionAttendances->push($attendance);
                }
            }
            
            if ($normalSectionAttendances->isNotEmpty()) {
                $normalAttendances->put($section, $normalSectionAttendances);
            }
            
            if ($specialSectionAttendances->isNotEmpty()) {
                $specialAttendances->put($section, $specialSectionAttendances);
            }
        }
        
        $sheets[] = new TaRegularSheet(
            $normalAttendances,
            $this->compensation,
            $this->student,
            $this->semester,
            $this->selectedYearMonth,
            $this->teacherName
        );
        
        if ($specialAttendances->isNotEmpty() || $this->compensation['specialPay'] > 0) {
            $sheets[] = new TaSpecialSheet(
                $specialAttendances,
                $this->compensation,
                $this->student,
                $this->semester,
                $this->selectedYearMonth,
                $this->teacherName
            );
        }
        
        return $sheets;
    }

    
}