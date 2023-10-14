document.addEventListener("DOMContentLoaded", function() {
    const bodyDataPage = document.body.getAttribute("data-page");

    if (bodyDataPage === "cashflow") {

        console.log(document.getElementById('recalculateButton')); // It should print the button element

        document.getElementById('recalculateButton').addEventListener('click', function() {
            console.log("Button was clicked");
            if (validateInputs()) {
                adjustColumns();
                updateCashFlowAnalysisTable();
            }
        });        

        document.getElementById("restore-button").addEventListener("click", function() {
            location.reload();
        });

        updateCashFlowAnalysisTable();
    }
    
    else if (bodyDataPage === "landing") {
        propertiesArray.forEach((property) => {
            let encodedAddressJs = encodeURIComponent(property.Address).replace(/%20/g, '+');
            // console.log("JavaScript encoded:", encodedAddressJs);
            let metrics = calculatePropertyMetrics(property);
            // console.log("Metrics for:", property.Address, metrics);

            let propertyContainer = document.querySelector(`.property[data-address="${encodedAddressJs}"]`);
            // console.log("Container for", property.Address, propertyContainer);

            if (propertyContainer) {
                // Update the displayed stats
                propertyContainer.querySelector('.values-row .annualized-return').textContent = `${metrics.irr.toFixed(1)}%`;
                propertyContainer.querySelector('.values-row .cap-rate').textContent = `${(metrics.capRate * 100).toFixed(1)}%`;

                const formattedMonthlyCashFlow = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(metrics.monthlyCashFlow);
                
                propertyContainer.querySelector('.values-row .monthly-cash-flow').textContent = formattedMonthlyCashFlow;   
            }
        });
    }
});