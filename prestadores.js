function getPrestador(url, selectIds) {
    axios({
        method: 'post',
        url: url,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        data: {}
    })
    .then(function (response) {
        if (response.data.error) {
            alert(response.data.message);
        } else {
            const prestadores = response.data.data;
            selectIds.forEach(selectId => {
                preencheSelectInput(prestadores, $(selectId));
            });
        }
    })
    .catch(function (error) {
        console.error('Erro na requisição:', error);
    });
}

function preencheSelectInput(prestadores, $select) {
    $select.empty();
    $select.append('<option value="" selected>Prestador</option>');
    prestadores.forEach(prestador => {
        const Prest = `${prestador.CD_PRESTADOR} - ${prestador.NM_PRESTADOR}`;
        $select.append(`<option value="${prestador.CD_PRESTADOR}">${Prest}</option>`);
    });
}


function initSelect2(selectId, modalId, placeholder) {
    if (!$(selectId).hasClass('select2-hidden-accessible')) {
        $(selectId).select2({
            dropdownParent: $(modalId),
            placeholder: placeholder, //* texto para exibir no placeholder
            width: '100%'
        });
    }
}
