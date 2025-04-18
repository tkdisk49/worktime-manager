@extends('layouts/app')

@section('title')
    会員登録
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register">
        <div class="register__wrapper">
            <h2 class="register__title">会員登録</h2>

            <form method="POST" action="{{ route('register') }}" class="register__form">
                @csrf
                <div class="register__form-group">
                    <label for="name">名前</label>
                    <input type="text" name="name" value="{{ old('name') }}">
                    @error('name')
                        <p class="register__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="register__form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <p class="register__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="register__form-group">
                    <label for="password">パスワード</label>
                    <input type="password" name="password">
                    @error('password')
                        <p class="register__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="register__form-group">
                    <label for="password_confirmation">パスワード確認</label>
                    <input type="password" name="password_confirmation">
                </div>

                <div class="register__form-group">
                    <button type="submit" class="register__form-button">登録する</button>
                    <a href="/login" class="register__form-link">ログインはこちら</a>
                </div>
            </form>
        </div>
    </div>
@endsection
