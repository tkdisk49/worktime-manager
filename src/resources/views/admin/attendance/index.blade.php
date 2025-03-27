<h1>勤怠一覧画面（管理者）</h1>
<p>ここに従業員の勤怠一覧を表示</p>

<form method="POST" action="{{ route('admin.logout') }}">
    @csrf
    <button type="submit">ログアウト</button>
</form>