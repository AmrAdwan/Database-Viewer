<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Database Viewer</title>
    <style>
        ul { list-style-type: none; padding: 0; }
        li { cursor: pointer; margin-bottom: 10px; }
        .hidden { display: none; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <h1 class="my-4">Database Viewer</h1>
        <p><a class="btn btn-info" href="instructions.php">Instructions</a></p>

        <div class="mb-4">
            <h2>Select Database Type</h2>
            <select id="dbTypeSelect" class="form-select" onchange="loadDatabases()">
                <option value="mysql">MySQL</option>
                <option value="sqlsrv">SQL Server</option>
                <option value="pgsql">PostgreSQL</option>
            </select>
        </div>

        <div class="mb-4">
            <h2>Databases</h2>
            <div id="databases"></div>
        </div>

        <div class="mb-4">
            <h2>Tables</h2>
            <div id="tables"></div>
        </div>

        <div class="mb-4">
            <h2>Table Data</h2>
            <div id="table-data"></div>
        </div>

        <div class="mb-4">
            <h2>Columns</h2>
            <div id="columns"></div>
        </div>
    </div>

    <!-- Edit Record Modal -->
    <div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRecordModalLabel">Edit Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRecordForm">
                        <div id="editRecordFields"></div>
                        <input type="hidden" id="editDatabase" name="database">
                        <input type="hidden" id="editTable" name="table">
                        <input type="hidden" id="editRowId" name="row_id">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadDatabases() {
            const dbType = document.getElementById('dbTypeSelect').value;
            fetch('list_databases.php?db-type=' + dbType)
                .then(response => response.text())
                .then(data => document.getElementById('databases').innerHTML = data);
        }

        function showTables(database) {
            const dbType = document.getElementById('dbTypeSelect').value;
            fetch('list_tables.php?database=' + database + '&db-type=' + dbType)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('tables').innerHTML = data;
                    // Call showColumns with the first table found
                    const tablesSelect = document.querySelector('#tables select');
                    if (tablesSelect) {
                        tablesSelect.addEventListener('change', function () {
                            showTableData(database, this.value);
                            showColumns(database, this.value);
                        });
                    }
                });
        }

        function showTableData(database, table) {
            const dbType = document.getElementById('dbTypeSelect').value;
            fetch('list_table_data.php?database=' + database + '&table=' + table + '&db-type=' + dbType)
                .then(response => response.text())
                .then(data => document.getElementById('table-data').innerHTML = data);
        }

        function showColumns(database, table) {
            fetch('list_columns.php?database=' + database + '&table=' + table)
                .then(response => response.json())
                .then(data => {
                    const columnsDiv = document.getElementById('columns');
                    data.forEach(column => {
                        columnsDiv.innerHTML += `
                            <div class="mb-3">
                                <label for="${column.COLUMN_NAME}" class="form-label">${column.COLUMN_NAME} (${column.DATA_TYPE})</label>
                                <select class="form-select" id="${column.COLUMN_NAME}" name="${column.COLUMN_NAME}">
                                    <option value="int" ${column.DATA_TYPE === 'int' ? 'selected' : ''}>int</option>
                                    <option value="varchar(255)" ${column.DATA_TYPE === 'varchar' ? 'selected' : ''}>varchar(255)</option>
                                    <option value="text" ${column.DATA_TYPE === 'text' ? 'selected' : ''}>text</option>
                                    <option value="date" ${column.DATA_TYPE === 'date' ? 'selected' : ''}>date</option>
                                    <option value="datetime" ${column.DATA_TYPE === 'datetime' ? 'selected' : ''}>datetime</option>
                                    <option value="float" ${column.DATA_TYPE === 'float' ? 'selected' : ''}>float</option>
                                    <option value="double" ${column.DATA_TYPE === 'double' ? 'selected' : ''}>double</option>
                                </select>
                                <br>
                                <button class="btn btn-primary btn-sm" onclick='editColumnType("${database}", "${table}", "${column.COLUMN_NAME}")'>Edit</button>
                            </div>
                        `;
                    });
                });
        }

        function editRecord(database, table, rowId) {
            fetch('get_record.php?database=' + database + '&table=' + table + '&row_id=' + rowId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    const formFields = document.getElementById('editRecordFields');
                    formFields.innerHTML = '';
                    for (const [key, value] of Object.entries(data)) {
                        formFields.innerHTML += `
                            <div class="mb-3">
                                <label for="${key}" class="form-label">${key}</label>
                                <input type="text" class="form-control" id="${key}" name="${key}" value="${value}">
                            </div>
                        `;
                    }
                    document.getElementById('editDatabase').value = database;
                    document.getElementById('editTable').value = table;
                    document.getElementById('editRowId').value = rowId;
                    const modal = new bootstrap.Modal(document.getElementById('editRecordModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching the record.');
                });
        }

        function editColumnType(database, table, column) {
            const newType = document.getElementById(column).value;
            fetch('edit_column.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ database, table, column, newType })
            })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    showColumns(database, table);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the column type.');
                });
        }

        document.getElementById('editRecordForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('update_record.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editRecordModal'));
                    modal.hide();
                    const database = formData.get('database');
                    const table = formData.get('table');
                    showTableData(database, table);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the record.');
                });
        });
    </script>
</body>
</html>
