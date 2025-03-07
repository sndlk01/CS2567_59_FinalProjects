<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TDBMApiService
{
    private $baseUrl = 'https://tdbm.computing.kku.ac.th/api/get_data';
    private $timeout = 180; // 3 minutes

    private function fetchPaginatedData($tableName, $page = 1, $perPage = 1000)
    {
        return Http::timeout($this->timeout)
            ->get("{$this->baseUrl}?table_name={$tableName}&page={$page}&per_page={$perPage}")
            ->json();
    }

    public function getTeachers()
    {
        return $this->fetchData('teachers');
    }

    public function getCurriculums()
    {
        return $this->fetchData('curriculums');
    }

    public function getSubjects()
    {
        return $this->fetchData('subjects');
    }

    public function getCourses()
    {
        return $this->fetchData('courses');
    }

    public function getSemesters()
    {
        return $this->fetchData('semesters');
    }

    public function getClassTypes()
    {
        return $this->fetchData('classtypes');
    }

    public function getCoursesTeachers()
    {
        return $this->fetchData('coursesteachers');
    }

    public function getCurriculumSubjects()
    {
        return $this->fetchData('curriculumsubjects');
    }

    public function getFileUploads()
    {
        return $this->fetchData('fileuploads');
    }

    public function getMajors()
    {
        return $this->fetchData('majors');
    }

    public function getProfiles()
    {
        return $this->fetchData('profiles');
    }

    public function getSchedules()
    {
        return $this->fetchData('schedules');
    }

    public function getStudentClasses()
    {
        return $this->fetchData('studentclasses');
    }

    public function getWageWeights()
    {
        return $this->fetchData('wageweights');
    }

    public function getTeachings()
    {
        return $this->fetchData('teachings');
    }

    public function getExtraTeachings()
    {
        return $this->fetchData('extra_teachings');
    }

    private function fetchData($tableName)
    {
        return Http::timeout($this->timeout)
            ->get("{$this->baseUrl}?table_name={$tableName}")
            ->json();
    }
}
