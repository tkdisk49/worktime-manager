@extends('layouts/admin_app')

@section('title')
    勤怠一覧
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendances/index.css') }}">
@endsection

@section('content')
    <div class="attendance-index">
        <h2 class="attendance-index__title">{{ $currentDate->format('Y年m月d日') }}の勤怠</h2>

        <div class="attendance-index__nav">
            <form action="{{ route('admin.attendance.list') }}" method="GET" class="attendance-index__nav-form">
                <input type="hidden" name="date" value="{{ $currentDate->copy()->subDay()->format('Y-m-d') }}">
                <button type="submit" class="attendance-index__nav-button">
                    <x-fas-arrow-left class="attendance-index__nav-icon" />
                    <p>前日</p>
                </button>
            </form>

            <div class="attendance-index__current">
                <x-radix-calendar class="attendance-index__calendar-icon" />
                <p>{{ $currentDate->format('Y/m/d') }}</p>
            </div>

            <form action="{{ route('admin.attendance.list') }}" method="GET" class="attendance-index__nav-form">
                <input type="hidden" name="date" value="{{ $currentDate->copy()->addDay()->format('Y-m-d') }}">
                <button type="submit" class="attendance-index__nav-button">
                    <p>翌日</p>
                    <x-fas-arrow-right class="attendance-index__nav-icon" />
                </button>
            </form>
        </div>

        <div class="attendance-index__table-wrapper">
            <table class="attendance-index__table">
                <thead class="attendance-index__thead">
                    <tr class="attendance-index__tr">
                        <th class="attendance-index__th">名前</th>
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
                                {{ $attendance->user->name }}
                            </td>
                            <td class="attendance-index__td">
                                {{ $attendance->clock_in ? $attendance->formatted_clock_in : '' }}
                            </td>
                            <td class="attendance-index__td">
                                {{ $attendance->clock_out ? $attendance->formatted_clock_out : '' }}
                            </td>
                            <td class="attendance-index__td">
                                {{ $attendance->formatted_total_break_time }}
                            </td>
                            <td class="attendance-index__td">
                                {{ $attendance->formatted_total_work_time }}
                            </td>
                            <td class="attendance-index__td">
                                {{-- 勤怠詳細画面(管理者)作成後リンク追加 --}}
                                <a href="#" class="attendance-index__detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="attendance-index__tr">
                            <td colspan="6" class="attendance-index__td attendance-index__id--empty">本日の勤怠記録はありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
