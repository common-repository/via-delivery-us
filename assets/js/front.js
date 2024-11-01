(function($) {
    'use strict';

    $(document).ready(function(){
        var widget = new ViaDeliveryWidget();
    });

    function ViaDeliveryWidget()
    {
        if (this === window) {
            return new ViaDeliveryWidget()
        }

        this.opened   = false;
        this.$overlay = null;
        this.$frame   = null;

        this.init();
    }

    ViaDeliveryWidget.prototype.init = function() {
        this.bindEvents();
    }

    ViaDeliveryWidget.prototype.bindEvents = function()
    {
        $(window).on('message', $.proxy(this, 'onSelectPoint'));
        $(document.body).on('updated_checkout', $.proxy(this, 'onUpdatedCheckout'));
        $(document.body).on('updated_cart_totals', $.proxy(this, 'onUpdatedCheckout'));
        $(document.body).on('click','.viadelivery_select_pickpoint', $.proxy(function(){ this.open(); return false; }, this));
        $(document.body).on('click', '#via_selectpoint_overlay', $.proxy(function(){ this.close(); return false; }, this));
        $(document.body).on('click','.via_selectpoint_close', $.proxy(function(){ this.close(); return false; }, this));

        this.onUpdatedCheckout();
    }

    ViaDeliveryWidget.prototype.open = function() 
    {
        if (this.opened) {
            return this;
        }

        this._getOverlay().show();
        this._getFrame( this.getMapUrl() ).show();
        this.opened = true;

        return this;
    }

    ViaDeliveryWidget.prototype.close = function() 
    {
        this._getOverlay().remove();
        this._getFrame().remove();

        this.$overlay = this.$frame = null;
        this.opened   = false;

        return this;
    }

    ViaDeliveryWidget.prototype.recalculate = function() 
    {
        $(document.body).trigger('update_checkout');
        $('select.shipping_method, :input[name^=shipping_method]').trigger('change');
    }

    ViaDeliveryWidget.prototype.getMapUrl = function() 
    {
        var country       = $('#billing_country').val() || $('#calc_shipping_country').val();
        var region        = $('#billing_state').val() || $('#calc_shipping_state').val();
        var city          = $('#billing_city').val() || $('#calc_shipping_city').val();
        var street        = ($('#billing_address_1').val() + ' ' +  $('#billing_address_2').val()) || $('#calc_shipping_city').val();
        var zip_code      = $('#billing_postcode').val() || $('#calc_shipping_postcode').val();
        var paymentMethod = $('input[name="payment_method"]:checked').val() || '';
        var mapUrl        = $('#via_iframe_url').val();

        if ($('#ship-to-different-address-checkbox').is(':checked')) {
            country = $('#shipping_country').val();
            region  = $('#shipping_state').val();
            city    = $('#shipping_city').val();
            street  = $('#shipping_address_1').val() + ' ' + $('#shipping_address_2').val();
            zip_code= $('#shipping_postcode').val();
        }

        if (mapUrl) {
            return mapUrl
                .replace('_COUNTRY_', country)
                .replace('_REGION_', region)
                .replace('_CITY_', city)
                .replace('_STREET_', street)
                .replace('_ZIP_CODE_', zip_code)
                .replace('_PAYMENT_METHOD_', paymentMethod);
        }

        return '/wp-json/woo-viadelivery/map/'
            + '?city='+ city
            + '&region='+ region
            + '&country='+ country
            + '&zip_code='+ zip_code
            + '&payment='+ paymentMethod
        ;
    }

    ViaDeliveryWidget.prototype.getSelectedPoint = function()
    {
        return $('#via_selected_point').val() || false;
    }

    ViaDeliveryWidget.prototype._getOverlay = function()
    {
        return this.$overlay = this.$overlay || $('<div id="via_selectpoint_overlay" />').css({
            position: 'fixed',
            left: 0,
            top: 0,
            right: 0,
            bottom: 0,
            background: 'rgba(0,0,0,.3)',
            zIndex: '9998',
        }).appendTo('body');
    }

    ViaDeliveryWidget.prototype._getFrame = function(url)
    {
        return this.$frame = this.$frame || $('<div id="via_selectpoint_popup"><iframe src="'+ url +'" style="width: 100%; height: 100%"></iframe><a href="#" class="via_selectpoint_close close"></a></div>')
            .css({
                position: 'fixed',
                left: '50%',
                top: '50%',
                width: '800px',
                height: '600px',
                maxWidth: '90%',
                maxHeight: '90%',
                background: '#000',
                transform: 'translate(-50%, -50%)',
                zIndex: '9999',
            }).appendTo('body');
    }

    ViaDeliveryWidget.prototype._getSelectPointText = function()
    {
        return $('#via_select_button_text').val() || 'Select Point';
    }

    ViaDeliveryWidget.prototype._getSelectPointCss = function()
    {
        return $('#via_select_button_css').val();
    }

    ViaDeliveryWidget.prototype.onUpdatedCheckout = function(e)
    {
        $('#via_selected').val('');
        $('#viadelivery_select_pickpoint').remove();

        $('#shipping_method, .shipping_method').each($.proxy(function(index, element){
            var $shippingMethod  = $(element);
            var selectShipping   = $shippingMethod.val();

            if (selectShipping.indexOf('viadelivery') < 0) {
                return ;
            }

            var $selectPickPoint = $('.viadelivery_select_pickpoint').hide();

            if ($selectPickPoint.length <= 0) {
                $selectPickPoint = $('<div class="viadelivery_select_pickpoint"><a href="javascript:void(0)" /></div>')
                    .appendTo($shippingMethod.closest('li'))
                    .hide()
                ;
            }

            $selectPickPoint
                    .find('a')
                    .html(this._getSelectPointText())
                    .addClass(this._getSelectPointCss())
                ;

            if ($shippingMethod.prop('checked')) {
                $('#via_selected').val('Y');

                // show "Select pickpoint" button only on Checkout page
                if ($('#billing_first_name').length) {
                    $('<div />')
                        .append($selectPickPoint.clone(true, true).attr('id', 'viadelivery_select_pickpoint'))
                        .prependTo('.shipping_address')
                    ;

                    $selectPickPoint.show();
                }
            }

            // label formatting
            var label = $('label[for="shipping_method_0_viadelivery"]');
            if (label.length) {
                var str = label.text().replace(/ \(.*\)/, '');

                // Checkout page
                if ($('#billing_first_name').length) {
/*
                    var pos_start = label.text().search(/\(/);
                    var pos_end = label.text().search(/\)/);
                    console.log(label.text());
                    label.text(str + " " + label.text().substring(pos_start + 1, pos_end + 1));
*/
                }
                // Cart page
                else {
                    label.text(str);
                }
            }

        }, this));
    }

    ViaDeliveryWidget.prototype.onSelectPoint = function(e)
    {
        var event = e.originalEvent;
        if (event.origin !== 'https://widget.viadelivery.pro') return;

        var data  = JSON.parse(event.data);

        if (!data.emit || data.emit !== 'via-map') return;

        var address = $('#billing_address_1');

        // if ($('#ship-to-different-address-checkbox').is(':checked')) {
            address = $('#shipping_address_1');
        // }

        if (data) {
            address.val(data.city + ', ' + ' (' + data.description + ')');

            this.saveSelectPoint(data);
            this.recalculate();
            this.close();
        }
    }

    ViaDeliveryWidget.prototype.saveSelectPoint = function(point)
    {
        $('#via_selected_point').val(point.id);

        var url = $('#via_save_point_url').val();
        
        if (url) {
            $.ajax({
                url : url,
                type: 'POST',
                data: {'point': point.id},
                dataType: 'json',
            });
        }
    }

})(jQuery);