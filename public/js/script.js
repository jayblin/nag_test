const filter = $('#filter');
const table = $('#report');


filter.on('click', e => {
    if (e.target instanceof HTMLButtonElement) {

        const ms =  $('#monthSelect', filter);
        const cts = $('#clientTypeSelect', filter);

        let data = {
            clientType: cts.val(),
            date: (new Date(ms.val())).getTime() / 1000,
        };

        $.ajax('api/GetReport', {
            method: 'POST',
            headers: {
                'Content-type': 'application/json'
            },
            data: JSON.stringify(data)
        })
            .then(res => {
                // вставить эту дату как минимально возможную в выдорщик месяца
                const json = JSON.parse(res);

                if (json.error !== false) {
                    console.warn(json.message);
                    return;
                }

                const columns = [
                    'serviceName',
                    'startBalance',
                    'gain',
                    'loss',
                    'recalc',
                    'totalBalance',
                ];

                const report = json.result;

                const tableRows = $('tr', table);

                let i = 1;
                for (const service of report) {

                    const row = document.createElement('tr');

                    for (const col of columns) {
                        const td = document.createElement('td');
                        td.innerText = service[col];
                        row.appendChild(td);
                    }

                    if (tableRows[i]) {
                        tableRows[i].replaceWith(row);
                    }
                    else {
                        table.append(row);
                    }

                    i++;
                }
            })
            .catch(err => {
                console.warn(err);
            });
    }
});


// получить дату самого первого платежа
$.ajax('api/GetFirstPaymentDate')
    .then(res => {
        // вставить эту дату как минимально возможную в выдорщик месяца
        const json = JSON.parse(res);

        if (json.error !== false) {
            console.warn(json.message);
            return;
        }

        const d = new Date(json.result);
        const now = new Date();
        $('#monthSelect', filter).attr({
            min: `${d.getFullYear()}-${d.getMonth().toString().padStart(2, 0)}`,
            max: `${now.getFullYear()}-${now.getMonth().toString().padStart(2, 0)}`,
            value: `${now.getFullYear()}-${now.getMonth().toString().padStart(2, 0)}`
        });
    })
    .catch(err => {
        console.warn(err);
    });


// получить типы клиентов
$.ajax('api/GetClientTypes')
    .then(res => {
        // вставить их в выпадающий список

        const json = JSON.parse(res);

        if (json.error !== false) {
            console.warn(json.message);
            return;
        }

        const clientTypes = json.result;
        const clientTypeSelect = $('#clientTypeSelect', filter);

        const defaultOption = document.createElement('option');
        defaultOption.value = "";
        defaultOption.text = "Любой";
        defaultOption.defaultSelected = true;
        clientTypeSelect.append(defaultOption);

        for (const id in clientTypes) {
            const option = document.createElement('option');
            option.value = id;
            option.text = clientTypes[id];
            clientTypeSelect.append(option);
        }
    })
    .catch(err => {
        console.warn(err);
    });