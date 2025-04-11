document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.querySelector("#accounting-table-body");
    const addRowContainer = document.querySelector("#add-row-container");

    // Function to update total
    function updateTotal() {
        let total = 0;
        document.querySelectorAll(".amount-input").forEach(function (input) {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById("total_amount").value = total.toFixed(2);
    }

    // Function to create a new row
    function createNewRow() {
        const newRow = document.createElement("tr");
        newRow.classList.add("entry-row");

        // Clone the account select options
        const accountSelect = document.querySelector('select[name="account_id[]"]').cloneNode(true);
        accountSelect.name = "account_id[]";
        accountSelect.className = "form-control account-select";
        accountSelect.value = ""; // Reset selection

        // Create account code input
        const codeInput = document.createElement("input");
        codeInput.type = "text";
        codeInput.className = "form-control account-code";
        codeInput.name = "account_code[]";
        codeInput.readOnly = true;

        // Create amount input
        const amountInput = document.createElement("input");
        amountInput.type = "number";
        amountInput.className = "form-control amount-input";
        amountInput.name = "amount[]";
        amountInput.step = "0.01";
        amountInput.required = true;

        // Create delete button
        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.className = "btn btn-danger btn-sm delete-row";
        deleteButton.innerHTML = "Delete";
        deleteButton.addEventListener("click", function() {
            newRow.remove();
            updateTotal();
        });

        // Create cells
        const accountCell = document.createElement("td");
        accountCell.colSpan = 2;
        accountCell.appendChild(accountSelect);

        const codeCell = document.createElement("td");
        codeCell.appendChild(codeInput);

        const amountCell = document.createElement("td");
        amountCell.appendChild(amountInput);

        const deleteCell = document.createElement("td");
        deleteCell.appendChild(deleteButton);

        newRow.appendChild(accountCell);
        newRow.appendChild(codeCell);
        newRow.appendChild(amountCell);
        newRow.appendChild(deleteCell);

        // Add event listeners
        amountInput.addEventListener("input", updateTotal);
        
        accountSelect.addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.account_code) {
                codeInput.value = selectedOption.dataset.account_code;
            } else {
                codeInput.value = "";
            }
        });

        return newRow;
    }

    // Add new row functionality
    const addRowButton = document.getElementById("addAccountRow");
    if (addRowButton) {
        addRowButton.addEventListener("click", function () {
            const newRow = createNewRow();
            tableBody.insertBefore(newRow, addRowContainer);
            
            // Apply custom dropdown to the new select
            setTimeout(function() {
                const newSelect = newRow.querySelector('select[name="account_id[]"]');
                if (newSelect && !newSelect.classList.contains('custom-dropdown-processed')) {
                    newSelect.classList.add('custom-dropdown-processed');
                    
                    // Create a simple dropdown for the new row
                    const dropdownContainer = document.createElement('div');
                    dropdownContainer.className = 'custom-dropdown';
                    
                    const dropdownToggle = document.createElement('div');
                    dropdownToggle.className = 'dropdown-toggle';
                    dropdownToggle.textContent = 'Select Account';
                    
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
                    
                    Array.from(newSelect.options).forEach(option => {
                        if (option.value === '') return;
                        const dropdownItem = document.createElement('div');
                        dropdownItem.className = 'dropdown-item';
                        dropdownItem.dataset.value = option.value;
                        dropdownItem.dataset.oopapId = option.getAttribute('data-oopap_id');
                        dropdownItem.dataset.accountCode = option.getAttribute('data-account_code');
                        
                        // Include account code in the display text
                        const accountCode = option.getAttribute('data-account_code') || '';
                        const displayText = accountCode ? `${option.text} (${accountCode})` : option.text;
                        dropdownItem.textContent = displayText;
                        
                        dropdownItem.addEventListener('click', function() {
                            newSelect.value = this.dataset.value;
                            dropdownToggle.textContent = displayText;
                            dropdownMenu.classList.remove('show');
                            
                            // Update the account code input
                            const codeInput = newRow.querySelector('.account-code');
                            if (codeInput && this.dataset.accountCode) {
                                codeInput.value = this.dataset.accountCode;
                            }
                            
                            // Trigger change event
                            const event = new Event('change', { bubbles: true });
                            newSelect.dispatchEvent(event);
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
                    
                    newSelect.style.display = 'none';
                    dropdownContainer.appendChild(dropdownToggle);
                    dropdownContainer.appendChild(dropdownMenu);
                    newSelect.parentNode.insertBefore(dropdownContainer, newSelect);
                }
            }, 100);
        });
    }

    // Add delete buttons to existing rows
    function addDeleteButtonsToExistingRows() {
        const existingRows = document.querySelectorAll("#accounting-table-body tr.entry-row");
        
        existingRows.forEach(row => {
            if (!row.querySelector('.delete-row')) {
                const deleteButton = document.createElement("button");
                deleteButton.type = "button";
                deleteButton.className = "btn btn-danger btn-sm delete-row";
                deleteButton.innerHTML = "Delete";
                
                deleteButton.addEventListener("click", function() {
                    row.remove();
                    updateTotal();
                });
                
                const deleteCell = document.createElement("td");
                deleteCell.appendChild(deleteButton);
                
                row.appendChild(deleteCell);
            }
        });
    }

    addDeleteButtonsToExistingRows();

    // Set up event listeners for existing amount inputs
    document.querySelectorAll(".amount-input").forEach(input => {
        input.addEventListener("input", updateTotal);
    });

    // Set up event listeners for existing account selects
    document.querySelectorAll(".account-select").forEach(select => {
        select.addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            const row = this.closest("tr");
            const codeInput = row.querySelector(".account-code");
            
            if (selectedOption && selectedOption.dataset.account_code) {
                codeInput.value = selectedOption.dataset.account_code;
            } else {
                codeInput.value = "";
            }
        });
    });

    // Initialize account codes for existing rows
    document.querySelectorAll(".account-select").forEach(select => {
        const selectedOption = select.options[select.selectedIndex];
        const row = select.closest("tr");
        const codeInput = row.querySelector(".account-code");
        
        if (selectedOption && selectedOption.dataset.account_code) {
            codeInput.value = selectedOption.dataset.account_code;
        }
    });
}); 