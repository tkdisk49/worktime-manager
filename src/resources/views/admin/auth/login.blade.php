{{-- ログイン画面（管理者） --}}
@extends('layouts/admin_app')

@section('title')
    管理者ログイン
@endsection

@section('css')
@endsection

@section('content')
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
@endsection
