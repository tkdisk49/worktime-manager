{{-- 出勤登録画面（一般ユーザー） --}}
@extends('layouts/app')

@section('title')
勤怠登録
@endsection

@section('css')
@endsection

@section('content')
<p>出勤登録画面（一般ユーザー）</p>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">ログアウト</button>
</form>
@endsection