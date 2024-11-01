<?php
use Ipol\Woo\ViaDelivery\Helpers\View;
?>
<h1><?php echo __('Via.Delivery', 'viadelivery'); ?></h1>

<div class="via-dashboard">
    <div class="via-dashboard__item via-dashboard__item--full">
        <h3 class="via-dashboard__item-title"><?php echo __('Delivery by city', 'viadelivery'); ?></h3>

        <?php echo View::load('backend/dashboard/dash-cities', $args); ?>

        <!--iframe src="https://widget.viadelivery.pro/via.charts/cities.html?id=<?php echo esc_html($settings['shop_id']); ?>&locale=<?php echo esc_html($locale); ?>" frameborder="0" width="100%" height="240px" scrolling="no"></!--iframe-->
    </div>

    <div class="via-dashboard__item via-dashboard__item--half">
        <h3 class="via-dashboard__item-title"><?php echo __('Payments by type', 'viadelivery'); ?></h3>

        <?php echo View::load('backend/dashboard/dash-payment', $args); ?>

        <!--iframe src="https://widget.viadelivery.pro/via.charts/payment-types.html?id=<?php echo esc_html($settings['shop_id']); ?>&locale=<?php echo esc_html($locale); ?>" frameborder="0" width="100%" height="240px" scrolling="no"></!--iframe-->
    </div>

    <div class="via-dashboard__item via-dashboard__item--half">
        <h3 class="via-dashboard__item-title"><?php echo __('Delivery statuses', 'viadelivery'); ?></h3>

        <?php echo View::load('backend/dashboard/dash-status', $args); ?>

        <!--iframe src="https://widget.viadelivery.pro/via.charts/unclaimed.html?id=<?php echo esc_html($settings['shop_id']); ?>&amp;locale=<?php echo esc_html($locale); ?>" frameborder="0" width="100%" height="240px" scrolling="no"></!--iframe-->
    </div>

    <div class="via-dashboard__item via-dashboard__item--full">
        <h3 class="via-dashboard__item-title"><?php echo __('Number of shipments per day', 'viadelivery'); ?></h3>

        <?php echo View::load('backend/dashboard/dash-shipments-per-day', $args); ?>

        <!--iframe src="https://widget.viadelivery.pro/via.charts/shipments-per-day.html?id=<?php echo esc_html($settings['shop_id']); ?>&locale=<?php echo esc_html($locale); ?>" frameborder="0" width="100%" height="240px" scrolling="no"></!--iframe-->
    </div>

    <!--    Debug information-->
    <?php
        if (get_option('woocommerce_viadelivery_settings', ['debug_mode' => ''] )['debug_mode'] == 'yes') {
    ?>
    <h1><?php echo 'Debug information'; ?></h1>

    <div class="via-dashboard__item via-dashboard__item--full">
        <?php
            $dir = explode('/', __DIR__);
            array_pop($dir);
            array_pop($dir);
            array_pop($dir);
        ?>

        <h3 class="via-dashboard__item-title">js.log</h3>
        <?php
            $filename = implode('/', $dir) . '/assets/js.log';
            if (file_exists($filename))
                echo '<pre>' . file_get_contents($filename) . '</pre>';
        ?>
    </div>

    <div class="via-dashboard__item via-dashboard__item--full">
        <h3 class="via-dashboard__item-title">shipment.log</h3>
        <?php
            $filename = implode('/', $dir) . '/assets/calculate.log';
            if (file_exists($filename))
                echo '<pre>' . file_get_contents($filename) . '</pre>';
        ?>
    </div>

    <div class="via-dashboard__item via-dashboard__item--full">
        <h3 class="via-dashboard__item-title">api.log</h3>
            <?php
                $filename = implode('/', $dir) . '/assets/api.log';
                if (file_exists($filename))
                    echo '<pre>' . file_get_contents($filename) . '</pre>';
            ?>
    </div>

    <?php
        }
    ?>

</div>

<style>
    .via-dashboard {
        display: grid;
        grid-template-columns: repeat(2,minmax(0,1fr));
        grid-gap: 16px;
        gap: 16px;
    }

    .via-dashboard__item {
        box-shadow: rgba(0, 0, 0, 0) 0px 0;
        padding: 24px;
        border-radius: 4px;
        background: #fff;
    }
    
    .via-dashboard__item--full {
        grid-column: span 2/span 2;
    }

    .via-dashboard__item--half {
        grid-column: span 1/span 1;
    }

    .via-dashboard__item-title {
        margin-bottom: 16px;
        font-weight: bold;
    }
</style>