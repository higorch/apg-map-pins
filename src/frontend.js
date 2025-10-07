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

    console.log(styles);

    let map;
    let markers = [];
    let allLocals = []; // armazena todos os locais para renderDetails

    // --- ESTILOS DO MAPA ---
    const mapStyles = [
        { featureType: "water", stylers: [{ color: styles.water_color }] },
        { featureType: "landscape", stylers: [{ color: styles.landscape_color }, { lightness: -10 }] },
        { featureType: "road", stylers: [{ color: styles.road_color }] },
        { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: styles.road_labels_text_color }] },
        { featureType: "poi", stylers: [{ visibility: "off" }] },
        { featureType: "transit", stylers: [{ visibility: "off" }] },
        { featureType: "administrative", elementType: "geometry", stylers: [{ color: styles.administrative_color }] },
        { featureType: "administrative", elementType: "labels.text.fill", stylers: [{ color: styles.administrative_labels_text_color }] }
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

    // --- CRIA MARCADOR ---
    function createMarker({ map, position, title, icon, localId }) {
        const marker = new google.maps.Marker({ map, position, title, icon });
        marker.localId = localId;
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

        const filtered = selectedId
            ? locals.filter(loc => loc.id == selectedId)
            : locals;

        filtered.forEach(loc => {
            const item = $(`
                <div class="apgmappins-item">
                    <div class="apgmappins-info"><b>Empresa</b><span>${loc.fields.company || "-"}</span></div>
                    <div class="apgmappins-info"><b>Representante</b><span>${loc.fields.responsible || "-"}</span></div>
                    <div class="apgmappins-info"><b>Telefone Fixo</b><span>${loc.fields.landline || "-"}</span></div>
                    <div class="apgmappins-info"><b>Telefone Móvel</b><span>${loc.fields.mobile_phone || "-"}</span></div>
                    <div class="apgmappins-info"><b>Email</b><span>${loc.fields.email || "-"}</span></div>
                    <div class="apgmappins-info"><b>Site</b><span>${loc.fields.site || "-"}</span></div>
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
                        title: loc.city || "Local",
                        icon: svgMarker,
                        localId: loc.id
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

        if (!selectedId) {
            // Mostrar todos os marcadores
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => bounds.extend(marker.getPosition()));
            if (!bounds.isEmpty()) map.fitBounds(bounds);
            return;
        }

        // Mostrar apenas marcador selecionado
        const marker = markers.find(m => m.localId == selectedId);
        if (marker) {
            map.panTo(marker.getPosition());
            map.setZoom(17);
            marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => marker.setAnimation(null), 1400);
        }
    });

    // --- INICIALIZA ---
    initMap();

})(jQuery);
