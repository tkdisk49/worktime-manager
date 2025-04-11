{{-- 勤怠詳細画面（一般ユーザー） --}}
@extends('layouts.app')

@section('title')
    勤怠詳細
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employee/attendances/show.css') }}">
@endsection

@section('content')
    <main class="attendance-show">
        <h2 class="attendance-show__title">勤怠詳細</h2>

        {{-- 申請済みビュー --}}
        @if ($hasPendingRequest)
            <form class="attendance-show__form">
                <table class="attendance-show__table">
                    <tr>
                        <th>名前</th>
                        <td>{{ Auth::user()->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>{{ $formattedDate }}</td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <input type="time" value="{{ $attendance->modification->new_clock_in }}" disabled>
                            〜
                            <input type="time" value="{{ $attendance->modification->new_clock_out }}" disabled>
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            @foreach ($attendance->breakTimeModifications as $mod)
                                <div class="attendance-show__break-row">
                                    <input type="time" value="{{ $mod->new_break_start }}" disabled>
                                    〜
                                    <input type="time" value="{{ $mod->new_break_end }}" disabled>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea class="attendance-show__new-remarks" disabled>{{ $attendance->modification->new_remarks }}</textarea>
                        </td>
                    </tr>
                </table>

                <p class="attendance-show__notice">* 承認待ちのため修正はできません。</p>
            </form>

            {{-- 新規申請ビュー --}}
        @else
            <form action="{{ route('attendance.modification.store', ['id' => $attendance->id]) }}" method="POST"
                class="attendance-show__form">
                @csrf
                <table class="attendance-show__table">
                    <tr>
                        <th>名前</th>
                        <td>{{ Auth::user()->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>{{ $formattedDate }}</td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <input type="time" name="new_clock_in" value="{{ $attendance->formatted_clock_in }}">
                            〜
                            <input type="time" name="new_clock_out" value="{{ $attendance->formatted_clock_out }}">
                            @error('new_clock_in')
                                <div class="attendance-show__error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            @foreach ($attendance->breakTimes as $index => $break)
                                <div class="attendance-show__break-row">
                                    <input type="hidden" name="existing_breaks[{{ $index }}][id]"
                                        value="{{ $break->id }}">
                                    <input type="time" name="existing_breaks[{{ $index }}][start]"
                                        value="{{ $break->formatted_break_start }}">
                                    〜
                                    <input type="time" name="existing_breaks[{{ $index }}][end]"
                                        value="{{ $break->formatted_break_end }}">
                                    @error("existing_breaks.$index.start")
                                        <div class="attendance-show__error">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                            <div class="attendance-show__break-row">
                                <input type="time" name="new_break_start" value="{{ old('new_break_start') }}">
                                〜
                                <input type="time" name="new_break_end" value="{{ old('new_break_end') }}">
                                @error('new_break_start')
                                    <div class="attendance-show__error">{{ $message }}</div>
                                @enderror
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="new_remarks" class="attendance-show__new-remarks">{{ old('new_remarks') }}</textarea>
                            @error('new_remarks')
                                <div class="attendance-show__error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div class="attendance-show__form-submit">
                    <button type="submit" class="attendance-show__form-button">修正</button>
                </div>
            </form>
        @endif
    </main>
@endsection
