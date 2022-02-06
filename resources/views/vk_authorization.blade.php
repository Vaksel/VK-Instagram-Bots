@extends('layouts.app')

@section('content')
<div class="container">
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
        })
    </script>
    <form action="/authorize" id="authDataForm">
        <label for="appIdInput">
            ID приложения:
            <input type="text" id="appIdInput" name="appIdInput">
        </label>
        <label for="appSecretKeyInput">
            Секретный ключ:
            <input type="text" id="appSecretKeyInput" name="appSecretKeyInput">
        </label>
        <label for="appTokenName">
            Имя токена:
            <input type="text" id="appTokenName" name="appTokenName">
        </label>
        <label for="appTokenType">
            Тип токена(пользователь/сообщество):
            <input type="checkbox" id="appTokenType" name="appTokenType">
        </label>
        <div>
            <p>Разрешения для токена пользователя</p>
            <div>
                <label for="">
                    Notify
                    <input type="checkbox" name="user_permission-notify">
                </label>
                <label for="">
                    Friends
                    <input type="checkbox" name="user_permission-friends">
                </label>
                <label for="">
                    Photos
                    <input type="checkbox" name="user_permission-photos">
                </label>
                <label for="">
                    Audio
                    <input type="checkbox" name="user_permission-audio">
                </label>
                <label for="">
                    Video
                    <input type="checkbox" name="user_permission-video">
                </label>
                <label for="">
                    Stories
                    <input type="checkbox" name="user_permission-stories">
                </label>
                <label for="">
                    Pages
                    <input type="checkbox" name="user_permission-pages">
                </label>
                <label for="">
                    Status
                    <input type="checkbox" name="user_permission-status">
                </label>
                <label for="">
                    Notes
                    <input type="checkbox" name="user_permission-notes">
                </label>
                <label for="">
                    Messages
                    <input type="checkbox" name="user_permission-messages">
                </label>
                <label for="">
                    Wall
                    <input type="checkbox" name="user_permission-wall">
                </label>
                <label for="">
                    Ads
                    <input type="checkbox" name="user_permission-ads">
                </label>
                <label for="">
                    Offline
                    <input type="checkbox" name="user_permission-offline">
                </label>
                <label for="">
                    Docs
                    <input type="checkbox" name="user_permission-docs">
                </label>
                <label for="">
                    Groups
                    <input type="checkbox" name="user_permission-groups">
                </label>
                <label for="">
                    Notifications
                    <input type="checkbox" name="user_permission-notifications">
                </label>
                <label for="">
                    Stats
                    <input type="checkbox" name="user_permission-stats">
                </label>
                <label for="">
                    Email
                    <input type="checkbox" name="user_permission-email">
                </label>
                <label for="">
                    Market
                    <input type="checkbox" name="user_permission-market">
                </label>
            </div>
            <p>Разрешения для токена сообщества</p>
            <div>
                <label for="">
                    Stories
                    <input type="checkbox" name="group_permission-stories">
                </label>
                <label for="">
                    Photos
                    <input type="checkbox" name="group_permission-stories">
                </label>
                <label for="">
                    AppWidget
                    <input type="checkbox" name="group_permission-app_widget">
                </label>
                <label for="">
                    Messages
                    <input type="checkbox" name="group_permission-messages">
                </label>
                <label for="">
                    Docs
                    <input type="checkbox" name="group_permission-docs">
                </label>
                <label for="">
                    Manage
                    <input type="checkbox" name="group_permission-manage">
                </label>
            </div>
        </div>


        <input type="submit" id="getKeyButton" value="Получить токен">
        <input type="hidden" name="_token" value="rwqG5pADI3aYpZrZ8UmGCFbOAvsjl8WNQxg2nMZp"></form>
    <script type="text/javascript" src="/resources/js/authorization.js">
    </script>
</div>
@endsection
