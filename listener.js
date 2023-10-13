document.addEventListener("DOMContentLoaded", function() {
    const bodyDataPage = document.body.getAttribute("data-page");


    if (bodyDataPage === "cashflow") {
        const holdingPeriodInput = document.getElementById('holdingPeriod');
            
        if (holdingPeriodInput) {
            holdingPeriodInput.addEventListener('input', function() {
                adjustColumns();
            });
        }
    
        // Attach event listeners to all input fields in both forms
        let formFieldsOtherInputs = document.querySelectorAll("#financialAnalysisFormOtherInputs input");
        let formFieldsKeyInputs = document.querySelectorAll("#financialAnalysisFormKeyInputs input");
        
        // Combine both NodeList into an array and then iterate
        let allFormFields = [...formFieldsOtherInputs, ...formFieldsKeyInputs];
        
        allFormFields.forEach(field => {
            field.addEventListener("input", updateCashFlowAnalysisTable);
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