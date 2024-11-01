<?php
    $activeTab = isset($_GET['active_via_tab']) ? sanitize_text_field($_GET['active_via_tab']) : 'main';
    
    $tabs = [
        'main' => 'Основные',
    ];
?>
    <nav class="nav-tab-wrapper woo-nav-tab-wrapper" data-tabs-content-level="1">
        <?php foreach ($tabs as $id => $tabname): ?>
            <a href="#" class="nav-tab dpd-tab <?php 
                echo $id == $activeTab ? 'nav-tab-active' : ''; ?>"
                data-tab-content-id="<?php echo esc_html($id); ?>">
                <?php echo esc_html($tabname); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="tab-wrapper">
        <?php foreach ($tabs as $id => $tabname): ?>
            <div class="dpd-tab-content-1" id="<?php echo esc_html($id); ?>" <?php echo $id == $activeTab ? '' : 'style="display:none;"' ?>>
                <?php echo \Ipol\Woo\ViaDelivery\Helpers\View::load('backend/settings/tab-'.$id); ?>
            </div>
        <?php endforeach; ?>
    </div>