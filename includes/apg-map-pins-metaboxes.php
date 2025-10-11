<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Metaboxes_Apg_Map_Pins
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);
        add_action('save_post', [$this, 'save_metabox']);
    }

    public function register_metaboxes()
    {
        add_meta_box('apg_map_pins_details_metabox_id', __('Detalhes da localização', 'apgmappins'), [$this, 'apg_map_pins_details_metabox_html'], ['apg-map-pins'], 'normal', 'high');
    }

    // Exibe metabox (inclui nonce)
    public function apg_map_pins_details_metabox_html($post)
    {
        $entries = get_post_meta($post->ID, '_apg_map_pins_details', true);
        $territories = get_terms([
            'taxonomy'   => 'territories',
            'hide_empty' => false,
            'orderby'    => 'term_id',
            'order'      => 'ASC', // último filho mais novo
        ]);

        $marker_fill_color = isset($entries['marker_fill_color']['value']) ? $entries['marker_fill_color']['value'] : '#522aab';
        $marker_stroke_color = isset($entries['marker_stroke_color']['value']) ? $entries['marker_stroke_color']['value'] : '#FFFFFF';
        $territory    = isset($entries['territory']['value']) ? $entries['territory']['value'] : '';
        $latitude     = isset($entries['latitude']['value']) ? $entries['latitude']['value'] : '';
        $longitude    = isset($entries['longitude']['value']) ? $entries['longitude']['value'] : '';
        $landline     = isset($entries['landline']['value']) ? $entries['landline']['value'] : '';
        $mobile_phone = isset($entries['mobile_phone']['value']) ? $entries['mobile_phone']['value'] : '';
        $email        = isset($entries['email']['value']) ? $entries['email']['value'] : '';
        $responsible  = isset($entries['responsible']['value']) ? $entries['responsible']['value'] : '';
        $company      = isset($entries['company']['value']) ? $entries['company']['value'] : '';
        $site         = isset($entries['site']['value']) ? $entries['site']['value'] : '';

        // Nonce field para proteção
        wp_nonce_field('apg_map_pins_details_save', 'apg_map_pins_details_nonce');

        $html  = '<table class="form-table apg-map-pins" role="presentation">';

        $html .= '<tr><th scope="row">' . esc_html(__('Cor geral maracador', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" class="color-picker" data-alpha-enabled="true" name="apg_map_pin_marker_fill_color" value="' . esc_attr($marker_fill_color) . '" style="width:100%;"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Cor da borda marcador', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" class="color-picker" data-alpha-enabled="true" name="apg_map_pin_marker_stroke_color" value="' . esc_attr($marker_stroke_color) . '" style="width:100%;"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Território', 'apgmappins')) . ':</th>';
        $html .= '<td><select name="apg_map_pin_territory">';
        $html .= $this->render_territory_options($territories, $territory);
        $html .= '</select></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Latitude', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" name="apg_map_pin_latitude" value="' . esc_attr($latitude) . '" style="width:100%;" placeholder="-16.818873157462946"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Longitude', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" name="apg_map_pin_longitude" value="' . esc_attr($longitude) . '" style="width:100%;" placeholder="-49.2136985865062"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Telefone Fixo', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" name="apg_map_pin_landline" value="' . esc_attr($landline) . '" style="width:100%;" placeholder="+55 (62) 3200-0000"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Telefone Móvel', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" name="apg_map_pin_mobile_phone" value="' . esc_attr($mobile_phone) . '" style="width:100%;" placeholder="+55 (62) 99156-0854"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('E-mail', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="email" name="apg_map_pin_email" value="' . esc_attr($email) . '" style="width:100%;" placeholder="exemplo@email.com"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Responsável', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" name="apg_map_pin_responsible" value="' . esc_attr($responsible) . '" style="width:100%;" placeholder="Maria da Silva"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Empresa', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" name="apg_map_pin_company" value="' . esc_attr($company) . '" style="width:100%;" placeholder="AP Global Technologies"></td></tr>';

        $html .= '<tr><th scope="row">' . esc_html(__('Site', 'apgmappins')) . ':</th>';
        $html .= '<td><input type="text" name="apg_map_pin_site" value="' . esc_attr($site) . '" style="width:100%;" placeholder="apglobal.dev"></td></tr>';

        $html .= '</table>';

        echo $html;
    }

    // Gravação segura do metabox
    public function save_metabox($post_id)
    {
        // Proteções básicas
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;

        // Verifica nonce
        if (empty($_POST['apg_map_pins_details_nonce']) || !wp_verify_nonce($_POST['apg_map_pins_details_nonce'], 'apg_map_pins_details_save')) {
            return;
        }

        // Verifica capability - só editores/autores conforme tipo de post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Definição dos campos esperados
        $fields = [
            'marker_fill_color'   => 'apg_map_pin_marker_fill_color',
            'marker_stroke_color' => 'apg_map_pin_marker_stroke_color',
            'territory'           => 'apg_map_pin_territory',
            'latitude'            => 'apg_map_pin_latitude',
            'longitude'           => 'apg_map_pin_longitude',
            'landline'            => 'apg_map_pin_landline',
            'mobile_phone'        => 'apg_map_pin_mobile_phone',
            'email'               => 'apg_map_pin_email',
            'responsible'         => 'apg_map_pin_responsible',
            'company'             => 'apg_map_pin_company',
            'site'                => 'apg_map_pin_site',
        ];

        $entries = [];

        // Helper: sanitiza telefone (mantém +, dígitos, espaços, parênteses, traço)
        $sanitize_phone = function ($value) {
            $value = strip_tags($value);
            // remove tudo exceto dígitos e símbolos permitidos
            return preg_replace('/[^\d\+\-\(\)\s\.]/', '', $value);
        };

        foreach ($fields as $key => $field_name) {
            if (!isset($_POST[$field_name])) {
                continue;
            }

            $raw = wp_unslash($_POST[$field_name]); // desfaz magic quotes se houver

            switch ($key) {
                case 'latitude':
                case 'longitude':
                    // aceita vírgula ou ponto, tenta converter para float; se inválido, ignora
                    $normalized = str_replace(',', '.', trim($raw));
                    if ($normalized === '') {
                        $value = '';
                    } else {
                        // usar FILTER_VALIDATE_FLOAT
                        $float = filter_var($normalized, FILTER_VALIDATE_FLOAT);
                        if ($float === false) {
                            $value = ''; // ou null, dependendo do que você prefere salvar
                        } else {
                            // formata com ponto decimal padrão (string) para consistência
                            $value = (string) $float;
                        }
                    }
                    break;

                case 'email':
                    $email = sanitize_email($raw);
                    if ($email && is_email($email)) {
                        $value = $email;
                    } else {
                        $value = ''; // email inválido -> salva vazio
                    }
                    break;

                case 'landline':
                case 'mobile_phone':
                    $value = $sanitize_phone($raw);
                    break;

                default: // responsible, company, etc.
                    $value = sanitize_text_field($raw);
                    break;
            }

            // apenas armazena se não for vazio (ou armazena vazio se preferir)
            $entries[$key]['value'] = $value;
        }

        // Atualiza meta — WP cuidará da serialização segura
        update_post_meta($post_id, '_apg_map_pins_details', $entries);
    }

    private function render_territory_options($terms, $selected = 0)
    {
        $html = '';

        // Pega os termos de nível raiz (pais)
        $parents = array_filter($terms, fn($t) => $t->parent == 0);

        foreach ($parents as $parent) {
            // Pega os filhos do pai atual
            $children = array_filter($terms, fn($t) => $t->parent == $parent->term_id);

            if ($children) {
                // Cria optgroup para o pai
                $html .= '<optgroup label="' . esc_html($parent->name) . '">';

                foreach ($children as $child) {
                    // O nome do filho já contém a sigla, ex: "Aparecida de Goiânia (GO)"
                    $is_selected = selected($selected, $child->term_id, false);
                    $html .= '<option value="' . esc_attr($child->term_id) . '" ' . $is_selected . '>' . esc_html($child->name) . '</option>';
                }

                $html .= '</optgroup>';
            }
        }

        return $html;
    }
}

new Metaboxes_Apg_Map_Pins();
