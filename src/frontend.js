import { setOptions, importLibrary } from "@googlemaps/js-api-loader";

(async function ($) {
    $(document).ready(async function () {

        const mapEl = $("#apgmappins-map");
        if (!mapEl.length) return;

        const key = mapEl.data("key");
        const zoom = mapEl.data("zoom") || 6;

        setOptions({ key });

        const { Map } = await importLibrary("maps");
        const { Marker } = await importLibrary("marker");

        // Inicializa mapa
        const map = new Map(mapEl[0], {
            center: { lat: -16.81892962588058, lng: -49.21375223472516 }, // Brasil
            zoom: zoom,
        });

        let markers = [];
        let allLocations = [];

        const selectEl = $("#apgmappins-filter");

        // 🔹 Requisição AJAX
        $.ajax({
            url: apg_map_ajax.ajax_url,
            type: "POST",
            dataType: "json",
            data: {
                action: "apg_map_pins_get_locations",
                security: apg_map_ajax.security
            },
            success: function (response) {
                if (!response.success) return;
                const { map: mapData, selects } = response.data;

                allLocations = mapData;

                populateSelect(selects);
                renderMarkers(allLocations);                
            },
            error: function (xhr, status, error) {
                console.error("Erro ao carregar locais:", error);
            }
        });

        // 🔹 Preenche o select agrupado por país
        function populateSelect(selects) {
            selectEl.empty().append('<option value="">Selecione uma localização</option>');

            Object.keys(selects).forEach(country => {
                const optgroup = $("<optgroup>").attr("label", country);
                Object.keys(selects[country]).forEach(label => {
                    const item = selects[country][label];
                    const option = $("<option>")
                        .val(item.id)
                        .attr("data-lat", item.lat)
                        .attr("data-lng", item.lng)
                        .text(label);
                    optgroup.append(option);
                });
                selectEl.append(optgroup);
            });

            selectEl.on("change", function () {
                const id = $(this).val();
                if (!id) {
                    renderMarkers(allLocations);
                    return;
                }

                const location = allLocations.find(loc => loc.id == id);
                if (location) renderMarkers([location]);
            });
        }

        // 🔹 Renderiza os marcadores
        function renderMarkers(locations, color = "#FF0000") { // cor padrão vermelho
            // Remove antigos
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            if (!locations.length) return;

            const bounds = new google.maps.LatLngBounds();

            locations.forEach(loc => {
                if (!loc.lat || !loc.lng) return;

                // SVG customizado para o pin
                const svgIcon = {
                    path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z", // pin
                    fillColor: color,
                    fillOpacity: 1,
                    strokeColor: "#000",
                    strokeWeight: 1,
                    scale: 1.5,
                    anchor: new google.maps.Point(12, 24), // âncora do pin
                };

                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(loc.lat), lng: parseFloat(loc.lng) },
                    map,
                    title: loc.title,
                    icon: svgIcon
                });

                const infoContent = `
                    <div class="apgmappins-tooltip">
                        <h4>${loc.title}</h4>
                        <p><strong>Empresa:</strong> ${loc.fields.company || "-"}</p>
                        <p><strong>Responsável:</strong> ${loc.fields.responsible || "-"}</p>
                        <p><strong>Telefone:</strong> ${loc.fields.mobile_phone || loc.fields.landline || "-"}</p>
                        <p><strong>Email:</strong> ${loc.fields.email || "-"}</p>
                        <p><strong>Site:</strong> ${loc.fields.site ? `<a href="${loc.fields.site}" target="_blank">${loc.fields.site}</a>` : "-"}</p>
                        <p><strong>Cidade:</strong> ${loc.city} - ${loc.state}, ${loc.country}</p>
                    </div>
                `;

                const infoWindow = new google.maps.InfoWindow({ content: infoContent });

                marker.addListener("click", () => {
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
                bounds.extend(marker.getPosition());
            });

            map.fitBounds(bounds);
        }
    });
})(jQuery);
