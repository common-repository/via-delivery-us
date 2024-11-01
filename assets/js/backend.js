(function($) {
    'use strict';

    $.fn.serializeObject = $.fn.serializeObject || function()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $(document).ready(function(){
        var widget = new ViaDeliveryWidget();
    });

    function ViaDeliveryWidget()
    {
        if (this === window) {
            return new ViaDeliveryWidget()
        }

        this.init();
    }

    ViaDeliveryWidget.prototype.init = function() {
        this.bindEvents();
    }

    ViaDeliveryWidget.prototype.bindEvents = function()
    {
        $(window).on('message', $.proxy(this, 'onSelectPoint'));
        $(document.body).on('submit', '#via-order-form', $.proxy(function(){ this.submit(); return false; }, this));
        $(document.body).on('change', '.via-calcs-form', $.proxy(function(){ this.submit(); return false; }, this));
        
        $(document.body).on('click',  '.via-order-submit', $.proxy(function(e){
            var $button = $(e.currentTarget || e.target);
            
            var ajaxUrl = $button.data('url') || $button.attr('href');
            var data    = $button.data();
            var reload  = $button.data('reload');
            var confirm = $button.data('confirm');

            if (confirm && !window.confirm(confirm)) {
                return false;
            }

            this.submit(data, function() {
                if (reload) {
                    document.location.href = document.location.href;
                }
            }, ajaxUrl); 

            return false; 
        }, this));

        $(document)
    }

    ViaDeliveryWidget.prototype.submit = function(data, callback, ajaxUrl)
    {
        var $form = $('#via-order-form');
        var data  = $.extend({}, data || {}, $form.serializeObject());

        $.ajax({
            url     : ajaxUrl || $form.data('action'),
            data    : data,
            type    : 'post',
            dataType: 'html',
            success : function(html) {
                $form.replaceWith(html);

                callback && callback();
            }
        });
    }

    ViaDeliveryWidget.prototype.onSelectPoint = function(e)
    {
        var event = e.originalEvent;

        if (event.origin !== 'https://widget.viadelivery.pro') //get message not from viadelivery? It's not our message
            return;

        var data  = JSON.parse(event.data); 

        if (data) {
            $('#via_selected_point').val(data.id);
        }
    }
})(jQuery)

