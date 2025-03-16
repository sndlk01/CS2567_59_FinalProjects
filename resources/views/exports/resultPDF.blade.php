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
            font-size: 14pt;
            /* line-height: 1; */
        }

        body {
            padding: 0;
            margin: 0;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        h1,
        h2,
        h3 {
            text-align: center;
        }

        h2 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 0;
        }

        h3 {
            font-size: 14pt;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        strong,
        b {
            font-weight: bold;
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
        }

        th {
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 5px;
        }

        td {
            /* padding: 2px; */
            padding-left: 5px;
            vertical-align: middle;
        }

        .no-border {
            border: none !important;
        }

        .no-border td {
            border: none !important;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid black;
            text-align: center;
            vertical-align: middle;
            margin-right: 5px;
        }

        .signature-section {
            display: flex;
            align-items: flex-start;
            width: 100%;
        }

        .signature-section .no-border {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-section .no-border td {
            vertical-align: top;
            padding: 10px;
        }

        .signature-section .center {
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @php
        // สร้างข้อมูลสำหรับแสดงผลจากข้อมูลที่ได้รับมา
        $regularData = [];
        $specialData = [];

        // ข้อมูลโครงการปกติ
        if ($hasRegularProject) {
            $regularData[] = [
                'section' => 'ทุก section',
                'type' => 'ปกติ',
                'hours' => $regularLectureHours + $regularLabHours,
                'rate' => $compensationRates['regularLecture'],
                'amount' => $regularPay,
                'subject_id' => $course->subjects->subject_id ?? '',
            ];
        }

        // ข้อมูลโครงการพิเศษ
        if ($hasSpecialProject) {
            $specialData[] = [
                'section' => 'ทุก section',
                'type' => 'พิเศษ',
                'hours' => $specialLectureHours + $specialLabHours,
                'rate' => $isFixedPayment
                    ? 'เหมาจ่าย ' . number_format($fixedAmount, 0) . ' บาท'
                    : $compensationRates['specialLecture'],
                'amount' => $specialPay,
                'subject_id' => $course->subjects->subject_id ?? '',
                'is_fixed' => $isFixedPayment,
            ];
        }

        // กำหนดค่าทั้งหมดสำหรับแสดงผล
        $actualRegularPay = isset($actualRegularPay) ? $actualRegularPay : $regularPay;
        $actualSpecialPay = isset($actualSpecialPay) ? $actualSpecialPay : $specialPay;
    @endphp

    <!--  โครงการปกติ แสดงเฉพาะเมื่อมีข้อมูล -->
    @if ($hasRegularProject)
        <h3>หลักฐานการจ่ายเงินอื่น ๆ</h3>

        <div class="center" style="line-height: 1;">
            <h3>เบิกตามฎีกาที่...................................... วันที่..................... เดือน
                ...................................... พ.ศ. .......................
                <br>
                ข้าพเจ้าผู้มีรายนามข้างท้ายนี้ได้รับเงินจากส่วนราชการ วิทยาลัยการคอมพิวเตอร์ มหาวิทยาลัยขอนแก่น
                เป็นค่าตอบแทนผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน
                <br>สาขาวิชาวิทยาการคอมพิวเตอร์
                ประจำ{{ $semester->semesters == '1' ? 'ภาคต้น' : ($semester->semesters == '2' ? 'ภาคปลาย' : 'ภาคฤดูร้อน') }}
                ปีการศึกษา {{ $year }}
                <br>ตามหนังสืออนุมัติที่ ........... ลงวันที่ เดือน พ.ศ. {{ $year }}
                ได้เป็นการถูกต้องแล้วจึงลงลายมือชื่อไว้เป็นสำคัญ
            </h3>
        </div>

        <div class="center" style="margin: 5px 0;">
            @php
                $degreeLevel = $student->degree_level ?? 'undergraduate';
                $isGraduate = in_array($degreeLevel, ['master', 'doctoral', 'graduate']);
            @endphp
            <h3>รายวิชาระดับ
                (<span style="display: inline-block; width: 10px;">{{ !$isGraduate ? '/' : ' ' }}</span>)
                ปริญญาตรี
                (<span style="display: inline-block; width: 10px;">{{ $isGraduate ? '/' : ' ' }}</span>)
                บัณฑิตศึกษา <br>
                (<span style="display: inline-block; width: 10px;">/</span>) ภาคปกติ
                (<span style="display: inline-block; width: 10px;"> </span>) โครงการพิเศษ
            </h3>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ลำดับที่</th>
                    <th style="width: 20%;">ชื่อผู้สอน</th>
                    <th style="width: 8%;">ระดับ<br />ตรี/โท/เอก</th>
                    <th style="width: 8%;">จำนวน<br />ชั่วโมง</th>
                    <th style="width: 8%;">อัตรา<br />ต่อหน่วย</th>
                    <th style="width: 10%;">จำนวนเงิน</th>
                    <th style="width: 10%;">รับจริง</th>
                    <th style="width: 12%;">วัน เดือน ปี<br />ที่รับเงิน</th>
                    <th style="width: 10%;">ลายมือชื่อผู้รับเงิน</th>
                    <th style="width: 9%;">หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNumber = 1; @endphp
                @foreach ($regularData as $data)
                    <tr>
                        <td class="center">{{ $rowNumber++ }}</td>
                        <td>{{ $student->name }}</td>
                        <td class="center">
                            @if ($student->degree_level == 'bachelor')
                                ป.ตรี
                            @elseif($student->degree_level == 'master')
                                ป.โท
                            @elseif($student->degree_level == 'doctoral')
                                ป.เอก
                            @else
                                ป.ตรี
                            @endif
                        </td>
                        <td class="center">{{ number_format($data['hours'], 0) }}</td>
                        <td class="center">
                            {{ is_numeric($data['rate']) ? number_format($data['rate'], 0) : $data['rate'] }}</td>
                        <td class="center">{{ number_format($data['amount'], 0) }}</td>
                        <td class="center">
                            @if (isset($transaction) && $transaction)
                                {{ number_format($actualRegularPay, 0) }}
                            @else
                                {{ number_format($data['amount'], 0) }}
                            @endif
                        </td>
                        <td></td>
                        <td></td>
                        <td>{{ $data['subject_id'] }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="6" class="center"><strong>รวมเบิกเป็นเงินทั้งสิ้น</strong></td>
                    <td colspan="4" class="center"><strong>{{ number_format($actualRegularPay, 2) }}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="center"><strong>(ตัวอักษร)</strong></td>
                    <td colspan="8" class="center"><strong>
                            {{ \App\Helpers\ThaiNumberHelper::convertToText(number_format($actualRegularPay, 2, '.', '')) }}ถ้วน
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <table class="no-border">
                <tr>
                    <td width="50%" class="center">
                        <p>ลงชื่อ...................................................ผู้จ่ายเงิน </p>
                    </td>
                    <td width="50%" class="center">
                        <p>ลงชื่อ................................................... <br>
                            ({{ $headName }}) <br>
                            ตำแหน่ง หัวหน้าสาขาวิชาวิทยาการคอมพิวเตอร์
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        @if ($hasSpecialProject)
            <div class="page-break"></div>
        @endif
    @endif

    <!--  โครงการพิเศษ แสดงเฉพาะเมื่อมีข้อมูล -->
    @if ($hasSpecialProject)
        <h3>หลักฐานการจ่ายเงินอื่น ๆ</h3>

        <div class="center" style="line-height: 1;">
            <h3>เบิกตามฎีกาที่...................................... วันที่..................... เดือน
                ...................................... พ.ศ. .......................
                <br>
                ข้าพเจ้าผู้มีรายนามข้างท้ายนี้ได้รับเงินจากส่วนราชการ วิทยาลัยการคอมพิวเตอร์ มหาวิทยาลัยขอนแก่น
                เป็นค่าตอบแทนผู้ช่วยสอนและผู้ช่วยปฏิบัติงาน
                <br>สาขาวิชาวิทยาการคอมพิวเตอร์
                ประจำ{{ $semester->semesters == '1' ? 'ภาคต้น' : ($semester->semesters == '2' ? 'ภาคปลาย' : 'ภาคฤดูร้อน') }}
                ปีการศึกษา {{ $year }}
                <br>ตามหนังสืออนุมัติที่ ........... ลงวันที่ เดือน พ.ศ. {{ $year }}
                ได้เป็นการถูกต้องแล้วจึงลงลายมือชื่อไว้เป็นสำคัญ
            </h3>
        </div>

        <div class="center" style="margin: 5px 0;">
            @php
                $degreeLevel = $student->degree_level ?? 'undergraduate';
                $isGraduate = in_array($degreeLevel, ['master', 'doctoral', 'graduate']);
            @endphp
            <h3>รายวิชาระดับ
                (<span style="display: inline-block; width: 10px;">{{ !$isGraduate ? '/' : ' ' }}</span>)
                ปริญญาตรี
                (<span style="display: inline-block; width: 10px;">{{ $isGraduate ? '/' : ' ' }}</span>)
                บัณฑิตศึกษา <br>
                (<span style="display: inline-block; width: 10px;"> </span>) ภาคปกติ
                (<span style="display: inline-block; width: 10px;">/</span>) โครงการพิเศษ
                @if ($isFixedPayment)
                    <br><strong>(แบบเหมาจ่าย)</strong>
                @endif
            </h3>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ลำดับที่</th>
                    <th style="width: 20%;">ชื่อผู้สอน</th>
                    <th style="width: 8%;">ระดับ<br />ตรี/โท/เอก</th>
                    <th style="width: 8%;">จำนวน<br />ชั่วโมง</th>
                    <th style="width: 8%;">อัตรา<br />ต่อหน่วย</th>
                    <th style="width: 10%;">จำนวนเงิน</th>
                    <th style="width: 10%;">รับจริง</th>
                    <th style="width: 12%;">วัน เดือน ปี<br />ที่รับเงิน</th>
                    <th style="width: 10%;">ลายมือชื่อผู้รับเงิน</th>
                    <th style="width: 9%;">หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNumber = 1; @endphp
                @foreach ($specialData as $data)
                    <tr>
                        <td class="center">{{ $rowNumber++ }}</td>
                        <td>{{ $student->name }}</td>
                        <td class="center">
                            @if ($student->degree_level == 'bachelor')
                                ป.ตรี
                            @elseif($student->degree_level == 'master')
                                ป.โท
                            @elseif($student->degree_level == 'doctoral')
                                ป.เอก
                            @else
                                ป.ตรี
                            @endif
                        </td>
                        <td class="center">
                            @if ($isFixedPayment)
                                <span
                                    style="text-decoration: line-through;">{{ number_format($data['hours'], 0) }}</span>
                            @else
                                {{ number_format($data['hours'], 0) }}
                            @endif
                        </td>
                        <td class="center">
                            {{ is_numeric($data['rate']) ? number_format($data['rate'], 0) : $data['rate'] }}</td>
                        <td class="center">{{ number_format($data['amount'], 0) }}</td>
                        <td class="center">
                            @if (isset($transaction) && $transaction)
                                {{ number_format($actualSpecialPay, 2) }}
                            @else
                                {{ number_format($specialPay, 2) }}
                            @endif
                        </td>
                        <td></td>
                        <td></td>
                        <td>{{ $data['subject_id'] }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="6" class="center"><strong>รวมเบิกเป็นเงินทั้งสิ้น</strong></td>
                    <td colspan="4" class="center"><strong>
                            @if (isset($transaction) && $transaction)
                                {{ number_format($actualSpecialPay, 2) }}
                            @else
                                {{ number_format($specialPay, 2) }}
                            @endif
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="center"><strong>(ตัวอักษร)</strong></td>
                    <td colspan="8" class="center">
                        <strong>
                            {{ \App\Helpers\ThaiNumberHelper::convertToText(
                                isset($transaction) && $transaction
                                    ? number_format($actualSpecialPay, 2, '.', '')
                                    : number_format($specialPay, 2, '.', ''),
                            ) }}ถ้วน
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <table class="no-border">
                <tr>
                    <td width="50%" class="center">
                        <p>ลงชื่อ...................................................ผู้จ่ายเงิน </p>
                    </td>
                    <td width="50%" class="center">
                        <p>ลงชื่อ................................................... <br>
                            ({{ $headName }}) <br>
                            ตำแหน่ง หัวหน้าสาขาวิชาวิทยาการคอมพิวเตอร์
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @if (!$hasRegularProject && !$hasSpecialProject)
        <h3>หลักฐานการจ่ายเงินอื่น ๆ</h3>
        <div class="center" style="margin-top: 30px;">
            <p>ไม่พบข้อมูลการลงเวลาในช่วงเวลาที่เลือก</p>
        </div>
    @endif
</body>

</html>
