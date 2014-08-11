/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function () {
    'use strict';

    var bodyWidth = $('body').width();
    var bodyHeight = $('body').height();

    /* browse */
    audiojs.events.ready(function() {
        var as = audiojs.createAll({
            imageLocation: baseUrl + '/packages/sule/kotakin/audiojs/player-graphics.gif',
            swfLocation: baseUrl + '/packages/sule/kotakin/audiojs/audiojs.swf'
        });
    });

    videojs.options.flash.swf = baseUrl + '/packages/sule/kotakin/videojs/video-js.swf';

    var viewWidth = bodyWidth;
    if (bodyWidth > 800) {
        viewWidth = 800;

        $('.view-modal .modal-dialog').css({
            width: viewWidth,
            marginLeft: -((viewWidth / 2) - 15)
        });
    } else {
        $('.view-modal .modal-dialog').css({
            width: viewWidth
        });
    }
    
    $('.view-modal .modal-body').height(bodyHeight);

    $('.img-swipebox').swipebox({
        useCSS : true, // false will force the use of jQuery for animations
        hideBarsDelay : 3000, // 0 to always show caption and action bar
        videoMaxWidth : 1140, // videos max width
        beforeOpen: function(){}, // called before opening
        afterClose: function(){} // called after closing
    });

    $('#new-folder-modal').on('shown.bs.modal', function () {
        $('input[name="name"]', this).focus();
        $('form', this).append($('<input type="hidden" name="_new_folder" value="1" />'));
    });
    $('.rename-modal').on('shown.bs.modal', function () {
        $('input[name="name"]', this).focus();
        $('input[name="destination"]', this).focus();
    });

    $('.a-close-file-modal').click(function () {
        var modal = $('#new-file-modal');

        $('.fileinput-button', modal).addClass('hide');
        $('button', modal).prop('disabled', true);
        $(this).text($(this).data('process-txt'));

        var files = $('#uploaded-files').val();
        if (files !== '') {
            $.ajax({
                dataType: 'json',
                type: 'POST',
                data: {
                    files: files,
                    _token: csrfToken
                },
                url: baseUrl + '/' + userSlug + '/notify',
                error: function() {
                    window.location.reload();
                },
                success: function(response) {
                    window.location.reload();
                }
            });
        } else {
            $('.fileinput-button', modal).removeClass('hide');
            $('button', modal).prop('disabled', false);
            $(this).text($(this).data('default-txt'));

            window.location.reload();
        }

        files = null;
        modal = null;
    });

    $('.view-modal').on('shown.bs.modal', function () {
        var iframe = $('.view-iframe', this);
        var slug = $('.view-item-slug', this).val();
        
        if (iframe.data('opened') === 0) {
            iframe.attr('src', baseUrl + '/' + userSlug + '/file/' + slug + '?dl=1&source=1');

            iframe.data('opened', 1);
        }

        slug = null;
        iframe = null;
    });

    /* file upload */
    var options = {
        autoUpload: true,
        filesContainer: '#new-file-modal .files', 
        progress: function (e, data) {
            var progress = parseInt(data.loaded / (data.total * 1.1) * 100, 10);
            var bar = data.context.children().children(".progress");
            $(bar).css("width", progress + "%");
        }
    };
    if (allowedFileExts !== '') {
        var fileTypesRegexp = new RegExp('(\.|\/)(' + allowedFileExts + ')$', 'i');
        options.acceptFileTypes = fileTypesRegexp;
        fileTypesRegexp = null;
    }
    $('#fileupload').fileupload(options);

    $('#fileupload').bind('fileuploadadd', function () {
        $('.a-close-file-modal').addClass('hidden');
        $('.choose-files-intro').addClass('hidden');
    }).bind('fileuploadstart', function () {
        $('.a-close-file-modal').addClass('hidden');
        $('.choose-files-intro').addClass('hidden');
    }).bind('fileuploadstop', function () {
        $('.a-close-file-modal').removeClass('hidden');
    }).bind('fileuploaddestroyed', function () {
        $('.a-close-file-modal').removeClass('hidden');
    }).bind('fileuploaddone', function(e, data) {
        var files = $('#uploaded-files').val().split(',');
        $.each(data.result.files, function(index, item){
            files.push(item.id)
        });
        $('#uploaded-files').val(files.join(','));
        files = null;
    }).bind('fileuploadfail', function(e, data) {
        if (typeof data._response.jqXHR != 'undefined') {
            if (data._response.jqXHR.status == 403) {
                $('#account-modal').modal({
                    keyboard: false
                }).on('hidden.bs.modal', function () {
                    window.location.reload();
                })
            }
        }
    });
});