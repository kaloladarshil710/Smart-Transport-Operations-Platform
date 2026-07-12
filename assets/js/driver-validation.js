/** Lightweight immediate feedback for safety-score fields. */
document.addEventListener('input',event=>{if(event.target.name==='safety_score'){const value=Number(event.target.value);event.target.setCustomValidity(value>=0&&value<=100?'':'Safety score must be between 0 and 100.');}});
