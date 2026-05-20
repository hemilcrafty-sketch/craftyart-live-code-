console.log("Virtual.js Loaded")
document.addEventListener("DOMContentLoaded", function () {
    const conditionElement = document.getElementById("conditions");
    conditionPending = true;
    const columnSelects = document.getElementsByClassName('column');
    const operatorSelects = document.getElementsByClassName('operator');

    const moreTemplateVirtualContainer = document.getElementById("moreTemplateVirtualContainer");
    moreTemplateVirtualContainer.querySelector(".save-condition").addEventListener("click", function (event) {
        saveCondition(event, moreTemplateVirtualContainer)
    });

    Array.from(columnSelects).forEach(select => {
        select.addEventListener('change', function () {
            handleColumnChange(this);
        });
    });

    Array.from(operatorSelects).forEach(select => {
        select.addEventListener('change', function () {
            handleOperatorChange(this);
        });
    });
    resetQueryData();

    moreTemplateVirtualContainer.querySelector(".add-sorting").addEventListener("click", function (event) {
        addSorting(event, moreTemplateVirtualContainer);
    });

    moreTemplateVirtualContainer.querySelector(".add-limit").addEventListener("click", function (event) {
        addLimit(event, moreTemplateVirtualContainer);
    });
});

var conditionPending = false;

function resetQueryData() {
    const columnSelects = document.getElementsByClassName('column');
    Array.from(columnSelects).forEach(select => {
        select.selectedIndex = 0;
        select.dispatchEvent(new Event('change'));
    });
}

function getFilteredOperators(columnType, operators, isMultiple) {
    return Object.fromEntries(
        Object.entries(operators).filter(([key]) => {
            if (columnType === 'number') {
                return ['=', '!=', '>', '<', '>=', '<='].includes(key);
            } else if (columnType === 'string') {
                if (isMultiple) {
                    return ['IN', 'NOT IN', 'IS NULL', 'IS NOT NULL'].includes(key);
                }
                return ['=', '!=', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL'].includes(key);
            } else if (columnType === 'boolean')
                return ['='].includes(key);
            else if (columnType === 'datepicker')
                return ['RANGE'].includes(key)
        })
    );
}

function handleColumnChange(selectElement, value = null, selectedOperator = null) {
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let isDependent = selectedOption.dataset.dependent == "1";
    let tableName = selectedOption.dataset.table;
    let columnName = selectedOption.dataset.columnName;
    let columnType = selectedOption.dataset.columnType;
    let columnId = selectedOption.dataset.columnId;
    let isMultiple = selectedOption.dataset.ismultiple == "1";
    let database = selectedOption.dataset.database || '';
    let conditionRow = selectElement.closest(".condition-row");
    let filteredOperators = getFilteredOperators(columnType, operators, isMultiple);
    let operatorDropdown = conditionRow.querySelector('.operator');
    operatorDropdown.innerHTML = "";
    operatorDropdown.disabled = columnType == 'boolean' || columnType == 'datepicker' ? true : false;
    operatorDropdown.style.pointerEvents = columnType == 'boolean' || columnType == 'datepicker' ? "none" : "auto";
    operatorDropdown.style.backgroundColor = columnType == 'boolean' || columnType == 'datepicker' ? "#e9ecef" : "";

    Object.entries(filteredOperators).forEach(([key, value]) => {
        let option = document.createElement("option");
        option.value = key;
        option.textContent = value;
        if (selectedOperator && selectedOperator === option.value) {
            option.selected = true;
        }
        operatorDropdown.appendChild(option);
        if (selectedOperator) operatorDropdown.dispatchEvent(new Event('change'));
    });

    operatorDropdown.disabled = false;

    let valueFieldContainer = conditionRow.querySelector(".value-container");
    console.log("selectElement", selectElement, " selectedOperator ", selectedOperator)
    if (isDependent) {
        const databaseParam = database ? `/${database}` : '';
        console.log(databaseParam);
        const fetchUrl = `${APP_BASE_URL}/get-options/${tableName}/${columnId}/${columnName}${databaseParam}`;
        console.log('=== FETCH DETAILS ===');
        console.log('Table Name:', tableName);
        console.log('Column ID:', columnId);
        console.log('Column Name:', columnName);
        console.log('Database:', database);
        console.log('Database Param:', databaseParam);
        console.log('Full URL:', fetchUrl);
        console.log('====================');

        fetch(fetchUrl)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Fetched data:', data);
                console.log('columnId:', columnId, 'columnName:', columnName);
                createMultiSelectDropdown(valueFieldContainer, data, columnId, columnName, true, value, columnType, selectedOperator);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    } else {
        if (columnType === 'string') {
            const databaseParam = database ? `/${database}` : '';
            fetch(`${APP_BASE_URL}/get-unique-options/Designs/${columnName}${databaseParam}`)
                .then(response => response.json())
                .then(data => {
                    createMultiSelectDropdown(valueFieldContainer, data, columnName, columnName, false, value, columnType, selectedOperator);
                });
        } else {
            if (columnType === 'datepicker' || columnType === 'number')
                createMultiSelectDropdown(valueFieldContainer, null, columnName, columnName, false, value, columnType, selectedOperator);
            else
                createMultiSelectDropdown(valueFieldContainer, [{
                    'value': 0,
                    'text': false
                }, {
                    'value': 1,
                    'text': true
                }], columnName, columnName, false, value, columnType, selectedOperator);
        }
    }
}

function createMultiSelectDropdown(container, data, valueKey, textKey, isDependent, value, columnType, selectedOperator) {
    let dropdownContainer = container.querySelector(".dropdown-container");
    let dropdown = dropdownContainer.querySelector(".value-dropdown");
    if (!dropdown) {
        return;
    }

    // Clear previous selections and options
    dropdown.innerHTML = "";

    // Populate dropdown with new options
    if (columnType !== 'datepicker' && columnType !== 'number') {
        data.forEach(item => {
            let option = document.createElement("option");
            option.value = isDependent ? item[valueKey] : item.value;
            if (columnType === 'boolean') {
                option.textContent = isDependent ? item[textKey] : item.text;
            } else
                option.textContent = isDependent ? item[textKey] : item.text;
            dropdown.appendChild(option);
        });
    }

    // Initialize Select2 for the dropdown
    $(dropdown).select2({
        placeholder: "Select values",
        allowClear: true,
        width: '100%'
    });

    // Reset input fields
    let valueInput = container.querySelector(".value-input");
    let fromValue = container.querySelector(".from-value");
    let toValue = container.querySelector(".to-value");

    if (valueInput) {
        valueInput.value = "";
        valueInput.style.display = "none";
        valueInput.setAttribute("placeholder", "Enter Value");
        valueInput.setAttribute("type", "number");
        if (columnType === "datepicker") {
            valueInput.setAttribute("placeholder", "Select Date");
            valueInput.setAttribute("readonly", "true");
            valueInput.setAttribute("type", "text");
            valueInput.classList.add("form-control", "datetimepicker-range", "value-input");
            valueInput.setAttribute("id", "value-input");
            $(valueInput).daterangepicker();
        } else {
            valueInput.setAttribute("placeholder", "Enter Value");
            valueInput.classList.add("form-control", "value-input");
            valueInput.removeAttribute("readonly");
            valueInput.setAttribute("type", "number");
            valueInput.classList.remove("datetimepicker-range");
            if ($(valueInput).data("daterangepicker")) {
                $(valueInput).daterangepicker("destroy");
            }
            $(valueInput).off();
        }
    }

    if (fromValue) {
        fromValue.value = "";
        fromValue.style.display = "none";
        fromValue.setAttribute("placeholder", "From Value");
    }
    if (toValue) {
        toValue.value = "";
        toValue.style.display = "none";
        toValue.setAttribute("placeholder", "To Value");
    }

    const conditionElement = container.closest("#conditions");
    const newRow = conditionElement.querySelector('.condition-row:last-child');
    const operatorSelect = newRow.querySelector('.operator');

    // Initialize sortable functionality for multi-select
    if (["IN", "NOT IN"].includes(selectedOperator) && columnType !== 'datepicker' && columnType !== 'number') {
        initializeSortableDropdown(dropdown);
    }

    if (value) {
        if (["IN", "NOT IN", "=", "!="].includes(selectedOperator) && columnType !== 'datepicker' && columnType !== 'number') {
            let selectedValues = value.replace(/[\[\]()']/g, "").split(",").map(val => val.trim());
            $(dropdown).val(selectedValues).trigger('change');

            // Update sortable list with selected values
            updateSortableList(dropdown, selectedValues);
        } else {
            if (columnType === 'number') {
                const numValue = parseInt(value.replace("'", ""), 10);
                valueInput.value = numValue;
            } else {
                setTimeout(() => {
                    if (value) {
                        $(valueInput).val(value).trigger("change");
                    }
                }, 10)
            }
            valueInput.style.display = 'block';
        }
    } else {
        operatorSelect.selectedIndex = 0;
        operatorSelect.dispatchEvent(new Event('change'));
    }
}

// Initialize sortable functionality for dropdown
function initializeSortableDropdown(dropdown) {
    const dropdownContainer = dropdown.closest('.dropdown-container');

    // Remove existing sortable container if it exists
    const existingSortable = dropdownContainer.querySelector('.sortable-selected-list');
    if (existingSortable) {
        existingSortable.remove();
    }

    // Create sortable container for selected items
    const sortableContainer = document.createElement('div');
    sortableContainer.className = 'sortable-selected-list mt-2';
    sortableContainer.style.border = '1px solid #ddd';
    sortableContainer.style.borderRadius = '4px';
    sortableContainer.style.padding = '8px';
    sortableContainer.style.minHeight = '40px';
    sortableContainer.style.backgroundColor = '#f8f9fa';

    dropdownContainer.appendChild(sortableContainer);

    // Make the container sortable
    $(sortableContainer).sortable({
        placeholder: "ui-state-highlight",
        update: function (event, ui) {
            updateDropdownFromSortable(dropdown, sortableContainer);
        }
    });

    // Update sortable list when dropdown selection changes
    $(dropdown).on('change', function () {
        const selectedValues = $(this).val() || [];
        updateSortableList(dropdown, selectedValues);
    });

    // Initialize with any pre-selected values
    const selectedValues = $(dropdown).val() || [];
    updateSortableList(dropdown, selectedValues);
}

// Update sortable list based on dropdown selection
function updateSortableList(dropdown, selectedValues) {
    const dropdownContainer = dropdown.closest('.dropdown-container');
    const sortableContainer = dropdownContainer.querySelector('.sortable-selected-list');

    if (!sortableContainer) return;

    // Clear existing sortable items
    sortableContainer.innerHTML = '';

    if (selectedValues.length === 0) {
        const placeholder = document.createElement('div');
        placeholder.className = 'text-muted';
        placeholder.textContent = 'No items selected. Selected items will appear here for reordering.';
        placeholder.style.padding = '10px';
        placeholder.style.textAlign = 'center';
        placeholder.style.fontStyle = 'italic';
        sortableContainer.appendChild(placeholder);
        return;
    }

    // Add selected items to sortable list
    selectedValues.forEach(value => {
        const option = Array.from(dropdown.options).find(opt => opt.value === value);
        if (option) {
            const sortableItem = document.createElement('div');
            sortableItem.className = 'sortable-item ui-state-default';
            sortableItem.style.padding = '8px 12px';
            sortableItem.style.margin = '4px 0';
            sortableItem.style.backgroundColor = 'white';
            sortableItem.style.border = '1px solid #ccc';
            sortableItem.style.borderRadius = '4px';
            sortableItem.style.cursor = 'move';
            sortableItem.style.display = 'flex';
            sortableItem.style.alignItems = 'center';
            sortableItem.style.gap = '8px';
            sortableItem.dataset.value = option.value;

            // Add drag handle
            const dragHandle = document.createElement('span');
            dragHandle.innerHTML = '☰';
            dragHandle.style.cursor = 'move';
            dragHandle.style.color = '#6c757d';

            const itemText = document.createElement('span');
            itemText.textContent = option.text;

            sortableItem.appendChild(dragHandle);
            sortableItem.appendChild(itemText);
            sortableContainer.appendChild(sortableItem);
        }
    });
}

// Update dropdown selection based on sortable list order
function updateDropdownFromSortable(dropdown, sortableContainer) {
    const sortableItems = sortableContainer.querySelectorAll('.sortable-item');
    const orderedValues = Array.from(sortableItems).map(item => item.dataset.value);

    // Update dropdown selected values to maintain selection
    $(dropdown).val(orderedValues).trigger('change');
}

function handleOperatorChange(selectElement) {
    let conditionRow = selectElement.closest(".condition-row");
    let operator = selectElement.value;
    let valueFieldContainer = conditionRow.querySelector(".value-container");
    valueFieldContainer.querySelectorAll('.dropdown-container, .value-input, .from-value, .to-value').forEach(
        input => {
            input.style.display = 'none';
        });
    const columnSelect = conditionRow.querySelector(".column");
    const selectedOption = columnSelect.options[columnSelect.selectedIndex];

    var columnType = selectedOption.dataset.columnType;

    if (operator) {
        if (["BETWEEN", "NOT BETWEEN"].includes(operator)) {
            valueFieldContainer.querySelector('.from-value').style.display = 'block';
            valueFieldContainer.querySelector('.to-value').style.display = 'block';
        } else if (["IN", "NOT IN", "=", "!="].includes(operator) && columnType !== 'number') {
            let dropdownContainer = valueFieldContainer.querySelector(".dropdown-container");
            dropdownContainer.style.display = 'block';
            let dropdown = dropdownContainer.querySelector(".value-dropdown")
            if (["IN", "NOT IN"].includes(operator)) {
                dropdown.setAttribute("multiple", "multiple");
                // Initialize sortable for multi-select
                initializeSortableDropdown(dropdown);
            } else {
                dropdown.removeAttribute("multiple");
                // Remove sortable container for single select
                const sortableContainer = dropdownContainer.querySelector('.sortable-selected-list');
                if (sortableContainer) {
                    sortableContainer.remove();
                }
            }
            $(dropdown).val(null).trigger("change");
        } else {
            valueFieldContainer.querySelector('.value-input').removeAttribute("readonly");
            if (["IS NULL", "IS NOT NULL"].includes(operator)) {
                valueFieldContainer.querySelector('.value-input').setAttribute("readonly", "true");
            }
            valueFieldContainer.querySelector('.value-input').value = '';
            valueFieldContainer.querySelector('.value-input').style.display = 'block';

            // Remove sortable container for non-multi-select operators
            const dropdownContainer = valueFieldContainer.querySelector(".dropdown-container");
            if (dropdownContainer) {
                const sortableContainer = dropdownContainer.querySelector('.sortable-selected-list');
                if (sortableContainer) {
                    sortableContainer.remove();
                }
            }
        }
    } else {
        valueFieldContainer.querySelector('.value-input').removeAttribute("readonly");
        valueFieldContainer.querySelector('.value-input').value = '';
        valueFieldContainer.querySelector('.value-input').style.display = 'block';
        valueFieldContainer.querySelector('.value-input').setAttribute("type", "text");

        // Remove sortable container
        const dropdownContainer = valueFieldContainer.querySelector(".dropdown-container");
        if (dropdownContainer) {
            const sortableContainer = dropdownContainer.querySelector('.sortable-selected-list');
            if (sortableContainer) {
                sortableContainer.remove();
            }
        }
    }
}

function decodeHTMLEntities(text) {
    let temp = document.createElement("textarea");
    temp.innerHTML = text;
    return temp.value;
}

function addSorting(event, parentElement) {
    const sortingRow = event.target.closest(".sorting-row");
    const sortingOption = sortingRow.querySelector(".sorting");
    const selectedOption = sortingOption.options[sortingOption.selectedIndex];
    const column = selectedOption.value;
    const columnName = selectedOption.getAttribute("data-query-column");

    const sortingOrderOption = sortingRow.querySelector(".sorting-order");
    const selectedOrderOption = sortingOrderOption.options[sortingOrderOption.selectedIndex];
    const selectedOrder = selectedOrderOption.value;
    const selectedOrderName = selectedOrderOption.getAttribute("data-sort-name");

    let table = parentElement.querySelector("#conditionsTable tbody");
    let existingRow = [...table.rows].find(row => row.cells[0].textContent === column);

    if (existingRow) {
        alert("This column is already added for sorting!");
        return;
    }

    setValueInTable(column, columnName, 'SORT', selectedOrder, selectedOrderName, null, parentElement);
}

function addLimit(event, parentElement) {
    let table = parentElement.querySelector("#conditionsTable tbody");
    const limitInputVal = parentElement.querySelector("#limit-value").value;
    if (!limitInputVal) {
        alert("Please add limit");
        return;
    }
    let existingRow = [...table.rows].find(row => row.cells[0].textContent === 'limit');

    if (existingRow) {
        alert("Limit is already added!");
        return;
    }
    setValueInTable('limit', 'limit', 'LIMIT', limitInputVal, limitInputVal, null, parentElement);
    parentElement.querySelector("#limit-value").value = ""
}

function saveCondition(event, parentElement) {
    const conditionRow = event.target.closest(".condition-row");
    const columnSelect = conditionRow.querySelector(".column");
    const selectedOption = columnSelect.options[columnSelect.selectedIndex];
    const column = selectedOption.value;
    const columnName = selectedOption.getAttribute("data-query-column");
    const isMultiple = selectedOption.getAttribute("data-ismultiple");
    const columnType = selectedOption.getAttribute("data-column-type");
    let operator = conditionRow.querySelector(".operator").value;
    let valueFieldContainer = conditionRow.querySelector(".value-container");
    const editingRowIndex = event.target.getAttribute("data-editing-row");
    let query = '';
    let value = '';
    let showValue = '';

    if (!column || (!operator && columnType !== 'datepicker')) {
        alert("Please select a column and an operator before saving.");
        return;
    }

    if (["BETWEEN", "NOT BETWEEN"].includes(operator)) {
        const fromValue = valueFieldContainer.querySelector(".from-value").value;
        const toValue = valueFieldContainer.querySelector(".to-value").value;
        if (!fromValue || !toValue) {
            alert("Please enter both 'From' and 'To' values for the BETWEEN operator.");
            return;
        }
        value = `'${fromValue}' AND '${toValue}'`;
        showValue = `'${fromValue}' AND '${toValue}'`;
    } else if (["IN", "NOT IN", "=", "!="].includes(operator) && columnType != 'number') {
        const multiSelect = valueFieldContainer.querySelector(".value-dropdown");
        if (multiSelect) {
            // Get values in the order they appear in the sortable list
            let selectedValues = [];
            const sortableContainer = valueFieldContainer.querySelector('.sortable-selected-list');
            if (sortableContainer && sortableContainer.querySelector('.sortable-item')) {
                const sortableItems = sortableContainer.querySelectorAll('.sortable-item');
                selectedValues = Array.from(sortableItems).map(item => item.dataset.value);
            } else {
                // Fallback to dropdown order if no sortable container
                selectedValues = $(multiSelect).val() || [];
            }

            if (selectedValues.length === 0) {
                alert("Please select at least one value.");
                return;
            }

            const selectedOptions = selectedValues.map(val => `'${val}'`);
            const selectedValueTexts = selectedValues.map(val => {
                const option = Array.from(multiSelect.options).find(opt => opt.value === val);
                return option ? option.text : val;
            });

            if (isMultiple) {
                value = `[${selectedOptions.join(', ')}]`;
                showValue = `[${selectedValueTexts.join(', ')}]`;
            } else {
                if (operator === "=" || operator === "!=") {
                    if (selectedOptions.length === 1) {
                        value = selectedOptions[0];
                        showValue = selectedValueTexts[0];
                    } else {
                        value = `(${selectedOptions.join(', ')})`;
                        showValue = `(${selectedValueTexts.join(', ')})`;
                    }
                } else {
                    value = `(${selectedOptions.join(', ')})`;
                    showValue = `(${selectedValueTexts.join(', ')})`;
                }
            }
            query = `${columnName} ${operator} ${value}`;
        }
    } else if (["LIKE %..%", "NOT LIKE %..%"].includes(operator)) {
        const inputValue = valueFieldContainer.querySelector(".value-input").value;
        value = `'%${inputValue}%'`;
        showValue = `${inputValue}`;
        if (operator === "LIKE %..%") {
            query = `${columnName} LIKE ${value}`;
            operator = "LIKE";
        } else {
            query = `${columnName} NOT LIKE ${value}`;
            operator = "NOT LIKE";
        }
    } else {
        let inputValue = valueFieldContainer.querySelector(".value-input").value;
        if (operator === "IS NOT NULL" || operator === 'IS NULL') inputValue = 'Null';
        if (!inputValue) {
            alert("Please enter a value for the selected operator.");
            return;
        } else
            value = `'${inputValue}'`;

        showValue = `${inputValue}`;
    }

    setValueInTable(column, columnName, operator, value, showValue, editingRowIndex, parentElement);
}

// The rest of your functions remain the same...
function setValueInTable(column, columnName, operator, value, showValue, editingRowIndex, parentElement) {
    if (editingRowIndex) {
        let table = parentElement.querySelector("#conditionsTable tbody");
        let row = table.rows[editingRowIndex - 1];
        row.cells[0].textContent = column;
        row.cells[1].textContent = columnName;
        row.cells[2].textContent = operator;
        row.cells[3].textContent = value;
        row.cells[4].textContent = showValue;
        event.target.removeAttribute("data-editing-row");
        parentElement.querySelector(".exit-edit-mode")?.remove();
    } else {
        let tableBody = parentElement.querySelector("#conditionsTable tbody");
        let tableRow = document.createElement("tr");
        tableRow.innerHTML = `
            <td>${column}</td>
            <td style="display:none">${columnName}</td>
            <td>${operator}</td>
            <td style="display:none">${value}</td>
            <td>${showValue}</td>
            <td>
                <div class="row align-items-center ps-2" style="gap:10px;">
                    ${operator !== 'SORT' && operator !== 'LIMIT' ? `<button type="button" style="width:100px" class="btn btn-success edit-condition">Edit</button>` : ''}
                    <button type="button" style="width:100px" class="btn btn-danger remove-condition">Remove</button>
                </div>
            </td>
        `;

        tableBody.appendChild(tableRow);

        tableRow.querySelector(".remove-condition").addEventListener('click', function (event) {
            removeCondition(event, parentElement);
        });

        let editButton = tableRow.querySelector(".edit-condition");
        if (editButton) {
            editButton.addEventListener('click', function (event) {
                editCondition(event, parentElement);
            });
        }
    }

    moveSortRowsToBottom(parentElement);
    generateAndSetQuery(parentElement);
    conditionPending = false;
    resetQueryData();
}

function removeCondition(event, parentElement) {
    event.target.closest("tr").remove();
    generateAndSetQuery(parentElement);
}

function editCondition(event, parentElement) {
    const tableRow = event.target.closest("tr");
    let column = tableRow.querySelector("td:nth-child(1)").textContent;
    let operator = tableRow.querySelector("td:nth-child(3)").textContent;
    let value = tableRow.querySelector("td:nth-child(4)").textContent;
    value = value.replaceAll("'", "");
    const columnSelect = parentElement.querySelector("#column");
    const operatorSelect = parentElement.querySelector("#operator");
    if (columnSelect && operatorSelect) {
        columnSelect.value = column;
        operatorSelect.value = operator;

        if (["BETWEEN", "NOT BETWEEN"].includes(operator)) {
            const fromValue = parentElement.querySelector(".from-value");
            const toValue = valueFieldContainer.querySelector(".to-value");
            fromValue.value = value;
            toValue.value = value;
            columnSelect.dispatchEvent(new Event('change'));
        } else if (["IN", "NOT IN", "=", "!="].includes(operator)) {
            parentElement.querySelector(".value-dropdown");
            handleColumnChange(columnSelect, value, operator);
        } else if (["LIKE %..%", "NOT LIKE %..%"].includes(operator)) {
            const inputValue = parentElement.querySelector(".value-input");
            inputValue.value = value;
            columnSelect.dispatchEvent(new Event('change'));
        } else {
            const inputValue = parentElement.querySelector(".value-input");
            inputValue.value = value;
            handleColumnChange(columnSelect, value, operator);
            columnSelect.dispatchEvent(new Event('change'));
        }
        let exitEditBtn = document.createElement("button");
        exitEditBtn.setAttribute("type", "button");
        exitEditBtn.classList.add("btn", "btn-warning", "ms-2", "exit-edit-mode");
        exitEditBtn.textContent = "Cancel Edit";
        const saveBtn = parentElement.querySelector(".save-condition");
        if (!parentElement.querySelector(".exit-edit-mode")) {
            saveBtn.insertAdjacentElement("afterend", exitEditBtn);
        }
        parentElement.querySelector(".exit-edit-mode").addEventListener('click', function () {
            resetQueryData();
            parentElement.querySelector(".exit-edit-mode").remove();
            parentElement.querySelector(".save-condition").removeAttribute("data-editing-row");
        })
        parentElement.querySelector(".save-condition").setAttribute("data-editing-row", tableRow.rowIndex);
    } else { }
}

function moveSortRowsToBottom(parentElement) {
    let tableBody = parentElement.querySelector("#conditionsTable tbody");
    let rows = Array.from(tableBody.querySelectorAll("tr"));
    let sortRows = rows.filter(row => row.cells[2].textContent.trim() === "SORT");
    let limitRows = rows.filter(row => row.cells[2].textContent.trim() === "LIMIT");
    let otherRows = rows.filter(row => row.cells[2].textContent.trim() !== "SORT" && row.cells[2].textContent.trim() !== "LIMIT");
    tableBody.innerHTML = "";

    otherRows.forEach(row => tableBody.appendChild(row));
    sortRows.forEach(row => tableBody.appendChild(row));
    limitRows.forEach(row => tableBody.appendChild(row));
}

function generateAndSetQuery(parentElement) {
    let conditions = parentElement.querySelectorAll("#conditionsTable tbody tr");
    let queryParts = [];

    conditions.forEach(condition => {
        let column = condition.querySelector("td:nth-child(2)").textContent;
        let operator = condition.querySelector("td:nth-child(3)").textContent;
        let value = condition.querySelector("td:nth-child(4)").textContent;
        queryParts.push(`${column} ${operator} ${value}`);
    });

    parentElement.querySelector("#generatedQuery").value = queryParts.join(" && ");
}