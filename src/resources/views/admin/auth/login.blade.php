{{-- ログイン画面（管理者） --}}
<h1>管理者ログイン画面</h1>

<form method="POST" action="{{ url('/admin/login') }}">
    @csrf

    <label>メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}">
    @error('email')
    <p>{{ $message }}</p>
    @enderror

    <label>パスワード</label>
    <input type="password" name="password">
    @error('password')
    <p>{{ $message }}</p>
    @enderror

    <button type="submit">ログイン</button>
</form>