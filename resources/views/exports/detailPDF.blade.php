<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew Bold.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew Italic.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew BoldItalic.ttf') }}") format('truetype');
        }

        * {
            font-family: 'THSarabunNew', sans-serif !important;
            font-size: 12pt;
            line-height: 0.8;
        }


        .center {
            text-align: center;
        }

        h2 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 0;
            font-family: 'THSarabunNew', sans-serif !important;
        }

        strong,
        b {
            font-family: 'THSarabunNew', sans-serif !important;
            font-weight: bold;
        }

        h3 {
            font-size: 14pt;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table,
        th,
        td {
            border: 1px solid black;
            padding: 1px;
        }

        th {
            font-weight: bold;
            text-align: center;
            font-size: 14pt;
        }

        td {
            padding: 3px 5px;
            font-size: 12pt;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid black;
            text-align: center;
            margin: 0 4px;
        }

        .total-section {
            margin-top: 15px;
            font-size: 14pt;
        }

        .total-section p {
            margin: 5px 0;
        }

        .signature-section {
            margin-top: 40px;
            width: 100%;
        }

        .signature-box {
            float: left;
            width: 33%;
            text-align: center;
        }

        .signature-box p {
            margin: 5px 0;
        }

        .signature-line {
            margin: 25px 0 5px 0;
        }

        .clear {
            clear: both;
        }

        .no-border {
            border: none !important;
        }

        .no-border td {
            border: none !important;
        }

        .text-end {
            text-align: right;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <!-- โครงการปกติ แสดงเฉพาะเมื่อมีข้อมูลโครงการปกติ -->
    @if ($hasRegularProject)
        <div class="center">
            <h3>แบบใบเบิกค่าตอบแทนผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน</h3>
            <h3>วิทยาลัยการคอมพิวเตอร์ มหาวิทยาลัยขอนแก่น</h3>
            <div style="margin: 10px 0; line-height: 1;">
                ภาคการศึกษา
                (<span>{{ $semester->semesters == 'ต้น' ? '/' : ' ' }}</span>) ต้น
                (<span>{{ $semester->semesters == 'ปลาย' ? '/' : ' ' }}</span>) ปลาย
                (<span>{{ $semester->semesters == 'ฤดูร้อน' ? '/' : ' ' }}</span>) ฤดูร้อน
                ปีการศึกษา {{ $year }}
                <br>
                ประจำเดือน {{ $monthText }}
                <br>
                รายวิชาระดับ
                (<span> </span>) ปริญญาตรี
                (<span> </span>) บัณฑิตศึกษา
                <br>
                (<span>/</span>) ภาคปกติ
                (<span> </span>) โครงการพิเศษ
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">ลำดับที่</th>
                    <th rowspan="2" style="width: 20%;">ชื่อ-สกุล</th>
                    <th rowspan="2" style="width: 8%;">ระดับ</th>
                    <th colspan="2">ระยะเวลาที่สอน</th>
                    <th colspan="2">จำนวนชั่วโมงที่สอน</th>
                    <th rowspan="2" style="width: 20%;">หมายเหตุ</th>
                </tr>
                <tr>
                    <th style="width: 10%;">ว/ด/ป</th>
                    <th style="width: 12%;">รหัสวิชา</th>
                    <th style="width: 8%;">บรรยาย</th>
                    <th style="width: 8%;">ปฏิบัติการ</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $isFirstRow = true;
                @endphp
                @forelse ($regularAttendances as $attendance)
                    <tr>
                        <td style="text-align: center;">
                            @if ($isFirstRow)
                                {{ $no }}
                            @endif
                        </td>
                        <td>
                            @if ($isFirstRow)
                                {{ $student->name }}
                                @php $isFirstRow = false; @endphp
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @php
                                $degreeLevel = $student->degree_level ?? 'undergraduate';
                                if ($degreeLevel == 'bachelor') {
                                    echo 'ป.ตรี';
                                } elseif ($degreeLevel == 'master') {
                                    echo 'ป.โท';
                                } elseif ($degreeLevel == 'doctoral') {
                                    echo 'ป.เอก';
                                } else {
                                    echo 'ป.ตรี';
                                }
                            @endphp
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['type'] === 'regular')
                                {{ \Carbon\Carbon::parse($attendance['data']->start_time)->format('d-m-y') }}
                            @elseif ($attendance['type'] === 'extra')
                                @php
                                    if (isset($attendance['date'])) {
                                        $dateParts = explode(' ', $attendance['date']);
                                        $dateOnly = $dateParts[0] ?? null;
                                    } else {
                                        // ใช้ข้อมูลจาก data แทนถ้าไม่มี 'date'
                                        $dateOnly = isset($attendance['data']->class_date)
                                            ? $attendance['data']->class_date
                                            : \Carbon\Carbon::now()->format('Y-m-d');
                                    }
                                @endphp
                                {{ \Carbon\Carbon::parse($dateOnly)->format('d-m-y') }}
                            @else
                                {{ \Carbon\Carbon::parse($attendance['data']->start_work)->format('d-m-y') }}
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->class->course->subjects->subject_id ?? '-' }}
                            @elseif ($attendance['type'] === 'extra')
                                {{ $attendance['data']->class->course->subjects->subject_id ?? '-' }}
                            @else
                                {{ $attendance['data']->classes->course->subjects->subject_id ?? '-' }}
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['class_type'] === 'LECTURE')
                                {{ number_format($attendance['hours'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['class_type'] === 'LAB')
                                {{ number_format($attendance['hours'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->attendance->note ?? 'ช่วยตรวจงาน / เช็คชื่อ' }}
                            @elseif ($attendance['type'] === 'extra')
                                การสอนชดเชย
                            @else
                                {{ $attendance['data']->detail ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">ไม่พบข้อมูลการลงเวลาในโครงการปกติ</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="5" style="text-align: center;"><strong>รวมเวลาที่สอน</strong></td>
                    <td style="text-align: center;">{{ number_format($regularLectureHoursSum, 2) }}</td>
                    <td style="text-align: center;">{{ number_format($regularLabHoursSum, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="mb-4">
            <table class="table no-border">
                <tbody>
                    <tr>
                        <td><strong>จำนวนเงินที่ขอเบิก</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>ปริญญาตรี (ภาคปกติ)</td>
                        <td class="text-end">{{ number_format($regularLectureHoursSum + $regularLabHoursSum, 2) }}</td>
                        <td>ชั่วโมง</td>
                        <td>อัตราชั่วโมงละ</td>
                        <td class="text-end">{{ number_format($compensationRates['regularLecture'], 2) }}</td>
                        <td>บาท</td>
                        <td>เป็นเงิน</td>
                        <td class="text-end">
                            {{ number_format(($regularLectureHoursSum + $regularLabHoursSum) * $compensationRates['regularLecture'], 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6"><strong>รวมเป็นเงินทั้งสิ้น</strong></td>
                        <td class="text-end">
                            <strong>{{ number_format(($regularLectureHoursSum + $regularLabHoursSum) * $compensationRates['regularLecture'], 2) }}
                                บาท</strong>
                        </td>
                        <td><strong>=
                                {{ \App\Helpers\ThaiNumberHelper::convertToText(number_format(($regularLectureHoursSum + $regularLabHoursSum) * $compensationRates['regularLecture'], 2, '.', '')) }}ถ้วน
                                =</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="clear"></div>
        <div style="margin-top: 20px;">
            <p>หมายเหตุ : ขอเบิกจ่ายเพียง
                {{ number_format(isset($actualRegularPay) ? $actualRegularPay : ($regularLectureHoursSum + $regularLabHoursSum) * $compensationRates['regularLecture'], 2) }}
                บาท
            </p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">ลงชื่อ ................................................</div>
                <p>({{ $student->name }})</p>
                <p>ผู้ปฏิบัติงาน</p>
                <p>วันที่ {{ $formattedDate['day'] }} {{ $formattedDate['month'] }} พ.ศ. {{ $formattedDate['year'] }}
                </p>
            </div>
            <div class="signature-box">
                <div class="signature-line">ลงชื่อ ................................................</div>
                <p>({{ $teacherFullTitle }})</p>
                <p>อาจารย์ผู้สอน</p>
                <p>วันที่ {{ $formattedDate['day'] }} {{ $formattedDate['month'] }} พ.ศ. {{ $formattedDate['year'] }}
                </p>
            </div>
            <div class="signature-box">
                <div class="signature-line">ลงชื่อ ................................................</div>
                <p>({{ $headName }})</p>
                <p>ตำแหน่ง หัวหน้าสาขาวิชาวิทยาการคอมพิวเตอร์</p>
                <p>วันที่ {{ $formattedDate['day'] }} {{ $formattedDate['month'] }} พ.ศ. {{ $formattedDate['year'] }}
                </p>
            </div>
        </div>
    @endif

    @if ($hasRegularProject && $hasSpecialProject)
        <div class="page-break"></div>
    @endif

    <!--  โครงการพิเศษ แสดงเฉพาะเมื่อมีข้อมูลโครงการพิเศษ -->
    @if ($hasSpecialProject)
        <div class="center">
            <h3>แบบใบเบิกค่าตอบแทนผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน</h3>
            <h3>วิทยาลัยการคอมพิวเตอร์ มหาวิทยาลัยขอนแก่น</h3>
            <div style="margin: 10px 0; line-height: 1;">
                ภาคการศึกษา
                (<span>{{ $semester->semesters == 'ต้น' ? '/' : ' ' }}</span>) ต้น
                (<span>{{ $semester->semesters == 'ปลาย' ? '/' : ' ' }}</span>) ปลาย
                (<span>{{ $semester->semesters == 'ฤดูร้อน' ? '/' : ' ' }}</span>) ฤดูร้อน
                ปีการศึกษา {{ $year }}
                <br>
                ประจำเดือน {{ $monthText }}
                <br>
                รายวิชาระดับ
                (<span> </span>) ปริญญาตรี
                (<span> </span>) บัณฑิตศึกษา
                <br>
                (<span> </span>) ภาคปกติ
                (<span>/</span>) โครงการพิเศษ
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">ลำดับที่</th>
                    <th rowspan="2" style="width: 20%;">ชื่อ-สกุล</th>
                    <th rowspan="2" style="width: 8%;">ระดับ</th>
                    <th colspan="2">ระยะเวลาที่สอน</th>
                    <th colspan="2">จำนวนชั่วโมงที่สอน</th>
                    <th rowspan="2" style="width: 20%;">หมายเหตุ</th>
                </tr>
                <tr>
                    <th style="width: 10%;">ว/ด/ป</th>
                    <th style="width: 12%;">รหัสวิชา</th>
                    <th style="width: 8%;">บรรยาย</th>
                    <th style="width: 8%;">ปฏิบัติการ</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $isFirstRow = true;
                @endphp
                @forelse ($specialAttendances as $attendance)
                    <tr>
                        <td style="text-align: center;">
                            @if ($isFirstRow)
                                {{ $no }}
                            @endif
                        </td>
                        <td>
                            @if ($isFirstRow)
                                {{ $student->name }}
                                @php $isFirstRow = false; @endphp
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @php
                                $degreeLevel = $student->degree_level ?? 'undergraduate';
                                if ($degreeLevel == 'bachelor') {
                                    echo 'ป.ตรี';
                                } elseif ($degreeLevel == 'master') {
                                    echo 'ป.โท';
                                } elseif ($degreeLevel == 'doctoral') {
                                    echo 'ป.เอก';
                                } else {
                                    echo 'ป.ตรี';
                                }
                            @endphp
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['type'] === 'regular')
                                {{ \Carbon\Carbon::parse($attendance['data']->start_time)->format('d-m-y') }}
                            @elseif ($attendance['type'] === 'extra')
                                @php
                                    if (isset($attendance['date'])) {
                                        $dateParts = explode(' ', $attendance['date']);
                                        $dateOnly = $dateParts[0] ?? null;
                                    } else {
                                        // ใช้ข้อมูลจาก data แทนถ้าไม่มี 'date'
                                        $dateOnly = isset($attendance['data']->class_date)
                                            ? $attendance['data']->class_date
                                            : \Carbon\Carbon::now()->format('Y-m-d');
                                    }
                                @endphp
                                {{ \Carbon\Carbon::parse($dateOnly)->format('d-m-y') }}
                            @else
                                {{ \Carbon\Carbon::parse($attendance['data']->start_work)->format('d-m-y') }}
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->class->course->subjects->subject_id ?? '-' }}
                            @elseif ($attendance['type'] === 'extra')
                                {{ $attendance['data']->class->course->subjects->subject_id ?? '-' }}
                            @else
                                {{ $attendance['data']->classes->course->subjects->subject_id ?? '-' }}
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['class_type'] === 'LECTURE')
                                {{ number_format($attendance['hours'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['class_type'] === 'LAB')
                                {{ number_format($attendance['hours'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->attendance->note ?? 'ช่วยตรวจงาน / เช็คชื่อ' }}
                            @elseif ($attendance['type'] === 'extra')
                                การสอนชดเชย
                            @else
                                {{ $attendance['data']->detail ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">ไม่พบข้อมูลการลงเวลาในโครงการพิเศษ</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="5" style="text-align: center;"><strong>รวมเวลาที่สอน</strong></td>
                    <td style="text-align: center;">{{ number_format($specialLectureHoursSum, 2) }}</td>
                    <td style="text-align: center;">{{ number_format($specialLabHoursSum, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="mb-4">
            <table class="table no-border">
                <tbody>
                    <tr>
                        <td><strong>จำนวนเงินที่ขอเบิก</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @if ($isFixedPayment)
                        <tr>
                            <td>- ปริญญาโท/เอก (เหมาจ่าย)</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>เป็นเงิน</td>
                            <td class="text-end">{{ number_format($specialPay, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>ปริญญาตรี (โครงการพิเศษ)</td>
                            <td class="text-end">{{ number_format($specialLectureHoursSum + $specialLabHoursSum, 2) }}
                            </td>
                            <td>ชั่วโมง</td>
                            <td>อัตราชั่วโมงละ</td>
                            <td class="text-end">{{ number_format($compensationRates['specialLecture'], 2) }}</td>
                            <td>บาท</td>
                            <td>เป็นเงิน</td>
                            <td class="text-end">
                                {{ number_format(($specialLectureHoursSum + $specialLabHoursSum) * $compensationRates['specialLecture'], 2) }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="6"><strong>รวมเป็นเงินทั้งสิ้น</strong></td>
                        <td class="text-end">
                            <strong>{{ number_format($isFixedPayment ? $specialPay : ($specialLectureHoursSum + $specialLabHoursSum) * $compensationRates['specialLecture'], 2) }}
                                บาท</strong>
                        </td>
                        <td><strong>=
                                {{ \App\Helpers\ThaiNumberHelper::convertToText(number_format($isFixedPayment ? $specialPay : ($specialLectureHoursSum + $specialLabHoursSum) * $compensationRates['specialLecture'], 2, '.', '')) }}ถ้วน
                                =</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="clear"></div>
        <div style="margin-top: 20px;">
            <div style="margin-top: 20px;">
                <p>หมายเหตุ : ขอเบิกจ่ายเพียง
                    {{ number_format(isset($actualSpecialPay) ? $actualSpecialPay : $specialPay, 2) }}
                    บาท
                </p>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">ลงชื่อ ................................................</div>
                <p>({{ $student->name }})</p>
                <p>ผู้ปฏิบัติงาน</p>
                <p>วันที่ {{ $formattedDate['day'] }} {{ $formattedDate['month'] }} พ.ศ. {{ $formattedDate['year'] }}
                </p>
            </div>
            <div class="signature-box">
                <div class="signature-line">ลงชื่อ ................................................</div>
                <p>({{ $teacherFullTitle }})</p>
                <p>อาจารย์ผู้สอน</p>
                <p>วันที่ {{ $formattedDate['day'] }} {{ $formattedDate['month'] }} พ.ศ. {{ $formattedDate['year'] }}
                </p>
            </div>
            <div class="signature-box">
                <div class="signature-line">ลงชื่อ ................................................</div>
                <p>({{ $headName }})</p>
                <p>ตำแหน่ง หัวหน้าสาขาวิชาวิทยาการคอมพิวเตอร์</p>
                <p>วันที่ {{ $formattedDate['day'] }} {{ $formattedDate['month'] }} พ.ศ. {{ $formattedDate['year'] }}
                </p>
            </div>
        </div>
    @endif
</body>

</html>
