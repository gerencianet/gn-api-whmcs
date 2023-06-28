//---------------------------------Envia as funções Autocomplete para o arquivo viewInvoiceModal.js ----
function getClientDocument(attributes) {
    let documentoEncontrado = null;
    $(attributes.customFields).each(function(indexInArray, field) {
        let documento = field.value.replace(/\D/g, '');

        if (verifyCPF(documento) || verifyCNPJ(documento)) {
            documentoEncontrado = documento;
            return false; // interrompe o loop
        }
    });
    return documentoEncontrado;
}

function autoComplete() {
    $.get('modules/gateways/efi/gerencianet_lib/functions/frontend/ajax/ClientDataAjaxHandler.php', function(data) {
        let cliente = {};
        const attributes = JSON.parse(data);
        cliente.telefone = attributes.phoneNumber.split('.')[1];
        cliente.fullName = `${attributes.firstName} ${attributes.lastName}`;
        cliente.email = attributes.email;
        cliente.rua = attributes.address1.split(',')[0];
        cliente.numero = attributes.address1.split(',')[1];
        cliente.cidade = attributes.city;
        cliente.cep = attributes.postcode.replace('-', '');
        cliente.estado = attributes.state;
        cliente.documento = getClientDocument(attributes);


        autocompleteBillet(cliente);
        autocompleteCredit(cliente);
        autocompletePix(cliente);
        autocompleteOpenFinance(cliente);

    });
}

function autocompleteOpenFinance(cliente) {
    const inputsAnimation = [
        cliente.documento.length == 11 ? {
            inputName: "#documentClientOF",
            inputValue: cliente.documento
        } : {
            inputName: "#documentPJClientOF",
            inputValue: cliente.documento
        }
    ];

    applyAnimations(inputsAnimation)
    $('#autocompleteOpenFinance').click(() => {
        if ($('#autocompleteOpenFinance').is(':checked')) {
            applyAnimations(inputsAnimation)
        } else {
            const inputsDefaultValue = [{
                    inputName: "#documentClientOF",
                    inputValue: ''
                },
                {
                    inputName: "#documentPJClientOF",
                    inputValue: ''
                },
            ];
            applyAnimations(inputsDefaultValue)
        }

    })

    $('#autocompleteOpenFinance').prop('checked', true);

}

// ------------------------------------- Auto Complete Boleto ---------------------------------------------

function autocompleteBillet(cliente) {
    const inputsAnimation = [{
        inputName: "#nameBillet",
        inputValue: cliente.fullName
    }, {
        inputName: "#clientEmailBillet",
        inputValue: cliente.email
    }, {
        inputName: "#documentClientBillet",
        inputValue: cliente.documento
    }, {
        inputName: "#telephoneBillet",
        inputValue: cliente.telefone
    }];
    applyAnimations(inputsAnimation)

    $('#autocompleteBillet').click(() => {
        if ($('#autocompleteBillet').is(':checked')) {
            applyAnimations(inputsAnimation)
        } else {
            const inputsDefaultValue = [{
                inputName: "#nameBillet",
                inputValue: ''
            }, {
                inputName: "#clientEmailBillet",
                inputValue: ''
            }, {
                inputName: "#documentClientBillet",
                inputValue: ''
            }, {
                inputName: "#telephoneBillet",
                inputValue: ''
            }];
            applyAnimations(inputsDefaultValue)
        }

    })



    $('#autocompleteBillet').prop('checked', true);

}
// ------------------------------------- Autocomplete Pix ---------------------------------------------

function autocompletePix(cliente) {
    const inputsAnimation = [{
        inputName: "#clientNamePix",
        inputValue: cliente.fullName
    }, {
        inputName: "#documentClientPix",
        inputValue: cliente.documento
    }];
    applyAnimations(inputsAnimation)
    $('#autocompletePix').prop('checked', true);


    $('#autocompletePix').click(() => {
        if ($('#autocompletePix').is(':checked')) {
            applyAnimations(inputsAnimation)

        } else {
            const inputsDefaultValue = [{
                inputName: "#clientNamePix",
                inputValue: ''
            }, {
                inputName: "#documentClientPix",
                inputValue: ''
            }];
            applyAnimations(inputsDefaultValue)
        }

    })
}

// ------------------------------------- Autocomplete Cartão ---------------------------------------------

function autocompleteCredit(cliente) {
    const inputsAnimation = [{
        inputName: "#nameCredit",
        inputValue: cliente.fullName
    }, {
        inputName: "#clientEmailCredit",
        inputValue: cliente.email
    }, {
        inputName: "#documentClientCredit",
        inputValue: cliente.documento
    }, {
        inputName: "#telephoneCredit",
        inputValue: cliente.telefone
    }, {
        inputName: "#rua",
        inputValue: cliente.rua
    }, {
        inputName: "#numero",
        inputValue: cliente.numero
    }, {
        inputName: "#cidade",
        inputValue: cliente.cidade
    }, {
        inputName: "#bairro",
        inputValue: cliente.bairro
    }, {
        inputName: "#cep",
        inputValue: cliente.cep
    }, {
        inputName: "#estado",
        inputValue: cliente.estado
    }, {
        inputName: "#dataNasce",
        inputValue: ''
    }];

    applyAnimations(inputsAnimation)



    $('#autocompleteCredit').prop('checked', true);

    $('#autocompleteCredit').click(() => {

        if ($('#autocompleteCredit').is(':checked')) {
            applyAnimations(inputsAnimation)



        } else {
            const inputsDefaultValue = [{
                inputName: "#nameCredit",
                inputValue: ''
            }, {
                inputName: "#clientEmailCredit",
                inputValue: ''
            }, {
                inputName: "#documentClientCredit",
                inputValue: ''
            }, {
                inputName: "#telephoneCredit",
                inputValue: ''
            }, {
                inputName: "#rua",
                inputValue: ''
            }, {
                inputName: "#numero",
                inputValue: ''
            }, {
                inputName: "#cidade",
                inputValue: ''
            }, {
                inputName: "#bairro",
                inputValue: ''
            }, {
                inputName: "#cep",
                inputValue: ''
            }, {
                inputName: "#estado",
                inputValue: ''
            }, {
                inputName: "#dataNasce",
                inputValue: ''
            }];
            applyAnimations(inputsDefaultValue)



        }

    })
}

function applyAnimations(inputs) {
    inputs.forEach((inputObj) => {
        // Seleciona o elemento input
        let input = $(`${inputObj.inputName}`);

        // Anima a opacidade do elemento para zero
        input.animate({ opacity: 0 }, 500, function() {
            // Quando a animação terminar, atualiza o valor do input
            input.val(inputObj.inputValue);
            input.trigger('keydown');
            input.trigger('input');

            // Anima a opacidade do elemento de volta para 1
            input.animate({ opacity: 1 }, 500);

        });
    });

}