<div class="apgmappins-box">
    <div class="apgmappins-header">
        <h3><?php echo esc_attr($title); ?></h3>
        <div class="apgmappins-filter">
            <select id="apgmappins-choice"></select>
        </div>
    </div>

    <div class="apgmappins-content">
        <div id="apgmappins-map" data-key="<?php echo esc_attr($key); ?>" data-styles="<?php echo esc_attr($styles); ?>"></div>
        <div id="apgmappins-details"></div>
    </div>
</div>
