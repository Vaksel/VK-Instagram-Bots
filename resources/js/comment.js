let commentStickers = document.getElementsByClassName('ig-ac-option-item-comment');

for (let i=0; i<commentStickers.length; i++)
{
    commentStickers[i].addEventListener('click', function(event) {
        // let id = event.target.dataset.toggleId;
        // if (!id) return;
        //
        // let elem = document.getElementById(id);

        console.log(event.target);

        let commenthashtagForm = document.getElementById('v-pills-commenthashtag');
        let commentconcurrentsForm = document.getElementById('v-pills-commentconcurrents');

        let commentVal = this.querySelector("textarea").textContent.trim();

        console.log(commentVal);

        if(commenthashtagForm.classList.contains('active')) {
            console.log($('#v-pills-commenthashtag .emojionearea-editor'));

           $('#v-pills-commenthashtag .emojionearea-editor').html(commentVal);

        } else if(commentconcurrentsForm.classList.contains('active')){

            $('#v-pills-commentconcurrents .emojionearea-editor').textContent = commentVal;
        }
        // elem.hidden = !elem.hidden;
    });
}

function writeCommentValueToSearchField(event) {
        // let id = event.target.dataset.toggleId;
        // if (!id) return;
        //
        // let elem = document.getElementById(id);

        console.log(event.target);

        let commenthashtagForm = document.getElementById('v-pills-commenthashtag');
        let commentconcurrentsForm = document.getElementById('v-pills-commentconcurrents');

        let commentVal = event.target.querySelector("textarea").textContent.trim();

        console.log(commentVal);

        if(commenthashtagForm.classList.contains('active')) {
            console.log($('#v-pills-commenthashtag .emojionearea-editor'));

            $('#v-pills-commenthashtag .emojionearea-editor').html(commentVal);

        } else if(commentconcurrentsForm.classList.contains('active')){

            $('#v-pills-commentconcurrents .emojionearea-editor').textContent = commentVal;
        }
        // elem.hidden = !elem.hidden;
}
//
// function setCookie(name, value, options = {}) {
//
//     options = {
//         path: '/',
//         // при необходимости добавьте другие значения по умолчанию
//         ...options
//     };
//
//     if (options.expires instanceof Date) {
//         options.expires = options.expires.toUTCString();
//     }
//
//     let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
//
//     for (let optionKey in options) {
//         updatedCookie += "; " + optionKey;
//         let optionValue = options[optionKey];
//         if (optionValue !== true) {
//             updatedCookie += "=" + optionValue;
//         }
//     }
//
//     document.cookie = updatedCookie;
// }
//
// function getCookie(name) {
//     let matches = document.cookie.match(new RegExp(
//         "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
//     ));
//     return matches ? decodeURIComponent(matches[1]) : undefined;
// }
//
// function deleteCookie(name) {
//     setCookie(name, "", {
//         'max-age': -1
//     })
// }
//
function getCommentTickersFromCookie() {
    getCookie('vt_comments') ? tickersCommentsArr = getCookie('vt_comments').split(',') : tickersCommentsArr = '';
    return tickersCommentsArr;
}
//
function pasteCommentTickers(cookie, text = '') {
    let tickersText = text;
    if(cookie) {
        tickersText = getCommentTickersFromCookie();
    }

        let html = createCommentTicker(tickersText);
        $(".list-add-comment").append(html);
}

function createCommentTicker(tickerName) {
    return '<div class="ig-ac-option-item-comment" onclick="writeCommentValueToSearchField(event)"><a href="javascript:void(0);" class="remove">' +
        '<i class="fas fa-times-circle text-danger"></i></a>' + tickerName +' <a class="name" href="https://vk.com/' +
        tickerName + '" target="_blank">' + '<i class="fas fa-arrow-right" size="2x"></i>' + '</a><input type="hidden" name="blacklist_tags[]" value="' + tickerName + '"></div>'
}


pasteCommentTickers(true);

// function getDate(event) {
//     console.log(event.target);
// }
