@extends('layouts.ipostx')

@php
    $gridData = [
        'dataProvider' => $dataProvider,
        'title' => 'Ваши токены',
        'useFilters' => false,
        'tableHover' => false,
        'columnFields' => [
            [
                'label'     => 'Часть токена(не передавайте токен сомнительным сайтам)',
                'value' => function ($row) {
                    return substr($row->token_value, 0, 50) . '...';
                }
            ],
            [
                'label' => 'Тип токена',
                'value' => function ($row) {
                    return $row->type ? 'Сообщество' : 'Пользователь';
                },
            ],
            [
                'label' => 'Статус',
                'value' => function ($row) {
                    return $row->active ? 'Активен' : 'Дезактивирован';
                },
            ],
            [
                'label'     => 'Дата добавления',
                'attribute' => 'created_at'
            ],
            [
                'label'     => 'Действия',
                'class' => Itstructure\GridView\Columns\ActionColumn::class,
                'actionTypes' => [
                    [
                        'class' => Itstructure\GridView\Actions\View::class, // Required
                        'htmlAttributes' => [ // Optional
                            'target' => '_blank',
                            'style' => 'color: yellow; font-size: 16px;',
                            'onclick' => "showToken(event)",
                            'id' => 'viewAction'
                        ]
                    ],
                    'edit' => function ($data) {
                        return route('tokenEdit', ['id' => $data->id]);
                    },
                    [
                        'class' => Itstructure\GridView\Actions\Delete::class, // Required
                        'url' => function ($data) { // Optional
                            return route('tokenDelete', ['id' => $data->id]);
                        },
                        'htmlAttributes' => [ // Optional
                            'target' => '_blank',
                            'style' => 'color: yellow; font-size: 16px;',
                            'onclick' => 'return window.confirm("Вы уверены что хотите удалить этот токен?");'
                        ]
                    ]
                ]
            ],
        ]
    ];
@endphp

@section('content')
    <style>

        .modal-content {
            position: relative;
            background-color: #E1BEE7;
            margin: auto;
            padding: 0;
            border: 5px solid #7B1FA2;
            width: 65%;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
            -webkit-animation-name: animatetop;
            -webkit-animation-duration: 0.4s;
            animation-name: animatetop;
            animation-duration: 0.4s
        }


        @-webkit-keyframes animatetop {
            from {top:-300px; opacity:0}
            to {top:0; opacity:1}
        }

        @keyframes animatetop {
            from {top:-300px; opacity:0}
            to {top:0; opacity:1}
        }


        .close {
            color: white;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-header {
            padding: 2px 16px;
            background-color: #9C27B0;
            color: white;
        }

        .modal-body {padding: 2px 16px;}

        .modal-footer {
            padding: 2px 16px;
            background-color: #9C27B0;
            color: white;
        }

        tbody {
            color: white;
        }
    </style>

    <div class="container" style="margin-top: 100px;">
        @if (session('token_change_success'))
            <div class="alert alert-success">
                <ul>
                    <li>{{ session('token_change_success') }}</li>
                </ul>
            </div>
        @endif
        @if (session('token_change_fail'))
            <div class="alert alert-danger">
                <ul>
                    <li>{{ session('token_change_success') }}</li>
                </ul>
            </div>
        @endif
            <div class="f-flex flex-row">
                <a href="{{route('tokenAdd')}}" class="btn btn-success">Добавить токен</a>
                <a href="http://oauth.vk.com/authorize?client_id=7766150&display=page&redirect_uri=https%3A%2F%2Foauth.vk.com%2Fblank.html&scope=friends%2Cphotos%2Caudio%2Cvideo%2Cstatus%2Cwall%2Cgroups%2Coffline%2Cstats%2Cemail&response_type=token&v=5.130" target="_blank" class="btn btn-info">Получить токен</a>
            </div>

        @gridView($gridData)
    </div>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Ваш токен:</p>
                <div class="d-flex justify-content-between">
                    <input type="text" class="form-control" readonly id="vk_token" style="margin-top: 20px;">
                    <input type="button" class="btn btn-warning" value="Скопировать" onclick="copyToken()" style="margin: 20px;">
                </div>

            </div>
        </div>
    </div>
    <script>
            let modal = document.getElementById('myModal');

            var span = document.getElementsByClassName("close")[0];

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            function showToken(event)
            {
                event.preventDefault();

                $(document).ready(function() {
                    $.ajax({
                        method: 'POST',
                        url: document.getElementById('viewAction').getAttribute('href'),
                        data: { _token: '{{csrf_token()}}'},
                        dataType: 'json'
                    })
                        .done(function (res) {
                            if(res.success)
                            {
                                console.log(res);
                                $('#vk_token').val(res.token);
                                modal.style.display = "block";
                            }
                            else
                            {
                                alert('Попробуйте снова. Ошибка: ' + res.error);
                            }
                            return true;
                        })
                        .fail(function (res) {
                            alert('Попробуйте снова. Ошибка: ' + 'ошибка при отправке запроса на сервер');
                            return false;
                        })
                });
            }

            function copyToken()
            {
                let tokenField = document.getElementById('vk_token');

                tokenField.select();

                document.execCommand('copy');

                alert('Ваш токен в буфере обмена');
            }

    </script>
@endsection
