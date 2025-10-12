(function ($) {

    $(document).ready(function () {

        $(".color-picker").wpColorPicker();

        let choicesInstance;
        let territoryEl = $('select[name="apg_map_pin_territory"]');

        // Verifica se o elemento existe
        if (territoryEl.length > 0) {
            choicesInstance = new Choices(territoryEl.get(0), {
                removeItemButton: true,
                placeholderValue: "Selecione o território",
                shouldSort: false,
                position: "auto",
                searchResultLimit: 50,
                loadingText: "Carregando...",
                noResultsText: "Nenhum resultado encontrado",
                noChoicesText: "Nenhuma opção disponível",
                itemSelectText: ""
            });
        }

    });

})(jQuery);
