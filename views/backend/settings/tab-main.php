<table class="form-table">
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="via_shop_id"><?php echo __( 'Store ID', 'viadelivery'); ?></label>
        </th>

        <td class="forminp">
            <fieldset>
                <input id="via_shop_id" type="text" name="shop_id" value="<?php echo esc_html($form_fields['shop_id']); ?>">
            </fieldset>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="via_secret_token"><?php echo __( 'The secret key', 'viadelivery'); ?></label>
        </th>

        <td class="forminp">
            <fieldset>
                <input id="via_secret_token" type="text" name="secret_token" value="<?php echo esc_html($form_fields['secret_token']); ?>">
            </fieldset>
        </td>
    </tr>
</table>