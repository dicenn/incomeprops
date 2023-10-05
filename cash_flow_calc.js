const finance = new Finance();
console.log("cash_flow_calc.js loaded");

function updateCashFlowAnalysisTable() {
    console.log("Updating Cash Flow Analysis Table..."); // Debugging statement
    // Recalculate totalRent first
    let unitInputs = document.querySelectorAll(".units");
    let rentPerUnitInputs = document.querySelectorAll(".rent-per-unit");
    let newTotalRent = 0;

    for (let i = 0; i < unitInputs.length; i++) {
        let units = parseFloat(unitInputs[i].value) || 0; // if not a number, default to 0
        let rentPerUnit = parseFloat(rentPerUnitInputs[i].value) || 0;
        let totalRentForType = units * rentPerUnit;

        document.querySelectorAll(".total-rent")[i].textContent = totalRentForType.toFixed(2);
        newTotalRent += totalRentForType;
    }

    document.getElementById("totalRent").textContent = newTotalRent.toFixed(2);
    totalRent = newTotalRent;  // Update the global totalRent variable
    document.getElementById("monthlyRent").value = totalRent.toFixed(2);

    // Pull values and compute as earlier
    let purchasePrice = parseFloat(document.getElementById("purchasePrice").value);
    let downpaymentPercentage = parseFloat(document.getElementById("downpaymentPercentage").value);
    let monthlyRent = parseFloat(document.getElementById("monthlyRent").value);
    let propertyTax = parseFloat(document.getElementById("propertyTax").value);
    let appreciationPercentage = parseFloat(document.getElementById("appreciationPercentage").value);
    let holdingPeriod = parseFloat(document.getElementById("holdingPeriod").value);
    let mortgageRate = parseFloat(document.getElementById("mortgageRate").value);
    let renovationCosts = parseFloat(document.getElementById("renovationCosts").value);
    let mortgageTerm = parseFloat(document.getElementById("mortgageTerm").value);
    let landTransferTaxPercentage = parseFloat(document.getElementById("landTransferTaxPercentage").value);
    let monthlyCapexReserve = parseFloat(document.getElementById("monthlyCapexReserve").value);
    let annualInsurance = parseFloat(document.getElementById("annualInsurance").value);
    let sellingCosts = parseFloat(document.getElementById("sellingCosts").value);
    let vacancyAllowance = parseFloat(document.getElementById("vacancyAllowance").value);

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
    // console.log(generateCashFlows(holdingPeriod, netCashFlow0, netCashFlow1, netCashFlowN))
    

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

    const proceedsFromSaleElementId = holdingPeriod >= 2 ? 'proceedsFromSaleYearN' : `proceedsFromSaleYear1`;
    updateElement(proceedsFromSaleElementId, proceedsFromSale);

    for (let key in dataMap) {
        // updateElement(`${key}Year0`, dataMap[key]);
        updateElement(`${key}Year1`, dataMap[key]);
        updateElement(`${key}YearN`, dataMap[key]);
    }

    // updating the investment summary table
    document.getElementById('irrValue').textContent = `${irr.toFixed(1)}%`;
    document.getElementById('noiValue').textContent = `$${noi.toFixed(0)}`;
    document.getElementById('capRateValue').textContent = `${(capRate * 100).toFixed(1)}%`;
    document.getElementById('monthlyCashFlowValue').textContent = `$${monthlyCashFlow.toFixed(0)}`;
    document.getElementById('initialInvestmentValue').textContent = `$${initialInvestment.toFixed(0)}`;
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
        element.textContent = "$" + value.toFixed(0);
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