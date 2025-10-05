import { setOptions, importLibrary } from "@googlemaps/js-api-loader";

(async function ($) {

    let map = $("#apgmappins-map");
    let key = map.data('key');

    setOptions({ key: key });

    const { Map } = await importLibrary("maps");    

    // Now you can use `Map` here to initialize your Google Map

})(jQuery);
