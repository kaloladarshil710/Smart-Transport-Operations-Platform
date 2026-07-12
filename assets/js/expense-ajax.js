/** Expense JSON helper. */
window.TransitOps=window.TransitOps||{};window.TransitOps.saveExpense=data=>fetch('/ajax/expense.php',{method:'POST',body:data}).then(r=>r.json());
