<?php
use Ipol\Woo\ViaDelivery\Helpers\View;

    $activeTab = isset($_GET['dpd_active_tab']) ? sanitize_text_field($_GET['dpd_active_tab']) : 'map';
    $tabs = [
        'map' => __('Common', 'viadelivery'),
    ];
?>


<form id="via-order-form" method="post" data-action="/wp-json/woo-viadelivery/order/<?php echo esc_html($order->getId()); ?>">
    <div class="order-content" style="display: flex; flex-direction: column;">
        <?/*
        <div>
            <div class="notifications">
                <?php if (!empty($errors)): ?>
                    <div class="notice notice-error inline">
                        <?php foreach ($errors as $error) { ?>
                            <p><?php echo esc_html($error); ?></p>
                        <?php } ?>  
                    </div>
                <?php endif; ?>

                <?php if (!empty($notifications)): ?>
                    <div class="notice notice-info inline">
                        <?php foreach ($notifications as $notification) { ?>
                            <p><?php echo esc_html($notification); ?></p>
                        <?php } ?>
                    </div>
                <?php endif; ?>
            </div>

            <nav class="nav-tab-wrapper woo-nav-tab-wrapper" data-tabs-content-level="1">
                <?php foreach ($tabs as $id => $tabname) { ?>
                    <a href="javascript:void(0);"
                    class="nav-tab dpd-tab <?php echo $id == $activeTab ? 'nav-tab-active' : '' ?>"
                    data-tab-content-id="<?php echo esc_html($id); ?>"
                    >
                        <?php echo esc_html($tabname); ?>
                    </a>
                <?php } ?>
            </nav>
        </div>*/?>

        <div class="tab-wrapper" style="flex-grow: 1; height: 100%">
            <?php foreach ($tabs as $id => $tabname) { ?>
                <div class="via-tab-content via-tab-content-<?php echo esc_html($id); ?>"
                     id="<?php echo esc_html($id); ?>"
                    <?php echo $id == $activeTab ? '' : 'style="display:none;"'; ?>
                     style="height: 100%"
                >
                    <?php include View::getPath('backend/order/tab-'. $id); ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="order-buttons">
        <?php if (!$order->isCreated()) { ?>
            <button
                class="button send-order button-primary via-order-submit"
                data-action="save"
                data-reload="reload"
                type="button"
            >
                <?php echo __('Save', 'viadelivery'); ?>
            </button>
        <?php } ?>
    </div>
</form>
