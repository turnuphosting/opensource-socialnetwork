//<script>
/**
 * 	Open Source Social Network
 *
 * @package   (softlab24.com).ossn
 * @author    OSSN Core Team <info@softlab24.com>
 * @copyright 2014-2017 SOFTLAB24 LIMITED
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
Ossn.RegisterStartupFunction(function() {
    $(document).ready(function() {
        /*
         * Friends List Open
         */

        $('.friends-tab').on('click', function(e) {
            var $friends_list;
            $friends_list = $('.friends-list');
            if ($friends_list.is(":not(:visible)")) {
                $friends_list.show();
            } else {
                $friends_list.hide();
            }
        });

        /*
         * Reboot chat after every 5 seconds
         */
        setInterval(function() {
            Ossn.ChatBoot()
        }, 5000);

        /*
         * Process sidebar chat height;
         */
        var sidebarheight = $(window).height();
        sidebarheight = sidebarheight - 45;
        $(".ossn-chat-windows-long").find('.inner').height(sidebarheight + 'px');

    });
});
Ossn.ChatOpenTab = function($user) {
    var $tabitem = $('#ftab-i' + $user);
    if ($tabitem.find('.tab-container').is(":not(:visible)")) {
        Ossn.ChatMarkViewed($user);
        $tabitem.find('.tab-container').show();
        $tabitem.css('width', '256px');
        $tabitem.find('form').show();
        $tabitem.find('input[type="text"]').show();
        $('#ftab' + $user).removeClass('ossn-chat-tab-active');
        $tabitem.find('.ossn-chat-new-message').hide();
        $tabitem.find('.ossn-chat-new-message').empty();
        Ossn.ChatScrollMove($user);
    }
};
Ossn.ChatCloseTab = function($user) {
    var $tabitem = $('#ftab-i' + $user);
    $tabitem.find('.tab-container').hide();
    $tabitem.find('form').hide();
    $tabitem.css('width', '200px');
    $tabitem.find('input[type="text"]').hide();
    $tabitem.removeClass('ossn-chat-tab-active');
    // close emoji container if still open because no emoji has been selected
    $('#master-moji-anchor').val('');
    $('#master-moji .emojii-container-main').hide();
};
Ossn.ChatTerminateTab = function($user) {
    return Ossn.CloseChat($user);
};
Ossn.ChatBoot = function() {
    $.ajax({
        url: Ossn.site_url + 'ossnchat/boot/ossn.boot.chat.js',
        dataType: "script",
        async: true,
        success: function(fetch) {
            fetch;
        }
    });
};

Ossn.ChatSendForm = function($user) {
    Ossn.ajaxRequest({
        url: Ossn.site_url + "action/ossnchat/send",
        form: '#ossn-chat-send-' + $user,

        beforeSend: function(request) {
            var $input = $('#ossn-chat-send-' + $user).find("input[type='text']");
            //chat: annoying procedure on pressing just [Enter] without any input #651
            if (!$.trim($input.val())) {
                $('#ftab-i' + $user).find('.ossn-chat-message-sending').hide();
                $('#ftab-i' + $user).find('input[name="message"]').val('');
                request.abort();
            } else {
                $('#ftab-i' + $user).find('.ossn-chat-message-sending').show();
            }
        },
        callback: function(callback) {
            if (callback['type'] == 1) {
                $('#ftab-i' + $user).find('.ossn-chat-message-sending').hide();
                $('#ftab-i' + $user).find('.data').append(callback['message']);
                $('#ftab-i' + $user).find('input[name="message"]').val('');
                Ossn.ChatScrollMove($user);
            }
        }
    });
};

Ossn.ChatnewTab = function($user) {
    Ossn.PostRequest({
        url: Ossn.site_url + "ossnchat/selectfriend?user=" + $user,
        action: false,
        callback: function(callback) {
            if ($('#ftab-i' + $user).length == 0) {
                if ($(".ossn-chat-containers").children(".friend-tab-item").size() < 4) {
                    $('.ossn-chat-containers').append(callback);
                }
            }
        }
    });
};
Ossn.ChatMarkViewed = function($fid) {
    Ossn.PostRequest({
        url: Ossn.site_url + "action/ossnchat/markread?fid=" + $fid,
        callback: function(callback) {
            return true;
        }
    });
};
Ossn.CloseChat = function($fid) {
    Ossn.PostRequest({
        url: Ossn.site_url + "action/ossnchat/close?fid=" + $fid,
        callback: function(callback) {
            if (callback == 1) {
                var $tabitem = $('#ftab-i' + $fid);
                $tabitem.remove();
            }
            if (callback == 0) {
                Ossn.MessageBox('syserror/unknown');
            }
        }
    });
};

Ossn.ChatScrollMove = function(fid) {
    var message = document.getElementById('ossn-chat-messages-data-' + fid);
    if (message) {
        message.scrollTop = message.scrollHeight;
        return message.scrollTop;
    }
};
Ossn.ChatExpand = function($username) {
    window.location = Ossn.site_url + 'messages/message/' + $username;
};
//message with user pagination
Ossn.ChatLoading = function($friend_guid) {
    $(document).ready(function() {
        $calledOnce = [];
        $('#ossn-chat-messages-data-' + $friend_guid).scroll(function() {
            if ($('#ossn-chat-messages-data-' + $friend_guid + ' .ossn-pagination').visibleInScroll().isVisible) {
                $element = $('#ossn-chat-messages-data-' + $friend_guid + ' .container-table-pagination');
                $next = $element.find('.ossn-pagination .active').next();
                var selfElement = $element;
                if ($next) {
                    $url = $next.find('a').attr('href');
                    $offset = Ossn.MessagesURLparam('offset_message_xhr_with_' + $friend_guid, $url);
                    $url = '?offset_message_xhr_with_' + $friend_guid + '=' + $offset;

                    if ($.inArray($url, $calledOnce) == -1 && $offset > 0) {
                        $calledOnce.push($url); //push to array so we don't need to call ajax request again for processed offset
                        $user_guid = $friend_guid;
                        Ossn.PostRequest({
                            url: Ossn.site_url + 'ossnchat/load' + $url + '&guid=' + $user_guid,
                            beforeSend: function() {
                                $('#ossn-chat-messages-data-' + $friend_guid).prepend('<div class="ossn-messages-with-pagination-loading"><div class="ossn-loading"></div></div>');
                            },
                            callback: function(callback) {
                                $element = $(callback); //make callback to jquery object
                                if ($element.length) {
                                    $clone = $element.find('.container-table-pagination').html();
                                    $element.find('.container-table-pagination').remove(); //remove pagination from contents as we'll replace contents of already existing pagination.
                                    $('#ossn-chat-messages-data-' + $friend_guid).prepend($element.html()); //append the new data
                                    selfElement.html($clone); //set pagination content with new pagination contents
                                    selfElement.prependTo('#ossn-chat-messages-data-' + $friend_guid); //append the pagnation back to at end
                                    $('#ossn-chat-messages-data-' + $friend_guid + ' .ossn-messages-with-pagination-loading').remove();
                                }
                                return;
                            },
                        });
                    } //if not in array **/
                }
            }
        });
    });
}