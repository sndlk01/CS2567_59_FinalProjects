@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">Courses</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- ตาราง subjects -->
            {{-- <div class="md:col-span-1">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="font-semibold mb-4">Filter by Subject</h2>
                <ul class="space-y-2">
                    @foreach ($subjects as $subject)
                        <li>
                            <p class="hover:bg-gray-100 p-2 rounded cursor-pointer">
                                {{ $subject['name_en'] }}
                                <span class="text-gray-600 text-sm block">{{ $subject['name_th'] }}</span>
                            </p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div> --}}

            <!-- ตาราง courses -->
            <div class="container mx-auto px-4">
                <div class="mb-6">
                    <select id="semester-select" class="form-select rounded border-gray-300">
                        <option value="all">All Semesters</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester['year'] }}">
                                Year {{ $semester['year'] }} / Semester {{ $semester['semester'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @forelse($courses as $course)
                                @if(isset($subjects[$course['subject_id']]) && isset($semesters[$course['semester_id']]))
                                    <div class="bg-white p-4 rounded course-card" data-year="{{ $semesters[$course['semester_id']]['year'] }}">
                                        <h3 class="font-semibold text-lg">{{ $subjects[$course['subject_id']]['name_en'] }}</h3>
                                        <p class="text-gray-600">{{ $subjects[$course['subject_id']]['name_th'] }}</p>
                                        <div class="mt-2">
                                            <span class="text-sm text-gray-500">Course ID: {{ $course['ref_course_id'] }}</span>
                                            <span class="text-sm text-gray-500 ml-2">Credits: {{ $subjects[$course['subject_id']]['credit'] }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-sm text-gray-500">
                                                Academic Year: {{ $semesters[$course['semester_id']]['year'] }}/{{ $semesters[$course['semester_id']]['semester'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <p class="col-span-2 text-center text-gray-500">No courses found</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
            document.getElementById('semester-select').addEventListener('change', function() {
                const selectedYear = this.value;
                const courses = document.querySelectorAll('.course-card');
                
                courses.forEach(course => {
                    if (selectedYear === 'all' || course.dataset.year === selectedYear) {
                        course.style.display = 'block';
                    } else {
                        course.style.display = 'none';
                    }
                });
            });
            </script>

        </div>
    </div>
@endsection
