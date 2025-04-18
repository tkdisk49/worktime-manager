@extends('layouts/app')

@section('title')
    勤怠登録
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employee/attendances/create.css') }}">
@endsection

@section('content')
    <div class="attendance-create">
        @if (session('error'))
            <div class="attendance-create__error">
                {{ session('error') }}
            </div>
        @endif

        <div class="attendance-create__status">
            <span class="attendance-create__status-label">{{ $statusLabel }}</span>
        </div>

        <div class="attendance-create__date-time">
            <p class="attendance-create__date">{{ $date }}</p>
            <p class="attendance-create__time">{{ $time }}</p>
        </div>

        @if ($user->isOffDuty())
            <form action="{{ route('attendance.work_start') }}" method="POST" class="attendance-create__form">
                @csrf
                <button type="submit" class="attendance-create__button">出勤</button>
            </form>
        @elseif ($user->isWorking())
            <div class="attendance-create__form-group">
                <form action="{{ route('attendance.work_end') }}" method="POST" class="attendance-create__form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="attendance-create__button">退勤</button>
                </form>
                <form action="{{ route('attendance.break_start') }}" method="POST" class="attendance-create__form">
                    @csrf
                    <button type="submit" class="attendance-create__button attendance-create__button--white">休憩入</button>
                </form>
            </div>
        @elseif ($user->isOnBreak())
            <form action="{{ route('attendance.break_end') }}" method="POST" class="attendance-create__form">
                @csrf
                @method('PATCH')
                <button type="submit" class="attendance-create__button attendance-create__button--white">休憩戻</button>
            </form>
        @elseif ($user->hasLeftWork())
            <div class="attendance-create__message">
                <p>お疲れ様でした。</p>
            </div>
        @endif
    </div>
@endsection
