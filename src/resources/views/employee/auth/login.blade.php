@extends('layouts/app')

@section('title')
    ログイン
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="login">
        <div class="login__wrapper">
            <h2 class="login__title">ログイン</h2>

            <form method="POST" action="{{ url('/login') }}" class="login__form">
                @csrf
                <div class="login__form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <p class="login__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login__form-group">
                    <label for="password">パスワード</label>
                    <input type="password" name="password">
                    @error('password')
                        <p class="login__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login__form-group">
                    <button type="submit" class="login__form-button">ログインする</button>
                    <a href="/register" class="login__form-link">会員登録はこちら</a>
                </div>
            </form>
        </div>
    </div>
@endsection
