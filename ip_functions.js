const finance = new Finance();
console.log("cash_flow_calc.js loaded");

function updateCashFlowAnalysisTable() {
    console.log("Updating Cash Flow Analysis Table...");
    let totalRent = 0;
    let totalUnitsCount = 0;

    document.querySelectorAll(".rent-row").forEach(row => {
        let units = parseFloat(row.querySelector(".units").value) || 0;
        let rentPerUnitInput = row.querySelector(".rent-per-unit");
        let rentPerUnit = parseFloat(rentPerUnitInput.value) || 0;
        let totalRentForType = units * rentPerUnit;

        rentPerUnitInput.value = units ? rentPerUnit : '';
        row.querySelector(".total-rent").textContent = units ? formatCurrency(totalRentForType.toFixed(2)) : '';  /* 3. Format to currency */
        
        totalRent += totalRentForType;
        totalUnitsCount += units;
    });

    document.getElementById("totalRentDisplay").textContent = formatCurrency(totalRent.toFixed(2));
    document.getElementById("monthlyRent").value = totalRent.toFixed(2);

    // document.getElementById("totalRent").textContent = formatCurrency(totalRent.toFixed(2)); /* 3. Format to currency */
    // document.getElementById("monthlyRent").value = formatCurrency(totalRent.toFixed(2)); /* 3. Format to currency */
    document.getElementById("totalUnits").textContent = totalUnitsCount;

    // Pull values and compute as earlier
    let purchasePrice = parseFloat(document.getElementById("purchasePrice").value);
    let downpaymentPercentage = parseFloat(document.getElementById("downpaymentPercentage").value) / 100;
    let monthlyRent = parseFloat(document.getElementById("monthlyRent").value);
    let propertyTax = parseFloat(document.getElementById("propertyTax").value);
    let appreciationPercentage = parseFloat(document.getElementById("appreciationPercentage").value) / 100;
    let holdingPeriod = parseFloat(document.getElementById("holdingPeriod").value);
    let mortgageRate = parseFloat(document.getElementById("mortgageRate").value) / 100;
    let renovationCosts = parseFloat(document.getElementById("renovationCosts").value);
    let mortgageTerm = parseFloat(document.getElementById("mortgageTerm").value);
    let landTransferTaxPercentage = parseFloat(document.getElementById("landTransferTaxPercentage").value) / 100;
    let monthlyCapexReserve = parseFloat(document.getElementById("monthlyCapexReserve").value);
    let annualInsurance = parseFloat(document.getElementById("annualInsurance").value);
    let sellingCosts = parseFloat(document.getElementById("sellingCosts").value) / 100;
    let vacancyAllowance = parseFloat(document.getElementById("vacancyAllowance").value) / 100;

    console.log(holdingPeriod)

    // Calcs for all of the line items in the cash flow table
    let initialInvestment = purchasePrice * downpaymentPercentage + landTransferTaxPercentage * purchasePrice + renovationCosts;
    let annualMortgageExpenses = PMT(mortgageRate, mortgageTerm, purchasePrice * (1 - downpaymentPercentage));
    let annualNonMortgageExpenses = propertyTax + monthlyCapexReserve * 12 + annualInsurance;
    let income = monthlyRent * 12 * (1 - vacancyAllowance)
    let netIncome = income - annualMortgageExpenses - annualNonMortgageExpenses
    let proceedsFromSale =
        FV_Annuity(purchasePrice, downpaymentPercentage, mortgageRate, holdingPeriod, mortgageTerm)
        + (purchasePrice * downpaymentPercentage)
        + ((purchasePrice + renovationCosts) * Math.pow((1 + appreciationPercentage),holdingPeriod) - purchasePrice)
        - ((purchasePrice + renovationCosts) * Math.pow((1 + appreciationPercentage),holdingPeriod) * sellingCosts)
    let netCashFlow0 = -initialInvestment
    let netCashFlow1 = netIncome + (holdingPeriod === 1 ? proceedsFromSale : 0)
    let netCashFlowN = netIncome + proceedsFromSale

    // investment summary table calcs
    let noi = monthlyRent * (1 - vacancyAllowance) * 12 - annualNonMortgageExpenses;
    let capRate = noi / purchasePrice;
    let monthlyCashFlow = netIncome / 12

    // cash inflows and cash outflows totals rows values
    let cashOutFlowsTotalYear0 = -initialInvestment
    let cashOutFlowsTotalYear1 = -annualMortgageExpenses + -annualNonMortgageExpenses
    let cashOutFlowsTotalYearN = -annualMortgageExpenses + -annualNonMortgageExpenses
    let cashInFlowsTotalYear1 = income
    let cashInFlowsTotalYearN = income + proceedsFromSale
    // console.log(generateCashFlows(holdingPeriod, netCashFlow0, netCashFlow1, netCashFlowN))
    
    updateElement('outflowsYear0', cashOutFlowsTotalYear0);
    updateElement('outflowsYear1', cashOutFlowsTotalYear1);
    updateElement('outflowsYearN', cashOutFlowsTotalYearN);
    updateElement('inflowsYear1', cashInFlowsTotalYear1);
    updateElement('inflowsYearN', cashInFlowsTotalYearN);

    if (generateCashFlows(holdingPeriod, netCashFlow0, netCashFlow1, netCashFlowN).length === 2) {
        irr = (netCashFlow1 / -netCashFlow0) - 1;
        irr *= 100;
    } else {
        try {
            irr = finance.IRR.apply(null,generateCashFlows(holdingPeriod, netCashFlow0, netCashFlow1, netCashFlowN));
        } catch (error) {
            console.warn("Failed to calculate IRR:", error.message);
            irr = null; // Or any other fallback value
        }
    }

    // Update table cells
    const dataMap = {
        mortgageExpenses: annualMortgageExpenses,
        expenses: annualNonMortgageExpenses,
        income: income,
        netIncome: netIncome
    };

    // Handle the exceptions separately
    updateElement('initialInvestmentYear0', -initialInvestment);
    updateElement('netCashFlowYear0', netCashFlow0);
    updateElement('netCashFlowYear1', netCashFlow1);
    updateElement('netCashFlowYearN', netCashFlowN);

    updateElement('mortgageExpensesYear1',-annualMortgageExpenses)
    updateElement('mortgageExpensesYearN',-annualMortgageExpenses)
    updateElement('expensesYear1',-annualNonMortgageExpenses)
    updateElement('expensesYearN',-annualNonMortgageExpenses)
    updateElement('incomeYear1',income)
    updateElement('incomeYearN',income)

    const proceedsFromSaleElementId = holdingPeriod >= 2 ? 'proceedsFromSaleYearN' : `proceedsFromSaleYear1`;
    updateElement(proceedsFromSaleElementId, proceedsFromSale);

    // for (let key in dataMap) {
    //     // updateElement(`${key}Year0`, dataMap[key]);
    //     updateElement(`${key}Year1`, dataMap[key]);
    //     updateElement(`${key}YearN`, dataMap[key]);
    // }

    // updating the investment summary table
    document.getElementById('noiValue').textContent = formatCurrency(noi);
    document.getElementById('monthlyCashFlowValue').textContent = formatCurrency(monthlyCashFlow);
    document.getElementById('initialInvestmentValue').textContent = formatCurrency(initialInvestment);
    document.getElementById('irrValue').textContent = `${irr.toFixed(1)}%`;
    document.getElementById('capRateValue').textContent = `${(capRate * 100).toFixed(1)}%`;
}

// pmt function for calculating the annual mortgage payment
function PMT(rate, nper, pv) {
    return rate * pv / (1 - Math.pow((1 + rate), -nper));
}

// fv_annuity function for calculating mortgage principal paid down by the end of the holding period
function FV_Annuity(purchasePrice, downpaymentPercentage, mortgageRate, nper, mortgageTerm) {
    // Calculate initial PMT
    let pmtValue = PMT(mortgageRate, mortgageTerm, purchasePrice * (1 - downpaymentPercentage));
    let balance = purchasePrice * (1 - downpaymentPercentage);
    let sum = 0;

    for (let i = 0; i < nper; i++) {
        let interestForTheYear = balance * mortgageRate;
        balance -= pmtValue; // Deduct the PMT value from the balance
        balance += interestForTheYear; // Add the interest for the year
    }
    return purchasePrice * (1 - downpaymentPercentage) - balance;
}

// function that updates the table elements based on changing user inputs
function updateElement(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = formatCurrency(value);
    }
}

// generate the cash flows array that will be passed to the IRR function
function generateCashFlows(holdingPeriod, netCashFlow0, netCashFlow1, netCashFlowN) {
    let cashFlows = [netCashFlow0];

    if (holdingPeriod === 1) {
        cashFlows.push(netCashFlow1);
    } else {
        for (let i = 1; i < holdingPeriod; i++) {
            cashFlows.push(netCashFlow1);
        }
        cashFlows.push(netCashFlowN);
    }

    return cashFlows;
}

function calculatePropertyMetrics(property) {
    let address = property.Address
    let purchasePrice = parseFloat(property.Price)
    // console.log(purchasePrice)
    let downpaymentPercentage = 0.2
    let monthlyRent = property['3_bedroom_units'] * property['3_bedroom_rent'] + 
                  property['2_bedroom_units'] * property['2_bedroom_rent'] + 
                  property['1_bedroom_units'] * property['1_bedroom_rent'] + 
                  property['0_bedroom_units'] * property['0_bedroom_rent'];
    // let propertyTax = intval(preg_replace('/[,.]/', '', substr($property['Property taxes'], 1, 6)))
    let propertyTax = parseInt(property['Property taxes'].substring(1, 7).replace(/[,.]/g, ''));
    let appreciationPercentage = 0.06
    let holdingPeriod = 5
    let mortgageRate = 0.06
    let renovationCosts = 0
    let mortgageTerm = 30
    let landTransferTaxPercentage = 0.04
    let monthlyCapexReserve = 400
    let annualInsurance = 1500
    let sellingCosts = 0.04
    let vacancyAllowance = 0.05

    // Calcs for all of the line items in the cash flow table
    let initialInvestment = purchasePrice * downpaymentPercentage + landTransferTaxPercentage * purchasePrice + renovationCosts;
    let annualMortgageExpenses = PMT(mortgageRate, mortgageTerm, purchasePrice * (1 - downpaymentPercentage));
    let annualNonMortgageExpenses = propertyTax + monthlyCapexReserve * 12 + annualInsurance;
    let income = monthlyRent * 12 * (1 - vacancyAllowance)
    let netIncome = income - annualMortgageExpenses - annualNonMortgageExpenses
    let proceedsFromSale =
        FV_Annuity(purchasePrice, downpaymentPercentage, mortgageRate, holdingPeriod, mortgageTerm)
        + (purchasePrice * downpaymentPercentage)
        + ((purchasePrice + renovationCosts) * Math.pow((1 + appreciationPercentage),holdingPeriod) - purchasePrice)
        - ((purchasePrice + renovationCosts) * Math.pow((1 + appreciationPercentage),holdingPeriod) * sellingCosts)
    let netCashFlow0 = -initialInvestment
    let netCashFlow1 = netIncome + (holdingPeriod === 1 ? proceedsFromSale : 0)
    let netCashFlowN = netIncome + proceedsFromSale



    let noi = monthlyRent * (1 - vacancyAllowance) * 12 - annualNonMortgageExpenses;
    let capRate = noi / purchasePrice;
    let monthlyCashFlow = netIncome / 12
    // console.log(generateCashFlows(holdingPeriod, netCashFlow0, netCashFlow1, netCashFlowN))
    irr = finance.IRR.apply(null,generateCashFlows(holdingPeriod, netCashFlow0, netCashFlow1, netCashFlowN));

    return {
        address: address,
        irr: irr,
        capRate: capRate,
        monthlyCashFlow: monthlyCashFlow
    };
}

function calculateTotal(selector) {
    let inputs = document.querySelectorAll(selector);
    return [...inputs].reduce((acc, input) => acc + (parseFloat(input.value) || 0), 0);
}

function formatCurrency(value) {
    // Ensure the value is a number
    const num = parseFloat(value);
    
    // Check if it's NaN (Not a Number)
    if (isNaN(num)) return value;

    // Convert the number to a string with thousands separators and no decimal places
    let formatted = Math.abs(num).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    // Add the dollar sign, and a minus sign before the dollar sign if it's negative
    return num < 0 ? '-$' + formatted : '$' + formatted;
}

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
        thead.querySelector('tr').innerHTML += `<th>Year 1 - ${holdingPeriodValue - 1} <span class="cashflow-sub-heading">(annually)</span></th>`;
        // thead.querySelector('tr').innerHTML += `<th>Year 1 - ${holdingPeriodValue - 1} (annually)</th>`;
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

function nextPhoto() {
    if (currentPhotoIndex < photos.length - 1) {  
        currentPhotoIndex++;
        document.getElementById('currentPhoto').src = photos[currentPhotoIndex];
    }
}

function prevPhoto() {
    if (currentPhotoIndex > 0) { 
        currentPhotoIndex--;
        document.getElementById('currentPhoto').src = photos[currentPhotoIndex];
    }
}

function validateInputs() {
    // Clear any previous error messages
    document.getElementById("errorMessage").textContent = "";

    // Example ranges (replace with your actual ranges)
    const inputRanges = {
        "purchasePrice": [0, 10000000],
        "purchasePrice": [0,100000000],
        "downpaymentPercentage": [0,100],
        "appreciationPercentage": [0,100],
        "holdingPeriod": [1,50],
        "mortgageRate": [0.01,100],
        "renovationCosts": [0,10000000],
        "mortgageTerm": [1,30],
        "landTransferTaxPercentage": [0,100],
        "monthlyCapexReserve": [0,100000],
        "annualInsurance": [0,100000],
        "sellingCosts": [0,100],
        "vacancyAllowance": [0,100]
    };

    let isValid = true;  // Assume all inputs are valid at the start

    for (let inputID in inputRanges) {
        let inputElement = document.getElementById(inputID);
        let value = inputElement.value.trim();  // Remove whitespace

        // Check if value is empty or not a number
        if (value === "" || isNaN(parseFloat(value))) {
            inputElement.style.backgroundColor = "#f4ccccff";  // Red fill
            inputElement.style.color = "#cc0000";  // Red text
            isValid = false;
        }
        // Check if number is outside the valid range
        else if (parseFloat(value) < inputRanges[inputID][0] || parseFloat(value) > inputRanges[inputID][1]) {
            inputElement.style.backgroundColor = "#f4ccccff";  // Red fill
            inputElement.style.color = "#cc0000";  // Red text
            isValid = false;
        }
        // If valid, reset any previous styles
        else {
            inputElement.style.backgroundColor = "";  // Reset fill
            inputElement.style.color = "";  // Reset text color
        }
    }

    if (!isValid) {
        const errorMsgElement = document.getElementById("errorMessage");
        errorMsgElement.textContent = "Please check the highlighted input(s); make sure it's a number or within a reasonable range.";
        errorMsgElement.style.fontSize = "0.8rem"; // Smaller font size
    }

    return isValid;
}