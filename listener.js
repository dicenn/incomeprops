document.addEventListener("DOMContentLoaded", function() {
    const bodyDataPage = document.body.getAttribute("data-page");


    if (bodyDataPage === "cashflow") {
        // You can skip checking for page type since you're including this script only for cashflow page
        const holdingPeriodInput = document.getElementById('holdingPeriod');
            
        if (holdingPeriodInput) {
            holdingPeriodInput.addEventListener('input', function() {
                adjustColumns();
            });
        }

        // Attach event listeners to all input fields
        let formFields = document.querySelectorAll("#financialAnalysisForm input");
        console.log("Fetched form fields:", formFields);
        
        formFields.forEach(field => {
            field.addEventListener("input", function() {
                console.log("Input changed:", field.id, field.value);
                updateCashFlowAnalysisTable();
            });
        });

        // Call the update function initially
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
