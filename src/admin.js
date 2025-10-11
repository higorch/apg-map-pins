(function ($) {

    $(".color-picker").wpColorPicker();

    let territoryEl = $('select[name="apg_map_pin_territory"]');

    const choicesInstance = new Choices(territoryEl.get(0), {
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

})(jQuery);