<?php

namespace App\Http\Controllers;
use App\Services\TDBMApiService;

class CourseController extends Controller
{
    private $tdbmService;

    public function __construct(TDBMApiService $tdbmService)
    {
        $this->tdbmService = $tdbmService;
    }

    public function index()
    {
        $courses = $this->tdbmService->getCourses();
        $subjects = collect($this->tdbmService->getSubjects())
            ->keyBy('subject_id')
            ->all();
        $semesters = collect($this->tdbmService->getSemesters())
            ->keyBy('semester_id')
            ->all();

        return view('layouts.ta.test', compact('courses', 'subjects', 'semesters'));
    }
}