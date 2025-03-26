<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>@yield('title')</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <!-- Nucleo Icons -->
    <link href="/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="/assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .g-sidenav-show {
            font-family: "Noto Sans Thai", sans-serif;
        }
    </style>
</head>

<body class="g-sidenav-show   bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    <aside
        class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0 p-0 h-100 w-100 d-flex align-items-center justify-content-center" href="/admin">
                <img src="/assets/img/logo-coc2.png" class="navbar-brand-img  h-100" alt="main_logo">
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link " href="{{ url('/announces') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-collection text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">จัดการประกาศ</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin-ta-requests">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-archive-2 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">คำร้องขอผู้ช่วยสอน</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/tausers">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-circle-08 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">จัดการข้อมูลผู้ช่วยสอน</span>
                        @if(($pendingRequestsCount ?? 0) > 0)
      <span class="ms-2 badge rounded-pill bg-danger">
        {{ $pendingRequestsCount }}
      </span>
    @endif
                    </a>
                </li>
            </ul>

            <!-- ปุ่มซิงค์ข้อมูลทั้งหมด อยู่ด้านล่างสุด -->
            <div class="mt-5">
                <ul class="navbar-nav">
                    <li class="nav-item mt-3">
                        <a class="nav-link bg-primary" href="{{ route('admin.sync-all-data') }}"
                            style="border-radius: 0.5rem; margin: 0 16px;">
                            <div
                                class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-cloud-download-95 text-white text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1 text-white">ซิงค์ข้อมูลทั้งหมด</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>

    <main class="main-content position-relative border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur"
            data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <!-- First Navbar -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white"
                                href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-white active" aria-current="page">Tables</li>
                    </ol>
                    {{-- <h6 class="font-weight-bolder text-white mb-0">Teacher</h6> --}}
                </nav>
                <!-- End First Navbar -->
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                    </div>

                    <ul class="navbar-nav  justify-content-end">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#"
                                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    v-pre>
                                    {{ Auth::user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('admin.change-password') }}">เปลี่ยนรหัสผ่าน</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                        {{ __('ออกจากระบบ') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest

                        <!-- Start Hamburger menu -->
                        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                                <div class="sidenav-toggler-inner">
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                </div>
                            </a>
                        </li>
                        <!-- End Hamburger menu -->
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <div style="font-family: 'Noto Sans Thai', sans-serif;">
                @yield('content')
            </div>
        </div>
    </main>

    <!--   Core JS Files   -->
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="/assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Fonts Awesome Icon -->
    <script src="https://kit.fontawesome.com/2db00bb8e9.js" crossorigin="anonymous"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="/assets/js/argon-dashboard.min.js?v=2.0.4"></script>
    @stack('scripts')
</body>

</html>
