@extends('layout')

@section('title')
    Отзывы
@endsection


@section('main_content')
    <h1>Форма добавления отзыва</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/review/check" method="post">
        @csrf
        <input type="email" name="email" id="email" placeholder="Введите email" class="form-control">
        <input type="text" name="subject" id="subject" placeholder="Введите отзыв" class="form-control">
        <textarea name="message" id="message" cols="30" rows="10" placeholder="Введите сообщение" class="form-control"></textarea><br>
        <button type="submit" class="btn btn-success">Отправить</button>
    </form>

    <h2>Все отзывы</h2>
    @foreach($reviews->all() as $el)
        <div class="alert alert-warning">
            <h3>{{ $el->subject }}</h3>
        </div>
    @endforeach
@endsection
