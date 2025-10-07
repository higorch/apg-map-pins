import { Loader } from "@googlemaps/js-api-loader";

(async function ($) {

    // Suprime aviso de depreciação do Marker
    const originalWarn = console.warn;
    console.warn = function (...args) {
        if (typeof args[0] === "string" && args[0].includes("google.maps.Marker is deprecated")) return;
        originalWarn.apply(console, args);
    };

    // --- ELEMENTOS E VARIÁVEIS ---
    const mapEl = $("#apgmappins-map");
    const filterEl = $("#apgmappins-choice");
    const key = mapEl.data("key");
    const styles = mapEl.data("styles");
    const loader = new Loader({ apiKey: key });

    let map;
    let markers = [];
    let allLocals = [];
    let currentInfoWindow = null;

    // --- ESTILOS DO MAPA ---
    const mapStyles = [
        { featureType: "water", stylers: [{ color: styles.water_color }] },
        { featureType: "landscape", stylers: [{ color: styles.landscape_color }, { lightness: -10 }] },
        { featureType: "road", stylers: [{ color: styles.road_color }] },
        { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: styles.road_labels_text_color }] },
        { featureType: "administrative", elementType: "geometry", stylers: [{ color: styles.administrative_color }] },
        { featureType: "administrative", elementType: "labels.text.fill", stylers: [{ color: styles.administrative_labels_text_color }] },
        { featureType: "poi", stylers: [{ visibility: "off" }] },
        { featureType: "transit", stylers: [{ visibility: "off" }] },
        { featureType: "administrative.locality", elementType: "labels.icon", stylers: [{ color: styles.administrative_labels_text_color, visibility: "off" }] },
    ];

    // --- INSTÂNCIA DO CHOICES ---
    const choicesInstance = new Choices(filterEl.get(0), {
        removeItemButton: true,
        placeholderValue: "Pesquisar",
        shouldSort: false,
        position: "auto",
        searchResultLimit: 50,
        loadingText: "Carregando...",
        noResultsText: "Nenhum resultado encontrado",
        noChoicesText: "Nenhuma opção disponível",
        itemSelectText: ""
    });

    // --- INICIALIZA O MAPA ---
    async function initMap() {
        const { Map } = await loader.importLibrary("maps");

        map = new Map(mapEl.get(0), {
            center: { lat: -15.8267, lng: -47.9218 },
            zoom: parseInt(styles.zoom),
            styles: mapStyles,
            disableDefaultUI: false,
        });

        renderMarkers();
    }

    // --- CRIA MARCADOR COM INFO WINDOW BONITO ---
    function createMarker({ map, position, title, icon, local }) {
        const marker = new google.maps.Marker({
            map,
            position,
            title,
            icon,
        });

        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div style="
                    min-width: 220px;
                    font-family: Arial, sans-serif;
                    border-radius: 8px;
                    padding: 10px 14px;
                    background-color: #fff;
                    box-shadow: 0 2px 12px rgba(0,0,0,0.15);
                    line-height: 1.5;
                ">
                    <h3 style="
                        margin: 0 0 6px 0;
                        font-size: 15px;
                        font-weight: 700;
                        color: ${styles.marker_fill_color};
                    ">${local.fields.company || local.fields.responsible || "N/A"}</h3>

                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                        <span style="margin-right:6px; color: ${styles.marker_fill_color};">👤</span>
                        <span>${local.fields.responsible || "N/A"}</span>
                    </div>

                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                        <span style="margin-right:6px; color: ${styles.marker_fill_color};">📞</span>
                        <span>${local.fields.landline || "N/A"}</span>
                    </div>

                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                        <span style="margin-right:6px; color: ${styles.marker_fill_color};">📱</span>
                        <span>${local.fields.mobile_phone || "N/A"}</span>
                    </div>

                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                        <span style="margin-right:6px; color: ${styles.marker_fill_color};">✉️</span>
                        <span>${local.fields.email || "N/A"}</span>
                    </div>

                    <div style="display: flex; align-items: center;">
                        <span style="margin-right:6px; color: ${styles.marker_fill_color};">🌐</span>
                        <span>${local.fields.site || "N/A"}</span>
                    </div>
                </div>
                `,
        });


        // Fecha o InfoWindow anterior, se houver
        google.maps.event.addListener(marker, 'click', () => {
            if (currentInfoWindow) {
                currentInfoWindow.close();
            }
            infoWindow.open(map, marker);
            currentInfoWindow = infoWindow;
        });

        marker.localId = local.id; // mantém referência para filtro por cidade
        markers.push(marker);

        return marker;
    }

    // --- LIMPA TODOS OS MARCADORES ---
    function clearMarkers() {
        markers.forEach(marker => marker.setMap(null));
        markers = [];
    }

    // --- RENDERIZA OS DETALHES ---
    function renderDetails(locals, selectedId = null) {
        const container = $("#apgmappins-details");
        container.empty();

        const filtered = selectedId ? locals.filter(loc => loc.id == selectedId) : locals;

        filtered.forEach(loc => {
            const item = $(`
                <div class="apgmappins-item">
                    <div class="apgmappins-info"><b>Empresa</b><span style="color: ${styles.marker_fill_color}">${loc.fields.company || "N/A"}</span></div>
                    <div class="apgmappins-info"><b>Representante</b><span style="color: ${styles.marker_fill_color}">${loc.fields.responsible || "N/A"}</span></div>
                    <div class="apgmappins-info"><b>Telefone Fixo</b><span style="color: ${styles.marker_fill_color}">${loc.fields.landline || "N/A"}</span></div>
                    <div class="apgmappins-info"><b>Telefone Móvel</b><span style="color: ${styles.marker_fill_color}">${loc.fields.mobile_phone || "N/A"}</span></div>
                    <div class="apgmappins-info"><b>Email</b><span style="color: ${styles.marker_fill_color}">${loc.fields.email || "N/A"}</span></div>
                    <div class="apgmappins-info"><b>Site</b><span style="color: ${styles.marker_fill_color}">${loc.fields.site || "N/A"}</span></div>
                </div>
            `);
            container.append(item);
        });

    }

    // --- RENDERIZA OS MARCADORES E POPULA O SELECT ---
    function renderMarkers() {
        $.ajax({
            url: apg_map_ajax.ajax_url,
            type: "POST",
            dataType: "json",
            data: {
                action: "apg_map_pins_get_locations",
                security: apg_map_ajax.security,
            },
            success: function (response) {
                if (!response.success || !response.data.map) return;

                clearMarkers();
                const bounds = new google.maps.LatLngBounds();

                // Salva todos os locais
                allLocals = response.data.map.map(loc => ({
                    id: loc.city.id,
                    city: loc.city.name,
                    fields: loc.fields
                }));

                // Renderiza marcadores
                allLocals.forEach(loc => {
                    const svgMarker = {
                        path: "M12 2 C8.13 2 5 5.13 5 9 c0 5.25 7 13 7 13 s7-7.75 7-13 c0-3.87-3.13-7-7-7 Z M12 9 m-2,0 a2,2 0 1,0 4,0 a2,2 0 1,0 -4,0",
                        fillColor: styles.marker_fill_color,
                        fillOpacity: 1,
                        strokeColor: styles.marker_stroke_color,
                        strokeWeight: 2,
                        scale: 2,
                        anchor: new google.maps.Point(12, 24),
                    };

                    createMarker({
                        map,
                        position: { lat: parseFloat(loc.fields.latitude), lng: parseFloat(loc.fields.longitude) },
                        title: loc.fields.company || loc.fields.responsible,
                        icon: svgMarker,
                        local: loc,
                    });

                    bounds.extend({ lat: parseFloat(loc.fields.latitude), lng: parseFloat(loc.fields.longitude) });
                });

                if (!bounds.isEmpty()) map.fitBounds(bounds);

                // Renderiza todos os detalhes inicialmente
                renderDetails(allLocals);

                // Popula select
                if (response.data.selects && Array.isArray(response.data.selects)) {
                    const formattedChoices = response.data.selects.map(country => ({
                        label: country.name,
                        id: country.id,
                        choices: country.locals.map(local => ({
                            value: local.id,
                            label: local.label + ' - ' + country.name
                        }))
                    }));

                    choicesInstance.clearChoices();
                    choicesInstance.setChoices(formattedChoices, "value", "label", true);
                }
            },
            error: function (xhr, status, error) {
                console.error("Erro ao carregar locais:", error);
            },
        });
    }

    // --- EVENTO DE SELEÇÃO ---
    filterEl.on("change", function () {
        const selectedId = choicesInstance.getValue(true);

        // Renderiza detalhes filtrados
        renderDetails(allLocals, selectedId);

        // Limpa animações anteriores
        markers.forEach(m => m.setAnimation(null));

        if (!selectedId) {
            // Mostrar todos os marcadores
            markers.forEach(marker => marker.setMap(map));
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => bounds.extend(marker.getPosition()));
            if (!bounds.isEmpty()) map.fitBounds(bounds);
            return;
        }

        // Mostrar apenas os marcadores da cidade selecionada
        markers.forEach(marker => {
            if (marker.localId == selectedId) {
                marker.setMap(map);
                marker.setAnimation(google.maps.Animation.BOUNCE);
                setTimeout(() => marker.setAnimation(null), 1400);
            } else {
                marker.setMap(null); // esconde os demais
            }
        });

        // Ajusta o mapa para caber todos os marcadores da cidade
        const bounds = new google.maps.LatLngBounds();
        markers
            .filter(m => m.localId == selectedId)
            .forEach(m => bounds.extend(m.getPosition()));
        if (!bounds.isEmpty()) map.fitBounds(bounds);
    });

    // --- INICIALIZA ---
    initMap();

})(jQuery);
