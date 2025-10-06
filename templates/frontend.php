<div class="apgmappins-box">

    <div class="apgmappins-header">
        <h3>Locais com representantes</h3>
        <div class="apgmappins-filter">
            <select id="apgmappins-filter">
                <option value="">Selecione um local</option>
            </select>
        </div>
    </div>

    <div class="apgmappins-content">
        <div id="apgmappins-map" data-key="<?php echo esc_attr($key); ?>" data-zoom="<?php echo esc_attr($zoom ?: 5); ?>"></div>
        <div id="apgmappins-details"></div>
    </div>

</div>