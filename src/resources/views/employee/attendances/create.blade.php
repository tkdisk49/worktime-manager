{{-- 出勤登録画面（一般ユーザー） --}}
@extends('layouts/app')

@section('title')
勤怠登録
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/employee/attendances/create.css') }}">
@endsection

@section('content')
<main class="attendance-main">
    @if (session('error'))
    <div class="attendance-main__error">
        {{ session('error') }}
    </div>
    @endif

    <div class="attendance-main__status">
        <span class="attendance-main__status-label">{{ $statusLabel }}</span>
    </div>

    <div class="attendance-main__date-time">
        <p class="attendance-main__date">{{ $date }}</p>
        <p class="attendance-main__time">{{ $time }}</p>
    </div>

    @if ($user->isOffDuty())
    <form action="{{ route('attendance.store') }}" method="POST" class="attendance-main__form">
        @csrf
        <button type="submit" class="attendance-main__button">出勤</button>
    </form>

    @elseif ($user->isWorking())
    <div class="attendance-main__form-group">
        <form action="{{ route('attendance.clock_out') }}" method="POST" class="attendance-main__form">
            @csrf
            @method('PATCH')
            <button type="submit" class="attendance-main__button">退勤</button>
        </form>
        <form action="" method="POST" class="attendance-main__form">
            @csrf
            <button type="submit" class="attendance-main__button attendance-main__button--white">休憩入</button>
        </form>
    </div>

    @elseif ($user->isOnBreak())
    <form action="" method="POST" class="attendance-main__form">
        @csrf
        <button type="submit" class="attendance-main__button attendance-main__button--white">休憩戻</button>
    </form>

    @elseif ($user->hasLeftWork())
    <div class="attendance-main__message">
        <p>お疲れ様でした。</p>
    </div>
    @endif
</main>
@endsection