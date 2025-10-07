import { Loader } from "@googlemaps/js-api-loader";

(async function ($) {

    // Suprime aviso de depreciação do Marker (limpo)
    const originalWarn = console.warn;
    console.warn = function (...args) {
        if (typeof args[0] === "string" && args[0].includes("google.maps.Marker is deprecated")) return;
        originalWarn.apply(console, args);
    };

    // --- ELEMENTOS E VARIÁVEIS ---
    const mapEl = $("#apgmappins-map");
    const filterEl = $("#apgmappins-choice");
    const key = mapEl.data("key");
    const zoom = parseInt(mapEl.data("zoom")) || 5;
    const loader = new Loader({ apiKey: key });

    let map;
    let markers = [];

    // --- ESTILOS DO MAPA ---
    const mapStyles = [
        { featureType: "water", stylers: [{ color: "#9474ff" }] },
        { featureType: "landscape", stylers: [{ color: "#F5F5F5" }, { lightness: -10 }] },
        { featureType: "road", stylers: [{ color: "#FFFFFF" }] },
        { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: "#4B0082" }] },
        { featureType: "poi", stylers: [{ visibility: "off" }] },
        { featureType: "transit", stylers: [{ visibility: "off" }] },
        { featureType: "administrative", elementType: "geometry", stylers: [{ color: "#4B0082" }] },
        { featureType: "administrative", elementType: "labels.text.fill", stylers: [{ color: "#4B0082" }] }
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
            center: { lat: -15.8267, lng: -47.9218 }, // Brasília padrão
            zoom,
            styles: mapStyles,
            disableDefaultUI: false,
        });

        renderMarkers();
    }

    // --- CRIA MARCADOR ---
    function createMarker({ map, position, title, icon, localId }) {
        const marker = new google.maps.Marker({ map, position, title, icon });
        marker.localId = localId; // associa o ID do local
        markers.push(marker);
        return marker;
    }

    // --- LIMPA TODOS OS MARCADORES ---
    function clearMarkers() {
        markers.forEach(marker => marker.setMap(null));
        markers = [];
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

                // Renderiza marcadores
                response.data.map.forEach(data => {
                    const lat = parseFloat(data.lat);
                    const lng = parseFloat(data.lng);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const svgMarker = {
                        path: "M12 2 C8.13 2 5 5.13 5 9 c0 5.25 7 13 7 13 s7-7.75 7-13 c0-3.87-3.13-7-7-7 Z M12 9 m-2,0 a2,2 0 1,0 4,0 a2,2 0 1,0 -4,0",
                        fillColor: "#522aab",
                        fillOpacity: 1,
                        strokeColor: "#FFFFFF",
                        strokeWeight: 2,
                        scale: 2,
                        anchor: new google.maps.Point(12, 24),
                    };

                    createMarker({
                        map,
                        position: { lat, lng },
                        title: data.city.name || "Local",
                        icon: svgMarker,
                        localId: data.city.id // id único da cidade
                    });

                    bounds.extend({ lat, lng });
                });

                if (!bounds.isEmpty()) map.fitBounds(bounds);

                // Preenche o select (Choices.js)
                if (response.data.selects && Array.isArray(response.data.selects)) {
                    const formattedChoices = response.data.selects.map(country => ({
                        label: country.name, // grupo
                        id: country.id,
                        choices: country.locals.map(local => ({
                            value: local.id,
                            label: local.label + ' - ' + country.name, // "Cidade (Estado)",
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

    // --- EVENTO DE SELEÇÃO NO SELECT ---
    filterEl.on("change", async function () {
        const selectedId = choicesInstance.getValue(true); // pega o id (value)

        if (!selectedId) {
            // Select limpo: mostra todos os marcadores
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => {
                bounds.extend(marker.getPosition());
            });
            if (!bounds.isEmpty()) map.fitBounds(bounds);
            return;
        }

        // Select com valor: mostra apenas o marcador selecionado
        const marker = markers.find(m => m.localId == selectedId);

        console.log(marker, markers);

        if (marker) {
            map.panTo(marker.getPosition());
            map.setZoom(12);

            // Efeito opcional (pular o marcador)
            marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => marker.setAnimation(null), 1400);
        }
    });

    // --- INICIALIZA ---
    initMap();

})(jQuery);
