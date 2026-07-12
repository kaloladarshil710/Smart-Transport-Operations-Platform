/** Expense amount validation. */
document.addEventListener('input',event=>{if(event.target.name==='amount')event.target.setCustomValidity(Number(event.target.value)>0?'':'Amount must be greater than zero.');});
