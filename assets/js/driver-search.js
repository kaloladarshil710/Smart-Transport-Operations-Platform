/** Debounced driver-autocomplete helper for assignment fields. */
window.TransitOps=window.TransitOps||{};window.TransitOps.searchDrivers=function(query){return fetch('/ajax/driver_search.php?q='+encodeURIComponent(query),{headers:{Accept:'application/json'}}).then(response=>response.json());};
