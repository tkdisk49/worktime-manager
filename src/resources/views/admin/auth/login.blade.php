{{-- ログイン画面（管理者） --}}
@extends('layouts/admin_app')

@section('title')
    管理者ログイン
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="login">
        <div class="login__wrapper">
            <h2 class="login__title">管理者ログイン</h2>

            <form method="POST" action="{{ route('admin.login.store') }}" class="login__form">
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
                    <button type="submit" class="login__form-button">管理者ログインする</button>
                </div>
            </form>
        </div>
    </div>
@endsection
