@extends($layout)

@section('title')
    申請一覧
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employee/requests/index.css') }}">
@endsection

@section('content')
    <div class="request-index">
        <h2 class="request-index__title">申請一覧</h2>

        <ul class="request-index__nav">
            <li
                class="request-index__tab {{ request('status', 'pending') === 'pending' ? 'request-index__tab--active' : '' }}">
                <a href="{{ route('employee.requests.index', ['status' => 'pending']) }}">承認待ち</a>
            </li>
            <li class="request-index__tab {{ request('status') === 'approved' ? 'request-index__tab--active' : '' }}">
                <a href="{{ route('employee.requests.index', ['status' => 'approved']) }}">承認済み</a>
            </li>
        </ul>

        <div class="request-index__table-wrapper">
            <table class="request-index__table">
                <thead class="request-index__thead">
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody class="request-index__tbody">
                    @if (request('status', 'pending') === 'pending')
                        @forelse ($pendingRequests as $requestItem)
                            <tr>
                                <td>承認待ち</td>
                                <td>{{ $requestItem->user->name }}</td>
                                <td>{{ $requestItem->formatted_work_date }}</td>
                                <td>{{ $requestItem->new_remarks }}</td>
                                <td>{{ $requestItem->formatted_created_at }}</td>
                                <td>
                                    @if (Auth::guard('admin')->check())
                                        <a href="{{ route('admin.approval.show', ['attendance_correct_request' => $requestItem->attendance_id]) }}"
                                            class="request-index__detail-link">詳細</a>
                                    @else
                                        <a href="{{ route('attendance.modification.show', ['id' => $requestItem->attendance_id]) }}"
                                            class="request-index__detail-link">詳細</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">申請中の勤怠記録はありません</td>
                            </tr>
                        @endforelse
                    @elseif (request('status') === 'approved')
                        @forelse ($approvedRequests as $requestItem)
                            <tr>
                                <td>承認済み</td>
                                <td>{{ $requestItem->user->name }}</td>
                                <td>{{ $requestItem->formatted_work_date }}</td>
                                <td>{{ $requestItem->new_remarks }}</td>
                                <td>{{ $requestItem->formatted_created_at }}</td>
                                <td>
                                    @if (Auth::guard('admin')->check())
                                        <a href="{{ route('admin.approval.show', ['attendance_correct_request' => $requestItem->attendance_id]) }}"
                                            class="request-index__detail-link">詳細</a>
                                    @else
                                        <a href="{{ route('attendance.modification.show', ['id' => $requestItem->attendance_id]) }}"
                                            class="request-index__detail-link">詳細</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">承認済みの勤怠申請はありません</td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
