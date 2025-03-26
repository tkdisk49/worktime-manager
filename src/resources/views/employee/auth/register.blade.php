{{-- 会員登録画面（一般ユーザー） --}}

<form method="POST" action="{{ route('register') }}">
    @csrf
    <input type="text" name="name" value="{{ old('name') }}">
    <p>
        @error('name')
        {{ $message }}
        @enderror
    </p>

    <input type="email" name="email" value="{{ old('email') }}">
    <p>
        @error('email')
        {{ $message }}
        @enderror
    </p>

    <input type="password" name="password">
    <input type="password" name="password_confirmation">
    <p>
        @error('password')
        {{ $message }}
        @enderror
    </p>

    <button type="submit">登録</button>
</form>