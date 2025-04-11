document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.querySelector("#accountingTableBody");
    const addRowButton = document.getElementById("addAccountRow");

    // Function to update totals
    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        // Get all debit and credit inputs except the footer row
        const debitInputs = document.querySelectorAll('tbody .debit-amount');
        const creditInputs = document.querySelectorAll('tbody .credit-amount');

        // Sum up debit amounts
        debitInputs.forEach(input => {
            totalDebit += parseFloat(input.value || 0);
        });

        // Sum up credit amounts
        creditInputs.forEach(input => {
            totalCredit += parseFloat(input.value || 0);
        });

        // Calculate the difference (total debit - total credit)
        const difference = totalDebit - totalCredit;

        // Update the footer row's credit field with the difference
        const footerCreditInput = document.querySelector('tfoot .credit-amount');
        if (footerCreditInput) {
            footerCreditInput.value = difference.toFixed(2);
        }
    }

    // Function to create a new row
    function createNewRow() {
        const newRow = document.createElement("tr");
        
        // Clone the account select options
        const accountSelect = document.querySelector('select[name="account_titles[]"]').cloneNode(true);
        accountSelect.name = "account_titles[]";
        accountSelect.className = "form-control account-select";
        accountSelect.value = ""; // Reset selection

        // Create debit input
        const debitInput = document.createElement("input");
        debitInput.type = "number";
        debitInput.className = "form-control debit-amount";
        debitInput.name = "debit_amounts[]";
        debitInput.step = "0.01";

        // Create credit input
        const creditInput = document.createElement("input");
        creditInput.type = "number";
        creditInput.className = "form-control credit-amount";
        creditInput.name = "credit_amounts[]";
        creditInput.step = "0.01";

        // Create delete button
        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.className = "btn btn-danger btn-sm delete-row";
        deleteButton.innerHTML = "Delete";
        deleteButton.addEventListener("click", function() {
            newRow.remove();
            calculateTotals();
        });

        // Create cells
        const accountCell = document.createElement("td");
        accountCell.colSpan = 2;
        accountCell.appendChild(accountSelect);

        const debitCell = document.createElement("td");
        debitCell.appendChild(debitInput);

        const creditCell = document.createElement("td");
        creditCell.appendChild(creditInput);

        const deleteCell = document.createElement("td");
        deleteCell.appendChild(deleteButton);

        newRow.appendChild(accountCell);
        newRow.appendChild(debitCell);
        newRow.appendChild(creditCell);
        newRow.appendChild(deleteCell);

        // Add event listeners
        debitInput.addEventListener("input", function() {
            if (this.value && parseFloat(this.value) > 0) {
                creditInput.value = ''; // Clear credit when debit has value
            }
            calculateTotals();
        });

        creditInput.addEventListener("input", function() {
            if (this.value && parseFloat(this.value) > 0) {
                debitInput.value = ''; // Clear debit when credit has value
            }
            calculateTotals();
        });

        return newRow;
    }

    // Function to create a custom dropdown for account selection
    function createCustomDropdown(selectElement) {
        const dropdownContainer = document.createElement('div');
        dropdownContainer.className = 'custom-dropdown';
        
        const dropdownToggle = document.createElement('div');
        dropdownToggle.className = 'dropdown-toggle';
        dropdownToggle.textContent = selectElement.options[selectElement.selectedIndex]?.text || 'Select Account';
        
        const dropdownMenu = document.createElement('div');
        dropdownMenu.className = 'dropdown-menu';
        
        const searchBox = document.createElement('div');
        searchBox.className = 'search-box';
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search...';
        searchBox.appendChild(searchInput);
        dropdownMenu.appendChild(searchBox);
        
        const dropdownItems = document.createElement('div');
        dropdownItems.className = 'dropdown-items';
        
        Array.from(selectElement.options).forEach(option => {
            if (option.value === '') return;
            const dropdownItem = document.createElement('div');
            dropdownItem.className = 'dropdown-item';
            dropdownItem.dataset.value = option.value;
            dropdownItem.dataset.uacs = option.getAttribute('data-uacs');
            dropdownItem.dataset.title = option.getAttribute('data-title');
            
            // Include account code in the display text
            const accountCode = option.getAttribute('data-uacs') || '';
            const displayText = accountCode ? `${option.text} (${accountCode})` : option.text;
            dropdownItem.textContent = displayText;
            
            dropdownItem.addEventListener('click', function() {
                selectElement.value = this.dataset.value;
                dropdownToggle.textContent = displayText;
                dropdownMenu.classList.remove('show');
                
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                selectElement.dispatchEvent(event);
            });
            
            dropdownItems.appendChild(dropdownItem);
        });
        
        dropdownMenu.appendChild(dropdownItems);
        dropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
            if (dropdownMenu.classList.contains('show')) {
                searchInput.focus();
            }
        });
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            dropdownItems.querySelectorAll('.dropdown-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        document.addEventListener('click', function(e) {
            if (!dropdownContainer.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        selectElement.style.display = 'none';
        dropdownContainer.appendChild(dropdownToggle);
        dropdownContainer.appendChild(dropdownMenu);
        selectElement.parentNode.insertBefore(dropdownContainer, selectElement);
        
        return dropdownContainer;
    }

    // Function to filter account titles
    function filterAccountTitles(select, selectedType) {
        const currentValue = select.value;
        Array.from(select.options).forEach(option => {
            if (option.value === "") return; // Skip the "Select Account" option

            const accountTitle = option.getAttribute('data-title')?.toLowerCase() || '';
            const accountCode = option.getAttribute('data-uacs') || '';
            if (selectedType === "cash_advance") {
                option.hidden = !accountTitle.includes('advance');
            } else if (selectedType === "transfer_fund") {
                option.hidden = !(accountTitle.includes('cash') && accountCode.startsWith('10'));
            } else {
                option.hidden = false;
            }
        });

        // Restore selection if it's still valid
        if (currentValue && select.querySelector(`option[value="${currentValue}"]`)) {
            select.value = currentValue;
        }
    }

    // Add new row functionality
    if (addRowButton) {
        addRowButton.addEventListener("click", function () {
            const newRow = createNewRow();
            tableBody.appendChild(newRow);
            
            // Apply custom dropdown to the new select
            setTimeout(function() {
                const newSelect = newRow.querySelector('select[name="account_titles[]"]');
                if (newSelect && !newSelect.classList.contains('custom-dropdown-processed')) {
                    newSelect.classList.add('custom-dropdown-processed');
                    createCustomDropdown(newSelect);
                    
                    // Filter account titles for the new row
                    const orsTypeSelect = document.getElementById("ors_type");
                    if (orsTypeSelect) {
                        filterAccountTitles(newSelect, orsTypeSelect.value);
                    }
                }
            }, 100);
        });
    }

    // Add delete buttons to existing rows
    function addDeleteButtonsToExistingRows() {
        const existingRows = document.querySelectorAll("#accountingTableBody tr");
        
        existingRows.forEach(row => {
            if (!row.querySelector('.delete-row')) {
                const deleteButton = document.createElement("button");
                deleteButton.type = "button";
                deleteButton.className = "btn btn-danger btn-sm delete-row";
                deleteButton.innerHTML = "Delete";
                
                deleteButton.addEventListener("click", function() {
                    row.remove();
                    calculateTotals();
                });
                
                const deleteCell = document.createElement("td");
                deleteCell.appendChild(deleteButton);
                
                row.appendChild(deleteCell);
            }
        });
    }

    // Apply custom dropdowns to existing selects
    function applyCustomDropdowns() {
        const accountSelects = document.querySelectorAll('select[name="account_titles[]"]');
        
        accountSelects.forEach(select => {
            if (!select.classList.contains('custom-dropdown-processed')) {
                select.classList.add('custom-dropdown-processed');
                createCustomDropdown(select);
            }
        });
    }

    // Initialize
    addDeleteButtonsToExistingRows();
    applyCustomDropdowns();

    // Add event listener for DV type changes
    const orsTypeSelect = document.getElementById('ors_type');
    if (orsTypeSelect) {
        orsTypeSelect.addEventListener('change', function () {
            const selectedType = this.value;
            const accountSelects = document.querySelectorAll('.account-select');
            accountSelects.forEach(select => {
                filterAccountTitles(select, selectedType);
            });
        });
    }
});