{{-- 出勤登録画面（一般ユーザー） --}}
<p>出勤登録画面（一般ユーザー）</p>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">ログアウト</button>
</form>