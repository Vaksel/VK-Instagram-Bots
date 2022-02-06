function sendRequest(url,objectForSend) {
    $.ajax({
        type:'POST',
        url:url,
        data: {
            data:objectForSend
        },
        success:function(res) {
            console.log(res);
        }
    });
}

document.getElementById('addToFriendsBtn').addEventListener('click', sendRequest('/addToFriend', {
    tags:document.getElementById('addToFriendsTags').value,
}))

document.getElementById('deleteFromFriendsBtn').addEventListener('click', sendRequest('/addToFriend', {
    tags:document.getElementById('deleteFromFriendsTags').value,
}))
