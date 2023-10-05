function adjustColumns() {
    const holdingPeriodValue = parseInt(document.getElementById('holdingPeriod').value);
    const thead = document.getElementById('cashflow-thead');
    const tbody = document.getElementById('cashflow-tbody');

    // Clear existing columns in thead
    thead.innerHTML = `<tr>
                        <th>Item</th>
                        <th>Year 0</th>
                       </tr>`;

    // Add columns based on holdingPeriodValue in thead
    if (holdingPeriodValue === 1) {
        thead.querySelector('tr').innerHTML += `<th>Year 1</th>`;
    }

    if (holdingPeriodValue === 2) {
        thead.querySelector('tr').innerHTML += `<th>Year 1</th>`;
        thead.querySelector('tr').innerHTML += `<th>Year 2</th>`;
    }

    if (holdingPeriodValue > 2) {
        thead.querySelector('tr').innerHTML += `<th>Year 1 - ${holdingPeriodValue - 1} (annually)</th>`;
        thead.querySelector('tr').innerHTML += `<th>Year ${holdingPeriodValue}</th>`;
    }

    // Adjusting tbody
    tbody.querySelectorAll('tr').forEach(row => {
        const label = row.querySelector('td').textContent;
        const baseKey = Array.from(row.children).find(td => td.id).id.replace(/Year\d+$/, '');
        
        // Reset the row
        row.innerHTML = `<td>${label}</td><td id="${baseKey}Year0"></td>`;

        if (holdingPeriodValue === 1) {
            row.innerHTML += `<td id="${baseKey}Year1"></td>`;
            // row.innerHTML += `<td id="${baseKey}Year2"></td>`;
        }

        if (holdingPeriodValue >= 2) {
            row.innerHTML += `<td id="${baseKey}Year1"></td>`;
            row.innerHTML += `<td id="${baseKey}YearN"></td>`;
        }
    });

    // Log the table structure
    // console.log('Current Table Structure:');
    // console.log(document.getElementById('cashflow-thead').outerHTML);
    // console.log(document.getElementById('cashflow-tbody').outerHTML);
}
