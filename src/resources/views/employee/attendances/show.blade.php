@extends($layout)

@section('title')
    勤怠詳細
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employee/attendances/show.css') }}">
@endsection

@section('content')
    <div class="attendance-show">
        <h2 class="attendance-show__title">勤怠詳細</h2>

        {{-- 申請済みビュー --}}
        @if ($hasPendingRequest)
            <form class="attendance-show__form">
                <div class="attendance-show__table-wrapper">
                    <table class="attendance-show__table">
                        <tr class="attendance-show__tr">
                            <th>名前</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <p>{{ $attendance->user->name }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>日付</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <p>{{ $formattedYear }}</p>
                                        <p>{{ $formattedMonthDay }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>出勤・退勤</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <p>{{ $attendance->modification->formatted_new_clock_in }}</p>
                                        <p>〜</p>
                                        <p>{{ $attendance->modification->formatted_new_clock_out }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>休憩</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    @foreach ($attendance->breakTimeModifications as $mod)
                                        <div class="attendance-show__td-content">
                                            <p>{{ $mod->formatted_new_break_start }}</p>
                                            <p>〜</p>
                                            <p>{{ $mod->formatted_new_break_end }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>備考</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <p>{{ $attendance->modification->new_remarks }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="attendance-show__notice">*承認待ちのため修正はできません。</p>
            </form>
        @else
            {{-- 新規申請ビュー --}}
            <form action="{{ $formAction }}" method="POST" class="attendance-show__form">
                @csrf
                @if ($formMethod === 'patch')
                    @method('PATCH')
                @endif
                <div class="attendance-show__table-wrapper">
                    <table class="attendance-show__table">
                        <tr class="attendance-show__tr">
                            <th>名前</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <p>{{ $attendance->user->name }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>日付</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <p>{{ $formattedYear }}</p>
                                        <p>{{ $formattedMonthDay }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>出勤・退勤</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <input type="time" name="new_clock_in"
                                            value="{{ $attendance->formatted_clock_in }}" class="attendance-show__input">
                                        <p>〜</p>
                                        <input type="time" name="new_clock_out"
                                            value="{{ $attendance->formatted_clock_out }}" class="attendance-show__input">
                                    </div>
                                    @error('new_clock_in')
                                        <div class="attendance-show__error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>休憩</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    @foreach ($attendance->breakTimes as $index => $break)
                                        <div class="attendance-show__td-content">
                                            <input type="hidden" name="existing_breaks[{{ $index }}][id]"
                                                value="{{ $break->id }}">
                                            <input type="time" name="existing_breaks[{{ $index }}][start]"
                                                value="{{ $break->formatted_break_start }}" class="attendance-show__input">
                                            <p>〜</p>
                                            <input type="time" name="existing_breaks[{{ $index }}][end]"
                                                value="{{ $break->formatted_break_end }}" class="attendance-show__input">
                                        </div>
                                        @error("existing_breaks.$index.start")
                                            <div class="attendance-show__error">{{ $message }}</div>
                                        @enderror
                                    @endforeach
                                    <div class="attendance-show__td-content">
                                        <input type="time" name="new_break_start" value="{{ old('new_break_start') }}"
                                            class="attendance-show__input">
                                        <p>〜</p>
                                        <input type="time" name="new_break_end" value="{{ old('new_break_end') }}"
                                            class="attendance-show__input">
                                    </div>
                                    @error('new_break_start')
                                        <div class="attendance-show__error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-show__tr">
                            <th>備考</th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <textarea name="new_remarks" class="attendance-show__new-remarks">{{ old('new_remarks') }}</textarea>
                                    </div>
                                    @error('new_remarks')
                                        <div class="attendance-show__error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="attendance-show__form-submit">
                    <button type="submit" class="attendance-show__form-button">修正</button>
                </div>
            </form>
        @endif
    </div>
@endsection
