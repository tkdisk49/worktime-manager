@extends('layouts/admin_app')

@section('title')
    {{ $staff->name }}さんの勤怠
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendances/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/attendances/staff_attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-index">
        <h2 class="attendance-index__title">{{ $staff->name }}さんの勤怠</h2>

        <div class="attendance-index__nav">
            <form action="{{ route('admin.staff.attendance.monthly', ['id' => $staff->id]) }}" method="GET"
                class="attendance-index__nav-form">
                <input type="hidden" name="month" value="{{ $currentDate->copy()->subMonth()->format('Y-m') }}">
                <button type="submit" class="attendance-index__nav-button">
                    <x-fas-arrow-left class="attendance-index__nav-icon" />
                    <p>前月</p>
                </button>
            </form>

            <div class="attendance-index__current">
                <x-radix-calendar class="attendance-index__calendar-icon" />
                <p>{{ $currentDate->format('Y/m') }}</p>
            </div>

            <form action="{{ route('admin.staff.attendance.monthly', ['id' => $staff->id]) }}" method="GET"
                class="attendance-index__nav-form">
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
                                {{ $attendance->formatted_work_date }}
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
                                <a href="{{ route('attendance.modification.show', ['id' => $attendance->id]) }}"
                                    class="attendance-index__detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="attendance-index__tr">
                            <td colspan="6" class="attendance-index__td attendance-index__td--empty">当月の勤怠記録はありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="attendance-index__button">
            <form action="{{ route('admin.staff.attendance.monthly.csv', ['id' => $staff->id]) }}" method="GET">
                <input type="hidden" name="month" value="{{ $currentDate->format('Y-m') }}">
                <button type="submit" class="attendance-index__csv-export-button">CSV出力</button>
            </form>
        </div>
    </div>
@endsection
