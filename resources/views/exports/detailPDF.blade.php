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

        /* Bold */
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew Bold.ttf') }}") format('truetype');
        }

        /* Italic */
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew Italic.ttf') }}") format('truetype');
        }

        /* Bold Italic */
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew BoldItalic.ttf') }}") format('truetype');
        }

        * {
            font-family: 'THSarabunNew', sans-serif !important;
            font-size: 14pt;
            line-height: 1.3;
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
            font-size: 16pt;
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
            padding: 5px;
        }

        th {
            font-weight: bold;
            text-align: center;
            font-size: 14pt;
        }

        td {
            padding: 3px 5px;
            font-size: 14pt;
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
    </style>
</head>

<body>
    <div class="center">
        <h2>แบบใบเบิกค่าตอบแทนผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน</h2>
        <h3>วิทยาลัยการคอมพิวเตอร์ มหาวิทยาลัยขอนแก่น</h3>
        {{-- <div style="margin: 10px 0;">
            ภาคการศึกษา
            (<span class="checkbox">{{ $semester == 'ต้น' ? '/' : ' ' }}</span>) ต้น
            (<span class="checkbox">{{ $semester == 'ปลาย' ? '/' : ' ' }}</span>) ปลาย
            (<span class="checkbox">{{ $semester == 'ฤดูร้อน' ? '/' : ' ' }}</span>) ฤดูร้อน
            ปีการศึกษา {{ $year }}
        </div> --}}
        <div style="margin-bottom: 15px;">
            ประจำเดือน {{ $monthText }}
        </div>
    </div>

    {{-- <div style="margin-bottom: 15px;">
        รายวิชาระดับ
        (<span class="checkbox">/</span>) ปริญญาตรี
        (<span class="checkbox"> </span>) บัณฑิตศึกษา<br>
        (<span class="checkbox"> </span>) ภาคปกติ
        (<span class="checkbox">/</span>) โครงการพิเศษ
    </div> --}}

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
            @php $no = 1; @endphp
            @foreach ($attendancesBySection as $section => $attendances)
                @foreach ($attendances as $attendance)
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>{{ $student->name }}</td>
                        <td style="text-align: center;">ป.ตรี</td>
                        <td style="text-align: center;">
                            {{ \Carbon\Carbon::parse($attendance['type'] === 'regular' ? $attendance['data']->start_time : $attendance['data']->start_work)->format('d-m-y') }}
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->class->course->subjects->subject_id ?? 'N/A' }}
                            @else
                                {{ $attendance['data']->classes->course->subjects->subject_id ?? ($attendance['data']->class_id ?? 'N/A') }}
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->class_type !== 'L' ? number_format($attendance['hours'], 2) : '-' }}
                            @else
                                {{ $attendance['data']->class_type !== 'L' ? number_format($attendance['hours'], 2) : '-' }}
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->class_type === 'L' ? number_format($attendance['hours'], 2) : '-' }}
                            @else
                                {{ $attendance['data']->class_type === 'L' ? number_format($attendance['hours'], 2) : '-' }}
                            @endif
                        </td>
                        <td>
                            @if ($attendance['type'] === 'regular')
                                {{ $attendance['data']->attendance->note ?? 'ช่วยตรวจงาน / เช็คชื่อ' }}
                            @else
                                {{ $attendance['data']->detail ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
            <tr>
                <td colspan="5" style="text-align: center;"><strong>รวมเวลาที่สอน</strong></td>
                <td style="text-align: center;">{{ number_format($regularBroadcastHours + $regularLabHours, 2) }}</td>
                <td style="text-align: center;">{{ number_format($specialTeachingHours, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- ส่วนตาราง no-border สำหรับแสดงรายละเอียดค่าตอบแทน -->
    <div class="mb-4">
        <table class="table no-border">
            <tbody>
                <tr>
                    <td>- ปริญญาตรี (ภาคปกติ)</td>
                    <td class="text-end">{{ number_format($regularBroadcastHours + $regularLabHours, 2) }}</td>
                    <td>ชั่วโมง</td>
                    <td>อัตราชั่วโมงละ</td>
                    <td class="text-end">40.00</td>
                    <td>บาท</td>
                    <td>เป็นเงิน</td>
                    <td class="text-end">{{ number_format($regularPay, 2) }}</td>
                </tr>
                <tr>
                    <td>- ปริญญาตรี (โครงการพิเศษ)</td>
                    <td class="text-end">{{ number_format($specialTeachingHours, 2) }}</td>
                    <td>ชั่วโมง</td>
                    <td>อัตราชั่วโมงละ</td>
                    <td class="text-end">50.00</td>
                    <td>บาท</td>
                    <td>เป็นเงิน</td>
                    <td class="text-end">{{ number_format($specialPay, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6">รวมเป็นเงินทั้งสิ้น</td>
                    <td class="text-end">{{ number_format($totalPay, 2) }} บาท</td>
                    <td>= {{ $totalPayText }} =</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">ลงชื่อ ................................................</div>
            <p>(................................................)</p>
            <p>ผู้ปฏิบัติงาน</p>
            <p>วันที่........เดือน....................พ.ศ............</p>
        </div>
        <div class="signature-box">
            <div class="signature-line">ลงชื่อ ................................................</div>
            <p>(................................................)</p>
            <p>อาจารย์ผู้สอน</p>
            <p>วันที่........เดือน....................พ.ศ............</p>
        </div>
        <div class="signature-box">
            <div class="signature-line">ลงชื่อ ................................................</div>
            <p>(................................................)</p>
            <p>ตำแหน่ง หัวหน้าสาขาวิชาวิทยาการคอมพิวเตอร์</p>
            <p>วันที่........เดือน....................พ.ศ............</p>
        </div>
    </div>

    <div class="clear"></div>
    <div style="margin-top: 20px;">
        <p>หมายเหตุ : ขอเบิกจ่ายเพียง {{ number_format($totalPay, 2) }} บาท</p>
    </div>
</body>

</html>
