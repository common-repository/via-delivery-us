<h1><?php echo __('Via.Delivery', 'viadelivery'); ?></h1>

<form method="POST">
    <div class="notice notice-info inline" style="margin-left: 0">
        <p>
            <?php echo sprintf(
                __('If you have no API connection credentials, please fill out the <a href="%s">registration form</a>.', 'viadelivery'),
                $signUpUrl
            ); ?>
        </p>
    </div>

    <br>

    <fieldset style="border: 1px solid; padding: 15px; display: inline-block;">
        <legend><h3><?php echo __('Via.Delivery API настройки подключения', 'viadelivery'); ?></h3></legend>
        
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="woocommerce_viadelivery_shop_id"><?php echo __('ShopID', 'viadelivery'); ?></label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo __('ShopID', 'viadelivery'); ?></span></legend>
                            <input class="input-text regular-input " type="text" name="woocommerce_viadelivery_shop_id" id="woocommerce_viadelivery_shop_id" style="" value="" placeholder="" required="required">
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="woocommerce_viadelivery_secret_token"><?php echo __('Secret token', 'viadelivery'); ?></label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo __('Secret token', 'viadelivery'); ?></span></legend>
                            <input class="input-text regular-input " type="text" name="woocommerce_viadelivery_secret_token" id="woocommerce_viadelivery_secret_token" style="" value="" placeholder="" required="required">
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button name="save" class="button-primary woocommerce-save-button" type="submit" value="<?php echo __('Authorize', 'viadelivery'); ?>"><?php echo __('Authorize', 'viadelivery') ?></button>
        </p>
    </fieldset>
</form>