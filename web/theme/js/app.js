var app = {
    user_name: null,
    refresh: null,
    current_latest_message: null,
    chat_log: null,
    message: null,
    user_id: null,
    init: function () {
        this.chat_log = $("#chat-log");
        this.message = $("#message-input");
        this.user_id = $("#message-user-id").val();
        this.user_name = $("#new-user-name");
        this.current_latest_message = $(".message").last().data('message-id');
        // message send
        $("#message-form").on("submit", function (e) {
            e.preventDefault();
            app.saveMessage();
            app.message.val("");

            return false;
        });
        // name change
        $("#name-change-form").on('submit', function () {
            $.post("/set-user-name", {user_id: app.user_id, name: app.user_name.val()}, function (data) {
                if (data.status == 'error') {
                    alert("Ooops!");
                }
            }, 'json');
            $('#user-rename').modal('toggle');
            return false;
        });

        // window resize
        this.chatResize();
        this.chatScroll();
        $(window).on('resize', app.chatResize);

        $('#upload').on("change", function () {
            $("#input-forms").hide();
            $("#loading").show();
            var formData = new FormData($("#form-upload")[0]);
            for (var i = 0, len = document.getElementById('upload').files.length; i < len; i++) {
                formData.append("upload" + (i + 1), document.getElementById('upload').files[i]);
            }
            formData.append("user_id", app.user_id);

            //send formData to server-side
            $.ajax({
                url: "/upload-images",
                type: 'post',
                data: formData,
                dataType: 'json',
                async: true,
                processData: false,  // tell jQuery not to process the data
                contentType: false,   // tell jQuery not to set contentType
                error: function (request) {
                    alert("Ooops!");
                },
                success: function (json) {
                    $("#input-forms").show();
                    $("#loading").hide();
                }
            });
        });

        // chat refresh
        this.refresh = setInterval(function () {
            app.showMessages();
        }, 500);
        this.addEvents();
    },
    addEvents: function () {
        // message delete
        $(".glyphicon-remove").on('click', function () {
            $.post("/delete-message", {
                user_id: app.user_id,
                message_id: $(this).parents(".message").data("message-id")
            }, function (data) {
                if (data.status == 'error') {
                    alert("Ooops!");
                }
            }, 'json');

            return false;
        });
        // message like
        $(".glyphicon-heart").on('click', function () {
            $.post("/like-message", {user_id: app.user_id, message_id: $(this).parents(".message").data("message-id")});
            return false;
        });
    },
    chatResize: function () {
        app.chat_log.css('height', ($(window).height() - 50) + "px");
        app.message.focus();
    },
    chatScroll: function () {
        app.chat_log.scrollTop(app.chat_log.prop('scrollHeight') - app.chat_log.height());
    },
    showMessages: function () {
        $.post("/get-chat-log", function (data) {
            app.chat_log.replaceWith(data);
            app.chat_log = $("#chat-log");
            app.addEvents();
            app.chatResize();
            app.current_latest_message = $(".message").last().data('message-id');
            app.chatScroll();

        });

        return this;
    },
    saveMessage: function (message) {
        $.post("/save-message", {user_id: app.user_id, message: app.message.val()}, function (data) {
            if (data.status == 'error') {
                alert("Ooops!");
            }
        }, 'json');

        return this;
    }
};

$(function () {
    app.init();
});