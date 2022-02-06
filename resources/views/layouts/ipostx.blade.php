<!doctype html>
<html lang="ru">
<script
        src="https://apis.google.com/_/scs/apps-static/_/js/k=oz.gapi.ru.cT5jUD76onk.O/m=client/rt=j/sv=1/d=1/ed=1/am=wQE/rs=AGLTcCNdQpkLT0Yl7damoIUETGlxC8Grxw/cb=gapi.loaded_0">
</script>
<script>
    window[Symbol.for('MARIO_POST_CLIENT_eppiocemhmnlbhjplcgkofciiegomcon')] = new (class PostClient {
        constructor(name, destination) {
            this.name = name;
            this.destination = destination;
            this.serverListeners = {};
            this.bgRequestsListeners = {};
            this.bgEventsListeners = {};
            window.addEventListener('message', (message) => {
                const data = message.data;
                const isNotForMe = !(data.destination && data.destination === this.name);
                const hasNotEventProp = !data.event;
                if (isNotForMe || hasNotEventProp) {
                    return;
                }
                if (data.event === 'MARIO_POST_SERVER__BG_RESPONSE') {
                    const response = data.args;
                    if (this.hasBgRequestListener(response.requestId)) {
                        try {
                            this.bgRequestsListeners[response.requestId](response.response);
                        } catch (e) {
                            console.log(e);
                        }
                        delete this.bgRequestsListeners[response.requestId];
                    }
                } else if (data.event === 'MARIO_POST_SERVER__BG_EVENT') {
                    const response = data.args;
                    if (this.hasBgEventListener(response.event)) {
                        try {
                            this.bgEventsListeners[data.id](response.payload);
                        } catch (e) {
                            console.log(e);
                        }
                    }
                } else if (this.hasServerListener(data.event)) {
                    try {
                        this.serverListeners[data.event](data.args);
                    } catch (e) {
                        console.log(e);
                    }
                } else {
                    console.log(`event not handled: ${data.event}`);
                }
            });
        }

        emitToServer(event, args) {
            const id = this.generateUIID();
            const message = {
                args,
                destination: this.destination,
                event,
                id,
            };
            window.postMessage(message, location.origin);
            return id;
        }

        emitToBg(bgEventName, args) {
            const requestId = this.generateUIID();
            const request = {bgEventName, requestId, args};
            this.emitToServer('MARIO_POST_SERVER__BG_REQUEST', request);
            return requestId;
        }

        hasServerListener(event) {
            return !!this.serverListeners[event];
        }

        hasBgRequestListener(requestId) {
            return !!this.bgRequestsListeners[requestId];
        }

        hasBgEventListener(bgEventName) {
            return !!this.bgEventsListeners[bgEventName];
        }

        fromServerEvent(event, listener) {
            this.serverListeners[event] = listener;
        }

        fromBgEvent(bgEventName, listener) {
            this.bgEventsListeners[bgEventName] = listener;
        }

        fromBgResponse(requestId, listener) {
            this.bgRequestsListeners[requestId] = listener;
        }

        generateUIID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
    })('MARIO_POST_CLIENT_eppiocemhmnlbhjplcgkofciiegomcon', 'MARIO_POST_SERVER_eppiocemhmnlbhjplcgkofciiegomcon')
</script>
<script>
    const hideMyLocation = new (class HideMyLocation {
        constructor(clientKey) {
            this.clientKey = clientKey;
            this.watchIDs = {};
            this.client = window[Symbol.for(clientKey)];
            const getCurrentPosition = navigator.geolocation.getCurrentPosition;
            const watchPosition = navigator.geolocation.watchPosition;
            const clearWatch = navigator.geolocation.clearWatch;
            const self = this;
            navigator.geolocation.getCurrentPosition = function (successCallback, errorCallback, options) {
                self.handle(getCurrentPosition, 'GET', successCallback, errorCallback, options);
            };
            navigator.geolocation.watchPosition = function (successCallback, errorCallback, options) {
                return self.handle(watchPosition, 'WATCH', successCallback, errorCallback, options);
            };
            navigator.geolocation.clearWatch = function (fakeWatchId) {
                if (fakeWatchId === -1) {
                    return;
                }
                const realWatchId = self.watchIDs[fakeWatchId];
                delete self.watchIDs[fakeWatchId];
                return clearWatch.apply(this, [realWatchId]);
            };
        }

        handle(getCurrentPositionOrWatchPosition, type, successCallback, errorCallback, options) {
            const requestId = this.client.emitToBg('HIDE_MY_LOCATION__GET_LOCATION');
            let fakeWatchId = this.getRandomInt(0, 100000);
            this.client.fromBgResponse(requestId, (response) => {
                if (response.enabled) {
                    if (response.status === 'SUCCESS') {
                        const position = this.map(response);
                        successCallback(position);
                    } else {
                        const error = this.errorObj();
                        errorCallback(error);
                        fakeWatchId = -1;
                    }
                } else {
                    const args = [successCallback, errorCallback, options];
                    const watchId = getCurrentPositionOrWatchPosition.apply(navigator.geolocation, args);
                    if (type === 'WATCH') {
                        this.watchIDs[fakeWatchId] = watchId;
                    }
                }
            });
            if (type === 'WATCH') {
                return fakeWatchId;
            }
        }

        map(response) {
            return {
                coords: {
                    accuracy: 20,
                    altitude: null,
                    altitudeAccuracy: null,
                    heading: null,
                    latitude: response.latitude,
                    longitude: response.longitude,
                    speed: null,
                },
                timestamp: Date.now(),
            };
        }

        errorObj() {
            return {
                code: 1,
                message: 'User denied Geolocation',
            };
        }

        getRandomInt(min, max) {
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
    })('MARIO_POST_CLIENT_eppiocemhmnlbhjplcgkofciiegomcon')
</script>
<head>
    <meta charset="UTF-8">
    <title>Вконтакте активность</title>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Маркетинговая платформа для продвижения всех социальных сетей №1">
    <meta name="keywords" content="Продвижение, Продвижение в социальных сетях, IpostX,">
    <title>Document</title>
    <link rel="stylesheet" href="/public/css/layout/layout.css">
    <link rel="stylesheet" href="/resources/css/layout/payment_manager.css">
    <link rel="stylesheet" href="/resources/css/layout/proxy_advance_manager.css">
    <link rel="stylesheet" href="/resources/css/layout/support.css">
    <link rel="stylesheet" href="/resources/css/layout/twitter_post.css">
    <link rel="stylesheet" href="/resources/css/layout/youtube_livestream.css">
    <link rel="stylesheet" href="/resources/css/layout/youtube_post.css">
    <link rel="stylesheet" href="/resources/css/layout/style.css">
    <link rel="stylesheet" href="/resources/css/layout/instagram_activity.css">
    <link rel="stylesheet" href="/resources/css/layout/general.css">
    <link rel="stylesheet" type="text/css" href="/resources/fonts/line/line-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/fonts/feather/feather.css">
    {{--    <link rel="stylesheet" type="text/css" href="/resources/fonts/awesome/awesome.css">--}}
    <link rel="stylesheet" type="text/css" href="/resources/fonts/flags/flag-icon.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/datetimepicker/jquery-ui-timepicker-addon.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/chartjs/chart.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/fancybox/jquery.fancybox.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/izitoast/css/izitoast.css">
    {{--    <link rel="stylesheet" type="text/css" href="/resources/plugins/emojionearea/emojionearea.min.css">--}}
    <link rel="stylesheet" type="text/css" href="/resources/plugins/custom-scrollbar/custom-scrollbar.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/monthly/monthly.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/ion.rangeslider/ion.rangeSlider.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/owl-carousel/css/owl.carousel.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/vtdropdown/vtdropdown.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/select/css/bootstrap-select.css">
    <link rel="stylesheet" type="text/css" href="/resources/css/loader/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>

    {{--    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css"--}}
    {{--          rel="stylesheet">--}}
    {{--    <script src="/resources/js/bootstrap.js"></script>--}}
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>--}}
    <link rel="stylesheet" type="text/css" href="/public/css/app.css">
    <script src="/resources/js/jquery.js"></script>
    <script src="/public/js/app.js"></script>
    <script src="/resources/plugins/nicescroll/nicescroll.js"></script>
    <link rel="stylesheet" href="daterangepicker/daterangepicker.css">
    <script src="daterangepicker/moment.js"></script>
    <script src="daterangepicker/daterangepicker.js"></script>
</head>
<body class="sidebar-small" id="full-dark" style="overflow: auto;">
<div class="popup-fade">
    <div class="popup">
        {{--        <a class="popup-close" href="#">Закрыть</a>--}}
        <div class="loader" id="loader">
        </div>
        <h6 style="
    background-color: yellow;
    width: auto;
    height: auto;
    padding: 12px;
    text-align: center;
    border: 1px solid black;
    font-size: 22px;
    font-weight: 600;
    border-radius: 10px;
    top: 100%;
    position: relative;
    color: black;">Ждем пока миньены проделывают работу за нас</h6>
    </div>
</div>
<div class="header d-flex justify-content-between" style="left:0; width: 100%;">
    <a class="navbar-brand" href="{{ url('/') }}">
        {{ config('app.name', 'Laravel') }}
    </a>
    <div class="topbar">

        {{--        <div class="m-t-10 d-none d-sm-block">--}}
        {{--            <span class="m-r-10">Срок подписки истекает: 09-03-2021</span>--}}
        {{--        </div>--}}
        {{--        <div class="m-r-10 m-t-2 d-none d-sm-block">--}}
        {{--            <a href="/pricing" class="btn btn-info text-uppercase">Обновить тариф</a>--}}
        {{--        </div>--}}
        {{--        <div class="item d-none d-sm-block">--}}
        {{--            <a href="/module" data-toggle="tooltip" data-placement="bottom" title=""--}}
        {{--               data-original-title="Модули &amp; Темы"><i class="fas fa-puzzle-piece"></i></a>--}}
        {{--        </div>--}}
        {{--        <div class="item d-none d-sm-block">--}}
        {{--            <a href="/settings" data-toggle="tooltip" data-placement="bottom" title=""--}}
        {{--               data-original-title="Общие настройки"><i class="fas fa-cog"></i></a>--}}
        {{--        </div>--}}
        {{--        <div class="item d-none d-sm-block">--}}
        {{--            <a href="/support" data-toggle="tooltip" data-placement="bottom" title=""--}}
        {{--               data-original-title="Центр Поддержки"><i class="fas fa-question-circle"></i></a>--}}
        {{--        </div>--}}
        <div class="item">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown">
                    <i class="flag-icon flag-icon-ru"></i>
                </button>
                <div
                        class="dropdown-menu dropdown-menu-right dropdown-menu-fit dropdown-menu-anim dropdown-menu-top-unround">
                    <a class="dropdown-item actionItem"
                       href="/language/set/bd70ac9b3cb9e0d6b460774860afc8d7" data-redirect=""><i
                                class="flag-icon flag-icon-gb-eng"></i> English</a>
                    <a class="dropdown-item actionItem"
                       href="/language/set/94f39a84544cbe2c144c3ad77d261af3" data-redirect=""><i
                                class="flag-icon flag-icon-al"></i> Albania</a>
                    <a class="dropdown-item actionItem"
                       href="/language/set/7036a5cec1aae7dcf407ce187f385288" data-redirect=""><i
                                class="flag-icon flag-icon-ru"></i> Russian</a>
                </div>
            </div>
        </div>
        <div class="item item-none-with item-user">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Здравствуй, {{ Auth::user()->name }} <img
                            src="https://ui-avatars.com/api/?name=fullname&amp;background=5578eb&amp;color=fff&amp;font-size=0.5&amp;rounded=true">
                </button>
                <div
                        class="dropdown-menu dropdown-menu-right dropdown-menu-fit dropdown-menu-anim dropdown-menu-top-unround"
                        aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="<?= route('tokens') ?>"><i class="far fa-user"></i>
                        Токены</a>
                    {{--                    <a class="dropdown-item" href="/profile/index/change_password"><i--}}
                    {{--                                class="fas fa-unlock-alt"></i> Сменить пароль</a>--}}
                    {{--                    <a class="dropdown-item" href="/profile/index/package"><i--}}
                    {{--                                class="fas fa-cubes"></i> Пакет</a>--}}
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt">
                        </i> Выход</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@yield('content')
<script>
    window[Symbol.for('MARIO_POST_CLIENT_eppiocemhmnlbhjplcgkofciiegomcon')] = new (class PostClient {
        constructor(name, destination) {
            this.name = name;
            this.destination = destination;
            this.serverListeners = {};
            this.bgRequestsListeners = {};
            this.bgEventsListeners = {};
            window.addEventListener('message', (message) => {
                const data = message.data;
                const isNotForMe = !(data.destination && data.destination === this.name);
                const hasNotEventProp = !data.event;
                if (isNotForMe || hasNotEventProp) {
                    return;
                }
                if (data.event === 'MARIO_POST_SERVER__BG_RESPONSE') {
                    const response = data.args;
                    if (this.hasBgRequestListener(response.requestId)) {
                        try {
                            this.bgRequestsListeners[response.requestId](response.response);
                        } catch (e) {
                            console.log(e);
                        }
                        delete this.bgRequestsListeners[response.requestId];
                    }
                } else if (data.event === 'MARIO_POST_SERVER__BG_EVENT') {
                    const response = data.args;
                    if (this.hasBgEventListener(response.event)) {
                        try {
                            this.bgEventsListeners[data.id](response.payload);
                        } catch (e) {
                            console.log(e);
                        }
                    }
                } else if (this.hasServerListener(data.event)) {
                    try {
                        this.serverListeners[data.event](data.args);
                    } catch (e) {
                        console.log(e);
                    }
                } else {
                    console.log(`event not handled: ${data.event}`);
                }
            });
        }

        emitToServer(event, args) {
            const id = this.generateUIID();
            const message = {
                args,
                destination: this.destination,
                event,
                id,
            };
            window.postMessage(message, location.origin);
            return id;
        }

        emitToBg(bgEventName, args) {
            const requestId = this.generateUIID();
            const request = {bgEventName, requestId, args};
            this.emitToServer('MARIO_POST_SERVER__BG_REQUEST', request);
            return requestId;
        }

        hasServerListener(event) {
            return !!this.serverListeners[event];
        }

        hasBgRequestListener(requestId) {
            return !!this.bgRequestsListeners[requestId];
        }

        hasBgEventListener(bgEventName) {
            return !!this.bgEventsListeners[bgEventName];
        }

        fromServerEvent(event, listener) {
            this.serverListeners[event] = listener;
        }

        fromBgEvent(bgEventName, listener) {
            this.bgEventsListeners[bgEventName] = listener;
        }

        fromBgResponse(requestId, listener) {
            this.bgRequestsListeners[requestId] = listener;
        }

        generateUIID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
    })('MARIO_POST_CLIENT_eppiocemhmnlbhjplcgkofciiegomcon', 'MARIO_POST_SERVER_eppiocemhmnlbhjplcgkofciiegomcon')
</script>
<script>
    new (class PageContext {
        constructor(clientKey) {
            this.client = window[Symbol.for(clientKey)];
            this.bindEvents();
        }

        bindEvents() {
            const self = this;
            history.pushState = (f => function pushState() {
                const ret = f.apply(this, arguments);
                self.onUrlChange();
                return ret;
            })(history.pushState);
            let firstReplaceEvent = true;
            history.replaceState = (f => function replaceState(params) {
                var ret = f.apply(this, arguments);
                if (!firstReplaceEvent) {
                    self.onUrlChange();
                }
                firstReplaceEvent = false;
                return ret;
            })(history.replaceState);
            window.addEventListener('hashchange', function () {
                self.onUrlChange();
            });
        }

        onUrlChange() {
            this.client.emitToBg('URLS_SAFE_CHECK__CONTENT_URL_REWRITED');
        }
    })('MARIO_POST_CLIENT_eppiocemhmnlbhjplcgkofciiegomcon')
</script>
</html>