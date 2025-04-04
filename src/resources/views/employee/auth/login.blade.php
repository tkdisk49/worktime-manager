{{-- ログイン画面（一般ユーザー） --}}
@extends('layouts/app')

@section('title')
ログイン
@endsection

@section('css')

@endsection

@section('content')
<form method="POST" action="{{ url('/login') }}">
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
@endsection