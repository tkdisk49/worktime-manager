{{-- 勤怠一覧画面（一般ユーザー） --}}
@extends('layouts/app')

@section('title')
    勤怠一覧
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employee/attendances/index.css') }}">
@endsection

@section('content')
    <main class="attendance-index">
        <h2 class="attendance-index__title">勤怠一覧</h2>

        <div class="attendance-index__nav">
            <form action="{{ route('attendance.index') }}" method="GET" class="attendance-index__nav-form">
                <input type="hidden" name="month" value="{{ $currentDate->copy()->subMonth()->format('Y-m') }}">
                <button type="submit" class="attendance-index__nav-button">
                    <x-fas-arrow-left class="attendance-index__nav-icon" />
                    <p>前月</p>
                </button>
            </form>

            <div class="attendance-index__current-month">
                <x-radix-calendar class="attendance-index__calendar-icon" />
                <p>{{ $currentDate->format('Y/m') }}</p>
            </div>

            <form action="{{ route('attendance.index') }}" method="GET" class="attendance-index__nav-form">
                <input type="hidden" name="month" value="{{ $currentDate->copy()->addMonth()->format('Y-m') }}">
                <button type="submit" class="attendance-index__nav-button">
                    <p>翌月</p>
                    <x-fas-arrow-right class="attendance-index__nav-icon" />
                </button>
            </form>
        </div>

        <div class="attendance-index__table-wrapper">
            <table class="attendance-index__table">
                <thead class="attendance-index__thead">
                    <tr class="attendance-index__tr">
                        <th class="attendance-index__th">日付</th>
                        <th class="attendance-index__th">出勤</th>
                        <th class="attendance-index__th">退勤</th>
                        <th class="attendance-index__th">休憩</th>
                        <th class="attendance-index__th">合計</th>
                        <th class="attendance-index__th">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr class="attendance-index__tr">
                            <td class="attendance-index__td">
                                {{ \Carbon\Carbon::parse($attendance->work_date)->isoFormat('MM/DD(ddd)') }}
                            </td>
                            <td class="attendance-index__td">
                                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                            </td>
                            <td class="attendance-index__td">
                                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                            </td>
                            <td class="attendance-index__td">
                                {{ isset($attendance->total_break_time)
                                    ? sprintf('%d:%02d', floor($attendance->total_break_time / 60), $attendance->total_break_time % 60)
                                    : '' }}
                            </td>
                            <td class="attendance-index__td">
                                {{ isset($attendance->total_work_time)
                                    ? sprintf('%d:%02d', floor($attendance->total_work_time / 60), $attendance->total_work_time % 60)
                                    : '' }}
                            </td>
                            <td class="attendance-index__td">
                                <a href="#" class="attendance-index__detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="attendance-index__tr">
                            <td colspan="6" class="attendance-index__td attendance-index__id--empty">勤怠データがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
@endsection
