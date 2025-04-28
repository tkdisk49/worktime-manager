{{-- 修正申請承認画面（管理者） --}}
@extends('layouts.admin_app')

@section('title')
    勤怠詳細
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employee/attendances/show.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/approvals/show.css') }}">
@endsection

@section('content')
    <div class="attendance-show">
        <h2 class="attendance-show__title">勤怠詳細</h2>

        {{-- updateメソッド作成後form action記載 --}}
        <form action="{{ route('admin.approval.update', ['attendance_correct_request' => $attendance->id]) }}" method="POST"
            class="attendance-show__form">
            @csrf
            @method('PATCH')
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

                    @foreach ($attendance->breakTimeModifications as $breakTimeMod)
                        <tr class="attendance-show__tr">
                            <th>
                                @if ($loop->first)
                                    休憩
                                @else
                                    休憩{{ $loop->iteration }}
                                @endif
                            </th>
                            <td>
                                <div class="attendance-show__td-container">
                                    <div class="attendance-show__td-content">
                                        <p>{{ $breakTimeMod->formatted_new_break_start }}</p>
                                        <p>〜</p>
                                        <p>{{ $breakTimeMod->formatted_new_break_end }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach

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

            <div class="attendance-show__form-submit">
                @if ($hasPendingRequest)
                    <button type="submit" class="attendance-show__form-button">承認</button>
                @else
                    <button type="button" class="attendance-show__form-button form-button--disabled" disabled>承認済み</button>
                @endif
            </div>
        </form>
    </div>
@endsection
