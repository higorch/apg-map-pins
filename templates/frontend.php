<div class="apgmappins-box">
    <div class="apgmappins-header">
        <h3><?php echo esc_attr($title); ?></h3>
        <div class="apgmappins-filter">
            <select id="apgmappins-choice"></select>
        </div>
    </div>
    <div class="apgmappins-content<?php echo $map_side_bar_details == 'false' ? ' full-width-map' : ''; ?>">
        <div id="apgmappins-map" data-key="<?php echo esc_attr($key); ?>" data-map_side_bar_details="<?php echo esc_attr($map_side_bar_details); ?>" data-styles="<?php echo esc_attr($styles); ?>"></div>
        <?php if ($map_side_bar_details == 'true'): ?>
            <div id="apgmappins-details"></div>
        <?php endif; ?>
    </div>
</div>