function setCookie(name, value, options = {}) {

    options = {
        path: '/',
        // при необходимости добавьте другие значения по умолчанию
        ...options
    };

    if (options.expires instanceof Date) {
        options.expires = options.expires.toUTCString();
    }

    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

    for (let optionKey in options) {
        updatedCookie += "; " + optionKey;
        let optionValue = options[optionKey];
        if (optionValue !== true) {
            updatedCookie += "=" + optionValue;
        }
    }

    document.cookie = updatedCookie;
}

document.getElementById('getKeyButton').addEventListener('click', (e) => {
    // const data = $('#authDataForm').serializeArray();
    // let scope = '';
    // const baseUrl = 'http://oauth.vk.com/authorize?client_id=XXXXXX&display=popup&redirect_uri=http://'+document.domain+'/vk_login.php&response_type=code';
    //
    // for(let i=4; i<data.length; i++) {
    //     scope+=(data[i].name.split('-')[1] + ',');
    //     i+2 > data.length ? scope = scope.slice(0,-1) : null;
    // }

    // let url = `http://oauth.vk.com/authorize?client_id=${data[0].value}&scope=${scope}&display=popup&redirect_uri=http://`+document.domain+'/authorize&response_type=code';
    // let url = `http://`+document.domain+'/authorize';

    // setCookie('vk_app_id', data[0].value);
    // setCookie('vk_app_secret', data[1].value);
    // setCookie('vk_token_name', data[2].value);
    // setCookie('vk_token_type', data[3].value);
    // setCookie('vk_app_scope', scope);

    let url = 'http://oauth.vk.com/authorize?client_id=7762245&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=friends,photos,audio,video,status,wall,groups,offline,stats,email&response_type=token&v=5.130'
    e.preventDefault();

    document.location.href = url;

})


