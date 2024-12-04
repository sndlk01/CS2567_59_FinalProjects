<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teachers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TeachersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            $teachers = [
                [
                    'prefix' => 'นางสาว',
                    'position' => 'รศ.',
                    'degree' => 'ดร.',
                    'name' => 'สิรภัทร เชี่ยวชาญวัฒนา',
                    'email' => 'sunkra@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'ศรัณย์ อภิชนตระกูล',
                    'email' => 'sarunap@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'พุธษดี ศิริแสงตระกูล',
                    'email' => 'pusadee@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'ชิตสุธา สุ่มเล็ก',
                    'email' => 'chitsutha@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'สาธิต กระเวนกิจ',
                    'email' => 'satikr@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'ณกร วัฒนกิจ',
                    'email' => 'nagon@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'ชานนท์ เดชสุภา',
                    'email' => 'chanode@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ศ.',
                    'degree' => 'ดร.',
                    'name' => 'ศาสตรา วงศ์ธนวสุ',
                    'email' => 'wongsar@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ศ. ',
                    'degree' => 'ดร.',
                    'name' => 'จักรชัย โสอินทร์',
                    'email' => 'chakso@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'รศ.',
                    'degree' => 'ดร.',
                    'name' => 'ปัญญาพล หอระตะ',
                    'email' => 'punhor1@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'รศ.',
                    'degree' => 'ดร.',
                    'name' => 'งามนิจ อาจอินทร์',
                    'email' => 'ngamnij@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'รศ.',
                    'degree' => 'ดร.',
                    'name' => 'อุรฉัตร โคแก้ว',
                    'email' => 'urachart@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'รศ.',
                    'degree' => 'ดร.',
                    'name' => 'ชัยพล กีรติกสิกร',
                    'email' => 'chaiyapon@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'รศ.',
                    'degree' => 'ดร.',
                    'name' => 'วรารัตน์ สงฆ์แป้น',
                    'email' => 'wararat@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => null,
                    'name' => 'สันติ ทินตะนัย',
                    'email' => 'sunti@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'คำรณ สุนัติ',
                    'email' => 'skhamron@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'พิพัธน์ เรืองแสง',
                    'email' => 'reungsang@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => null,
                    'name' => 'บุญทรัพย์ ไวคำ',
                    'email' => 'boonsup@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'วชิราวุธ ธรรมวิเศษ',
                    'email' => 'twachi@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'อุราวรรณ จันทร์เกษ',
                    'email' => 'curawa@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'สุมณฑา เกษมวิลาศ',
                    'email' => 'sumkas@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'สายยัญ สายยศ',
                    'email' => 'saiyan@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'ปวีณา วันชัย',
                    'email' => 'wpaweena@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'สิลดา อินทรโสธรฉันท์',
                    'email' => 'silain@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'ผศ.',
                    'degree' => 'ดร.',
                    'name' => 'มัลลิกา วัฒนะ',
                    'email' => 'monlwa@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ. .',
                    'degree' => 'ดร.',
                    'name' => 'ไพรสันต์ ผดุงเวียง',
                    'email' => 'praipa@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ. .',
                    'degree' => 'ดร.',
                    'name' => 'ศักดิ์พจน์ ทองเลี่ยมนาค',
                    'email' => 'sakpod@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'พบพร ด่านวิรุทัย',
                    'email' => 'pobda@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'พงษ์ศธร จันทร์ยอย',
                    'email' => 'pongsathon@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'วรัญญา วรรณศรี',
                    'email' => 'waruwu@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ. .',
                    'degree' => 'ดร.',
                    'name' => 'จักรกฤษณ์ แก้วโยธา',
                    'email' => 'jakkritk@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'เพชร อิ่มทองคำ',
                    'email' => 'phetim@kku.ac.th',
                ],
                [
                    'prefix' => 'Mr.',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'Arfat Ahmad Khan',
                    'email' => 'arfatkhan@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'วาสนา พุฒกลาง',
                    'email' => 'putklang_w@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'ไอศูรย์ กาญจนสุรัตน์',
                    'email' => 'isoonkan@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'ภัคราช มุสิกะวัน',
                    'email' => 'pakamu@kku.ac.th',
                ],
                [
                    'prefix' => 'นางสาว',
                    'position' => 'อ.',
                    'degree' => 'ดร.',
                    'name' => 'ญานิกา คงโสรส',
                    'email' => 'yaniko@kku.ac.th',
                ],
                [
                    'prefix' => 'นาย',
                    'position' => 'อ.',
                    'degree' => null,
                    'name' => 'ธนพล ตั้งชูพงศ์',
                    'email' => 'thanaphon@kku.ac.th',
                ],
            ];
        
        foreach ($teachers as $teacher) {
            $user = DB::table('users')->where('email', $teacher['email'])->first();

            if ($user) {
                Teachers::create([
                    'prefix' => $teacher['prefix'],
                    'position' => $teacher['position'],
                    'degree' => $teacher['degree'],
                    'name' => $teacher['name'],
                    'email' => $teacher['email'],
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
