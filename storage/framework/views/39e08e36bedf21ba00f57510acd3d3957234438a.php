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
    
    <link rel="stylesheet" type="text/css" href="/resources/fonts/flags/flag-icon.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/datetimepicker/jquery-ui-timepicker-addon.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/chartjs/chart.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/fancybox/jquery.fancybox.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/izitoast/css/izitoast.css">
    
    <link rel="stylesheet" type="text/css" href="/resources/plugins/custom-scrollbar/custom-scrollbar.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/monthly/monthly.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/ion.rangeslider/ion.rangeSlider.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/owl-carousel/css/owl.carousel.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/vtdropdown/vtdropdown.css">
    <link rel="stylesheet" type="text/css" href="/resources/plugins/select/css/bootstrap-select.css">
    <link rel="stylesheet" type="text/css" href="/resources/css/loader/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>

    
    
    
    
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
<div class="header" style="left:0; width: 100%;">
    <a class="btn btn-warning" style="color: black;" href="https://ipostx.ru/dashboard">
        Выйти из модуля и перейти на панель управления
    </a>
    <div class="topbar">
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        <div class="tokens btn btn-light" style="color: #222b45 !important; margin-right: 10px !important; margin-bottom: 7px !important;">
            <a  style="color: #222b45 !important;" href="<?= route('tokens') ?>"><i class="far fa-user"></i>
                Токены</a>
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
                    Здравствуй, <?php echo e(Auth::user()->name); ?> <img
                            src="<?php echo e($avatarUrl); ?>">
                </button>
                <div
                        class="dropdown-menu dropdown-menu-right dropdown-menu-fit dropdown-menu-anim dropdown-menu-top-unround"
                        aria-labelledby="dropdownMenuButton">




                    <a class="dropdown-item" href="<?php echo e(route('logout')); ?>"
                       onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt">
                        </i> Выход</a>
                    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                        <?php echo csrf_field(); ?>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>





















































































































































































































































































































































































































































































































<div class="wrapper" style="margin-left: 0px;">

    <div class="subheader instagram-activity" style="left:0;padding-left:6%;">
        <div class="wrap">
            <div class="subheader-main wrap-m w-100 p-r-0">
                <div class="wrap-c">
                    <h3 class="title"><i class="fab fa-vk" style="color: #d62976"></i> Активность Vkontakte </h3>
                </div>
            </div>
            <div class="subheader-main wrap-m w-100 p-r-0">
                <div class="wrap-c">
                    <h2 class="title" style="background-color: red;">При использовании впн вы не можете делать большие
                        запросы (сброс после 3-5 минут обработки)</h2>
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
                            
                            <img src="<?php echo e($vkPhotoUrl); ?>">
                            
                        </div>
                        <div class="info">
                            <div class="title"><?php echo e(Auth::user()->name); ?></div>
                            <div class="desc">Профиль</div>
                        </div>

                    </div>

                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                </div>

            </div>

            <div class="ig-ac-main">

                <div class="row">

                    

                    

                    
                    
                    

                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    

                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    

                    
                    <div class="col-md-4">
                        <div class="tasks">
                            <div class="title fw-6 text-info">Выберите, что вы хотите сделать</div>
                            <div class="item-task wrap-m">
                                <div class="wrap-c">
                                    <label class="i-switch i-switch--outline i-switch--info m-t-6 m-r-6">
                                        <input type="checkbox" name="todos[like]" class="action-save"
                                               data-type="like" checked="true" value="1">
                                        <span></span>
                                    </label>
                                    <div class="name fs-16">
                                        Лайк <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                                data-trigger="hover" data-placement="top" data-html="true" title=""
                                                data-original-title="Включите этот переключатель, чтобы автоматизировать свою активность лайков. <br/> <br/> Счетчик показывает, сколько фотографий и видео вам понравилось с момента начала вашей последней активности."></i>

                                    </div>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    <?php echo e($data['data']['all_likes'] ?? '0'); ?>

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
                                        Комментарий <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                                       data-trigger="hover" data-placement="top" data-html="true"
                                                       title=""
                                                       data-original-title="Включите этот переключатель, чтобы автоматизировать свои комментарии. <br/> <br/> Счетчик показывает, сколько фотографий и видео вы прокомментировали с момента начала вашей последней активности."></i>
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
                                        Работа с друзьями <i class="fa fa-question-circle"
                                                            data-toggle="tooltip-custom" data-trigger="hover"
                                                            data-placement="top" data-html="true" title=""
                                                            data-original-title="Включите этот переключатель, чтобы автоматизировать просмотр истории."></i>
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
                                        Подписки
                                    </div>

                                    <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                       data-trigger="hover" data-placement="top" data-html="true"
                                       title=""
                                       data-original-title="Включите этот переключатель, чтобы автоматизировать свои подписки. <br/> <br/> Счетчик показывает, на сколько пользователей вы подписались с момента начала вашей последней активности."></i>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    <?php echo e($data['data']['accept_followers'] ?? '0'); ?>

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
                                        Отписки
                                    </div>
                                    <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                       data-trigger="hover" data-placement="top" data-html="true"
                                       title=""
                                       data-original-title="Включите этот переключатель, чтобы автоматизировать свои отписки. <br/> <br/> Счетчик показывает, на сколько пользователей вы отписались с момента начала своей последней активности."></i>
                                </div>
                                <div class="wrap-c fs-18 fw-6">
                                    <?php echo e($data['data']['deleted_friends'] ?? '0'); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 recent-activity-wrap">
                        <div class="title m-b-8 fw-6 text-info">Недавняя активность</div>
                        <div class="nicescroll no-update" style="overflow-y: auto; outline: none;"
                             tabindex="2">

                            <?php if(session('commentFieldRequired')): ?>
                                <br>
                                <div class="alert alert-warning" role="alert">
                                    <?php echo e(session('commentFieldRequired')); ?>

                                </div>
                            <?php endif; ?>

                                <?php if(session('token_fail')): ?>
                                    <br>
                                    <div class="alert alert-warning" role="alert">
                                        <?php echo e(session('token_fail')); ?>

                                    </div>
                                <?php endif; ?>

                                <?php if(session('auth_fail')): ?>
                                    <br>
                                    <div class="alert alert-warning" role="alert">
                                        <?php echo e(session('auth_fail')); ?>

                                    </div>
                                <?php endif; ?>

                            <?php if(session('successTagRec')): ?>
                                <br>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('successTagRec')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('not_valid_comment_concurrents')): ?>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('not_valid_comment_concurrents')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('concurrentCommentsSuccess')): ?>
                                <br>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('concurrentCommentsSuccess')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('search_records.auto_likes')): ?>
                                <br>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('search_records.auto_likes')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('search_records_flood.auto_likes')): ?>
                                <br>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('search_records_flood.auto_likes')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('adding_friend')): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('adding_friend')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_photo_like')): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('com_photo_like')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_photo_like_captcha')): ?>
                                <div class="alert alert-warning" role="alert">
                                    <?php echo e(session('com_photo_like_captcha')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_photo_like_unhadled')): ?>
                                <div class="alert alert-warning" role="alert">
                                    <?php echo e(session('com_photo_like_unhadled')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_photo_like_flood')): ?>
                                <div class="alert alert-warning" role="alert">
                                    <?php echo e(session('com_photo_like_flood')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_post_like')): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('com_post_like')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_post_like_captcha')): ?>
                                <div class="alert alert-warning" role="alert">
                                    <?php echo e(session('com_post_like_captcha')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_post_like_unhandled')): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('com_post_like_unhandled')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('com_post_like_flood')): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('com_post_like_flood')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('posts.autolike_followers_success') && !session('posts.autolike_followers')): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('posts.autolike_followers_success')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('posts.autolike_followers')): ?>
                                <div class="alert alert-warning" role="alert">
                                    <?php echo e(session('posts.autolike_followers')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('not_valid_like_concurrents')): ?>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('not_valid_like_concurrents')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('autoLikesSuccess') && !session('photos.autolike_concurrents') && !session('posts.autolike_concurrents')): ?>
                                <br>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('autoLikesSuccess')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('photos.autolike_concurrents') && session('posts.autolike_concurrents')): ?>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('photos.autolike_concurrents')); ?>

                                </div>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('posts.autolike_concurrents')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('auto_like_criteries_success')): ?>
                                <br>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('auto_like_criteries_success')); ?>

                                </div>
                            <?php endif; ?>
                            <?php if(session('posts.autolike_members')): ?>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('posts.autolike_members')); ?>

                                </div>
                            <?php endif; ?>
                            <?php if(session('photos.autolike_members')): ?>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('photos.autolike_members')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('photos.autolike_members')): ?>
                                <br>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(session('photos.autolike_members')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('delete_friend')): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo e(session('delete_friend')); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(session('accepting_friends')): ?>
                                <div class="alert alert-info" role="alert">
                                    <?php echo e(session('accepting_friends')); ?>

                                </div>
                            <?php endif; ?>


                            
                        </div>
                    </div>
                    <div class="col-md-4"></div>
                </div>

            </div>

            <div class="ig-ac-options">

                <div class="ig-ac-tab nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                     aria-orientation="vertical">
                    
                    
                    
                    
                    
                    
                    
                    <a id="v-pills-comment-tab" data-toggle="pill" href="#v-pills-comment"
                       role="tab" aria-controls="v-pills-comment" aria-selected="true" class="active"><i
                                class="far fa-comment-alt"></i> Добавление комментариев</a>
                    <a id="v-pills-commenthashtag-tab" data-type="comment" data-toggle="pill"
                       href="#v-pills-commenthashtag"
                       role="tab" aria-controls="v-pills-commenthashtag" aria-selected="false" class=""><i
                                class="far fa-comment-alt"></i> Комментарии по хэштегам</a>
                    <a id="v-pills-commentconcurrents-tab" data-type="comment" data-toggle="pill"
                       href="#v-pills-commentconcurrents"
                       role="tab" aria-controls="v-pills-commentconcurrents" aria-selected="false" class=""><i
                                class="far fa-comment-alt"></i> Комментарии по конкурентам</a>
                    <!-- <a id="v-pills-watching-story-tab" data-type="watching_story" data-toggle="pill" href="#v-pills-watching-story" role="tab" aria-controls="v-pills-watching-story" aria-selected="false"><i class="far fa-eye"></i> Смотреть истории</a> -->
                    <a id="v-pills-follow-tab" data-type="follow" data-toggle="pill" href="#v-pills-follow"
                       role="tab" aria-controls="v-pills-follow" aria-selected="false" class=""><i
                                class="fas fa-user-plus"></i> Подписки</a>
                    <a id="v-pills-unfollow-tab" data-type="unfollow" data-toggle="pill" href="#v-pills-unfollow"
                       role="tab" aria-controls="v-pills-unfollow" aria-selected="false" class=""><i
                                class="fas fa-user-minus"></i> Отписки</a>
                    
                    
                    
                    <a id="v-pills-repost-tab" data-type="like" data-toggle="pill" href="#v-pills-repost"
                       role="tab" aria-controls="v-pills-repost" aria-selected="false" class=""><i
                                class="fas fa-reply-all"></i> Добавление тегов</a>
                    <a id="v-pills-search-tab" data-type="like" data-toggle="pill" href="#v-pills-search-tag" role="tab"
                       aria-controls="v-pills-search-tab" aria-selected="false"><i class="fas fa-hashtag"></i> Лайки по
                        хэштегам</a>

                    <a id="v-pills-tag-tab" data-type="friends" data-toggle="pill" href="#v-pills-tag" role="tab"
                       aria-controls="v-pills-tag" aria-selected="false"><i class="fas fa-hashtag"></i> Теги для
                        добавления друзей</a>
                    <a id="v-pills-blacklist-tab" data-type="friends" data-toggle="pill" href="#v-pills-blacklist" role="tab"
                       aria-controls="v-pills-blacklist" aria-selected="false"><i class="fas fa-ban"></i> Теги для
                        удаления друзей</a>
                    <a id="v-pills-location-tab" data-type="like" data-toggle="pill" href="#v-pills-location" role="tab"
                       aria-controls="v-pills-location" aria-selected="false"><i class="fas fa-heart"></i>
                        Лайки на комментарии</a>
                    <a id="v-pills-likefriends-tab" data-type="like" data-toggle="pill" href="#v-pills-likefriends" role="tab"
                       aria-controls="v-pills-likefriends" aria-selected="false"><i class="fas fa-heart"></i>
                        Лайки друзьям и подписчикам</a>
                    <a id="v-pills-likeconcurents-tab" data-type="like" data-toggle="pill" href="#v-pills-likeconcurents" role="tab"
                       aria-controls="v-pills-likeconcurents" aria-selected="false"><i
                                class="fas fa-heart"></i>
                        Лайки конкурентам</a>
                    <a id="v-pills-likecriteries-tab" data-type="like" data-toggle="pill" href="#v-pills-likecriteries" role="tab"
                       aria-controls="v-pills-likecriteries" aria-selected="false"><i class="fas fa-heart"></i>
                        Лайки подписчикам группы</a>
                    
                    
                    
                </div>

                <div class="ig-ac-content">
                    <div class="tab-content p-t-25" id="v-pills-tabContent">
                        
                        

                        

                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        

                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        

                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        

                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        

                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        

                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        

                        

                        
                        <div class="tab-pane fade" id="v-speed" role="tabpanel" aria-labelledby="v-speed-tab">

                            <div class="row">

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">Скорость активности </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="<b>Низкая</b> — безопасная скорость для выполнения<br/><br/><b>Нормальная</b> — умная скорость для выполнения<br/><br/><b>Быстрая</b> — не безопасная скорость выполнения<br/><br/>Попробуйте для начала<b>Низкая</b> потом если всё хорошо измените на <b>Нормальная</b> или <b>Быстрая</b> через несколько дней."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control speed-level" name="speeds[level]">
                                                <option selected="true" value="slow" data-speed="[2,1,2,2,2,1,1]">
                                                    Низкая
                                                </option>
                                                <option value="normal" data-speed="[4,2,4,4,4,2,2]">Нормальная
                                                </option>
                                                <option value="fast" data-speed="[6,3,6,6,6,3,3]">Быстрая</option>
                                                <option value="" disabled="" class="">Своя</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">Лайков/час </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Кол-во лайков которая ваша  активность постарается сделать за час.<br/><br/>Допустимые значения: <b>1</b>-<b>60</b><br/><br/><span class='text-danger'>Будьте осторожны!</span>"></i>
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
                                            <span class="p-r-5">Комментариев/час </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Кол-во Комментариев которая ваша  активность постарается сделать за час.<br/><br/>Допустимые значения: <b>1</b>-<b>20</b><br/><br/><span class='text-danger'>Будьте осторожны!</span>"></i>
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
                                            <span class="p-r-5">Просмотр историй/час </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Кол-во Просмотров историй которая ваша  активность постарается сделать за час.<br/><br/>Допустимые значения: <b>1</b>-<b>60</b><br/><br/><span class='text-danger'>Будьте осторожны!</span>"></i>
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
                                            <span class="p-r-5">Подписок/час </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Кол-во подписок которая ваша  активность постарается сделать за час.<br/><br/>Допустимые значения: <b>1</b>-<b>40</b><br/><br/><span class='text-danger'>Будьте осторожны!</span>"></i>
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
                                            <span class="p-r-5">Отписок/час </span>
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
                                            <span class="p-r-5">Сообщений/час </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Кол-во отправок сообщений которая ваша  активность постарается сделать за час.<br/><br/>Допустимые значения: <b>1</b>-<b>20</b><br/><br/><span class='text-danger'>Будьте осторожны!</span>"></i>
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
                                            <span class="p-r-5">Репостов/час </span>
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
                                            <span class="p-r-5">Возраст медиа </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Этот параметр поможет вам выбрать возраст медиа, с которым вы хотите взаимодействовать. От самого нового до самого старого."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[media_age]">
                                                <option selected="true" value="">Все</option>
                                                <option value="new">Новые</option>
                                                <option value="1h">За 1 час</option>
                                                <option value="12h">За 12 часов</option>
                                                <option value="1d">За 1 день</option>
                                                <option value="3d">За 3 дня</option>
                                                <option value="1w">За 1 неделю</option>
                                                <option value="2w">За 2 недели</option>
                                                <option value="1m">За 1 месяц</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">Тип медиа </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Этот параметр позволяет взаимодействовать только с определенным типом медиа: фото или видео. Также вы можете выбрать Все для взаимодействия с любым типом медиа."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[media_type]">
                                                <option selected="true" value="">Все</option>
                                                <option value="image">Фото</option>
                                                <option value="video">Видео</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">Мин. Лайков </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействовать только с теми медиа, у которых минимально выбранное количество лайков.<br/><br/>Используйте его вместе с <b>Макс. Лайков</b> для установки желаемого диапазона популярности медиа.<br/><br/>Рекомендуемое значение: 0.<br/><br/>Установите ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Макс. Лайков </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействовать только с теми медиа, у которых максимально выбранное количество лайков.<br/><br/>Используйте его вместе с <b>Мин. Лайков</b> для установки желаемого диапазона популярности медиа.<br/><br/>Рекомендуемые значения: 50–100.<br/><br/>Установите ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Мин. Комментарий </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействовать только с теми медиа, которые имеют минимальное количество комментариев. <br/> <br/> Используйте его вместе с <b> Макс. Комментарий </ b> устанавливает желаемый диапазон популярности медиа. <br/> <br/> Рекомендуемое значение: 0. <br/> <br/> Установите ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Макс. Комментарий </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействовать только с теми медиа, которые имеют максимальное выбранное количество комментариев. <br/> <br/> Используйте его вместе с <b> Мин. Комментарий </ b> устанавливает желаемый диапазон популярности медиа. <br/> <br/> Рекомендуемые значения: 20-50. <br/> <br/> Установите в ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Мин. Подписчиков </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействуйте только с пользователями, у которых минимальное количество подписчиков. <br/> <br/> Используйте его вместе с <b> Макс. Подписчиков </ b> устанавливает желаемый диапазон популярности пользователей. <br/> <br/> Рекомендуемые значения: 0-50. <br/> <br/> Установите ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Макс. Подписчиков </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействуйте только с пользователями, у которых максимальное количество подписчиков. <br/> <br/> Используйте его вместе с <b> Мин. Подписчиков </ b> устанавливает желаемый диапазон популярности пользователей. <br/> <br/> Рекомендуемые значения: 500–1000. <br/> <br/> Установите ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Мин. Подписок </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействуйте только с пользователями, у которых минимальное количество подписок. <br/> <br/> Используйте его вместе с <b>Макс. Подписок</ b> устанавливает желаемый диапазон популярности пользователей. <br/> <br/> Рекомендуемые значения: 50–100. <br/> <br/> Установите на ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Макс. Подписок </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Взаимодействуйте только с пользователями, у которых есть максимальное количество подписчиков. <br/> <br/> Используйте его вместе с <b> Мин. Подписок </ b> устанавливает желаемый диапазон популярности пользователей. <br/> <br/> Рекомендуемые значения: 300-500. <br/> <br/> Установите на ноль, чтобы отключить этот фильтр."></i>
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
                                            <span class="p-r-5">Фильтр профиля </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Этот фильтр поможет вам избежать нежелательных пользователей и их медиа во время вашей активности:<br/><br/><b>Выкл.</b> - фильтр отключен. <br/> <br/> <b> Низкий </b> - исключает пользователей, у которых нет аватара или нет опубликованных медиафайлов. <br/> <br/> <b> Средний </b> - исключает пользователей, у которых нет аватара, у которых менее 10 опубликованных медиа или не имеют имени в профиле. <br/> <br/> <b> Высокий </b> - исключает пользователей, у которых нет аватара, менее 30 опубликованных материалов, нет имени в профиле или информации."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[user_profile]">
                                                <option selected="true" value="">Выкл</option>
                                                <option value="low">Низкий</option>
                                                <option value="medium">Средний</option>
                                                <option value="high">Высокий</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">Фильтр отношений </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true" title=""
                                               data-original-title="Этот фильтр поможет вам исключить ваших собственных подписчиков/подписок из активности «Нравится», «Комментирование», «Подписка»: <br/> <br/> <b> Выкл. </ B> - фильтр отключен. <br/> <br/> <b> Подписчики </ b>. Вы не будете взаимодействовать со своими подписчиками и их медиа. <br/> <br/> <b> Подписки </ b>. Вы не будете взаимодействовать со своими подписками и их медиа. <br> /> <br/> <b> Оба </ b>. Вы не будете взаимодействовать со своими подписчиками, подписками и их медиа."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="filters[user_relation]">
                                                <option selected="true" value="">Выкл</option>
                                                <option value="followers">Подписчики</option>
                                                <option value="followings">Подписок</option>
                                                <option value="both">Оба</option>
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
                                            class="card-header"><?php echo e(__('Добавить комментарии')); ?></div>

                                    <div class="card-body">

                                        <div class="row m-b-25">

                                            <div class="col-md-12">
                                                <div class="ig-ac-option">
                                                    <div class="info">
                                                        <span class="p-r-5">Введите ваш комментарий </span>
                                                        <i class="fa fa-question-circle"
                                                           data-toggle="tooltip-custom"
                                                           data-trigger="hover" data-placement="top"
                                                           data-html="true" title=""
                                                           data-original-title="Добавьте хотя бы один комментарий, если вы включили Комментарии в разделе активности."></i>
                                                        <div class="form-group">
                                                    <textarea class="form-control form-add-comment post-message"
                                                              name="comment" required
                                                              style=""></textarea>
                                                        </div>
                                                        <button type="button" class="btn btn-info btn-add-comment">
                                                            Добавить
                                                            новый
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="m-b-0 list-add-comment">

                                            <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                                <div class="wrap-c"><i class="far fa-comment-alt p-r-5"></i>
                                                    Комментарии
                                                </div>
                                                <div class="wrap-c">
                                                    <a href="javascript:void(0);"
                                                       class="btn btn-label-danger btn-sm remove-all"><i
                                                                class="far fa-trash-alt"></i> Удалить все</a>
                                                </div>
                                            </div>
                                        <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>
                                                <div class="ig-ac-option-item-comment">
                                                    <a href="javascript:void(0);" class="remove"><i
                                                                class="fas fa-times-circle text-danger"></i></a> <?php echo e($comment); ?><br>
                                                    <textarea class="d-none"
                                                              name="comments[]"><?php echo e($comment); ?></textarea>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                                            class="card-header"><?php echo e(__('Поставить комментарии под постами которые имеют эти теги')); ?></div>

                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('giveCommentsOnSearchRecords')); ?>">
                                            <?php echo csrf_field(); ?>

                                            <div class="form-group row" id="addFriendByTag">
                                                <label for="number-requests"
                                                       class="col-4 col-form-label">Количество
                                                    комментариев (макс.200)</label>
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
                                                    Дата начала поиска
                                                    <input type="date" class="form-control searchStart" id="searchStart"
                                                           name="searchStart" placeholder="Дата" required>
                                                </div>

                                                <div style="margin: 2px;"></div>

                                                <div class="d-flex-column">
                                                    Дата конца поиска
                                                    <input type="date" class="form-control searchEnd" id="searchEnd"
                                                           name="searchEnd" placeholder="Дата" required>
                                                </div>
                                            </div>

                                            <div class="row m-b-25">

                                                <div class="col-md-12">
                                                    <div class="ig-ac-option">
                                                        <div class="info">
                                                            <span class="p-r-5">Введите ваш комментарий </span>
                                                            <i class="fa fa-question-circle"
                                                               data-toggle="tooltip-custom"
                                                               data-trigger="hover" data-placement="top"
                                                               data-html="true" title=""
                                                               data-original-title="Добавьте хотя бы один комментарий, если вы включили Комментарии в разделе активности."></i>
                                                            <div class="form-group">
                                                    <textarea class="form-control form-add-comment post-message"
                                                              name="comment" required
                                                              style=""></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-info btn-add-comment">
                                                                Начать
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="m-b-0 list-add-comment">

                                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                                    <div class="wrap-c"><i class="far fa-comment-alt p-r-5"></i>
                                                        Комментарии
                                                    </div>
                                                    <div class="wrap-c">
                                                        <a href="javascript:void(0);"
                                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                                    class="far fa-trash-alt"></i> Удалить все</a>
                                                    </div>
                                                </div>
                                                <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div>
                                                        <div class="ig-ac-option-item-comment">
                                                            <a href="javascript:void(0);" class="remove"><i
                                                                        class="fas fa-times-circle text-danger"></i></a> <?php echo e($comment); ?><br>
                                                            <textarea class="d-none"
                                                                      name="comments[]"><?php echo e($comment); ?></textarea>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                                            class="card-header"><?php echo e(__('Поставить комментарии людям которые лайкнули или оставили комментарий под записью')); ?></div>

                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('writeCommentsOnConcurrents')); ?>">
                                            <?php echo csrf_field(); ?>

                                            <div class="form-group row">
                                                <label for="post_url"
                                                       class="col-4 col-form-label">Ссылка на публикацию</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text"
                                                           name="post_url" id="post_url">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                
                                                
                                                
                                                

                                                
                                                
                                                
                                                
                                            </div>

                                            <div class="row m-b-25">

                                                <div class="col-md-12">
                                                    <div class="ig-ac-option">
                                                        <div class="info">
                                                            <span class="p-r-5">Введите ваш комментарий </span>
                                                            <i class="fa fa-question-circle"
                                                               data-toggle="tooltip-custom"
                                                               data-trigger="hover" data-placement="top"
                                                               data-html="true" title=""
                                                               data-original-title="Добавьте хотя бы один комментарий, если вы включили Комментарии в разделе активности."></i>
                                                            <div class="form-group">
                                                    <textarea class="form-control form-add-comment post-message"
                                                              style="" name="comment"></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-info btn-add-comment">
                                                                Начать
                                                            </button>
                                                            <br>
                                                            <div class="row" style="margin:5px; font-size:14px;">
                                                                * Комментарий будет проставлен на первую запись со стены
                                                                пользователя
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="m-b-0 list-add-comment">

                                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                                    <div class="wrap-c"><i class="far fa-comment-alt p-r-5"></i>
                                                        Комментарии
                                                    </div>
                                                    <div class="wrap-c">
                                                        <a href="javascript:void(0);"
                                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                                    class="far fa-trash-alt"></i> Удалить все</a>
                                                    </div>
                                                </div>

                                                <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div>
                                                        <div class="ig-ac-option-item-comment">
                                                            <a href="javascript:void(0);" class="remove"><i
                                                                        class="fas fa-times-circle text-danger"></i></a> <?php echo e($comment); ?><br>
                                                            <textarea class="d-none"
                                                                      name="comments[]"><?php echo e($comment); ?></textarea>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                                    <div class="card-header"><?php echo e(__('Заявки в друзья')); ?></div>

                                    <div class="card-header">
                                        <div class="wrap-c">
                                            <h4 style="">Лимит добавления друзей в ВК 50 человек в день</h4>
                                        </div>
                                    </div>

                                    <div class="card-header" style="padding-bottom: 20px; height: 90px !important;">
                                        <div class="wrap-c">
                                            <h4 style="">Для включения в работу какого либо модуля по поиску друзей, поставьте Галочку
                                                в чекбоксе «Искать» и нажмите кнопку «Начать» в нижней части страницы
                                            </h4>
                                        </div>
                                    </div>

                                    <form method="POST" action="<?php echo e(route('friends')); ?>">
                                        <?php echo csrf_field(); ?>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Поиск новых друзей по хэштегам</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    записей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="tag_requests"
                                                           value="1" min="1" max="50"
                                                           id="tag_requests">
                                                </div>
                                                <div class="col-8">
                                                    <div class="input-group mb-2 mr-sm-2"
                                                         id="addToFriendInp">
                                                        <label for="tags" class="col-4 col-form-label">Теги для
                                                            поиска</label>
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">#</div>
                                                        </div>
                                                        <input type="text" class="form-control"
                                                               name="tags"
                                                               id="inlineFormInputSearchTags"
                                                               placeholder="тег, тег...">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="d-flex-column">
                                                    Дата начала поиска
                                                    <input type="date" class="form-control searchStart"
                                                           id="searchStart" name="searchStart"
                                                           placeholder="Дата">
                                                </div>

                                                <div style="margin: 2px;"></div>

                                                <div class="d-flex-column">
                                                    Дата конца поиска
                                                    <input type="date" class="form-control searchEnd"
                                                           id="searchEnd" name="searchEnd" placeholder="Дата">
                                                </div>

                                                <div style="margin: 2px;"></div>

                                                <div class="col-2">
                                                    <label for="tagSearchAllowed" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="tagSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Поиск новых друзей в подписчиках группы</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    записей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="group_members_qty"
                                                           value="1" min="1" max="1000"
                                                           id="group_members_qty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ от начала списка</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="group_members_offset"
                                                           value="0" min="0" max="5000000"
                                                           id="group_members_offset">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/короткое имя группы</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="members_group_id"
                                                           id="members_group_id">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupMembersSearchAllowed" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="groupMembersSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Поиск новых друзей в комментариях к постам сообщества</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    записей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInPostCommentsQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInPostCommentsQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во постов для обхода</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPostComments"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во комментариев к постам больше</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_qty_for_findFriendsInPostComments"
                                                           value="1" min="1" max="1000"
                                                           id="comment_qty_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в постах</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPostComments"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в комментариях</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_offset_for_findFriendsInPostComments"
                                                           value="0" min="0" max="10000"
                                                           id="comment_offset_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/короткое имя группы</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPostComments"
                                                           id="owner_id_for_findFriendsInPostComments">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupMembersSearchAllowed" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="groupPostCommentsSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Поиск новых друзей в комментариях к фотографиям сообщества</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    записей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsPhotosInCommentsQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsPhotosInCommentsQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во фотографий для обхода</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPhotosComments"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во комментариев к фотографиям больше</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_qty_for_findFriendsInPhotosComments"
                                                           value="1" min="1" max="1000"
                                                           id="comment_qty_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в фотографиях</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPhotosComments"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в комментариях</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comment_offset_for_findFriendsInPhotosComments"
                                                           value="0" min="0" max="10000"
                                                           id="comment_offset_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/короткое имя профиля</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPhotosComments"
                                                           id="owner_id_for_findFriendsInPhotosComments">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupPhotosCommentsSearchAllowed" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="groupPhotosCommentsSearchAllowed">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Поиск новых друзей в лайках к постам профиля</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    записей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInPostsLikesQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInPostsLikesQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во постов для обхода</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPostsLikes"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во лайков к постам больше</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_qty_for_findFriendsInPostsLikes"
                                                           value="1" min="1" max="1000"
                                                           id="likes_qty_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в постах</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPostsLikes"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в лайках</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_offset_for_findFriendsInPostsLikes"
                                                           value="0" min="0" max="10000"
                                                           id="likes_offset_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/короткое имя группы</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPostsComments"
                                                           id="owner_id_for_findFriendsInPostsLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupLikedPostsAllow" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="groupLikedPostsAllow">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Поиск новых друзей в лайках к фотографиям профиля</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    записей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInPhotosLikesQty"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInPhotosLikesQty">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во фотографий для обхода</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_qty_for_findFriendsInPhotosLikes"
                                                           value="1" min="1" max="1000"
                                                           id="record_qty_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во лайков к фотографиям больше</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_qty_for_findFriendsInPhotosLikes"
                                                           value="1" min="1" max="1000"
                                                           id="likes_qty_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в фотографиях</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="record_offset_for_findFriendsInPhotosLikes"
                                                           value="0" min="0" max="10000"
                                                           id="record_offset_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в лайках</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_offset_for_findFriendsInPhotosLikes"
                                                           value="0" min="0" max="10000"
                                                           id="likes_offset_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">ID/короткое имя профиля</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsInPhotosLikes"
                                                           id="owner_id_for_findFriendsInPhotosLikes">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupLikedPhotosAllow" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="groupLikedPhotosAllow">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Поиск новых друзей в пользователях взаимодействующих с постом</h3>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    записей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="findFriendsInRecordConcurrents"
                                                           value="1" min="1" max="1000"
                                                           id="findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во лайков больше</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_qty_for_findFriendsInRecordConcurrents"
                                                           value="1" min="1" max="1000"
                                                           id="likes_qty_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во комментариев больше</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comments_qty_for_findFriendsInRecordConcurrents"
                                                           value="1" min="1" max="1000"
                                                           id="comments_qty_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в лайках</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="likes_offset_for_findFriendsInRecordConcurrents"
                                                           value="0" min="0" max="10000"
                                                           id="likes_offset_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">Отступ в комментариях</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="comments_offset_for_findFriendsInRecordConcurrents"
                                                           value="0" min="0" max="10000"
                                                           id="comments_offset_for_findFriendsInRecordConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">Url записи</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_id_for_findFriendsRecordsConcurrents"
                                                           id="owner_id_for_findFriendsRecordsConcurrents">
                                                </div>
                                            </div>
                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="concurrentsSearchAllow" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="concurrentsSearchAllow">
                                                    </label>
                                                </div>

                                                <div class="col-2">
                                                </div>

                                                <div class="col-4">
                                                    <label for="recordTypeIsPhoto" class="col-form-label">
                                                        Тип записи - фото
                                                        <input class="form-control" type="checkbox" name="recordTypeIsPhoto">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3" style="border: 1px solid rgb(73,85,139)">

                                            <h3>Добавление новых друзей из списка</h3>

                                            <div class="form-group row">
                                                <label for="members_group_id" class="col-4 col-form-label">Список ID/коротких имен пользователей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="text" name="owner_ids_list"
                                                           id="owner_ids_list" placeholder="id,id,shortName...">
                                                </div>
                                            </div>

                                            <div class="form-group row text-align-center">
                                                <div class="col-2">
                                                    <label for="groupLikedPhotosAllow" class="col-form-label">
                                                        Искать
                                                        <input class="form-control" type="checkbox" name="loadedListAllow">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body ml-3">

                                            <div class="card-header">
                                                Одобрение запросов  в друзья
                                            </div>

                                            <div class="form-group row">
                                                <label for="number-requests" class="col-4 col-form-label">К-во
                                                    друзей</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="requests"
                                                           value="1" min="1" max="1000"
                                                           id="number-requests">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-friends" class="col-4 col-form-label">Мин.
                                                    к-во
                                                    друзей у подписчика</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number" name="friends"
                                                           value="0" min="0" max="1000" id="number-friends">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-followers" class="col-4 col-form-label">Мин.
                                                    к-во
                                                    подписчиков у аккаунта подписчика</label>
                                                <div class="col-8">
                                                    <input class="form-control" type="number"
                                                           name="followers"
                                                           value="0" min="0" max="1000"
                                                           id="number-followers">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="number-posts" class="col-4 col-form-label">Мин.
                                                    к-во постов
                                                    на стене у подписчика</label>
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
                                                                   type="checkbox"> Фото обязательно у
                                                            подписчика
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
                                                                   value="1" type="checkbox"> Отклонить
                                                            заявки с
                                                            заблокироваными аккаунтами
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="gridCheck1">
                                                        <label class="form-check-label">
                                                            <input type="hidden" name="hidden" value="0"/>
                                                            <input class="form-check-input" name="hidden"
                                                                   value="1"
                                                                   type="checkbox"> Отклонить заявки с
                                                            скрытыми
                                                            аккаунтами
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
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
                                            class="card-header"><?php echo e(__('Удалить деактивированых друзей')); ?></div>

                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('delfriends')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <div class="form-group row">
                                                <label class="col-sm-4">Тип деактивации</label>
                                                <div class="col-sm-8">
                                                    <div class="form-check form-check-inline">
                                                        <input type="hidden" name="banned" value="0"/>
                                                        <input class="form-check-input" type="checkbox"
                                                               name="banned" value="1">
                                                        <label class="form-check-label"
                                                               for="inlineRadio1">Был забанен</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input type="hidden" name="deleted" value="0"/>
                                                        <input class="form-check-input" type="checkbox"
                                                               name="deleted" value="1">
                                                        <label class="form-check-label"
                                                               for="inlineRadio2">Удален</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-8">
                                                    <button type="submit"
                                                            class="btn btn-primary"><?php echo e(__('Удалить')); ?></button>
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
                                               data-original-title="Каким пользователям отправлять сообщения?<br/><br/><b>Новые подписчики</b> — Выберите этот вариант, если хотите отправлять сообщения только своим новым подписчикам<br/><br/><b>Цель</b> — выберите эту опцию, если вы хотите направить сообщение по настройкам цели"></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save" name="op_directs[by]">
                                                <option selected="true" value="follower">Новые подписчики
                                                </option>
                                                <option value="target">Цели</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row m-b-25">

                                <div class="col-md-12">
                                    <div class="ig-ac-option">
                                        <div class="info">
                                            <span class="p-r-5">Введите ваше сообщение </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Добавьте хотя бы одно сообщение, если вы включили отправку сообщений в разделе активности."></i>
                                            <div class="form-group">
                                                    <textarea class="form-control form-add-direct post-message"
                                                              style="display: none;"></textarea>
                                            </div>
                                            <button type="button" class="btn btn-info btn-add-direct">
                                                Добавить
                                                новый
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="m-b-0 list-add-direct">

                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                    <div class="wrap-c"><i class="fas fa-inbox p-r-5"></i> Сообщения</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> Удалить все</a>
                                    </div>
                                </div>

                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        Здравствуйте, Username ! Как дела?<br>
                                        <textarea class="d-none" name="directs[]">Здравствуйте, Username ! Как дела?
</textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        Привет, Username ! Как дела?<br>
                                        <textarea class="d-none" name="directs[]">Привет, Usernam ! Как дела?
</textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        Привет, Usernam ! Привет, как дела?<br>
                                        <textarea class="d-none" name="directs[]">Привет, Username ! Привет, как дела?
</textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="ig-ac-option-item-direct">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a>
                                        Привет, Username , как дела? <textarea class="d-none"
                                                                               name="directs[]">Привет, Username , как дела?</textarea>
                                    </div>
                                </div>

                            </div>

                            
                            
                            

                            
                            
                            
                            
                            

                            
                            
                            
                            
                            

                            
                            
                            
                            
                            

                            
                            
                            
                            
                            

                            
                            
                            

                            

                            
                            
                            
                            
                            
                            

                            
                            
                            
                            
                            
                            
                            
                            
                            
                            

                            

                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            


                            
                            
                            
                            
                            
                            
                            
                            
                            

                        </div>
                        <div class="tab-pane fade" id="v-pills-repost" role="tabpanel"
                             aria-labelledby="v-pills-repost-tab">

                            <div class="tab-pane fade active show" id="profile" role="tabpanel"
                                 aria-labelledby="profile-tab">
                                <div class="form-group">
                                    <textarea class="form-control form-add-tag-list"></textarea>
                                </div>
                                <button type="button" class="btn btn-info btn-add-tag-list">
                                    Добавить
                                </button>
                            </div>

                            <div class="m-b-0 list-add-tag">

                                <div class="ig-ac-option-title text-info fs-18 m-b-25 wrap-m">
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> Теги</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> Удалить все</a>
                                    </div>
                                </div>

                                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> <?php echo e($tag); ?> <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/<?php echo e($tag); ?>" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="<?php echo e($tag); ?>">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-search-tag" role="tabpanel"
                             aria-labelledby="v-pills-search-tab">

                            <div class="col-md-12">
                                <div class="tab-content" id="myTabContent">

                                    <div class="row">
                                        <div class="card col-12 ml-3">
                                            <div
                                                    class="card-header"><?php echo e(__('Лайки на записи по хэштегам')); ?></div>

                                            <div class="card-body">
                                                <form method="POST"
                                                      action="<?php echo e(route('likeAllSrchRdsByTags')); ?>">
                                                    <?php echo csrf_field(); ?>

                                                    <div class="form-group row" id="addFriendByTag">
                                                        <label for="number-requests"
                                                               class="col-4 col-form-label">Количество
                                                            лайков (макс.200)</label>
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
                                                                       placeholder="тег, тег...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="d-flex-column">
                                                            Дата начала поиска
                                                            <input type="date" class="form-control searchStart"
                                                                   id="searchStart" name="searchStart"
                                                                   placeholder="Дата" required>
                                                        </div>

                                                        <div style="margin: 2px;"></div>

                                                        <div class="d-flex-column">
                                                            Дата конца поиска
                                                            <input type="date" class="form-control searchEnd"
                                                                   id="searchEnd" name="searchEnd" placeholder="Дата"
                                                                   required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <button type="submit"
                                                                    class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
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
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> Теги</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> Удалить все</a>
                                    </div>
                                </div>

                                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> <?php echo e($tag); ?> <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/<?php echo e($tag); ?>" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="<?php echo e($tag); ?>">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-tag" role="tabpanel"
                             aria-labelledby="v-pills-tag-tab">

                            <div class="col-md-12">
                                <div class="tab-content" id="myTabContent">

                                    <div class="row">
                                        <div class="card col-12 ml-3">
                                            <div class="card-header"><?php echo e(__('Одобрение заявок в друзья по хештегам')); ?></div>

                                            <div class="card-body">
                                                <form method="POST" action="<?php echo e(route('addToFriend')); ?>">
                                                    <?php echo csrf_field(); ?>

                                                    <div class="form-group row" id="addFriendByTag">
                                                        <label for="number-requests"
                                                               class="col-4 col-form-label">Количество друзей</label>
                                                        <div class="col-8">
                                                            <input class="form-control" type="number"
                                                                   name="requests" value="0" min="0"
                                                                   max="1000" id="number-requests">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-4 col-form-label"
                                                               for="inlineFormInputGroupTags">Теги</label>
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
                                                                    class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
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
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> Теги</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> Удалить все</a>
                                    </div>
                                </div>

                                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> <?php echo e($tag); ?> <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/<?php echo e($tag); ?>" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="<?php echo e($tag); ?>">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-location" role="tabpanel"
                             aria-labelledby="v-pills-location-tab">

                            <div class="row">

                                <div class="card ml-3">
                                    <div
                                            class="card-header"><?php echo e(__('Поставить лайк на комментарии под фото')); ?></div>


                                    <?php if($captcha['captcha_photo']): ?>
                                        <div class="card" style="width: 18rem;">
                                            <img src="<?php echo e($captcha['captcha_photo']['captcha_img']); ?>"
                                                 class="card-img-top"
                                                 alt="...">
                                            <div class="card-body">
                                                <h5 class="card-title">Введите текст с картинки</h5>
                                                <form method="POST" action="<?php echo e(route('photosLike')); ?>">
                                                    <?php echo csrf_field(); ?>

                                                    <input type="hidden" name="captcha_sid"
                                                           value="<?php echo e($captcha['captcha_photo']['captcha_sid']); ?>">
                                                    <input type="hidden" name="last_comments"
                                                           value="<?php echo e($captcha['captcha_photo']['post_comments_position']); ?>">
                                                    <input type="hidden" name="last_comment"
                                                           value="<?php echo e($captcha['captcha_photo']['post_comment_position']); ?>">
                                                    <input type="text" name="captcha_key"
                                                           class="form-control">
                                                    <h5>ID/короткое имя профиля</h5>
                                                    <input type="text" class="form-control"
                                                           name="owner_id"
                                                           placeholder="">
                                                    <br>
                                                    <button type="submit"
                                                            class="btn btn-success form-control">Начать
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('photosLike')); ?>">
                                            <?php echo csrf_field(); ?>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="record_qty">
                                                    Кол-во постов для обхода</label>
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
                                                    К-во комментариев у каждой записи</label>
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
                                                    ID/короткое имя профиля</label>
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
                                                            class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card ml-3">
                                    <div
                                            class="card-header"><?php echo e(__('Поставить лайки на комментарии под постами')); ?></div>

                                    <?php if($captcha['captcha_post']): ?>
                                        <div class="card" style="width: 18rem;">
                                            <img src="<?php echo e($captcha['captcha_post']['captcha_img']); ?>"
                                                 class="card-img-top"
                                                 alt="...">
                                            <div class="card-body">
                                                <h5 class="card-title">Введите текст с картинки</h5>
                                                <form method="POST" action="<?php echo e(route('postsLike')); ?>">
                                                    <?php echo csrf_field(); ?>

                                                    <input type="hidden" name="captcha_sid"
                                                           value="<?php echo e($captcha['captcha_post']['captcha_sid']); ?>">
                                                    <input type="hidden" name="last_comments"
                                                           value="<?php echo e($captcha['captcha_post']['post_comments_position']); ?>">
                                                    <input type="hidden" name="last_comment"
                                                           value="<?php echo e($captcha['captcha_post']['post_comment_position']); ?>">
                                                    <input type="text" name="captcha_key"
                                                           class="form-control">
                                                    <h6>ID/короткое имя профиля</h6>
                                                    <input type="text" class="form-control"
                                                           name="owner_id"
                                                           placeholder="">
                                                    <br>
                                                    <button type="submit"
                                                            class="btn btn-success form-control">Отправить
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('postsLike')); ?>">
                                            <?php echo csrf_field(); ?>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="record_qty">
                                                    Кол-во лайков на комм-и под каждым постом</label>
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
                                                    К-во комментариев у каждой записи больше</label>
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
                                                    ID/короткое имя профиля</label>
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
                                                            class="btn btn-primary"><?php echo e(__('Отправить')); ?></button>
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
                                            class="card-header"><?php echo e(__('Поставить лайки своим подписчикам и друзьям')); ?></div>


                                    
                                    <?php if($captcha['captcha_post'] && session('com_post_like_captcha')): ?>
                                        <div class="card" style="width: 18rem;">
                                            <img src="<?php echo e($captcha['captcha_post']['captcha_img']); ?>"
                                                 class="card-img-top"
                                                 alt="...">
                                            <div class="card-body">
                                                <h5 class="card-title">Введите текст с картинки</h5>
                                                <form method="POST" action="<?php echo e(route('likeAllFriends')); ?>">
                                                    <?php echo csrf_field(); ?>

                                                    <input type="hidden" name="captcha_sid"
                                                           value="<?php echo e($captcha['captcha_post']['captcha_sid']); ?>">
                                                    <input type="hidden" name="last_comments"
                                                           value="<?php echo e($captcha['captcha_post']['post_comments_position']); ?>">
                                                    <input type="hidden" name="last_comment"
                                                           value="<?php echo e($captcha['captcha_post']['post_comment_position']); ?>">
                                                    <input type="text" name="captcha_key"
                                                           class="form-control">
                                                    <br>
                                                    <button type="submit"
                                                            class="btn btn-success form-control">Начать
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('likeAllFriends')); ?>">
                                            <?php echo csrf_field(); ?>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="inlineFormInputGroupTags">
                                                    К-во лайков каждому пользователю</label>
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
                                                    Всего лайков</label>
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
                                                            class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
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
                                            class="card-header"><?php echo e(__('Поставить лайки пользователям, которые лайкали или комментировали публикацию')); ?></div>


                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('likeAllConcurrents')); ?>">
                                            <?php echo csrf_field(); ?>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label"
                                                       for="url">
                                                    Ссылка на запись</label>
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
                                                    Количество записей для обхода у каждого пользователя(от новых к старым)</label>
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
                                                            class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
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
                                            class="card-header"><?php echo e(__('Лайки подписчикам группы')); ?></div>

                                    <div class="card-body">
                                        <form method="POST" action="<?php echo e(route('likeAllMembers')); ?>">
                                            <?php echo csrf_field(); ?>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label" for="inlineFormInputGroupTags">
                                                    Url-адрес сообщества</label>
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
                                                           type="checkbox"> Фото обязательно у подписчика
                                                </label>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label" for="auto_like_min_followers">
                                                    Минимальное к-во подписчиков у каждого пользователя</label>
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
                                                    К-во заявок у каждого пользователя для обхода</label>
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
                                                            class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
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
                                                        class="card-header"><?php echo e(__('Удалить друзей по хэштегам')); ?></div>

                                                <div class="card-body">
                                                    <form method="POST"
                                                          action="<?php echo e(route('deleteFromFriends')); ?>">
                                                        <?php echo csrf_field(); ?>

                                                        <div class="form-group row" id="deleteFriendByTag">
                                                            <label for="number-requests"
                                                                   class="col-4 col-form-label">К-во
                                                                удалений</label>
                                                            <div class="col-8">
                                                                <input class="form-control" type="number"
                                                                       name="" value="0" min="0" max="1000"
                                                                       id="number-requests">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-4 col-form-label"
                                                                   for="inlineFormInputGroupTags">Тэги</label>
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
                                                                           placeholder="тэг, тэг...">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-sm-8">
                                                                <button type="submit"
                                                                        class="btn btn-primary"><?php echo e(__('Начать')); ?></button>
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
                                    <div class="wrap-c"><i class="fas fa-hashtag p-r-5"></i> Теги</div>
                                    <div class="wrap-c">
                                        <a href="javascript:void(0);"
                                           class="btn btn-label-danger btn-sm remove-all"><i
                                                    class="far fa-trash-alt"></i> Удалить все</a>
                                    </div>
                                </div>

                                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="ig-ac-option-item-tag">
                                        <a href="javascript:void(0);" class="remove"><i
                                                    class="fas fa-times-circle text-danger"></i></a> <?php echo e($tag); ?> <a
                                                class="name"
                                                href="https://www.instagram.com/explore/tags/<?php echo e($tag); ?>" target="_blank">

                                            <i class="fas fa-arrow-right" size="2x"></i>
                                        </a>
                                        <input type="hidden" name="tags[]" value="<?php echo e($tag); ?>">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="v-pills-auto-stop" role="tabpanel"
                             aria-labelledby="v-pills-auto-stop-tab">

                            <div class="row">

                                <div class="col-md-4 col-sm-6 m-b-25">
                                    <div class="ig-ac-option wrap-m">
                                        <div class="info wrap-c">
                                            <span class="p-r-5">Счетчик лайков </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Когда это количество лайков будет сделано, ваша активность будет остановлена автоматически. Установите на ноль, чтобы отключить ограничение."></i>
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
                                            <span class="p-r-5">Счетчик комментариев </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Когда это количество комментариев будет сделано, ваша деятельность будет остановлена автоматически. Установите на ноль, чтобы отключить ограничение."></i>
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
                                            <span class="p-r-5">Счетчик просмотра историй </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Когда количество просмотренных историй будет закончено, ваша деятельность будет автоматически остановлена. Установите на ноль, чтобы отключить ограничение."></i>
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
                                            <span class="p-r-5">Счетчик подписок </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Когда количество подписок будет закончено, ваша деятельность будет автоматически остановлена. Установите на ноль, чтобы отключить ограничение."></i>
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
                                            <span class="p-r-5">Счетчик отписок </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Когда количество отписок будет закончено, ваша деятельность будет автоматически остановлена. Установите на ноль, чтобы отключить ограничение."></i>
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
                                            <span class="p-r-5">Счетчик сообщений </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Когда количество отправок сообщений будет закончено, ваша деятельность будет автоматически остановлена. Установите на ноль, чтобы отключить ограничение."></i>
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
                                            <span class="p-r-5">Счетчик репостов </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Когда количество репостов будет закончено, ваша деятельность будет автоматически остановлена. Установите на ноль, чтобы отключить ограничение."></i>
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
                                            <span class="p-r-5">Таймер </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Формат: HH:MM. Например, если вы установите значение <b>01:00</b>, то ваша деятельность автоматически прекратится через час. Если вы оставите поле пустым, активность будет работать бесконечно или пока ограничения Instagram для вашей учетной записи не будут достигнуты."></i>
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
                                            <span class="p-r-5">Стоп, если нет активности </span>
                                            <i class="fa fa-question-circle" data-toggle="tooltip-custom"
                                               data-trigger="hover" data-placement="top" data-html="true"
                                               title=""
                                               data-original-title="Автоматически останавливается через некоторое время без какой-либо активности."></i>
                                        </div>
                                        <div class="action wrap-c">
                                            <select class="form-control action-save"
                                                    name="stops[no_activity]">
                                                <option selected="true" value="no">Никогда</option>
                                                <option value="1h">1 час</option>
                                                <option value="3h">3 часа</option>
                                                <option value="12h">За 12 часов</option>
                                                <option value="1d">За 1 день</option>
                                                <option value="3d">За 3 дня</option>
                                                <option value="1w">За 1 неделю</option>
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
                                Расписание</h5>
                            <button type="button" class="close" data-dismiss="modal">×</button>
                        </div>
                        <div class="modal-body">

                            <p class="fs-12">Расписание позволяет вам настроить уникальное расписание по
                                времени и
                                дню для запуска вашей системной активности. Вы можете вручную выбрать
                                отдельные
                                часы, когда ваша активность должна быть активной, или вы можете использовать
                                следующие предустановки. </p>
                            <div class="text-center day-schedule-auto">
                                <a data-type="none" href="javascript:void(0);">Примечание</a>
                                <a data-type="all" href="javascript:void(0);">Все</a>
                                <a data-type="day" href="javascript:void(0);">Дневное время</a>
                                <a data-type="night" href="javascript:void(0);">Ночное время</a>
                                <br>
                                <a data-type="odd" href="javascript:void(0);">Нечетный</a>
                                <a data-type="even" href="javascript:void(0);">Четный</a>
                                <a data-type="alternate1" href="javascript:void(0);">Альтернативный 1</a>
                                <a data-type="alternate2" href="javascript:void(0);">Альтернативный 2</a>

                                <div class="type">
                                    <div class="item">
                                        <span class="box active"></span> Активность началась
                                    </div>
                                    <div class="item">
                                        <span class="box"></span> Активность на паузе
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
                                Сохранить
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть
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
<?php /**PATH /var/www/www-root/data/www/vk.ipostx.ru/resources/views/dashboard.blade.php ENDPATH**/ ?>