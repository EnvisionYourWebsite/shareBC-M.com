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

    var numberKeyCodes = [8, 9, 12, 13, 16, 17, 18, 37, 39, 46, 82, 91, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 144, 188, 190, 224];
    var bodyWidth = $(window).width();
    var bodyHeight = $(window).height();
    var totalCheckboxes = $('.collection-item').length;

    $('.pop').popover();

    /* browse */
    $('.valid-until').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    audiojs.events.ready(function() {
        var as = audiojs.createAll({
            imageLocation: baseUrl + '/packages/sule/kotakin/audiojs/player-graphics.gif',
            swfLocation: baseUrl + '/packages/sule/kotakin/audiojs/audiojs.swf'
        });
    });

    videojs.options.flash.swf = baseUrl + '/packages/sule/kotakin/videojs/video-js.swf';

    $('.collection-action').change(function(){
        $('#collection-action').val(this.value);

        if (this.value == 'email') {
            var row = {};
            var item = {};
            var items = [];

            $('.collection-item:checked').each(function(){
                items.push(this.value);
                row = $(this).parent().parent();
                item = $('#new-email-modal .attachment-item-example').clone();
                item.removeClass('attachment-item-example hide');
                item.addClass('attachment-item');
                item.find('img').prop('src', row.find('.col-icon img').prop('src'));
                item.find('span').text(row.find('.col-name a').text());
                item.appendTo($('#new-email-modal .attachments'));
            });

            if (items.length > 0) {
                $('#email-items').val(items.join(','));
            }

            items = null;
            item = null;
            row = null;

            $('#new-email-modal').modal().on('hide.bs.modal', function () {
                $('#collection-action').val('');
                $('#new-email-modal .attachment-item').remove();
            });
        }

        if (this.value == 'delete') {
            $('#selection-delete-modal').modal().on('hide.bs.modal', function () {
                $('#collection-action').val('');
            });
        }

        this.selectedIndex = 0;
    });

    $('#cancel-delete-selection').click(function(e){
        e.preventDefault();
        $('#selection-delete-modal').modal('hide');
    });

    $('#ok-delete-selection').click(function(e){
        e.preventDefault();
        
        var items = [];

        $('.collection-item:checked').each(function(){
            items.push(this.value);
        });

        if (items.length > 0) {
            $('#selected-items').val(items.join(','));
            $('#collection-frm').submit();
        }

        items = null;
    });

    $('.collection-checks').change(function(){
        if (this.checked) {
            $('.collection-actions').removeClass('hide')
            $('.collection-checks').prop('indeterminate', false);
            $('.collection-checks').prop('checked', true);
            $('.collection-item').prop('checked', true);
        } else {
            $('.collection-actions').addClass('hide')
            $('.collection-checks').prop('indeterminate', false);
            $('.collection-checks').prop('checked', false);
            $('.collection-item').prop('checked', false);
        }
    });

    $('.collection-item').change(function(){
        if ($('.collection-item:checked').length == totalCheckboxes) {
            $('.collection-checks').prop('indeterminate', false);
            $('.collection-checks').prop('checked', true);
        } else {
            $('.collection-checks').prop('indeterminate', true);
            $('.collection-checks').prop('checked', false);
        }

        if ($('.collection-item:checked').length == 0) {
            $('.collection-actions').addClass('hide')
            $('.collection-checks').prop('indeterminate', false);
            $('.collection-checks').prop('checked', false);
        } else {
            $('.collection-actions').removeClass('hide')
        }
    });

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

    $('#new-file-modal').on('hide.bs.modal', function () {
        window.location.reload();
    });

    $('.view-modal').on('shown.bs.modal', function () {
        var iframe = $('.view-iframe', this);
        var slug = $('.view-item-slug', this).val();
        
        if (iframe.data('opened') == 0) {
            iframe.attr('src', baseUrl + '/admin/file/' + slug + '?dl=1&source=1');

            iframe.data('opened', 1);
        }

        slug = null;
        iframe = null;
    });

    $('.move-paths').typeahead({
        local: availablePaths,
        items: 5,
        updater: function(item) {
            var container = this.$element.parent().parent();
            var currentPath = container.find('input[name="path"]').val();
            var allowed = true;

            if ('/' + currentPath + '/' == item) {
                container.find('div.alert').removeClass('hidden');
                allowed = false;
            } else {
                container.find('div.alert').addClass('hidden');
            }

            currentPath = null;
            container = null;

            if (allowed) {
                return item;
            } else {
                return '';
            }
        }
    });

    $('.dash-remove-share-selections').click(function(e){
        e.preventDefault();
        $('.share-user-selections', '#item-share-' + $(this).data('id'))[0].selectedIndex = -1;
    });

    $('.dash-reset-share-selections').click(function(e){
        e.preventDefault();
        $('form', '#item-share-' + $(this).data('id'))[0].reset();
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

    /* Preferences */
    $('#mail-driver').change(function(){
        if (this.value == 'smtp') {
            $('#email-credentials').removeClass('hidden');
        } else {
            $('#email-credentials').addClass('hidden');
        }

        if (this.value == 'sendmail') {
            $('#email-sendmail').removeClass('hidden');
        } else {
            $('#email-sendmail').addClass('hidden');
        }
    });

    $('#image-driver').change(function(){
        if (this.value == 'im') {
            $('#imagemagick-path').removeClass('hidden');
        } else {
            $('#imagemagick-path').addClass('hidden');
        }
    });

    $('#remove-email-recipients').click(function(e){
        e.preventDefault();
        $('#email-recipient')[0].selectedIndex = -1;
    });
});