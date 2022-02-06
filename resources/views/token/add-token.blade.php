@extends('layouts.ipostx')

@section('content')
    <div class="container" style="margin-top: 100px;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Добавить токен') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('tokenAdd') }}">
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Имя') }}</label>

                                <div class="col-md-6">
                                    <input id="token_name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="" required autocomplete="name" autofocus>

                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="token_value" class="col-md-4 col-form-label text-md-right">{{ __('Значение') }}</label>

                                <div class="col-md-6">
                                    <input id="token_value" type="text" class="form-control @error('name') is-invalid @enderror" name="token_value" value="" required autocomplete="value">

                                    @error('value')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('Тип') }}</label>

                                <div class="col-md-6">

                                    <select id="token_type" name="type" class="form-control">
                                        <option @selectedWhen('0', '0')>Пользователь</option>
                                        <option @selectedWhen('1', '0')>Сообщество</option>
                                    </select>

                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="value" class="col-md-4 col-form-label text-md-right">{{ __('Значение') }}</label>

                                <div class="col-md-6">

                                    <select id="token_active" name="active" class="form-control">
                                        <option @selectedWhen('0', '0')>Дезактивирован</option>
                                        <option @selectedWhen('1', '0')>Активен</option>
                                    </select>

                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Добавить') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection