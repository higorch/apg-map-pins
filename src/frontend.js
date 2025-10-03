import { setOptions, importLibrary } from "@googlemaps/js-api-loader";

setOptions({ key: "AIzaSyCYnLD0MCRlcATwL-I10i8LpjrYNbXKEM4" });

const { Map } = await importLibrary("maps");