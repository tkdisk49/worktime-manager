@extends('layouts/app')

@section('title')
    メール認証
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify_email.css') }}">
@endsection

@section('content')
    <div class="verify-email">
        <div class="verify-email__wrapper">
            <div class="verify-email__group">
                <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
                <p>メール認証を完了してください。</p>
            </div>

            <div class="verify-email__group">
                @if (app()->isLocal() || config('app.env') === 'testing')
                    <a href="{{ route('mailhog.redirect') }}" class="verify-email__link">認証はこちらから</a>
                @else
                    <p>メールに記載のリンクをクリックして下さい。</p>
                @endif
            </div>

            <div class="verify-email__group">
                <form method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" class="verify-email__resend-btn">認証メールを再送する</button>
                </form>
            </div>

            @if (session('message'))
                <p class="alert alert-success">{{ session('message') }}</p>
            @endif
        </div>
    </div>
@endsection
