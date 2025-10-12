import { Loader } from "@googlemaps/js-api-loader";

(async function ($) {

    $(document).ready(async function () {
        const mapEl = $("#apgmappins-map");
        const filterEl = $("#apgmappins-choice");
        const key = mapEl.data("key");
        const styles = mapEl.data("styles");
        const showSidebar = mapEl.data("map_side_bar_details") === "true";
        const loader = new Loader({ apiKey: key });

        let choicesInstance;
        let map;
        let markers = [];
        let allLocals = [];
        let currentInfoWindow = null;

        const defaultstyles = [
            { featureType: "water", stylers: [{ color: rgbToHex(styles.water_color) || "#9474ff" }] },
            { featureType: "landscape", stylers: [{ color: styles.landscape_color || "#F5F5F5" }, { lightness: -10 }] },
            { featureType: "road", stylers: [{ color: styles.road_color || "#FFFFFF" }] },
            { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: styles.road_labels_text_color || "#4B0082" }] },
            { featureType: "administrative", elementType: "geometry", stylers: [{ color: styles.administrative_color || "#9474ff" }] },
            { featureType: "administrative", elementType: "labels.text.fill", stylers: [{ color: styles.administrative_labels_text_color || "#9474ff" }] },
            { featureType: "poi", stylers: [{ visibility: "off" }] },
            { featureType: "transit", stylers: [{ visibility: "off" }] },
            { featureType: "administrative.locality", elementType: "labels.icon", stylers: [{ visibility: "off" }] },
        ];

        if (filterEl.length > 0) {
            choicesInstance = new Choices(filterEl.get(0), {
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
        }

        async function initMap() {
            const { Map } = await loader.importLibrary("maps");
            map = new Map(mapEl.get(0), {
                center: { lat: -15.8267, lng: -47.9218 },
                styles: defaultstyles,
                disableDefaultUI: false,
            });
            fetchAndRenderMarkers();
        }

        function rgbToHex(rgb) {
            const result = rgb.match(/\d+/g);
            if (!result) return '#000000';
            return "#" + result.map(x => parseInt(x).toString(16).padStart(2, '0')).join('');
        }

        function createMarker(local) {
            const position = { lat: parseFloat(local.fields.latitude), lng: parseFloat(local.fields.longitude) };
            const marker = new google.maps.Marker({
                map,
                position,
                title: local.fields.company || local.fields.responsible,
                icon: {
                    path: "M12 2 C8.13 2 5 5.13 5 9 c0 5.25 7 13 7 13 s7-7.75 7-13 c0-3.87-3.13-7-7-7 Z M12 9 m-2,0 a2,2 0 1,0 4,0 a2,2 0 1,0 -4,0",
                    fillColor: local.fields.marker_fill_color || "#522aab",
                    fillOpacity: 1,
                    strokeColor: local.fields.marker_stroke_color || "#FFFFFF",
                    strokeWeight: 2,
                    scale: 2,
                    anchor: new google.maps.Point(12, 24),
                }
            });

            const infoContent = [];
            if (local.fields.company) infoContent.push(`<div><b>🏢 Empresa:</b> ${local.fields.company}</div>`);
            if (local.fields.responsible) infoContent.push(`<div><b>👤 Representante:</b> ${local.fields.responsible}</div>`);
            if (local.fields.landline) infoContent.push(`<div><b>📞 Telefone Fixo:</b> ${local.fields.landline}</div>`);
            if (local.fields.mobile_phone) infoContent.push(`<div><b>📱 Telefone Móvel:</b> ${local.fields.mobile_phone}</div>`);
            if (local.fields.email) infoContent.push(`<div><b>✉️ Email:</b> ${local.fields.email}</div>`);
            if (local.fields.site) infoContent.push(`<div><b>🌐 Site:</b> ${local.fields.site}</div>`);

            const infoWindow = new google.maps.InfoWindow({
                content: `<div style="min-width:220px; font-family:Arial, sans-serif; border-radius:8px; padding:10px 14px; background-color:#fff; box-shadow:0 2px 12px rgba(0,0,0,0.15); line-height:1.5;">${infoContent.join("")}</div>`
            });

            marker.addListener("click", () => {
                if (currentInfoWindow) currentInfoWindow.close();
                infoWindow.open(map, marker);
                currentInfoWindow = infoWindow;
            });

            marker.localId = local.fields.territory || local.id;
            markers.push(marker);
            return marker;
        }

        function clearMarkers() {
            markers.forEach(m => m.setMap(null));
            markers = [];
        }

        function renderDetails(locals, selectedId = null) {
            if (!showSidebar) return;
            const container = $("#apgmappins-details");
            container.empty();

            const filtered = selectedId ? locals.filter(loc => loc.fields.territory == selectedId) : locals;
            filtered.forEach(loc => {
                const infoBlocks = [];
                if (loc.fields.company) infoBlocks.push(`<div class="apgmappins-info"><b>Empresa:</b> ${loc.fields.company}</div>`);
                if (loc.fields.responsible) infoBlocks.push(`<div class="apgmappins-info"><b>Representante:</b> ${loc.fields.responsible}</div>`);
                if (loc.fields.landline) infoBlocks.push(`<div class="apgmappins-info"><b>Telefone Fixo:</b> ${loc.fields.landline}</div>`);
                if (loc.fields.mobile_phone) infoBlocks.push(`<div class="apgmappins-info"><b>Telefone Móvel:</b> ${loc.fields.mobile_phone}</div>`);
                if (loc.fields.email) infoBlocks.push(`<div class="apgmappins-info"><b>Email:</b> ${loc.fields.email}</div>`);
                if (loc.fields.site) infoBlocks.push(`<div class="apgmappins-info"><b>Site:</b> ${loc.fields.site}</div>`);

                container.append(`<div class="apgmappins-item">${infoBlocks.join("")}</div>`);
            });
        }

        function fetchAndRenderMarkers() {
            $.ajax({
                url: apg_map_ajax.ajax_url,
                type: "POST",
                dataType: "json",
                data: { action: "apg_map_pins_get_locations", security: apg_map_ajax.security },
                success: function (response) {
                    if (!response.success || !response.data.map) return;

                    clearMarkers();
                    allLocals = response.data.map;
                    const bounds = new google.maps.LatLngBounds();

                    allLocals.forEach(loc => {
                        const marker = createMarker(loc);
                        bounds.extend(marker.getPosition());
                    });

                    if (allLocals.length === 1) {
                        const pos = bounds.getCenter();
                        const offset = 0.005;
                        bounds.extend({ lat: pos.lat() + offset, lng: pos.lng() + offset });
                        bounds.extend({ lat: pos.lat() - offset, lng: pos.lng() - offset });
                    }

                    map.fitBounds(bounds);
                    renderDetails(allLocals);

                    // Configura Choices
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
                error: (xhr, status, error) => console.error("Erro ao carregar locais:", error)
            });
        }

        filterEl.on("change", function () {
            const selectedId = choicesInstance.getValue(true);

            renderDetails(allLocals, selectedId);

            markers.forEach(marker => {
                marker.setMap(!selectedId || marker.localId == selectedId ? map : null);
            });

            const bounds = new google.maps.LatLngBounds();
            markers.filter(m => !selectedId || m.localId == selectedId).forEach(m => bounds.extend(m.getPosition()));

            if (!bounds.isEmpty() && markers.filter(m => !selectedId || m.localId == selectedId).length === 1) {
                const pos = bounds.getCenter();
                const offset = 0.005;
                bounds.extend({ lat: pos.lat() + offset, lng: pos.lng() + offset });
                bounds.extend({ lat: pos.lat() - offset, lng: pos.lng() - offset });
            }

            if (!bounds.isEmpty()) map.fitBounds(bounds);
        });

        initMap();

    });

})(jQuery);
