import { Loader } from "@googlemaps/js-api-loader";

(async function ($) {

    // ⚠️ Suprime o aviso de depreciação do Marker
    const originalWarn = console.warn;
    console.warn = function (...args) {
        if (typeof args[0] === "string" && args[0].includes("google.maps.Marker is deprecated")) {
            return; // ignora apenas este aviso específico
        }
        originalWarn.apply(console, args);
    };

    // implementaytion
    const mapEl = $("#apgmappins-map");
    const filterEl = $("#apgmappins-choice");
    const key = mapEl.data("key");
    const zoom = parseInt(mapEl.data("zoom"));
    const loader = new Loader({ apiKey: key });

    let map; // variável do mapa

    // Estilos do mapa
    const mapStyles = [
        // Água em tom roxo suave
        { featureType: "water", stylers: [{ color: "#9474ff" }] },

        // Terreno / relevo com contraste leve
        { featureType: "landscape", stylers: [{ color: "#F5F5F5" }, { lightness: -10 }] },

        // Ruas
        { featureType: "road", stylers: [{ color: "#FFFFFF" }] },
        { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: "#4B0082" }] },

        // POIs e transporte removidos para limpeza visual
        { featureType: "poi", stylers: [{ visibility: "off" }] },
        { featureType: "transit", stylers: [{ visibility: "off" }] },

        // Limites administrativos com contraste suave
        { featureType: "administrative", elementType: "geometry", stylers: [{ color: "#4B0082" }] },
        { featureType: "administrative", elementType: "labels.text.fill", stylers: [{ color: "#4B0082" }] }
    ];

    const choicesInstance = new Choices(filterEl.get(0), {
        removeItemButton: 'Remover',
        placeholderValue: 'Pesquisar',
        shouldSort: false,
        position: 'auto',
        loadingText: "Carregando...",
        noResultsText: "Nenhum resultado encontrado",
        noChoicesText: "Nenhuma opção disponível",
        itemSelectText: false,
        addItemText: (value) => `Pressione Enter para adicionar <b>"${value}"</b>`,
        removeItemLabelText: (value) => `Remover item: ${value}`,
        maxItemText: (max) => `Somente ${max} valores podem ser adicionados`,
        classNames: []
    });

    // Inicializa o mapa
    async function initMap() {
        const { Map } = await loader.importLibrary("maps");

        const mapOptions = {
            center: { lat: -15.8267, lng: -47.9218 }, // valor padrão (Brasília)
            zoom,
            styles: mapStyles,
            disableDefaultUI: false
        };

        map = new Map(mapEl.get(0), mapOptions);
        loadMarkers();
    }

    // Renderiza um marcador
    function renderMarker(markerObj) {
        new google.maps.Marker(markerObj);
    }

    // Carrega os locais via AJAX
    function loadMarkers() {
        $.ajax({
            url: apg_map_ajax.ajax_url,
            type: "POST",
            dataType: "json",
            data: {
                action: "apg_map_pins_get_locations",
                security: apg_map_ajax.security
            },
            success: function (response) {
                if (!response.success || !response.data.map) return;

                const bounds = new google.maps.LatLngBounds();

                response.data.map.forEach(data => {
                    const lat = parseFloat(data.lat);
                    const lng = parseFloat(data.lng);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const svgMarker = {
                        path: "M12 2 C8.13 2 5 5.13 5 9 c0 5.25 7 13 7 13 s7-7.75 7-13 c0-3.87-3.13-7-7-7 Z M12 9 m-2,0 a2,2 0 1,0 4,0 a2,2 0 1,0 -4,0",
                        fillColor: "#522aab",   // roxo Glia
                        fillOpacity: 1,         // preenchimento sólido
                        strokeColor: "#FFFFFF", // contraste elegante
                        strokeWeight: 2,      // contorno mais fino
                        scale: 2,             // menor tamanho do pin
                        anchor: new google.maps.Point(12, 24),
                    };

                    renderMarker({
                        map,
                        position: { lat, lng },
                        title: data.title || "Local",
                        icon: svgMarker,
                    });

                    bounds.extend({ lat, lng });
                });

                if (!bounds.isEmpty()) map.fitBounds(bounds);
            },
            error: function (xhr, status, error) {
                console.error("Erro ao carregar locais:", error);
            }
        });
    }

    // Inicializa tudo
    initMap();

})(jQuery);
