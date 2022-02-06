let tagStickers = document.getElementsByClassName('ig-ac-option-item-tag');

    for (let i=0; i<tagStickers.length; i++)
    {
        tagStickers[i].addEventListener('click', function(event) {
            // let id = event.target.dataset.toggleId;
            // if (!id) return;
            //
            // let elem = document.getElementById(id);

            console.log(event.target);

            let addFriendForm = document.getElementById('v-pills-tag');
            let deleteFriendForm = document.getElementById('v-pills-blacklist-tab');
            let searchPostsForm = document.getElementById('v-pills-search-tag');

            let tagVal = this.textContent.trim();

            console.log(tagVal);

            if(addFriendForm.classList.contains('active')) {

                let oldText = $('#addToFriendInp input').val();

                if(oldText.length > 0) {

                    let newText = oldText + ',' + tagVal;

                    $('#addToFriendInp input').val(newText);
                } else {

                    $('#addToFriendInp input').val(tagVal);
                }
            } else if(deleteFriendForm.classList.contains('active')){

                let oldText = $('#deleteFromFriendInp input').val();

                if(oldText.length > 0) {

                    let newText = oldText + ',' + tagVal;

                    $('#deleteFromFriendInp input').val(newText);
                } else {

                    $('#deleteFromFriendInp input').val(tagVal);
                }
            } else if(searchPostsForm.classList.contains('active')) {
                let oldText = $('#deleteFromFriendInp input').val();

                if(oldText.length > 0) {

                    let newText = oldText + ',' + tagVal;

                    $('#inlineFormInputSearchTags').val(newText);
                } else {

                    $('#inlineFormInputSearchTags').val(tagVal);
                }
            }
            // elem.hidden = !elem.hidden;
        });
    }

function writeValueToSearchField() {
    console.log(event.target);

    let addFriendForm = document.getElementById('v-pills-tag');
    let deleteFriendForm = document.getElementById('v-pills-blacklist-tab');
    let searchPostsForm = document.getElementById('v-pills-search-tag');

    let tagVal = event.target.textContent.trim();

    console.log(tagVal);

    if(addFriendForm.classList.contains('active')) {

        let oldText = $('#addToFriendInp input').val();

        if(oldText.length > 0) {

            let newText = oldText + ',' + tagVal;

            $('#addToFriendInp input').val(newText);
        } else {

            $('#addToFriendInp input').val(tagVal);
        }
    } else if(deleteFriendForm.classList.contains('active')){

        let oldText = $('#deleteFromFriendInp input').val();

        if(oldText.length > 0) {

            let newText = oldText + ',' + tagVal;

            $('#deleteFromFriendInp input').val(newText);
        } else {

            $('#deleteFromFriendInp input').val(tagVal);
        }
    } else if(searchPostsForm.classList.contains('active')) {
        let oldText = $('#deleteFromFriendInp input').val();

        if(oldText.length > 0) {

            let newText = oldText + ',' + tagVal;

            $('#inlineFormInputSearchTags').val(newText);
        } else {

            $('#inlineFormInputSearchTags').val(tagVal);
        }
    }
}

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

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function deleteCookie(name) {
    setCookie(name, "", {
        'max-age': -1
    })
}

    function getTickersFromCookie() {
        getCookie('vt_tags') ? tickersArr = getCookie('vk_tags').split(',') : tickersArr = '';
        return tickersArr;
    }

    function pasteHashtagTickers() {
        let tickersText = getTickersFromCookie();

        for(let i=0; i<tickersText.length;i++)
        {
            let html = createHashtagTicker(tickersText[i]);
            $(".list-add-tag").append(html);
        }
    }

    function createHashtagTicker(tickerName) {
        return '<div class="ig-ac-option-item-tag" onclick="writeValueToSearchField(event)"><a href="javascript:void(0);" class="remove">' +
        '<i class="fas fa-times-circle text-danger"></i></a>' + tickerName +' <a class="name" href="https://vk.com/' +
        tickerName + '" target="_blank">' + '<i class="fas fa-arrow-right" size="2x"></i>' + '</a><input type="hidden" name="blacklist_tags[]" value="' + tickerName + '"></div>'
    }

    pasteHashtagTickers();

    function getDate(event) {
        console.log(event.target);
}
$(function(){
    $('#searchStart').daterangepicker({
        singleDatePicker: true,
    });
});
$(function(){
    $('#searchEnd').daterangepicker({
        singleDatePicker: true,
    });
});

// document.querySelector('.searchStart').addEventListener('click', getDate(event));
//
// document.querySelector('.endStart').addEventListener('click', getDate(event));

// $('.searchStart').on('click', function (){
//     var myDate = $(".searchStart").data("DateTimePicker").date().toDate();
//     console.log(myDate);
//     //alert(myDate);
// });
//
// $('.endStart').on('click', function (){
//     var myDate = $(".endStart").data("DateTimePicker").date().toDate();
//     console.log(myDate);
//     //alert(myDate);
// });
