{{-- 管理者共通ヘッダー --}}
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__logo">
            <a href="/admin/login" class="header__logo-link">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header__logo-img">
            </a>
        </div>
        @auth
            <nav class="header__nav">
                <ul class="header__nav-list">
                    <li class="header__nav-item">
                        <a href="/admin/attendance/list" class="header__nav-link">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/admin/staff/list" class="header__nav-link">スタッフ一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/stamp_correction_request/list" class="header__nav-link">申請一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <form action="{{ route('admin.logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="header__nav-link logout-form__link">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        @endauth
    </header>

    <main class="content">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>

</html>
