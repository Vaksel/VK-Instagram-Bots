@extends('layouts.app')

@section('content')
    <div class="container">
        <h4>Ввод осуществляется через запятую без '#'</h4>
        <br>
        <div class="functionality_block">
            <h5>Введите хэштеги для добавления в друзья</h5>
            <input id="addToFriendsTags" type="text">
            <input id="addToFriendsBtn" type="button" value="Добавить">
        </div>
        <div class="functionality_block">
            <h5>Введите хэштеги для удаления из друзей</h5>
            <input id="deleteFromFriendsTags" type="text">
            <input id="deleteFromFriendsBtn" type="button" value="Удалить">
        </div>
    </div>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
        })
    </script>
@endsection