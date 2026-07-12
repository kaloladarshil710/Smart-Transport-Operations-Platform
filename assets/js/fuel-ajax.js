/** Fuel JSON helper. */
window.TransitOps=window.TransitOps||{};window.TransitOps.saveFuel=data=>fetch('/ajax/fuel.php',{method:'POST',body:data}).then(r=>r.json());
