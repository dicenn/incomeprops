function updateTotal() {
    let rows = document.querySelectorAll('.rent-row');

    rows.forEach(row => {
        let units = parseInt(row.querySelector('.units').value, 10) || 0;
        let rent = parseFloat(row.querySelector('.rent-per-unit').value) || 0;

        let totalRent = units * rent;
        row.querySelector('.total-rent').textContent = totalRent.toFixed(2);
    });
}