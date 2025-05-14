@extends('layouts/admin_app')

@section('title')
    スタッフ一覧
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendances/staff_list.css') }}">
@endsection

@section('content')
    <div class="staff-list">
        <h2 class="staff-list__title">スタッフ一覧</h2>

        <div class="staff-list__table-wrapper">
            <table class="staff-list__table">
                <thead class="staff-list__thead">
                    <tr class="staff-list__tr">
                        <th class="staff-list__th">名前</th>
                        <th class="staff-list__th">メールアドレス</th>
                        <th class="staff-list__th">月次勤怠</th>
                    </tr>
                </thead>

                <tbody class="staff-list__tbody">
                    @forelse ($staffs as $staff)
                        <tr class="staff-list__tr">
                            <td class="staff-list__td">{{ $staff->name }}</td>
                            <td class="staff-list__td">{{ $staff->email }}</td>
                            <td class="staff-list__td">
                                <a href="{{ route('admin.staff.attendance.monthly', ['id' => $staff->id]) }}" class="staff-list__detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="staff-list__tr">
                            <td colspan="3" class="staff-list__td staff-list__td--empty">従業員がいません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
