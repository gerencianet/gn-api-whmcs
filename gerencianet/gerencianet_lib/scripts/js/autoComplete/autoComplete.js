//---------------------------------Envia as funções Auto Complete para o arquivo viewInvoiceModal.js ----

function autoComplete() {
    autoCompleteBillet();
    autoCompleteCredit();
    autoCompletePix();
}

//---------------------------------- Retorna numero no formato (xx) xxxxx-xxxx ----------------------------

function returnNumeroTelefoneFormatado(numero) {
    let numformatado = '';
    numformatado += `(${numero[0]}${numero[1]}) `;
    for (let index = 2; index < 6; index++) {
        numformatado += `${numero[index]}`

    }
    numformatado += '-'
    for (let index = 6; index < numero.length; index++) {
        numformatado += `${numero[index]}`

    }

    return numformatado;
}

//---------------------------------- Retorna cep no formato xx.xxx-xxx ----------------------------

function returnCepFormatado(cep) {
    let cepFormatado = '';
    cepFormatado += `${cep[0]}${cep[1]}.`;

    for (let index = 2; index < 5; index++) {
        cepFormatado += `${cep[index]}`
    }

    cepFormatado += '-';

    for (let index = 5; index < 8; index++) {
        cepFormatado += `${cep[index]}`
    }


    return cepFormatado;
}
// ------------------------------------- Auto Complete Boleto ---------------------------------------------

function autoCompleteBillet() {
    let numero = $('.telefoneCliente').val();
    $('#nameBillet').val($('.nomeCompletoCliente').val());
    $('#clientEmailBillet').val($('.emailCliente').val());
    $('#telephoneBillet').val(`${returnNumeroTelefoneFormatado(numero)}`);
    $('#autocompleteBillet').prop('checked', true);

    $('#autocompleteBillet').click(() => {
        if ($('#autocompleteBillet').is(':checked')) {
            $('#nameBillet').val($('.nomeCompletoCliente').val());
            $('#clientEmailBillet').val($('.emailCliente').val());
            $('#telephoneBillet').val(`${returnNumeroTelefoneFormatado(numero)}`);
        } else {
            $('#nameBillet').val('');
            $('#clientEmailBillet').val('');
            $('#telephoneBillet').val('');
        }

    })
}
// ------------------------------------- Auto Complete Pix ---------------------------------------------

function autoCompletePix() {
    $('#clientNamePix').val($('.nomeCompletoCliente').val());
    $('#autocompletePix').prop('checked', true);


    $('#autocompletePix').click(() => {
        if ($('#autocompletePix').is(':checked')) {
            $('#clientNamePix').val($('.nomeCompletoCliente').val());

        } else {
            $('#clientNamePix').val('');
        }

    })
}

// ------------------------------------- Auto Complete Cartão ---------------------------------------------

function autoCompleteCredit() {
    let numero = $('.telefoneCliente').val();
    let cep = $('.cepCliente').val();
    let estado = $("#estado").find(`option[value='${$('.estadoCliente').val()}']`);
    $('#nameCredit').val($('.nomeCompletoCliente').val());
    $('#clientEmailCredit').val($('.emailCliente').val());
    $('#telephoneCredit').val(`${returnNumeroTelefoneFormatado(numero)}`);
    $('#rua').val($('.ruaCliente').val());
    $('#numero').val($('.numeroCasaCliente').val());
    $('#cidade').val($('.cidadeCliente').val());
    $('#bairro').val($('.bairroCliente').val());
    $('#cep').val(`${returnCepFormatado(cep)}`);
    estado.prop("selected", true);
    $('#autocompleteCredit').prop('checked', true);

    $('#autocompleteCredit').click(() => {
        
        if ($('#autocompleteCredit').is(':checked')) {
            $('#nameCredit').val($('.nomeCompletoCliente').val());
            $('#clientEmailCredit').val($('.emailCliente').val());
            $('#telephoneCredit').val(`${returnNumeroTelefoneFormatado(numero)}`);
            $('#rua').val($('.ruaCliente').val());
            $('#numero').val($('.numeroCasaCliente').val());
            $('#cidade').val($('.cidadeCliente').val());
            $('#bairro').val($('.bairroCliente').val());
            $('#cep').val(`${returnCepFormatado(cep)}`);
            estado.prop("selected", true);

        } else {
            $('#nameCredit').val('');
            $('#clientEmailCredit').val('');
            $('#telephoneCredit').val('');
            $('#rua').val('');
            $('#cidade').val('');
            $('#cep').val('');
            $('#numero').val('');
            $('#bairro').val('');
            estado.prop("selected", false);



        }

    })
}