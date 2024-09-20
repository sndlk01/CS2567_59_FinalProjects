<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ApiController extends Controller
{
    public function fetchData()
    {
        try {
            $url = 'https://tdbm.computing.kku.ac.th/api/get_data?table_name=semesters';
            
            $response = Http::get($url);
            $response->throw(); 
            
            $data = $response->json();
                        
            return view('adminHome', compact('data'));
        } catch (Exception $e) {
            return response()->view('errors.500', ['error' => 'Error 500 '], 500);
        }
    }
    
}
