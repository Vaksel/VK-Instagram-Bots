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
    <title>?????????????????? ????????????????????</title>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="?????????????????????????? ?????????????????? ?????? ?????????????????????? ???????? ???????????????????? ?????????? ???1">
    <meta name="keywords" content="??????????????????????, ?????????????????????? ?? ???????????????????? ??????????, IpostX,">
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
        {{--        <a class="popup-close" href="#">??????????????</a>--}}
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
    color: black;">???????? ???????? ?????????????? ?????????????????????? ???????????? ???? ??????</h6>
    </div>
</div>
<div class="header" style="left:0; width: 100%;">
    <a class="btn btn-warning" style="color: black;" href="https://ipostx.ru/dashboard">
        ?????????? ???? ???????????? ?? ?????????????? ???? ???????????? ????????????????????
    </a>
    <div class="topbar">
        {{--        <div class="m-t-10 d-none d-sm-block">--}}
        {{--            <span class="m-r-10">???????? ???????????????? ????????????????: 09-03-2021</span>--}}
        {{--        </div>--}}
        {{--        <div class="m-r-10 m-t-2 d-none d-sm-block">--}}
        {{--            <a href="/pricing" class="btn btn-info text-uppercase">???????????????? ??????????</a>--}}
        {{--        </div>--}}
        {{--        <div class="item d-none d-sm-block">--}}
        {{--            <a href="/module" data-toggle="tooltip" data-placement="bottom" title=""--}}
        {{--               data-original-title="???????????? &amp; ????????"><i class="fas fa-puzzle-piece"></i></a>--}}
        {{--        </div>--}}
        {{--        <div class="item d-none d-sm-block">--}}
        {{--            <a href="/settings" data-toggle="tooltip" data-placement="bottom" title=""--}}
        {{--               data-original-title="?????????? ??????????????????"><i class="fas fa-cog"></i></a>--}}
        {{--        </div>--}}
        {{--        <div class="item d-none d-sm-block">--}}
        {{--            <a href="/support" data-toggle="tooltip" data-placement="bottom" title=""--}}
        {{--               data-original-title="?????????? ??????????????????"><i class="fas fa-question-circle"></i></a>--}}
        {{--        </div>--}}
        <div class="tokens btn btn-light" style="color: #222b45 !important; margin-right: 10px !important; margin-bottom: 7px !important;">
            <a  style="color: #222b45 !important;" href="<?= route('tokens') ?>"><i class="far fa-user"></i>
                ????????????</a>
        </div>
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
                    ????????????????????, {{ Auth::user()->name }} <img
                            src="{{$avatarUrl}}">
                </button>
                <div
                        class="dropdown-menu dropdown-menu-right dropdown-menu-fit dropdown-menu-anim dropdown-menu-top-unround"
                        aria-labelledby="dropdownMenuButton">
{{--                    <a class="dropdown-item" href="/profile/index/change_password"><i--}}
{{--                                class="fas fa-unlock-alt"></i> ?????????????? ????????????</a>--}}
{{--                    <a class="dropdown-item" href="/profile/index/package"><i--}}
{{--                                class="fas fa-cubes"></i> ??????????</a>--}}
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt">
                        </i> ??????????</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
{{--<div class="sidebar">--}}
{{--    <a href="javascript:void(0);" class="sidebar-toggle">--}}
{{--        <i class="ft-chevrons-left"></i>--}}
{{--    </a>--}}

{{--    <div class="logo">--}}
{{--        <a href="https://www.ipostx.ru/dashboard">--}}
{{--            <span class="logo-small"><img--}}
{{--                        src="https://ipostx.ru/inc/themes/backend/default/assets/img/top_big.png"></span>--}}
{{--            <span class="logo-full"><img--}}
{{--                        src="https://ipostx.ru/inc/themes/backend/default/assets/img/logo-white.png"></span>--}}
{{--        </a>--}}
{{--    </div>--}}

{{--    <div class="menu mCustomScrollbar _mCS_1 mCS-autoHide" style="position: relative; overflow: visible;">--}}
{{--        <div id="mCSB_1" class="mCustomScrollBox mCS-minimal-dark mCSB_vertical mCSB_outside" style="max-height: none;"--}}
{{--             tabindex="0">--}}
{{--            --}}{{--            <div id="mCSB_1_container" class="mCSB_container" style="position: relative; top: -649px; left: 0px;"--}}
{{--            <div id="mCSB_1_container" class="mCSB_container" style="position: relative; left: 0px;"--}}
{{--                 dir="ltr">--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/dashboard">--}}
{{--                        <span class="menu-icon"><i class="fas fa-desktop" style=""></i></span>--}}
{{--                        <span class="menu-desc">????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/post">--}}
{{--                        <span class="menu-icon"><i class="far fa-paper-plane" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????????????????? ??????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/rss">--}}
{{--                        <span class="menu-icon"><i class="fas fa-rss-square" style=""></i></span>--}}
{{--                        <span class="menu-desc">RSS feed</span>--}}
{{--                    </a>--}}
{{--                </div>--}}

{{--                <div class="menu-separator"></div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-facebook-square" style=""></i></span>--}}
{{--                        <span class="menu-desc">Facebook</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/facebook_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/facebook_livestream">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item active">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-instagram" style=""></i></span>--}}
{{--                        <span class="menu-desc">Instagram</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/instagram_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/instagram_livestream">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="active">--}}
{{--                            <a href="https://www.ipostx.ru/instagram_activity">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/instagram_analytics">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">??????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/instagram_direct_message">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">???????????????? ??????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-twitter" style=""></i></span>--}}
{{--                        <span class="menu-desc">Twitter</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/twitter_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-linkedin" style=""></i></span>--}}
{{--                        <span class="menu-desc">Linkedin</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/linkedin_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-pinterest" style=""></i></span>--}}
{{--                        <span class="menu-desc">Pinterest</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/pinterest_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/pinterest_activity">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-telegram" style=""></i></span>--}}
{{--                        <span class="menu-desc">Telegram</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/telegram_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-vk" style=""></i></span>--}}
{{--                        <span class="menu-desc">Vk</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/vk_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-youtube" style=""></i></span>--}}
{{--                        <span class="menu-desc">Youtube</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/youtube_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/youtube_livestream">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fas fa-store" style=""></i></span>--}}
{{--                        <span class="menu-desc">Google my business</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/google_my_business_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fab fa-odnoklassniki" style=""></i></span>--}}
{{--                        <span class="menu-desc">Ok</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/ok_post">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}

{{--                <div class="menu-separator"></div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/proxy_advance_manager">--}}
{{--                        <span class="menu-icon"><i class="fas fa-user-shield" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????? ????????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}

{{--                <div class="menu-separator"></div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/schedules">--}}
{{--                        <span class="menu-icon"><i class="far fa-calendar-alt" style=""></i></span>--}}
{{--                        <span class="menu-desc">????????????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/account_manager">--}}
{{--                        <span class="menu-icon"><i class="far fa-user-circle" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????????? ??????????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/file_manager">--}}
{{--                        <span class="menu-icon"><i class="far fa-folder-open" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????????? ????????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/group_manager">--}}
{{--                        <span class="menu-icon"><i class="fas fa-users" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????????? ????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/caption">--}}
{{--                        <span class="menu-icon"><i class="far fa-list-alt" style=""></i></span>--}}
{{--                        <span class="menu-desc">??????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/watermark">--}}
{{--                        <span class="menu-icon"><i class="fas fa-medal" style=""></i></span>--}}
{{--                        <span class="menu-desc">?????????????? ????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}

{{--                <div class="menu-separator"></div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/support">--}}
{{--                        <span class="menu-icon"><i class="fas fa-question-circle" style=""></i></span>--}}
{{--                        <span class="menu-desc">?????????? ??????????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/package_manager">--}}
{{--                        <span class="menu-icon"><i class="fas fa-cubes" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????????? ??????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/proxy_manager">--}}
{{--                        <span class="menu-icon"><i class="fas fa-shield-alt" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????? ????????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/user_manager">--}}
{{--                        <span class="menu-icon"><i class="far fa-user" style=""></i></span>--}}
{{--                        <span class="menu-desc">??????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/language">--}}
{{--                        <span class="menu-icon"><i class="fas fa-globe" style=""></i></span>--}}
{{--                        <span class="menu-desc">????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/payment_manager">--}}
{{--                        <span class="menu-icon"><i class="fas fa-money-check" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????????? ????????????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/faqs_manager">--}}
{{--                        <span class="menu-icon"><i class="far fa-question-circle" style=""></i></span>--}}
{{--                        <span class="menu-desc">Faqs manager</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/module">--}}
{{--                        <span class="menu-icon"><i class="fas fa-puzzle-piece" style=""></i></span>--}}
{{--                        <span class="menu-desc">???????????? &amp; ????????</span>--}}
{{--                    </a>--}}
{{--                </div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="javascript:void(0);">--}}
{{--                        <span class="menu-icon"><i class="fas fa-cog" style=""></i></span>--}}
{{--                        <span class="menu-desc">??????????????????</span>--}}
{{--                    </a>--}}

{{--                    <span class="menu-arrow">--}}
{{--								<i class="ft-chevron-right"></i>--}}
{{--							</span>--}}

{{--                    <ul class="submenu">--}}

{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/settings">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">?????????? ??????????????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="https://www.ipostx.ru/social_network_configuration">--}}
{{--                                <span class="menu-icon"><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="menu-desc">?????????????????? ?????? ??????????</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}

{{--                <div class="menu-separator"></div>--}}


{{--                <div class="menu-item ">--}}
{{--                    <a href="https://www.ipostx.ru/cronjobs">--}}
{{--                        <span class="menu-icon"><i class="fas fa-sync" style=""></i></span>--}}
{{--                        <span class="menu-desc">Cronjobs</span>--}}
{{--                    </a>--}}
{{--                </div>--}}

{{--                <div class="menu-separator"></div>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--        <div id="mCSB_1_scrollbar_vertical"--}}
{{--             class="mCSB_scrollTools mCSB_1_scrollbar mCS-minimal-dark mCSB_scrollTools_vertical"--}}
{{--             style="display: block;">--}}
{{--            <div class="mCSB_draggerContainer">--}}
{{--                <div id="mCSB_1_dragger_vertical" class="mCSB_dragger"--}}
{{--                     style="position: absolute; min-height: 50px; display: block; height: 140px; max-height: 457.48px; top: 193px;">--}}
{{--                    <div class="mCSB_dragger_bar" style="line-height: 50px;"></div>--}}
{{--                </div>--}}
{{--                <div class="mCSB_draggerRail"></div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
<div class="wrapper" style="margin-left: 0px;">

    <div class="subheader instagram-activity" style="left:0;padding-left:6%;">
        <div class="wrap">
            <div class="subheader-main wrap-m w-100 p-r-0">
                <div class="wrap-c">
                    <h3 class="title"><i class="fab fa-vk" style="color: #d62976"></i> ???????????????????? Vkontakte </h3>
                </div>
            </div>
            <div class="subheader-main wrap-m w-100 p-r-0">
                <div class="wrap-c">
                    <h2 class="title" style="background-color: red;">?????? ?????????????????????????? ?????? ???? ???? ???????????? ???????????? ??????????????
                        ?????????????? (?????????? ?????????? 3-5 ?????????? ??????????????????)</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="content-one-column instagram-activity"
         style="overflow: auto; outline: none; position: relative; width:105%;" tabindex="1">


        <div class="ig-ac-settings">
            <div class="ig-ac-header">

                <div class="top wrap-m">
                    <div class="brand wrap-c">

                        <div class="avatar">
                            {{--                            <a href="https://instagram.com/muchbetterthanyouthought" target="_blank">--}}
                            <img src="{{$vkPhotoUrl}}">
                            {{--                            </a>--}}
                        </div>
                        <div class="info">
                            <div class="title">{{ Auth::user()->name }}</div>
                            <div class="desc">??????????????</div>
                        </div>

                    </div>

                    {{--                    <div class="menu wrap-c">--}}
                    {{--                        <ul>--}}
                    {{--                            <li class="active"><a--}}
                    {{--                                        href="/instagram_activity/page/settings/45550220883"><i--}}
                    {{--                                            class="fas fa-cog"></i> ??????????????????</a></li>--}}
                    {{--                            <li class=""><a href="/instagram_activity/page/profile/45550220883"><i--}}
                    {{--                                            class="fas fa-user"></i> ??????????????</a></li>--}}
                    {{--                            <li class=""><a href="/instagram_activity/page/stats/45550220883"><i--}}
                    {{--                                            class="far fa-chart-bar"></i> ????????????????????</a></li>--}}
                    {{--                            <li class=""><a href="/instagram_activity/page/log/45550220883"><i--}}
                    {{--                                            class="fas fa-history"></i> ????????</a></li>--}}
                    {{--                        </ul>--}}
                    {{--                        <div class="clearfix"></div>--}}
                    {{--                    </div>--}}
                </div>

            </div>

            <div class="ig-ac-main">

                <div class="row">

                    {{--                    <div class="col-md-4">--}}

                    {{--                        <div class="control">--}}

                    {{--                            <div class="loading text-center wrap-c">--}}
                    {{--                                <i class="ig-ac-status started far fa-clock text-info pe-spin m-auto fs-100 d-none"></i>--}}
                    {{--                                <i class="ig-ac-status stopped far fa-stop-circle text-danger m-auto fs-100"></i>--}}

                    {{--                            </div>--}}
                    {{--                            <div class="info">--}}
                    {{--                                <div class="item-info wrap-m">--}}
                    {{--                                    <div class="wrap-c">????????????</div>--}}
                    {{--                                    <span class="ig-ac-status started badge badge-success d-none">??????????????</span>--}}
                    {{--                                    <span class="ig-ac-status stopped badge badge-danger d-none">????????????????????</span>--}}
                    {{--                                    <span class="ig-ac-status none badge badge-dark">?????? ??????????????</span>--}}
                    {{--                                </div>--}}
                    {{--                                <div class="item-info wrap-m">--}}
                    {{--                                    <div class="wrap-c">???????????????? ??</div>--}}
                    {{--                                    <div class="wrap-c">--</div>--}}
                    {{--                                </div>--}}
                    {{--                            </div>--}}

                    {{--                            <div class="action">--}}
                    {{--                                <div class="btn-group btn-group-block" role="group">--}}
                    {{--                                    <a href="/instagram_activity/start/45550220883"--}}
                    {{--                                       class="btn btn-secondary ig-ac-btn-start ">??????????</a>--}}
                    {{--                                    <a href="/instagram_activity/stop/45550220883"--}}
                    {{--                                       class="btn btn-label-danger ig-ac-btn-stop d-none">????????</a>--}}
                    {{--                                    <div class="btn-group" role="group">--}}
                    {{--                                        <a href="javascript:void(0);" class="btn btn-secondary open_schedule_days"--}}
                    {{--                                           data-toggle="tooltip" data-target="#schedule_days" data-placement="top"--}}
                    {{--                                           title="" data-original-title="????????????????????">--}}
                    {{--                                            <i class="far fa-calendar-alt"></i>--}}
                    {{--                                        </a>--}}
                    {{--                                    </div>--}}
                    {{--                                </div>--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}

                    {{--                    </div>--}}
                    <div class="col-md-4">
                        <div class="tasks">
                            <div class="title fw-6 text-info">????????????????, ?????? ???? ???????????? ??????????????</div>
                            <div class="item-task wrap-m">
                                <div class="wrap-c">
                                    <label class="i-switch i-switch--outline i-switch--info m-t-6 m-r-6">
                                        <input type="checkbox" name="todos[like]" class="action-save"
                                               data-type="like" checked="true" value="1">
                                        <span></span>
                                    </label>
                                    <div class="name fs-16">
                                        ???????? <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                                data-trigger="hover" data-placement="top" data-html="true" title=""
                                                data-original-title="???????????????? ???????? ??????????????????????????, ?????????? ???????????????????????????????? ???????? ???????????????????? ????????????. <br/> <br/> ?????????????? ????????????????????, ?????????????? ???????????????????? ?? ?????????? ?????? ?????????????????????? ?? ?????????????? ???????????? ?????????? ?????????????????? ????????????????????."></i>

                                    </div>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    {{ $data['data']['all_likes'] ?? '0' }}
                                </div>
                            </div>
                            <div class="item-task wrap-m">
                                <div class="wrap-c">
                                    <label class="i-switch i-switch--outline i-switch--info m-t-6 m-r-6">
                                        <input type="checkbox" name="todos[comment]" class="action-save"
                                               data-type="comment" checked="true" value="1">
                                        <span></span>
                                    </label>
                                    <div class="name fs-16">
                                        ?????????????????????? <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                                       data-trigger="hover" data-placement="top" data-html="true"
                                                       title=""
                                                       data-original-title="???????????????? ???????? ??????????????????????????, ?????????? ???????????????????????????????? ???????? ??????????????????????. <br/> <br/> ?????????????? ????????????????????, ?????????????? ???????????????????? ?? ?????????? ???? ?????????????????????????????????? ?? ?????????????? ???????????? ?????????? ?????????????????? ????????????????????."></i>
                                    </div>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    0
                                </div>
                            </div>
                            <div class="item-task wrap-m">
                                <div class="wrap-c">
                                    <label class="i-switch i-switch--outline i-switch--info m-t-6 m-r-6">
                                        <input type="checkbox" name="todos[watching_story]" class="action-save"
                                               data-type="friends" checked="true" value="1">
                                        <span></span>
                                    </label>
                                    <div class="name fs-16">
                                        ???????????? ?? ???????????????? <i class="fa fa-question-circle"
                                                            data-toggle="tooltip-custom" data-trigger="hover"
                                                            data-placement="top" data-html="true" title=""
                                                            data-original-title="???????????????? ???????? ??????????????????????????, ?????????? ???????????????????????????????? ???????????????? ??????????????."></i>
                                    </div>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    0
                                </div>
                            </div>
                            <div class="item-task wrap-m">
                                <div class="wrap-c">
                                    <label class="i-switch i-switch--outline i-switch--info m-t-6 m-r-6">
                                        <input type="checkbox" name="todos[follow]" class="action-save"
                                               data-type="follow" checked="true" value="1">
                                        <span></span>
                                    </label>
                                    <div class="name fs-16">
                                        ????????????????
                                    </div>

                                    <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                       data-trigger="hover" data-placement="top" data-html="true"
                                       title=""
                                       data-original-title="???????????????? ???????? ??????????????????????????, ?????????? ???????????????????????????????? ???????? ????????????????. <br/> <br/> ?????????????? ????????????????????, ???? ?????????????? ?????????????????????????? ???? ?????????????????????? ?? ?????????????? ???????????? ?????????? ?????????????????? ????????????????????."></i>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    {{ $data['data']['accept_followers'] ?? '0' }}
                                </div>
                            </div>
                            <div class="item-task wrap-m">
                                <div class="wrap-c">
                                    <label class="i-switch i-switch--outline i-switch--info m-t-6 m-r-6">
                                        <input type="checkbox" name="todos[unfollow]" class="action-save"
                                               data-type="unfollow" checked="true" value="1">
                                        <span></span>
                                    </label>
                                    <div class="name fs-16">
                                        ??????????????
                                    </div>
                                    <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                       data-trigger="hover" data-placement="top" data-html="true"
                                       title=""
                                       data-original-title="???????????????? ???????? ??????????????????????????, ?????????? ???????????????????????????????? ???????? ??????????????. <br/> <br/> ?????????????? ????????????????????, ???? ?????????????? ?????????????????????????? ???? ???????????????????? ?? ?????????????? ???????????? ?????????? ?????????????????? ????????????????????."></i>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    {{ $data['data']['deleted_friends'] ?? '0' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 recent-activity-wrap">
                        <div class="title m-b-8 fw-6 text-info">???????????????? ????????????????????</div>
                        <div class="nicescroll no-update" style="overflow-y: auto; outline: none;"
                             tabindex="2">

                            @if (session('commentFieldRequired'))
                                <br>
                                <div class="alert alert-warning" role="alert">
                                    {{ session('commentFieldRequired') }}
                                </div>
                            @endif

                                @if (session('token_fail'))
                                    <br>
                                    <div class="alert alert-warning" role="alert">
                                        {{ session('token_fail') }}
                                    </div>
                                @endif

                                @if (session('auth_fail'))
                                    <br>
                                    <div class="alert alert-warning" role="alert">
                                        {{ session('auth_fail') }}
                                    </div>
                                @endif

                            @if (session('successTagRec'))
                                <br>
                                <div class="alert alert-success" role="alert">
                                    {{ session('successTagRec') }}
                                </div>
                            @endif

                            @if (session('not_valid_comment_concurrents'))
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    {{ session('not_valid_comment_concurrents') }}
                                </div>
                            @endif

                            @if (session('concurrentCommentsSuccess'))
                                <br>
                                <div class="alert alert-success" role="alert">
                                    {{ session('concurrentCommentsSuccess') }}
                                </div>
                            @endif

                            @if (session('search_records.auto_likes'))
                                <br>
                                <div class="alert alert-success" role="alert">
                                    {{ session('search_records.auto_likes') }}
                                </div>
                            @endif

                            @if (session('search_records_flood.auto_likes'))
                                <br>
                                <div class="alert alert-success" role="alert">
                                    {{ session('search_records_flood.auto_likes') }}
                                </div>
                            @endif

                            @if (session('adding_friend'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('adding_friend') }}
                                </div>
                            @endif

                            @if (session('com_photo_like'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('com_photo_like') }}
                                </div>
                            @endif

                            @if (session('com_photo_like_captcha'))
                                <div class="alert alert-warning" role="alert">
                                    {{ session('com_photo_like_captcha') }}
                                </div>
                            @endif

                            @if (session('com_photo_like_unhadled'))
                                <div class="alert alert-warning" role="alert">
                                    {{ session('com_photo_like_unhadled') }}
                                </div>
                            @endif

                            @if (session('com_photo_like_flood'))
                                <div class="alert alert-warning" role="alert">
                                    {{ session('com_photo_like_flood') }}
                                </div>
                            @endif

                            @if (session('com_post_like'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('com_post_like') }}
                                </div>
                            @endif

                            @if (session('com_post_like_captcha'))
                                <div class="alert alert-warning" role="alert">
                                    {{ session('com_post_like_captcha') }}
                                </div>
                            @endif

                            @if (session('com_post_like_unhandled'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('com_post_like_unhandled') }}
                                </div>
                            @endif

                            @if (session('com_post_like_flood'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('com_post_like_flood') }}
                                </div>
                            @endif

                            @if (session('posts.autolike_followers_success') && !session('posts.autolike_followers'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('posts.autolike_followers_success') }}
                                </div>
                            @endif

                            @if (session('posts.autolike_followers'))
                                <div class="alert alert-warning" role="alert">
                                    {{ session('posts.autolike_followers') }}
                                </div>
                            @endif

                            @if (session('not_valid_like_concurrents'))
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    {{ session('not_valid_like_concurrents') }}
                                </div>
                            @endif

                            @if (session('autoLikesSuccess') && !session('photos.autolike_concurrents') && !session('posts.autolike_concurrents'))
                                <br>
                                <div class="alert alert-success" role="alert">
                                    {{ session('autoLikesSuccess') }}
                                </div>
                            @endif

                            @if (session('photos.autolike_concurrents') && session('posts.autolike_concurrents'))
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    {{ session('photos.autolike_concurrents') }}
                                </div>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    {{ session('posts.autolike_concurrents') }}
                                </div>
                            @endif

                            @if (session('auto_like_criteries_success'))
                                <br>
                                <div class="alert alert-success" role="alert">
                                    {{ session('auto_like_criteries_success') }}
                                </div>
                            @endif
                            @if (session('posts.autolike_members'))
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    {{ session('posts.autolike_members') }}
                                </div>
                            @endif
                            @if (session('photos.autolike_members'))
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    {{ session('photos.autolike_members') }}
                                </div>
                            @endif

                            @if (session('photos.autolike_members'))
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    {{ session('photos.autolike_members') }}
                                </div>
                            @endif

                            @if (session('delete_friend'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('delete_friend') }}
                                </div>
                            @endif

                            @if (session('accepting_friends'))
                                <div class="alert alert-info" role="alert">
                                    {{ session('accepting_friends') }}
                                </div>
                            @endif


                            {{--                            <div class="empty small"></div>--}}
                        </div>
                    </div>
                    <div class="col-md-4"></div>
                </div>

            </div>

            <div class="ig-ac-options">

                <div class="ig-ac-tab nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                     aria-orientation="vertical">
                    {{--                    <a class="active" id="v-targeting-tab" data-toggle="pill" href="#v-targeting" role="tab"--}}
                    {{--                       aria-controls="v-targeting" aria-selected="true"><i class="fas fa-bullseye"></i> ????????</a>--}}
                    {{--                    <a id="v-speed-tab" data-toggle="pill" href="#v-speed" role="tab" aria-controls="v-speed"--}}
                    {{--                       aria-selected="false"><i class="fas fa-running"></i> ????????????????</a>--}}
                    {{--                    <a id="v-pills-filter-tab" data-toggle="pill" href="#v-pills-filter" role="tab"--}}
                    {{--                       aria-controls="v-pills-filter" aria-selected="false"><i class="fas fa-filter"></i>--}}
                    {{--                        Filters</a>--}}
                    <a id="v-pills-comment-tab" data-toggle="pill" href="#v-pills-comment"
                       role="tab" aria-controls="v-pills-comment" aria-selected="true" class="active"><i
                                class="far fa-comment-alt"></i> ???????????????????? ????????????????????????</a>
                    <a id="v-pills-commenthashtag-tab" data-type="comment" data-toggle="pill"
                       href="#v-pills-commenthashtag"
                       role="tab" aria-controls="v-pills-commenthashtag" aria-selected="false" class=""><i
                                class="far fa-comment-alt"></i> ?????????????????????? ???? ????????????????</a>
                    <a id="v-pills-commentconcurrents-tab" data-type="comment" data-toggle="pill"
                       href="#v-pills-commentconcurrents"
                       role="tab" aria-controls="v-pills-commentconcurrents" aria-selected="false" class=""><i
                                class="far fa-comment-alt"></i> ?????????????????????? ???? ??????????????????????</a>
                    <!-- <a id="v-pills-watching-story-tab" data-type="watching_story" data-toggle="pill" href="#v-pills-watching-story" role="tab" aria-controls="v-pills-watching-story" aria-selected="false"><i class="far fa-eye"></i> ???????????????? ??????????????</a> -->
                    <a id="v-pills-follow-tab" data-type="follow" data-toggle="pill" href="#v-pills-follow"
                       role="tab" aria-controls="v-pills-follow" aria-selected="false" class=""><i
                                class="fas fa-user-plus"></i> ????????????????</a>
                    <a id="v-pills-unfollow-tab" data-type="unfollow" data-toggle="pill" href="#v-pills-unfollow"
                       role="tab" aria-controls="v-pills-unfollow" aria-selected="false" class=""><i
                                class="fas fa-user-minus"></i> ??????????????</a>
                    {{--                    <a id="v-pills-direct-tab" data-type="direct" data-toggle="pill" href="#v-pills-direct"--}}
                    {{--                       role="tab" aria-controls="v-pills-direct" aria-selected="false" class=""><i--}}
                    {{--                            class="fas fa-inbox"></i> Auto bot message</a>--}}
                    <a id="v-pills-repost-tab" data-type="like" data-toggle="pill" href="#v-pills-repost"
                       role="tab" aria-controls="v-pills-repost" aria-selected="false" class=""><i
                                class="fas fa-reply-all"></i> ???????????????????? ??????????</a>
                    <a id="v-pills-search-tab" data-type="like" data-toggle="pill" href="#v-pills-search-tag" role="tab"
                       aria-controls="v-pills-search-tab" aria-selected="false"><i class="fas fa-hashtag"></i> ?????????? ????
                        ????????????????</a>

                    <a id="v-pills-tag-tab" data-type="friends" data-toggle="pill" href="#v-pills-tag" role="tab"
                       aria-controls="v-pills-tag" aria-selected="false"><i class="fas fa-hashtag"></i> ???????? ??????
                        ???????????????????? ????????????</a>
                    <a id="v-pills-blacklist-tab" data-type="friends" data-toggle="pill" href="#v-pills-blacklist" role="tab"
                       aria-controls="v-pills-blacklist" aria-selected="false"><i class="fas fa-ban"></i> ???????? ??????
                        ???????????????? ????????????</a>
                    <a id="v-pills-location-tab" data-type="like" data-toggle="pill" href="#v-pills-location" role="tab"
                       aria-controls="v-pills-location" aria-selected="false"><i class="fas fa-heart"></i>
                        ?????????? ???? ??????????????????????</a>
                    <a id="v-pills-likefriends-tab" data-type="like" data-toggle="pill" href="#v-pills-likefriends" role="tab"
                       aria-controls="v-pills-likefriends" aria-selected="false"><i class="fas fa-heart"></i>
                        ?????????? ?????????????? ?? ??????????????????????</a>
                    <a id="v-pills-likeconcurents-tab" data-type="like" data-toggle="pill" href="#v-pills-likeconcurents" role="tab"
                       aria-controls="v-pills-likeconcurents" aria-selected="false"><i
                                class="fas fa-heart"></i>
                        ?????????? ??????????????????????</a>
                    <a id="v-pills-likecriteries-tab" data-type="like" data-toggle="pill" href="#v-pills-likecriteries" role="tab"
                       aria-controls="v-pills-likecriteries" aria-selected="false"><i class="fas fa-heart"></i>
                        ?????????? ?????????????????????? ????????????</a>
                    {{--                    <a id="v-pills-auto-stop-tab" data-toggle="pill" href="#v-pills-auto-stop" role="tab"--}}
                    {{--                       aria-controls="v-pills-auto-stop" aria-selected="false"><i class="far fa-stop-circle"></i>--}}
                    {{--                        ???????? ????????</a>--}}
                </div>

                <div class="ig-ac-content">
                    <div class="tab-content p-t-25" id="v-pills-tabContent">
                        {{--                        <div class="tab-pane fade show active" id="v-targeting" role="tabpanel"--}}
                        {{--                             aria-labelledby="v-targeting-tab">--}}

                        {{--                            <div class="row">--}}

                        {{--                                <div class="col-md-4 col-sm-6 m-b-25">--}}
                        {{--                                    <div class="ig-ac-option wrap-m">--}}
                        {{--                                        <div class="info wrap-c">--}}
                        {{--                                            <span class="p-r-5">???????? </span>--}}
                        {{--                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"--}}
                        {{--                                               data-html="true" data-trigger="hover" data-placement="top" title=""--}}
                        {{--                                               data-original-title="???? ???????????? ?????????????????? ???????????????? ???? ???????????? ?????????????? ?????????? ?????? ???????????????????????????? ??????????, ?????????????????????? ?? <b> ??????????</b>, ?????????????????????? ?? ???????? ??????????????????, ?????? ?????? ?????????????????????????? ???? ??????????????????????????, ?????????????????????????? ?????? ??????????. <br/> <br/> <span class = 'text-info'> ????????:</span> ???????? ???????????????? ???????????????????? ???????????????? ???????????????????? ???? ???????? ???????????? ???????????????????? ????????????????????, ?????????????? ???? ???????????? ??????????????. <br/> <br/> <span class = 'text-warning'> ??????????: </span> ?????????? ???????????????????????? ???????? ????????????????, ???? ???????????? ???????????????? ?????? ?????????????? 1 ?????? ?? ???????????? <b> ????????</b>."></i>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="action wrap-c">--}}
                        {{--                                            <label class="i-checkbox i-checkbox--tick i-checkbox--brand p-l-17">--}}
                        {{--                                                <input type="checkbox" class="action-save" name="targets[tag]"--}}
                        {{--                                                       checked="true" value="1"><span></span>--}}
                        {{--                                            </label>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}

                        {{--                                <div class="col-md-4 col-sm-6 m-b-25">--}}
                        {{--                                    <div class="ig-ac-option wrap-m">--}}
                        {{--                                        <div class="info wrap-c">--}}
                        {{--                                            <span class="p-r-5">???????????????????????????? </span>--}}
                        {{--                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"--}}
                        {{--                                               data-html="true" data-trigger="hover" data-placement="top" title=""--}}
                        {{--                                               data-original-title="???? ???????????? ?????????????????? ???????????????? ???? ???????????? ?????????????? ?????????? ?????? ???????????????????????????? ??????????, ?????????????????????? ?? <b> ????????????????????????????</b>, ?????????????????????? ?? ???????? ??????????????????, ?????? ?????? ?????????????????????????? ???? ??????????????????????????, ?????????????????????????? ?????? ??????????. <br/> <br/> <span class = 'text-info'> ????????:</span> ???????? ???????????????? ???????????????????? ???????????????? ???????????????????? ???? ???????? ???????????? ???????????????????? ????????????????????, ?????????????? ???? ???????????? ??????????????. <br/> <br/> <span class = 'text-warning'> ??????????: </span> ?????????? ???????????????????????? ???????? ????????????????, ???? ???????????? ???????????????? ?????? ?????????????? 1 ???????????????????????????? ?? ???????????? <b> ????????????????????????????</b>."></i>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="action wrap-c">--}}
                        {{--                                            <label class="i-checkbox i-checkbox--tick i-checkbox--brand p-l-17">--}}
                        {{--                                                <input type="checkbox" class="action-save" name="targets[location]"--}}
                        {{--                                                       checked="true" value="1"><span></span>--}}
                        {{--                                            </label>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}

                        {{--                                <div class="col-md-4 col-sm-6 m-b-25">--}}
                        {{--                                    <div class="ig-ac-option wrap-m">--}}
                        {{--                                        <div class="info wrap-c">--}}
                        {{--                                            <span class="p-r-5">???????????????????? </span>--}}
                        {{--                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"--}}
                        {{--                                               data-html="true" data-trigger="hover" data-placement="top" title=""--}}
                        {{--                                               data-original-title="???? ???????????? ?????????????????? ???????????????? ???????????????? ???? ???????????? ?????????????? ???? ????????????????????????????, ?????????????? ?????????????? ???? <b> ?????????????? ?????????????????????????? </ b>, ???????????????????????? ?? ???????? ?????????????????? (???????????????????????? ???????? ??????????????????????????), ?? / ?????? ?????????????? ?????????? ?????? ???????????????????????????? 1-3 ?????????? ?????????????????? ??????????, ???????????????????????????? ?????????? ????????????????????????????. <Br /> ???? ?????????? ???????????? ?????????????????? ?????????????????? ???? ?????????? ?????????????????????? (??????????????????????????, ?????????????? ???????????? ???? ?????????? ?????????????? ??????????????), ???????????? <b> ?????? ?????????????? ???????????? </ b> ?????? <b> ?????? </ b>. <br/> <br/> <span class = ' text-info '> INFO: </ span> ???????? ???????????????? ???????????????????? ???????????????? ???????????????????? ???? ???????? ???????????? ???????????????????? ????????????????????, ?????????????? ???? ???????????? ??????????????. <br/> <br/> <span class =' text-warning '> ??????????: </ span> ?????????? ???????????????????????? ???????? ????????????????, ?????? ?????????? ?????????????????????????? ???????????????? ?????? ?????????????? 2 ?????????? ???????????????????????? ?? ???????????? <b> ?????????? ?????????????????????????? </ b>."></i>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="action wrap-c">--}}
                        {{--                                            <select class="form-control action-save" name="targets[follower]">--}}
                        {{--                                                <option value="">-</option>--}}
                        {{--                                                <option value="user">??????</option>--}}
                        {{--                                                <option value="me">?????? ??????????????</option>--}}
                        {{--                                                <option value="all">??????</option>--}}
                        {{--                                            </select>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}

                        {{--                                <div class="col-md-4 col-sm-6 m-b-25">--}}
                        {{--                                    <div class="ig-ac-option wrap-m">--}}
                        {{--                                        <div class="info wrap-c">--}}
                        {{--                                            <span class="p-r-5">???????????????? </span>--}}
                        {{--                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"--}}
                        {{--                                               data-html="true" data-trigger="hover" data-placement="top" title=""--}}
                        {{--                                               data-original-title="???? ???????????? ?????????????????? ???????????????? ???????????????? ???? ???????????? ?????????????? ???? ????????????????????????????, ???? ???????????????? ?? ???????????????????? ?????????????????? <b> ?????????? ?????????????????????????? </ b> (?????????????????? ???? ?????????????? ????????????????????????????), ?? / ?????? ?????????????? ?????????? ?????? ???????????????????????????? 1-3 ?????????????????? ??????????, ?????????????????????? ?????????? ????????????????????????????. <Br /> ???? ?????????? ???????????? ?????????????????? ?????????????????? ???? ?????????? ?????????????????????? (??????????????????????????, ???? ?????????????? ???? ??????????????????), ???????????? <b> ?????? ?????????? </ b> ?????? <b> ?????? </ b>. <br/> <br/> <span class = 'text- info '> INFO: </ span> ???????? ???????????????? ???????????????????? ???????????????? ???????????????????? ???? ???????? ???????????? ???????????????????? ????????????????????, ?????????????? ???? ???????????? ??????????????. <br/> <br/> <span class =' text-warning '> ??????????: </ span> ???????????????????????? ?? ???????? ?????????????????? ?????? ?????????? ?????????????????????????? ???????????????? ?????? ?????????????? 2 ?????????? ???????????????????????? ?? ???????????? <b> ?????????? ?????????????????????????? </ b>."></i>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="action wrap-c">--}}
                        {{--                                            <select class="form-control action-save" name="targets[following]">--}}
                        {{--                                                <option value="">-</option>--}}
                        {{--                                                <option value="user">??????</option>--}}
                        {{--                                                <option value="me">?????? ??????????????</option>--}}
                        {{--                                                <option value="all">??????</option>--}}
                        {{--                                            </select>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}

                        {{--                                <div class="col-md-4 col-sm-6 m-b-25">--}}
                        {{--                                    <div class="ig-ac-option wrap-m">--}}
                        {{--                                        <div class="info wrap-c">--}}
                        {{--                                            <span class="p-r-5">?????????? </span>--}}
                        {{--                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"--}}
                        {{--                                               data-html="true" data-trigger="hover" data-placement="top" title=""--}}
                        {{--                                               data-original-title="Based on selected Activity Actions, you can follow users who have liked the media posted by the <b>Usernames</b> added in your settings, and/or like or comment on 1-3 most recent media posted by those users.<br/> You can also target your own Likers (users who have liked your media) by selecting <b>My posts</b> or <b>All</b>.<br/><br/> <span class='text-info'>INFO:</span> This targeting source works independently of all other targeting sources that you can select.<br/><br/> <span class='text-warning'>IMPORTANT:</span> To use this source you may need to add at least 2 usernames in the <b>Usernames</b> list."></i>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="action wrap-c">--}}
                        {{--                                            <select class="form-control action-save" name="targets[liker]">--}}
                        {{--                                                <option value="">-</option>--}}
                        {{--                                                <option value="user">?????????? ????????????</option>--}}
                        {{--                                                <option value="me">My posts</option>--}}
                        {{--                                                <option value="all">??????</option>--}}
                        {{--                                            </select>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}

                        {{--                                <div class="col-md-4 col-sm-6 m-b-25">--}}
                        {{--                                    <div class="ig-ac-option wrap-m">--}}
                        {{--                                        <div class="info wrap-c">--}}
                        {{--                                            <span class="p-r-5">?????????????????????? </span>--}}
                        {{--                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"--}}
                        {{--                                               data-html="true" data-trigger="hover" data-placement="top" title=""--}}
                        {{--                                               data-original-title="???? ???????????? ?????????????????? ???????????????? ???????????????? ???? ???????????? ?????????????? ???? ????????????????????????????, ???????????????????????????????????????? ??????????????????????, ???????????????????????????? <b> ?????????????? ?????????????????????????? </ b>, ???????????????????????? ?? ???????? ??????????????????, ?? / ?????? ???????????????????????????? ?????? ?????????????????????????????????????? 1-3 ?????????? ?????????????????? ????????????????????, ???????????????????????????? ?????????? ????????????????????????????. < br /> ???? ?????????? ???????????? ?????????????????? ?????????????????? ???? ?????????? ?????????????????????? ?????????????????????????? (??????????????????????????, ?????????????? ?????????????????????????????????? ???????? ????????????????????), ???????????? <b> ?????? ?????????????????? </ b> ?????? <b> ?????? </ b>. <br/> <br/> <span class = 'text-info'> INFO: </ span> ???????? ???????????????? ???????????????????? ???????????????? ???????????????????? ???? ???????? ???????????? ???????????????????? ????????????????????, ?????????????? ???? ???????????? ??????????????. <br/> <br/> <span class = 'text-warning'> ??????????: < / span> ?????????? ???????????????????????? ???????? ????????????????, ?????? ?????????? ?????????????????????????? ???????????????? ?????? ?????????????? 2 ?????????? ???????????????????????? ?? ???????????? <b> ?????????? ?????????????????????????? </ b>."></i>--}}
                        {{--                                        </div>--}}
                        {{--                                        <div class="action wrap-c">--}}
                        {{--                                            <select class="form-control action-save" name="targets[commenter]">--}}
                        {{--                                                <option value="">-</option>--}}
                        {{--                                                <option value="user">?????????? ????????????</option>--}}
                        {{--                                                <option value="me">My posts</option>--}}
                        {{--                                                <option value="all">??????</option>--}}
                        {{--                                            </select>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}

                        {{--                            </div>--}}

                        {{--                        </div>--}}
                        <div class="tab-pane fade" id="v-speed" role="tabpanel" aria-labelledby="v-speed-tab">

                            <div class="row">

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">???????????????? ???????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="<b>????????????</b> ??? ???????????????????? ???????????????? ?????? ????????????????????<br/><br/><b>????????????????????</b> ??? ?????????? ???????????????? ?????? ????????????????????<br/><br/><b>??????????????</b> ??? ???? ???????????????????? ???????????????? ????????????????????<br/><br/>???????????????????? ?????? ????????????<b>????????????</b> ?????????? ???????? ?????? ???????????? ???????????????? ???? <b>????????????????????</b> ?????? <b>??????????????</b> ?????????? ?????????????????? ????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control speed-level" name="speeds[level]">
                                                <option selected="true" value="slow" data-speed="[2,1,2,2,2,1,1]">
                                                    ????????????
                                                </option>
                                                <option value="normal" data-speed="[4,2,4,4,4,2,2]">????????????????????
                                                </option>
                                                <option value="fast" data-speed="[6,3,6,6,6,3,3]">??????????????</option>
                                                <option value="" disabled="" class="">????????</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????????/?????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="??????-???? ???????????? ?????????????? ????????  ???????????????????? ?????????????????????? ?????????????? ???? ??????.<br/><br/>???????????????????? ????????????????: <b>1</b>-<b>60</b><br/><br/><span class='text-danger'>???????????? ??????????????????!</span>"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control item-speed action-save"
                                                   name="speeds[like]" value="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????????????????????/?????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="??????-???? ???????????????????????? ?????????????? ????????  ???????????????????? ?????????????????????? ?????????????? ???? ??????.<br/><br/>???????????????????? ????????????????: <b>1</b>-<b>20</b><br/><br/><span class='text-danger'>???????????? ??????????????????!</span>"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control item-speed action-save"
                                                   name="speeds[comment]" value="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">???????????????? ??????????????/?????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="??????-???? ???????????????????? ?????????????? ?????????????? ????????  ???????????????????? ?????????????????????? ?????????????? ???? ??????.<br/><br/>???????????????????? ????????????????: <b>1</b>-<b>60</b><br/><br/><span class='text-danger'>???????????? ??????????????????!</span>"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control item-speed action-save"
                                                   name="speeds[watching_story]" value="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????????????/?????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="??????-???? ???????????????? ?????????????? ????????  ???????????????????? ?????????????????????? ?????????????? ???? ??????.<br/><br/>???????????????????? ????????????????: <b>1</b>-<b>40</b><br/><br/><span class='text-danger'>???????????? ??????????????????!</span>"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control item-speed action-save"
                                                   name="speeds[follow]" value="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">??????????????/?????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Number of Unfollow actions that your activity will try to post in an hour.<br/><br/>Allowed values: <b>1</b>-<b>40</b><br/><br/><span class='danger'>Use with caution!</span>"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control item-speed action-save"
                                                   name="speeds[unfollow]" value="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">??????????????????/?????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="??????-???? ???????????????? ?????????????????? ?????????????? ????????  ???????????????????? ?????????????????????? ?????????????? ???? ??????.<br/><br/>???????????????????? ????????????????: <b>1</b>-<b>20</b><br/><br/><span class='text-danger'>???????????? ??????????????????!</span>"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control item-speed action-save"
                                                   name="speeds[direct]" value="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????????????/?????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Number of Repost medias actions that your activity will try to post in a day.<br/><br/> Allowed values: <b>1</b>-<b>60</b><br/><br/><span class='danger'>Use with caution!</span>"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control item-speed action-save"
                                                   name="speeds[repost]" value="1">
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-filter" role="tabpanel"
                             aria-labelledby="v-pills-filter-tab">

                            <div class="row">

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ?????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????? ???????????????? ?????????????? ?????? ?????????????? ?????????????? ??????????, ?? ?????????????? ???? ???????????? ??????????????????????????????????. ???? ???????????? ???????????? ???? ???????????? ??????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[media_age]">
                                                <option selected="true" value="">??????</option>
                                                <option value="new">??????????</option>
                                                <option value="1h">???? 1 ??????</option>
                                                <option value="12h">???? 12 ??????????</option>
                                                <option value="1d">???? 1 ????????</option>
                                                <option value="3d">???? 3 ??????</option>
                                                <option value="1w">???? 1 ????????????</option>
                                                <option value="2w">???? 2 ????????????</option>
                                                <option value="1m">???? 1 ??????????</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????? ?????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????? ???????????????? ?????????????????? ?????????????????????????????????? ???????????? ?? ???????????????????????? ?????????? ??????????: ???????? ?????? ??????????. ?????????? ???? ???????????? ?????????????? ?????? ?????? ???????????????????????????? ?? ?????????? ?????????? ??????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[media_type]">
                                                <option selected="true" value="">??????</option>
                                                <option value="image">????????</option>
                                                <option value="video">??????????</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">??????. ???????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="?????????????????????????????????? ???????????? ?? ???????? ??????????, ?? ?????????????? ???????????????????? ?????????????????? ???????????????????? ????????????.<br/><br/>?????????????????????? ?????? ???????????? ?? <b>????????. ????????????</b> ?????? ?????????????????? ?????????????????? ?????????????????? ???????????????????????? ??????????.<br/><br/>?????????????????????????? ????????????????: 0.<br/><br/>???????????????????? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[min_like]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????. ???????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="?????????????????????????????????? ???????????? ?? ???????? ??????????, ?? ?????????????? ?????????????????????? ?????????????????? ???????????????????? ????????????.<br/><br/>?????????????????????? ?????? ???????????? ?? <b>??????. ????????????</b> ?????? ?????????????????? ?????????????????? ?????????????????? ???????????????????????? ??????????.<br/><br/>?????????????????????????? ????????????????: 50???100.<br/><br/>???????????????????? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[max_like]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">??????. ?????????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="?????????????????????????????????? ???????????? ?? ???????? ??????????, ?????????????? ?????????? ?????????????????????? ???????????????????? ????????????????????????. <br/> <br/> ?????????????????????? ?????? ???????????? ?? <b> ????????. ?????????????????????? </ b> ?????????????????????????? ???????????????? ???????????????? ???????????????????????? ??????????. <br/> <br/> ?????????????????????????? ????????????????: 0. <br/> <br/> ???????????????????? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[min_comment]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????. ?????????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="?????????????????????????????????? ???????????? ?? ???????? ??????????, ?????????????? ?????????? ???????????????????????? ?????????????????? ???????????????????? ????????????????????????. <br/> <br/> ?????????????????????? ?????? ???????????? ?? <b> ??????. ?????????????????????? </ b> ?????????????????????????? ???????????????? ???????????????? ???????????????????????? ??????????. <br/> <br/> ?????????????????????????? ????????????????: 20-50. <br/> <br/> ???????????????????? ?? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[max_comment]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">??????. ?????????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????????????????????????????? ???????????? ?? ????????????????????????????, ?? ?????????????? ?????????????????????? ???????????????????? ??????????????????????. <br/> <br/> ?????????????????????? ?????? ???????????? ?? <b> ????????. ?????????????????????? </ b> ?????????????????????????? ???????????????? ???????????????? ???????????????????????? ??????????????????????????. <br/> <br/> ?????????????????????????? ????????????????: 0-50. <br/> <br/> ???????????????????? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[min_follower]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????. ?????????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????????????????????????????? ???????????? ?? ????????????????????????????, ?? ?????????????? ???????????????????????? ???????????????????? ??????????????????????. <br/> <br/> ?????????????????????? ?????? ???????????? ?? <b> ??????. ?????????????????????? </ b> ?????????????????????????? ???????????????? ???????????????? ???????????????????????? ??????????????????????????. <br/> <br/> ?????????????????????????? ????????????????: 500???1000. <br/> <br/> ???????????????????? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[max_follower]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">??????. ???????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????????????????????????????? ???????????? ?? ????????????????????????????, ?? ?????????????? ?????????????????????? ???????????????????? ????????????????. <br/> <br/> ?????????????????????? ?????? ???????????? ?? <b>????????. ????????????????</ b> ?????????????????????????? ???????????????? ???????????????? ???????????????????????? ??????????????????????????. <br/> <br/> ?????????????????????????? ????????????????: 50???100. <br/> <br/> ???????????????????? ???? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[min_following]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????. ???????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????????????????????????????? ???????????? ?? ????????????????????????????, ?? ?????????????? ???????? ???????????????????????? ???????????????????? ??????????????????????. <br/> <br/> ?????????????????????? ?????? ???????????? ?? <b> ??????. ???????????????? </ b> ?????????????????????????? ???????????????? ???????????????? ???????????????????????? ??????????????????????????. <br/> <br/> ?????????????????????????? ????????????????: 300-500. <br/> <br/> ???????????????????? ???? ????????, ?????????? ?????????????????? ???????? ????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="filters[max_following]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">???????????? ?????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????? ???????????? ?????????????? ?????? ???????????????? ?????????????????????????? ?????????????????????????? ?? ???? ?????????? ???? ?????????? ?????????? ????????????????????:<br/><br/><b>????????.</b> - ???????????? ????????????????. <br/> <br/> <b> ???????????? </b> - ?????????????????? ??????????????????????????, ?? ?????????????? ?????? ?????????????? ?????? ?????? ???????????????????????????? ??????????????????????. <br/> <br/> <b> ?????????????? </b> - ?????????????????? ??????????????????????????, ?? ?????????????? ?????? ??????????????, ?? ?????????????? ?????????? 10 ???????????????????????????? ?????????? ?????? ???? ?????????? ?????????? ?? ??????????????. <br/> <br/> <b> ?????????????? </b> - ?????????????????? ??????????????????????????, ?? ?????????????? ?????? ??????????????, ?????????? 30 ???????????????????????????? ????????????????????, ?????? ?????????? ?? ?????????????? ?????? ????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[user_profile]">
                                                <option selected="true" value="">????????</option>
                                                <option value="low">????????????</option>
                                                <option value="medium">??????????????</option>
                                                <option value="high">??????????????</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">???????????? ?????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="???????? ???????????? ?????????????? ?????? ?????????????????? ?????????? ?????????????????????? ??????????????????????/???????????????? ???? ???????????????????? ????????????????????, ??????????????????????????????????, ????????????????????: <br/> <br/> <b> ????????. </ B> - ???????????? ????????????????. <br/> <br/> <b> ???????????????????? </ b>. ???? ???? ???????????? ?????????????????????????????????? ???? ???????????? ???????????????????????? ?? ???? ??????????. <br/> <br/> <b> ???????????????? </ b>. ???? ???? ???????????? ?????????????????????????????????? ???? ???????????? ???????????????????? ?? ???? ??????????. <br> /> <br/> <b> ?????? </ b>. ???? ???? ???????????? ?????????????????????????????????? ???? ???????????? ????????????????????????, ???????????????????? ?? ???? ??????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[user_relation]">
                                                <option selected="true" value="">????????</option>
                                                <option value="followers">????????????????????</option>
                                                <option value="followings">????????????????</option>
                                                <option value="both">??????</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-comment" role="tabpanel"
                             aria-labelledby="v-pills-comment-tab">

                            <div class="row">
                                <div class="card col-12 ml-3">
                                    <div
                                            class="card-header">{{ __('???????????????? ??????????????????????') }}</div>

                                    <div class="card-body">

                                        <div class="row m-b-25">

                                            <div class="col-md-12">
                                                <div class="ig-ac-option">
                                                    <div class="info">
                                                        <span class="p-r-5">?????????????? ?????? ?????????????????????? </span>
                                                        <i class="fa fa-question-circle"
                                                           data-toggle="tooltip-custom"
                                                           data-trigger="hover" data-placement="top"
                                                           data-html="true" title=""
                                                           data-original-title="???????????????? ???????? ???? ???????? ??????????????????????, ???????? ???? ???????????????? ?????????????????????? ?? ?????????????? ????????????????????."></i>
                                                        <div class="form-group">
                                                    <textarea class="form-control form-add-comment post-message"
                                                              name="comment" required
                                                              style=""></textarea>
                                                        </div>
                                                        <button type="button" class="btn btn-info btn-add-comment">
                                                            ????????????????
                                                            ??????????
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="m-b-0 list-add-comment">

                                            <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                                <div class="wrap-c"><i class="far fa-comment-alt p-r-5"></i>
                                                    ??????????????????????
                                                </div>
                                                <div class="wrap-c">
                                                    <a href="javascript:void(0);"
                                                       class="btn btn-label-danger btn-sm remove-all"><i
                                                                class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                                </div>
                                            </div>
                                        @foreach($comments as $comment)
                                            <div>
                                                <div class="ig-ac-option-item-comment">
                                                    <a href="javascript:void(0);" class="remove"><i
                                                                class="fas fa-times-circle text-danger"></i></a> {{$comment}}<br>
                                                    <textarea class="d-none"
                                                              name="comments[]">{{$comment}}</textarea>
                                                </div>
                                            </div>
                                        @endforeach

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-commenthashtag" role="tabpanel"
                             aria-labelledby="v-pills-commenthashtag-tab">

                            <div class="row">
                                <div class="card col-12 ml-3">
                                    <div
                                            class="card-header">{{ __('?????????????????? ?????????????????????? ?????? ?????????????? ?????????????? ?????????? ?????? ????????') }}</div>

                                    <div class="card-body">
                                        <form method="POST" action="{{ route('giveCommentsOnSearchRecords') }}">
                                            @csrf

                                            <div class="form-group row" id="addFriendByTag">
                                                <label for="number-requests"
                                                       class="col-4 col-form-label">????????????????????
                                                    ???????????????????????? (????????.200)</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="requests" value="0" min="0"
                                                           max="1000" id="number-requests">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="inlineFormInputGroupTags">Tags</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2" id="addToFriendInp">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">#</div>
                                                        </div>
                                                        <input type="text" class="form-control"
                                                               name="tags"
                                                               id="inlineFormInputGroupTags"
                                                               placeholder="tag, tag...">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="d-flex-column">
                                                    ???????? ???????????? ????????????
                                                    <input type="date" class="form-control searchStart" id="searchStart"
                                                           name="searchStart" placeholder="????????" required>
                                                </div>

                                                <div style="margin: 2px;"></div>

                                                <div class="d-flex-column">
                                                    ???????? ?????????? ????????????
                                                    <input type="date" class="form-control searchEnd" id="searchEnd"
                                                           name="searchEnd" placeholder="????????" required>
                                                </div>
                                            </div>

                                            <div class="row m-b-25">

                                                <div class="col-md-12">
                                                    <div class="ig-ac-option">
                                                        <div class="info">
                                                            <span class="p-r-5">?????????????? ?????? ?????????????????????? </span>
                                                            <i class="fa fa-question-circle"
                                                               data-toggle="tooltip-custom"
                                                               data-trigger="hover" data-placement="top"
                                                               data-html="true" title=""
                                                               data-original-title="???????????????? ???????? ???? ???????? ??????????????????????, ???????? ???? ???????????????? ?????????????????????? ?? ?????????????? ????????????????????."></i>
                                                            <div class="form-group">
                                                    <textarea class="form-control form-add-comment post-message"
                                                              name="comment" required
                                                              style=""></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-info btn-add-comment">
                                                                ????????????
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="m-b-0 list-add-comment">

                                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                                    <div class="wrap-c"><i class="far fa-comment-alt p-r-5"></i>
                                                        ??????????????????????
                                                    </div>
                                                    <div class="wrap-c">
                                                        <a href="javascript:void(0);"
                                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                                    class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                                    </div>
                                                </div>
                                                @foreach($comments as $comment)
                                                    <div>
                                                        <div class="ig-ac-option-item-comment">
                                                            <a href="javascript:void(0);" class="remove"><i
                                                                        class="fas fa-times-circle text-danger"></i></a> {{$comment}}<br>
                                                            <textarea class="d-none"
                                                                      name="comments[]">{{$comment}}</textarea>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </div>


                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-commentconcurrents" role="tabpanel"
                             aria-labelledby="v-pills-commentconcurrents-tab">

                            <div class="row">
                                <div class="card col-12 ml-3">
                                    <div
                                            class="card-header">{{ __('?????????????????? ?????????????????????? ?????????? ?????????????? ???????????????? ?????? ???????????????? ?????????????????????? ?????? ??????????????') }}</div>

                                    <div class="card-body">
                                        <form method="POST" action="{{ route('writeCommentsOnConcurrents') }}">
                                            @csrf

                                            <div class="form-group row">
                                                <label for="post_url"
                                                       class="col-4 col-form-label">???????????? ???? ????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text"
                                                           name="post_url" id="post_url">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                {{--                                                <div class="d-flex-column">--}}
                                                {{--                                                    ???????? ???????????? ????????????--}}
                                                {{--                                                    <input type="text" class="dateTimePicker searchStart form-control" name="searchStart">--}}
                                                {{--                                                </div>--}}

                                                {{--                                                <div class="d-flex-column">--}}
                                                {{--                                                    ???????? ?????????? ????????????--}}
                                                {{--                                                    <input type="text" class="dateTimePicker endStart form-control" name="searchEnd">--}}
                                                {{--                                                </div>--}}
                                            </div>

                                            <div class="row m-b-25">

                                                <div class="col-md-12">
                                                    <div class="ig-ac-option">
                                                        <div class="info">
                                                            <span class="p-r-5">?????????????? ?????? ?????????????????????? </span>
                                                            <i class="fa fa-question-circle"
                                                               data-toggle="tooltip-custom"
                                                               data-trigger="hover" data-placement="top"
                                                               data-html="true" title=""
                                                               data-original-title="???????????????? ???????? ???? ???????? ??????????????????????, ???????? ???? ???????????????? ?????????????????????? ?? ?????????????? ????????????????????."></i>
                                                            <div class="form-group">
                                                    <textarea class="form-control form-add-comment post-message"
                                                              style="" name="comment"></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-info btn-add-comment">
                                                                ????????????
                                                            </button>
                                                            <br>
                                                            <div class="row" style="margin:5px; font-size:14px;">
                                                                * ?????????????????????? ?????????? ???????????????????? ???? ???????????? ???????????? ???? ??????????
                                                                ????????????????????????
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="m-b-0 list-add-comment">

                                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                                    <div class="wrap-c"><i class="far fa-comment-alt p-r-5"></i>
                                                        ??????????????????????
                                                    </div>
                                                    <div class="wrap-c">
                                                        <a href="javascript:void(0);"
                                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                                    class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                                    </div>
                                                </div>

                                                @foreach($comments as $comment)
                                                    <div>
                                                        <div class="ig-ac-option-item-comment">
                                                            <a href="javascript:void(0);" class="remove"><i
                                                                        class="fas fa-times-circle text-danger"></i></a> {{$comment}}<br>
                                                            <textarea class="d-none"
                                                                      name="comments[]">{{$comment}}</textarea>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </div>


                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-watching-story" role="tabpanel"
                             aria-labelledby="v-pills-watching-story-tab">

                        </div>
                        <div class="tab-pane fade" id="v-pills-follow" role="tabpanel"
                             aria-labelledby="v-pills-follow-tab">

                            <div class="row">

                                <div class="card ml-5">
                                    <div class="card-header">{{ __('???????????? ?? ????????????') }}</div>

                                    <div class="card-header">
                                        <div class="wrap-c">
                                            <h4 style="">?????????? ???????????????????? ???????????? ?? ???? 50 ?????????????? ?? ????????</h4>
                                        </div>
                                    </div>

                                    <div class="card-header" style="padding-bottom: 20px; height: 90px !important;">
                                        <div class="wrap-c">
                                            <h4 style="">?????? ?????????????????? ?? ???????????? ???????????? ???????? ???????????? ???? ???????????? ????????????, ?????????????????? ??????????????
                                                ?? ???????????????? ???????????????? ?? ?????????????? ???????????? ???????????????? ?? ???????????? ?????????? ????????????????
                                            </h4>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('friends') }}">
                                        @csrf

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>?????????? ?????????? ???????????? ???? ????????????????</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="tag_requests"
                                                           value="1" min="1" max="50"
                                                           id="tag_requests">
                                                </div>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2"
                                                         id="addToFriendInp">
                                                        <label for="tags" class="col-4 col-form-label">???????? ??????
                                                            ????????????</label>
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">#</div>
                                                        </div>
                                                        <input type="text" class="form-control"
                                                               name="tags"
                                                               id="inlineFormInputSearchTags"
                                                               placeholder="??????, ??????...">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="d-flex-column">
                                                    ???????? ???????????? ????????????
                                                    <input type="date" class="form-control searchStart"
                                                           id="searchStart" name="searchStart"
                                                           placeholder="????????">
                                                </div>

                                                <div style="margin: 2px;"></div>

                                                <div class="d-flex-column">
                                                    ???????? ?????????? ????????????
                                                    <input type="date" class="form-control searchEnd"
                                                           id="searchEnd" name="searchEnd" placeholder="????????">
                                                </div>

                                                <div style="margin: 2px;"></div>

                                                <div class="col-2">
                                                    <label for="tagSearchAllowed" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="tagSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>?????????? ?????????? ???????????? ?? ?????????????????????? ????????????</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="group_members_qty"
                                                           value="1" min="1" max="1000"
                                                           id="group_members_qty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ???? ???????????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="group_members_offset"
                                                           value="0" min="0" max="5000000"
                                                           id="group_members_offset">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/???????????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="members_group_id"
                                                           id="members_group_id">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupMembersSearchAllowed" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="groupMembersSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>?????????? ?????????? ???????????? ?? ???????????????????????? ?? ???????????? ????????????????????</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInPostCommentsQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInPostCommentsQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPostComments"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????????????????? ?? ???????????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_qty_for_findFriendsInPostComments"
                                                           value="1" min="1" max="1000"
                                                           id="comment_qty_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPostComments"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_offset_for_findFriendsInPostComments"
                                                           value="0" min="0" max="10000"
                                                           id="comment_offset_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/???????????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPostComments"
                                                           id="owner_id_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupMembersSearchAllowed" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="groupPostCommentsSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>?????????? ?????????? ???????????? ?? ???????????????????????? ?? ?????????????????????? ????????????????????</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsPhotosInCommentsQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsPhotosInCommentsQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPhotosComments"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????????????????? ?? ?????????????????????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_qty_for_findFriendsInPhotosComments"
                                                           value="1" min="1" max="1000"
                                                           id="comment_qty_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ??????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPhotosComments"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_offset_for_findFriendsInPhotosComments"
                                                           value="0" min="0" max="10000"
                                                           id="comment_offset_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/???????????????? ?????? ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPhotosComments"
                                                           id="owner_id_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupPhotosCommentsSearchAllowed" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="groupPhotosCommentsSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>?????????? ?????????? ???????????? ?? ???????????? ?? ???????????? ??????????????</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInPostsLikesQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInPostsLikesQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPostsLikes"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????? ?? ???????????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_qty_for_findFriendsInPostsLikes"
                                                           value="1" min="1" max="1000"
                                                           id="likes_qty_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPostsLikes"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_offset_for_findFriendsInPostsLikes"
                                                           value="0" min="0" max="10000"
                                                           id="likes_offset_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/???????????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPostsComments"
                                                           id="owner_id_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupLikedPostsAllow" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="groupLikedPostsAllow">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>?????????? ?????????? ???????????? ?? ???????????? ?? ?????????????????????? ??????????????</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInPhotosLikesQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInPhotosLikesQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPhotosLikes"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????? ?? ?????????????????????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_qty_for_findFriendsInPhotosLikes"
                                                           value="1" min="1" max="1000"
                                                           id="likes_qty_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ??????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPhotosLikes"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_offset_for_findFriendsInPhotosLikes"
                                                           value="0" min="0" max="10000"
                                                           id="likes_offset_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/???????????????? ?????? ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPhotosLikes"
                                                           id="owner_id_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupLikedPhotosAllow" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="groupLikedPhotosAllow">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>?????????? ?????????? ???????????? ?? ?????????????????????????? ?????????????????????????????????? ?? ????????????</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ??????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInRecordConcurrents"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_qty_for_findFriendsInRecordConcurrents"
                                                           value="1" min="1" max="1000"
                                                           id="likes_qty_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-???? ???????????????????????? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comments_qty_for_findFriendsInRecordConcurrents"
                                                           value="1" min="1" max="1000"
                                                           id="comments_qty_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_offset_for_findFriendsInRecordConcurrents"
                                                           value="0" min="0" max="10000"
                                                           id="likes_offset_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">???????????? ?? ????????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comments_offset_for_findFriendsInRecordConcurrents"
                                                           value="0" min="0" max="10000"
                                                           id="comments_offset_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">Url ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsRecordsConcurrents"
                                                           id="owner_id_for_findFriendsRecordsConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="concurrentsSearchAllow" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="concurrentsSearchAllow">
                                                    </label>
                                                </div>

                                                <div class="col-2">
                                                </div>

                                                <div class="col-4">
                                                    <label for="recordTypeIsPhoto" class="col-form-label">
                                                        ?????? ???????????? - ????????
                                                        <input class="form-control" type="checkbox" name="recordTypeIsPhoto">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>???????????????????? ?????????? ???????????? ???? ????????????</h3>

                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">???????????? ID/???????????????? ???????? ??????????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_ids_list"
                                                           id="owner_ids_list" placeholder="id,id,shortName...">
                                                </div>
                                            </div>

                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupLikedPhotosAllow" class="col-form-label">
                                                        ????????????
                                                        <input class="form-control" type="checkbox" name="loadedListAllow">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3">

                                            <div class="card-header">
                                                ?????????????????? ????????????????  ?? ????????????
                                            </div>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">??-????
                                                    ????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="requests"
                                                           value="1" min="1" max="1000"
                                                           id="number-requests">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-friends" class="col-4 col-form-label">??????.
                                                    ??-????
                                                    ???????????? ?? ????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number" name="friends"
                                                           value="0" min="0" max="1000" id="number-friends">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-followers" class="col-4 col-form-label">??????.
                                                    ??-????
                                                    ?????????????????????? ?? ???????????????? ????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="followers"
                                                           value="0" min="0" max="1000"
                                                           id="number-followers">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-posts" class="col-4 col-form-label">??????.
                                                    ??-???? ????????????
                                                    ???? ?????????? ?? ????????????????????</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number" name="posts"
                                                           value="0"
                                                           min="0" max="1000" id="example-number-posts">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="gridCheck1">
                                                        <label class="form-check-label">
                                                            <input type="hidden" name="photo" value="0"/>
                                                            <input class="form-check-input" name="photo"
                                                                   value="1"
                                                                   type="checkbox"> ???????? ?????????????????????? ??
                                                            ????????????????????
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="gridCheck1">
                                                        <label class="form-check-label">
                                                            <input type="hidden" name="delete-banned"
                                                                   value="0"/>
                                                            <input class="form-check-input"
                                                                   name="delete-banned"
                                                                   value="1" type="checkbox"> ??????????????????
                                                            ???????????? ??
                                                            ?????????????????????????????? ????????????????????
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="gridCheck1">
                                                        <label class="form-check-label">
                                                            <input type="hidden" name="hidden" value="0"/>
                                                            <input class="form-check-input" name="hidden"
                                                                   value="1"
                                                                   type="checkbox"> ?????????????????? ???????????? ??
                                                            ????????????????
                                                            ????????????????????
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('????????????') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-unfollow" role="tabpanel"
                             aria-labelledby="v-pills-unfollow-tab">

                            <div class="row">

                                <div class="card ml-3">
                                    <div
                                            class="card-header">{{ __('?????????????? ?????????????????????????????? ????????????') }}</div>

                                    <div class="card-body">
                                        <form method="POST" action="{{ route('delfriends') }}">
                                            @csrf
                                            <div class="form-group row">
                                                <label class="col-sm-4">?????? ??????????????????????</label>
                                                <div class="col-sm-8">
                                                    <div class="form-check form-check-inline">
                                                        <input type="hidden" name="banned" value="0"/>
                                                        <input class="form-check-input" type="checkbox"
                                                               name="banned" value="1">
                                                        <label class="form-check-label"
                                                               for="inlineRadio1">?????? ??????????????</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input type="hidden" name="deleted" value="0"/>
                                                        <input class="form-check-input" type="checkbox"
                                                               name="deleted" value="1">
                                                        <label class="form-check-label"
                                                               for="inlineRadio2">????????????</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('??????????????') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-direct" role="tabpanel"
                             aria-labelledby="v-pills-direct-tab">

                            <div class="row">

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">Auto bot messages by </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ?????????????????????????? ???????????????????? ???????????????????<br/><br/><b>?????????? ????????????????????</b> ??? ???????????????? ???????? ??????????????, ???????? ???????????? ???????????????????? ?????????????????? ???????????? ?????????? ?????????? ??????????????????????<br/><br/><b>????????</b> ??? ???????????????? ?????? ??????????, ???????? ???? ???????????? ?????????????????? ?????????????????? ???? ???????????????????? ????????"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="op_directs[by]">
                                                <option selected="true" value="follower">?????????? ????????????????????
                                                </option>
                                                <option value="target">????????</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row m-b-25">

                                <div class="col-md-12">
                                    <div class="ig-ac-option">
                                        <div class="info">
                                            <span class="p-r-5">?????????????? ???????? ?????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="???????????????? ???????? ???? ???????? ??????????????????, ???????? ???? ???????????????? ???????????????? ?????????????????? ?? ?????????????? ????????????????????."></i>
                                            <div class="form-group">
                                                    <textarea class="form-control form-add-direct post-message"
                                                              style="display: none;"></textarea>
                                            </div>
                                            <button type="button" class="btn btn-info btn-add-direct">
                                                ????????????????
                                                ??????????
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="m-b-0 list-add-direct">

                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                    <div class="wrap-c"><i class="fas fa-inbox p-r-5"></i> ??????????????????</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                    </div>
                                </div>

                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        ????????????????????????, Username ! ?????? ?????????<br>
                                        <textarea class="d-none" name="directs[]">????????????????????????, Username ! ?????? ?????????
</textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        ????????????, Username ! ?????? ?????????<br>
                                        <textarea class="d-none" name="directs[]">????????????, Usernam ! ?????? ?????????
</textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        ????????????, Usernam ! ????????????, ?????? ?????????<br>
                                        <textarea class="d-none" name="directs[]">????????????, Username ! ????????????, ?????? ?????????
</textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        ????????????, Username , ?????? ????????? <textarea class="d-none"
                                                                               name="directs[]">????????????, Username , ?????? ?????????</textarea>
                                    </div>
                                </div>

                            </div>

                            {{--                            <div class="card ml-3">--}}
                            {{--                                    <div--}}
                            {{--                                        class="card-header">{{ __('?????????????????? ?????????? ?????????? ?????????????? ???????????????????? ????????????') }}</div>--}}

                            {{--                                    @if (session('competitors_record_like_success'))--}}
                            {{--                                        <div class="alert alert-success" role="alert">--}}
                            {{--                                            {{ session('competitors_record_like_success') }}--}}
                            {{--                                        </div>--}}
                            {{--                                    @endif--}}

                            {{--                                    @if (session('competitors_record_like_captcha'))--}}
                            {{--                                        <div class="alert alert-warning" role="alert">--}}
                            {{--                                            {{ session('competitors_record_like_captcha') }}--}}
                            {{--                                        </div>--}}
                            {{--                                    @endif--}}

                            {{--                                    @if (session('competitors_record_like_unhandled'))--}}
                            {{--                                        <div class="alert alert-warning" role="alert">--}}
                            {{--                                            {{ session('competitors_record_like_unhandled') }}--}}
                            {{--                                        </div>--}}
                            {{--                                    @endif--}}

                            {{--                                    @if (session('competitors_record_like_flood'))--}}
                            {{--                                        <div class="alert alert-warning" role="alert">--}}
                            {{--                                            {{ session('competitors_record_like_flood') }}--}}
                            {{--                                        </div>--}}
                            {{--                                    @endif--}}

                            {{--                                    <div class="card-body">--}}
                            {{--                                        <form method="POST" action="{{ route('competitorsRecordLike') }}">--}}
                            {{--                                            @csrf--}}

                            {{--                                            <div class="row">--}}

                            {{--                                            @if ($captcha['competitors_record_like_captcha'] && session('competitors_record_like_captcha'))--}}
                            {{--                                                <div class="card" style="width: 18rem;">--}}
                            {{--                                                    <img src="{{$captcha['competitors_record_like_captcha']['captcha_img']}}"--}}
                            {{--                                                         class="card-img-top" alt="...">--}}
                            {{--                                                    <div class="card-body">--}}
                            {{--                                                        <h5 class="card-title">?????????????? ?????????? ?? ????????????????</h5>--}}

                            {{--                                                        <input type="hidden" name="captcha_sid"--}}
                            {{--                                                               value="{{$captcha['competitors_record_like_captcha']['captcha_sid']}}">--}}
                            {{--                                                        <input type="hidden" name="captcha_chunk_pause"--}}
                            {{--                                                               value="{{$captcha['competitors_record_like_captcha']['post_comments_position']}}">--}}
                            {{--                                                        <input type="hidden" name="captcha_chunk_item_pause"--}}
                            {{--                                                               value="{{$captcha['competitors_record_like_captcha']['post_comment_position']}}">--}}
                            {{--                                                        <input type="text" name="captcha_key" class="form-control">--}}
                            {{--                                                    </div>--}}
                            {{--                                                </div>--}}
                            {{--                                            @endif--}}

                            {{--                                            </div>--}}

                            {{--                                            <div class="form-group row">--}}
                            {{--                                                <label class="col-4 col-form-label" for="inlineFormInputGroupTags">--}}
                            {{--                                                    ID ????????????????????????/????????????????????(???????????? ?? - )</label>--}}
                            {{--                                                <div class="col-8">--}}
                            {{--                                                    <div class="input-group mb-2 mr-sm-2">--}}
                            {{--                                                        <input type="" class="form-control" name="group_id"--}}
                            {{--                                                               id="group_id" value=""--}}
                            {{--                                                               placeholder="">--}}
                            {{--                                                    </div>--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}
                            {{--                                            <div class="form-group row">--}}
                            {{--                                                <label class="form-check-label required_follower_photo">--}}
                            {{--                                                    <input type="hidden" name="hasPhoto" value="0"/>--}}
                            {{--                                                    <input class="form-check-input" name="hasPhoto" value="1"--}}
                            {{--                                                           type="checkbox"> ???????? ?????????????????????? ?? ????????????????????--}}
                            {{--                                                </label>--}}
                            {{--                                            </div>--}}
                            {{--                                            <div class="form-group row">--}}
                            {{--                                                <label class="col-4 col-form-label" for="inlineFormInputGroupTags">--}}
                            {{--                                                    ?????????????????????? ??-???? ?????????????????????? ?? ??????????????????????????</label>--}}
                            {{--                                                <div class="col-8">--}}
                            {{--                                                    <div class="input-group mb-2 mr-sm-2">--}}
                            {{--                                                        <input type="number" class="form-control"--}}
                            {{--                                                               name="auto_like_min_followers"--}}
                            {{--                                                               id="auto_like_min_followers"--}}
                            {{--                                                               placeholder="">--}}
                            {{--                                                    </div>--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}


                            {{--                                            <div class="form-group row">--}}
                            {{--                                                <div class="col-sm-8">--}}
                            {{--                                                    <button type="submit"--}}
                            {{--                                                            class="btn btn-primary">{{ __('????????????') }}</button>--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}
                            {{--                                        </form>--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}

                        </div>
                        <div class="tab-pane fade" id="v-pills-repost" role="tabpanel"
                             aria-labelledby="v-pills-repost-tab">

                            <div class="tab-pane fade active show" id="profile" role="tabpanel"
                                 aria-labelledby="profile-tab">
                                <div class="form-group">
                                    <textarea class="form-control form-add-tag-list"></textarea>
                                </div>
                                <button type="button" class="btn btn-info btn-add-tag-list">
                                    ????????????????
                                </button>
                            </div>

                            <div class="m-b-0 list-add-tag">

                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> ????????</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                    </div>
                                </div>

                                @foreach($tags as $tag)
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> {{$tag}} <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/{{$tag}}" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="{{$tag}}">
                                    </div>
                                @endforeach

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-search-tag" role="tabpanel"
                             aria-labelledby="v-pills-search-tab">

                            <div class="col-md-12">
                                <div class="tab-content" id="myTabContent">

                                    <div class="row">
                                        <div class="card col-12 ml-3">
                                            <div
                                                    class="card-header">{{ __('?????????? ???? ???????????? ???? ????????????????') }}</div>

                                            <div class="card-body">
                                                <form method="POST"
                                                      action="{{ route('likeAllSrchRdsByTags') }}">
                                                    @csrf

                                                    <div class="form-group row" id="addFriendByTag">
                                                        <label for="number-requests"
                                                               class="col-4 col-form-label">????????????????????
                                                            ???????????? (????????.200)</label>
                                                        <div class="col-8">
                                                            <input class="form-control" type="number"
                                                                   name="requests" value="0" min="0"
                                                                   max="1000" id="number-requests">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-4 col-form-label"
                                                               for="inlineFormInputSearchTags">Tags</label>
                                                        <div class="col-8">
                                                            <div class="input-group mb-2 mr-sm-2"
                                                                 id="addToFriendInp">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text">#</div>
                                                                </div>
                                                                <input type="text" class="form-control"
                                                                       name="tags"
                                                                       id="inlineFormInputSearchTags"
                                                                       placeholder="??????, ??????...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="d-flex-column">
                                                            ???????? ???????????? ????????????
                                                            <input type="date" class="form-control searchStart"
                                                                   id="searchStart" name="searchStart"
                                                                   placeholder="????????" required>
                                                        </div>

                                                        <div style="margin: 2px;"></div>

                                                        <div class="d-flex-column">
                                                            ???????? ?????????? ????????????
                                                            <input type="date" class="form-control searchEnd"
                                                                   id="searchEnd" name="searchEnd" placeholder="????????"
                                                                   required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <button type="submit"
                                                                    class="btn btn-primary">{{ __('????????????') }}</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="result-search-tag"></div>

                                </div>
                            </div>

                            <div class="m-b-0 list-add-tag">

                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> ????????</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                    </div>
                                </div>

                                @foreach($tags as $tag)
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> {{$tag}} <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/{{$tag}}" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="{{$tag}}">
                                    </div>
                                @endforeach

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-tag" role="tabpanel"
                             aria-labelledby="v-pills-tag-tab">

                            <div class="col-md-12">
                                <div class="tab-content" id="myTabContent">

                                    <div class="row">
                                        <div class="card col-12 ml-3">
                                            <div class="card-header">{{ __('?????????????????? ???????????? ?? ???????????? ???? ????????????????') }}</div>

                                            <div class="card-body">
                                                <form method="POST" action="{{ route('addToFriend') }}">
                                                    @csrf

                                                    <div class="form-group row" id="addFriendByTag">
                                                        <label for="number-requests"
                                                               class="col-4 col-form-label">???????????????????? ????????????</label>
                                                        <div class="col-8">
                                                            <input class="form-control" type="number"
                                                                   name="requests" value="0" min="0"
                                                                   max="1000" id="number-requests">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-4 col-form-label"
                                                               for="inlineFormInputGroupTags">????????</label>
                                                        <div class="col-8">
                                                            <div class="input-group mb-2 mr-sm-2"
                                                                 id="addToFriendInp">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text">#</div>
                                                                </div>
                                                                <input type="text" class="form-control"
                                                                       name="tags" required
                                                                       id="inlineFormInputGroupTags"
                                                                       placeholder="tag, tag...">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <button type="submit"
                                                                    class="btn btn-primary">{{ __('????????????') }}</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="result-search-tag"></div>

                                </div>
                            </div>

                            <div class="m-b-0 list-add-tag">

                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> ????????</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                    </div>
                                </div>

                                @foreach($tags as $tag)
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> {{$tag}} <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/{{$tag}}" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="{{$tag}}">
                                    </div>
                                @endforeach

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-location" role="tabpanel"
                             aria-labelledby="v-pills-location-tab">

                            <div class="row">

                                <div class="card ml-3">
                                    <div
                                            class="card-header">{{ __('?????????????????? ???????? ???? ?????????????????????? ?????? ????????') }}</div>


                                    @if ($captcha['captcha_photo'])
                                        <div class="card" style="width: 18rem;">
                                            <img src="{{$captcha['captcha_photo']['captcha_img']}}"
                                                 class="card-img-top"
                                                 alt="...">
                                            <div class="card-body">
                                                <h5 class="card-title">?????????????? ?????????? ?? ????????????????</h5>
                                                <form method="POST" action="{{ route('photosLike') }}">
                                                    @csrf

                                                    <input type="hidden" name="captcha_sid"
                                                           value="{{$captcha['captcha_photo']['captcha_sid']}}">
                                                    <input type="hidden" name="last_comments"
                                                           value="{{$captcha['captcha_photo']['post_comments_position']}}">
                                                    <input type="hidden" name="last_comment"
                                                           value="{{$captcha['captcha_photo']['post_comment_position']}}">
                                                    <input type="text" name="captcha_key"
                                                           class="form-control">
                                                    <h5>ID/???????????????? ?????? ??????????????</h5>
                                                    <input type="text" class="form-control"
                                                           name="owner_id"
                                                           placeholder="">
                                                    <br>
                                                    <button type="submit"
                                                            class="btn btn-success form-control">????????????
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <form method="POST" action="{{ route('photosLike') }}">
                                            @csrf

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="record_qty">
                                                    ??????-???? ???????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="record_qty"
                                                               id="record_qty"
                                                               min="1"
                                                               value="1"
                                                               max="200"
                                                               required
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="comment_qty">
                                                    ??-???? ???????????????????????? ?? ???????????? ????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="comment_qty"
                                                               id="comment_qty"
                                                               min="1"
                                                               value="1"
                                                               max="100"
                                                               required
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="inlineFormInputGroupTags">
                                                    ID/???????????????? ?????? ??????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="text" class="form-control"
                                                               name="owner_id"
                                                               id="owner_id"
                                                               required
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('????????????') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card ml-3">
                                    <div
                                            class="card-header">{{ __('?????????????????? ?????????? ???? ?????????????????????? ?????? ??????????????') }}</div>

                                    @if ($captcha['captcha_post'])
                                        <div class="card" style="width: 18rem;">
                                            <img src="{{$captcha['captcha_post']['captcha_img']}}"
                                                 class="card-img-top"
                                                 alt="...">
                                            <div class="card-body">
                                                <h5 class="card-title">?????????????? ?????????? ?? ????????????????</h5>
                                                <form method="POST" action="{{ route('postsLike') }}">
                                                    @csrf

                                                    <input type="hidden" name="captcha_sid"
                                                           value="{{$captcha['captcha_post']['captcha_sid']}}">
                                                    <input type="hidden" name="last_comments"
                                                           value="{{$captcha['captcha_post']['post_comments_position']}}">
                                                    <input type="hidden" name="last_comment"
                                                           value="{{$captcha['captcha_post']['post_comment_position']}}">
                                                    <input type="text" name="captcha_key"
                                                           class="form-control">
                                                    <h6>ID/???????????????? ?????? ??????????????</h6>
                                                    <input type="text" class="form-control"
                                                           name="owner_id"
                                                           placeholder="">
                                                    <br>
                                                    <button type="submit"
                                                            class="btn btn-success form-control">??????????????????
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <form method="POST" action="{{ route('postsLike') }}">
                                            @csrf

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="record_qty">
                                                    ??????-???? ???????????? ???? ????????-?? ?????? ???????????? ????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="record_qty"
                                                               id="record_qty"
                                                               min="1"
                                                               value="1"
                                                               max="200"
                                                               required
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="comment_qty">
                                                    ??-???? ???????????????????????? ?? ???????????? ???????????? ????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="comment_qty"
                                                               id="comment_qty"
                                                               min="1"
                                                               value="1"
                                                               max="100"
                                                               required
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="inlineFormInputGroupTags">
                                                    ID/???????????????? ?????? ??????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="text" class="form-control"
                                                               name="owner_id"
                                                               required
                                                               id="owner_id"
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('??????????????????') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-likefriends" role="tabpanel"
                             aria-labelledby="v-pills-location-tab">

                            <div class="row">
                                <div class="card ml-3">
                                    <div
                                            class="card-header">{{ __('?????????????????? ?????????? ?????????? ?????????????????????? ?? ??????????????') }}</div>


                                    {{-- TODO: ?????????????????? ???????????????? ???? ??????????????????, ???????????? ???????????????????? ???????? ????????, ???????? ???????????????? --}}
                                    @if ($captcha['captcha_post'] && session('com_post_like_captcha'))
                                        <div class="card" style="width: 18rem;">
                                            <img src="{{$captcha['captcha_post']['captcha_img']}}"
                                                 class="card-img-top"
                                                 alt="...">
                                            <div class="card-body">
                                                <h5 class="card-title">?????????????? ?????????? ?? ????????????????</h5>
                                                <form method="POST" action="{{ route('likeAllFriends') }}">
                                                    @csrf

                                                    <input type="hidden" name="captcha_sid"
                                                           value="{{$captcha['captcha_post']['captcha_sid']}}">
                                                    <input type="hidden" name="last_comments"
                                                           value="{{$captcha['captcha_post']['post_comments_position']}}">
                                                    <input type="hidden" name="last_comment"
                                                           value="{{$captcha['captcha_post']['post_comment_position']}}">
                                                    <input type="text" name="captcha_key"
                                                           class="form-control">
                                                    <br>
                                                    <button type="submit"
                                                            class="btn btn-success form-control">????????????
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <form method="POST" action="{{ route('likeAllFriends') }}">
                                            @csrf

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="inlineFormInputGroupTags">
                                                    ??-???? ???????????? ?????????????? ????????????????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="likeCount"
                                                               id="likeCount"
                                                               required
                                                               min="1"
                                                               value="1"
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="likeLimit">
                                                    ?????????? ????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="likeLimit"
                                                               id="likeLimit"
                                                               required
                                                               min="1"
                                                               max="200"
                                                               value="1"
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('????????????') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-likeconcurents" role="tabpanel"
                             aria-labelledby="v-pills-location-tab">

                            <div class="row">
                                <div class="card ml-3">
                                    <div
                                            class="card-header">{{ __('?????????????????? ?????????? ??????????????????????????, ?????????????? ?????????????? ?????? ???????????????????????????? ????????????????????') }}</div>


                                    <div class="card-body">
                                        <form method="POST" action="{{ route('likeAllConcurrents') }}">
                                            @csrf

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="url">
                                                    ???????????? ???? ????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="text" class="form-control"
                                                               name="url"
                                                               id="url"
                                                               required
                                                               placeholder="https://vk.com/wall594729447_78">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="postscount">
                                                    ???????????????????? ?????????????? ?????? ???????????? ?? ?????????????? ????????????????????????(???? ?????????? ?? ????????????)</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" min="0" max="100" value="0"
                                                               class="form-control"
                                                               name="postscount"
                                                               id="postscount"
                                                        >
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('????????????') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-likecriteries" role="tabpanel"
                             aria-labelledby="v-pills-location-tab">

                            <div class="row">
                                <div class="card ml-3">
                                    <div
                                            class="card-header">{{ __('?????????? ?????????????????????? ????????????') }}</div>

                                    <div class="card-body">
                                        <form method="POST" action="{{ route('likeAllMembers') }}">
                                            @csrf

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label" for="inlineFormInputGroupTags">
                                                    Url-?????????? ????????????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="" class="form-control" name="group_id"
                                                               id="group_id" value="" required
                                                               placeholder="https://vk.com/LEGIO_ORLY">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="form-check-label required_follower_photo ml-4">
                                                    <input type="hidden" name="hasPhoto" value="0"/>
                                                    <input class="form-check-input" name="hasPhoto" value="1"
                                                           type="checkbox"> ???????? ?????????????????????? ?? ????????????????????
                                                </label>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label" for="auto_like_min_followers">
                                                    ?????????????????????? ??-???? ?????????????????????? ?? ?????????????? ????????????????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="auto_like_min_followers"
                                                               id="auto_like_min_followers"
                                                               min="0"
                                                               value="0"
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label" for="record_count">
                                                    ??-???? ???????????? ?? ?????????????? ???????????????????????? ?????? ????????????</label>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2">
                                                        <input type="number" class="form-control"
                                                               name="record_count"
                                                               id="record_count"
                                                               min="0"
                                                               max="3"
                                                               value="0"
                                                               placeholder="">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('????????????') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-blacklist" role="tabpanel"
                             aria-labelledby="v-pills-blacklist-tab">

                            <div class="row m-b-25">
                                <div class="col-md-12">
                                    <div class="tab-content" id="myTabContent">

                                        <div class="row">
                                            <div class="card col-12 ml-3">
                                                <div
                                                        class="card-header">{{ __('?????????????? ???????????? ???? ????????????????') }}</div>

                                                <div class="card-body">
                                                    <form method="POST"
                                                          action="{{ route('deleteFromFriends') }}">
                                                        @csrf

                                                        <div class="form-group row" id="deleteFriendByTag">
                                                            <label for="number-requests"
                                                                   class="col-4 col-form-label">??-????
                                                                ????????????????</label>
                                                            <div class="col-8">
                                                                <input class="form-control" type="number"
                                                                       name="" value="0" min="0" max="1000"
                                                                       id="number-requests">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-4 col-form-label"
                                                                   for="inlineFormInputGroupTags">????????</label>
                                                            <div class="col-8">
                                                                <div class="input-group mb-2 mr-sm-2"
                                                                     id="deleteFromFriendInp">
                                                                    <div class="input-group-prepend">
                                                                        <div class="input-group-text">#
                                                                        </div>
                                                                    </div>
                                                                    <input type="text" class="form-control"
                                                                           name="tags" required
                                                                           id="inlineFormInputGroupTag"
                                                                           placeholder="??????, ??????...">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-sm-8">
                                                                <button type="submit"
                                                                        class="btn btn-primary">{{ __('????????????') }}</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="result-search-tag"></div>

                                    </div>
                                </div>
                            </div>

                            <div class="m-b-0 list-add-tag">

                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> ????????</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> ?????????????? ??????</a>
                                    </div>
                                </div>

                                @foreach($tags as $tag)
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> {{$tag}} <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/{{$tag}}" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="{{$tag}}">
                                    </div>
                                @endforeach

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-auto-stop" role="tabpanel"
                             aria-labelledby="v-pills-auto-stop-tab">

                            <div class="row">

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ???????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ?????? ???????????????????? ???????????? ?????????? ??????????????, ???????? ???????????????????? ?????????? ?????????????????????? ??????????????????????????. ???????????????????? ???? ????????, ?????????? ?????????????????? ??????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="stops[like]"
                                                   value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ???????????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ?????? ???????????????????? ???????????????????????? ?????????? ??????????????, ???????? ???????????????????????? ?????????? ?????????????????????? ??????????????????????????. ???????????????????? ???? ????????, ?????????? ?????????????????? ??????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="stops[comment]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ?????????????????? ?????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ???????????????????? ?????????????????????????? ?????????????? ?????????? ??????????????????, ???????? ???????????????????????? ?????????? ?????????????????????????? ??????????????????????. ???????????????????? ???? ????????, ?????????? ?????????????????? ??????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="stops[watching_story]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ???????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ???????????????????? ???????????????? ?????????? ??????????????????, ???????? ???????????????????????? ?????????? ?????????????????????????? ??????????????????????. ???????????????????? ???? ????????, ?????????? ?????????????????? ??????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="stops[follow]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ?????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ???????????????????? ?????????????? ?????????? ??????????????????, ???????? ???????????????????????? ?????????? ?????????????????????????? ??????????????????????. ???????????????????? ???? ????????, ?????????? ?????????????????? ??????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="stops[unfollow]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ?????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ???????????????????? ???????????????? ?????????????????? ?????????? ??????????????????, ???????? ???????????????????????? ?????????? ?????????????????????????? ??????????????????????. ???????????????????? ???? ????????, ?????????? ?????????????????? ??????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="stops[direct]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">?????????????? ???????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????? ???????????????????? ???????????????? ?????????? ??????????????????, ???????? ???????????????????????? ?????????? ?????????????????????????? ??????????????????????. ???????????????????? ???? ????????, ?????????? ?????????????????? ??????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="number" class="form-control action-save"
                                                   name="stops[repost]" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">???????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="????????????: HH:MM. ????????????????, ???????? ???? ???????????????????? ???????????????? <b>01:00</b>, ???? ???????? ???????????????????????? ?????????????????????????? ?????????????????????? ?????????? ??????. ???????? ???? ???????????????? ???????? ????????????, ???????????????????? ?????????? ???????????????? ???????????????????? ?????? ???????? ?????????????????????? Instagram ?????? ?????????? ?????????????? ???????????? ???? ?????????? ????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <input type="text" class="form-control action-save"
                                                   name="stops[timer]"
                                                   placeholder="00:00" value="">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">????????, ???????? ?????? ???????????????????? </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="?????????????????????????? ?????????????????????????????? ?????????? ?????????????????? ?????????? ?????? ??????????-???????? ????????????????????."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save"
                                                    name="stops[no_activity]">
                                                <option selected="true" value="no">??????????????</option>
                                                <option value="1h">1 ??????</option>
                                                <option value="3h">3 ????????</option>
                                                <option value="12h">???? 12 ??????????</option>
                                                <option value="1d">???? 1 ????????</option>
                                                <option value="3d">???? 3 ??????</option>
                                                <option value="1w">???? 1 ????????????</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
            </div>

            <div id="schedule_days" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="ft-calendar" aria-hidden="true"></i>
                                ????????????????????</h5>
                            <button type="button" class="close" data-dismiss="modal">??</button>
                        </div>
                        <div class="modal-body">

                            <p class="fs-12">???????????????????? ?????????????????? ?????? ?????????????????? ???????????????????? ???????????????????? ????
                                ?????????????? ??
                                ?????? ?????? ?????????????? ?????????? ?????????????????? ????????????????????. ???? ???????????? ?????????????? ??????????????
                                ??????????????????
                                ????????, ?????????? ???????? ???????????????????? ???????????? ???????? ????????????????, ?????? ???? ???????????? ????????????????????????
                                ?????????????????? ??????????????????????????. </p>
                            <div class="text-center day-schedule-auto">
                                <a data-type="none" href="javascript:void(0);">????????????????????</a>
                                <a data-type="all" href="javascript:void(0);">??????</a>
                                <a data-type="day" href="javascript:void(0);">?????????????? ??????????</a>
                                <a data-type="night" href="javascript:void(0);">???????????? ??????????</a>
                                <br>
                                <a data-type="odd" href="javascript:void(0);">????????????????</a>
                                <a data-type="even" href="javascript:void(0);">????????????</a>
                                <a data-type="alternate1" href="javascript:void(0);">???????????????????????????? 1</a>
                                <a data-type="alternate2" href="javascript:void(0);">???????????????????????????? 2</a>

                                <div class="type">
                                    <div class="item">
                                        <span class="box active"></span> ???????????????????? ????????????????
                                    </div>
                                    <div class="item">
                                        <span class="box"></span> ???????????????????? ???? ??????????
                                    </div>
                                </div>
                            </div>

                            <div class="day-schedule-selector">
                                <table class="table-day-schedule">
                                    <tbody>
                                    <tr>
                                        <th></th>
                                        <th>Sun</th>
                                        <th>Mon</th>
                                        <th>Tue</th>
                                        <th>Wed</th>
                                        <th>Thu</th>
                                        <th>Fri</th>
                                        <th>Sat</th>
                                    </tr>
                                    <tr>
                                        <td class="hour">12 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="0"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="0"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="0"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="0"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="0"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="0"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="0"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">1 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="1"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="1"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="1"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="1"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="1"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="1"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="1"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">2 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="2"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="2"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="2"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="2"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="2"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="2"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="2"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">3 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="3"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="3"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="3"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="3"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="3"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="3"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="3"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">4 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="4"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="4"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="4"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="4"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="4"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="4"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="4"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">5 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="5"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="5"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="5"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="5"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="5"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="5"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="5"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">6 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="6"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="6"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="6"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="6"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="6"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="6"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="6"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">7 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="7"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="7"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="7"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="7"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="7"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="7"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="7"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">8 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="8"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="8"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="8"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="8"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="8"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="8"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="8"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">9 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="9"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="9"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="9"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="9"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="9"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="9"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="9"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">10 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="10"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="10"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="10"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="10"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="10"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="10"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="10"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">11 AM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="11"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="11"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="11"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="11"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="11"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="11"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="11"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">12 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="12"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="12"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="12"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="12"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="12"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="12"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="12"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">1 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="13"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="13"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="13"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="13"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="13"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="13"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="13"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">2 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="14"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="14"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="14"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="14"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="14"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="14"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="14"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">3 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="15"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="15"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="15"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="15"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="15"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="15"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="15"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">4 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="16"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="16"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="16"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="16"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="16"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="16"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="16"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">5 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="17"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="17"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="17"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="17"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="17"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="17"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="17"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">6 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="18"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="18"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="18"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="18"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="18"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="18"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="18"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">7 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="19"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="19"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="19"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="19"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="19"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="19"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="19"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">8 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="20"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="20"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="20"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="20"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="20"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="20"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="20"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">9 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="21"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="21"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="21"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="21"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="21"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="21"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="21"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">10 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="22"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="22"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="22"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="22"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="22"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="22"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="22"
                                               class="item active"></a></td>
                                    </tr>
                                    <tr>
                                        <td class="hour">11 PM</td>
                                        <td><a href="javascript:void(0);" data-day="0" data-hour="23"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="1" data-hour="23"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="2" data-hour="23"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="3" data-hour="23"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="4" data-hour="23"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="5" data-hour="23"
                                               class="item active"></a></td>
                                        <td><a href="javascript:void(0);" data-day="6" data-hour="23"
                                               class="item active"></a></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="schedule_days" value="[
	[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
	[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
	[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
	[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
	[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
	[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
	[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]
]">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-info click-action-save"
                                    data-dismiss="modal">
                                ??????????????????
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">??????????????
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div id="ascrail2001" class="nicescroll-rails nicescroll-rails-vr"
             style="width: 5px; z-index: auto; cursor: default; position: absolute; top: 154.48px; left: 1129.79px; height: 314px; display: none; opacity: 0;">
            <div class="nicescroll-cursors"
                 style="position: relative; top: 0px; float: right; width: 5px; height: 0px; background-color: rgb(215, 215, 215); border: none; background-clip: padding-box; border-radius: 0px;"></div>
        </div>
        <div id="ascrail2002" class="nicescroll-rails nicescroll-rails-vr"
             style="width: 8px; z-index: auto; cursor: default; position: absolute; top: -119px; left: -78px; height: 0px; display: none;">
            <div class="nicescroll-cursors"
                 style="position: relative; top: 0px; float: right; width: 6px; height: 0px; background-color: rgb(221, 221, 221); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;"></div>
        </div>
        <div id="ascrail2002-hr" class="nicescroll-rails nicescroll-rails-hr"
             style="height: 8px; z-index: auto; top: -127px; left: -70px; position: absolute; cursor: default; display: none;">
            <div class="nicescroll-cursors"
                 style="position: absolute; top: 0px; height: 6px; width: 0px; background-color: rgb(221, 221, 221); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;"></div>
        </div>
        <div id="ascrail2003" class="nicescroll-rails nicescroll-rails-vr"
             style="width: 8px; z-index: auto; cursor: default; position: absolute; top: -119px; left: -78px; height: 0px; display: none;">
            <div class="nicescroll-cursors"
                 style="position: relative; top: 0px; float: right; width: 6px; height: 0px; background-color: rgb(221, 221, 221); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;"></div>
        </div>
        <div id="ascrail2003-hr" class="nicescroll-rails nicescroll-rails-hr"
             style="height: 8px; z-index: auto; top: -127px; left: -70px; position: absolute; cursor: default; display: none;">
            <div class="nicescroll-cursors"
                 style="position: absolute; top: 0px; height: 6px; width: 0px; background-color: rgb(221, 221, 221); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;"></div>
        </div>
        <div id="ascrail2004" class="nicescroll-rails nicescroll-rails-vr"
             style="width: 8px; z-index: auto; cursor: default; position: absolute; top: -119px; left: -78px; height: 0px; display: none;">
            <div class="nicescroll-cursors"
                 style="position: relative; top: 0px; float: right; width: 6px; height: 0px; background-color: rgb(221, 221, 221); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;"></div>
        </div>
        <div id="ascrail2004-hr" class="nicescroll-rails nicescroll-rails-hr"
             style="height: 8px; z-index: auto; top: -127px; left: -70px; position: absolute; cursor: default; display: none;">
            <div class="nicescroll-cursors"
                 style="position: absolute; top: 0px; height: 6px; width: 0px; background-color: rgb(221, 221, 221); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;"></div>
        </div>
    </div>
    <div id="ascrail2000" class="nicescroll-rails nicescroll-rails-vr"
         style="width: 5px; z-index: auto; cursor: default; position: absolute; top: 0px; left: 747.4px; height: 459px; display: block; opacity: 0;">
        <div class="nicescroll-cursors"
             style="position: relative; top: 0px; float: right; width: 5px; height: 163px; background-color: rgb(215, 215, 215); border: none; background-clip: padding-box; border-radius: 0px;"></div>
    </div>
</div>
<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs"
        data-app-key="zI4VV3yo6RgAAAAAAAAAAV2v6LL3bB7q-mIZEf88lNrw-qAPnZBhikehS9Gjj2zA"></script>
<script type="text/javascript" src="//apis.google.com/js/client.js" gapi_processed="true"></script>
<script type="text/javascript"
        src="https://www.ipostx.ru/inc/themes/backend/default/assets/plugins/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="/resources/plugins/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="/resources/js/core.js"></script>
<script src="/resources/js/hashtag.js"></script>
<script src="/resources/js/comment.js"></script>
<script src="/resources/js/loader.js"></script>
<script src="/resources/js/popup.js"></script>
<script type="text/javascript">
    $('.dateTimePicker').datepicker({
        format: 'mm-dd-yyyy'
    });
</script>
</div>
</div>
</div>
</body>
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
<script>
    function chooseRightFunctionality()
    {
        let functionality = window.location.href.split('#')[1];

        if(functionality !== null && functionality !== '')
        {
            document.getElementById(functionality).click();
        }
    }

    chooseRightFunctionality();
</script>
</html>
