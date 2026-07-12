/** Fuel numeric validation. */
document.addEventListener('input',event=>{if(event.target.matches('[data-fuel-liters],[data-fuel-price]'))event.target.setCustomValidity(Number(event.target.value)>=0?'':'Value cannot be negative.');});
