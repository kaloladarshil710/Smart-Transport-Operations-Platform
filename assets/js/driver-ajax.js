/** Driver AJAX namespace for future progressive enhancements. */
window.TransitOps=window.TransitOps||{};window.TransitOps.driverRequest=(url,data)=>fetch(url,{method:'POST',body:data,headers:{Accept:'application/json'}}).then(response=>response.json());
