# Módulo Efí para WHMCS

## Instalação

1. Faça o download da última versão do módulo;
2. Descompacte o arquivo baixado;
3. Copie o arquivo **efi.php** e a pasta **efi**, para o diretório **/modules/gateways** da instalação do WHMCS;
4. Altere as permissões do arquivo copiado utilizando o comando: `chmod 777 modules/gateways/efi.php`
5. Altere as permissões da pasta copiada utilizando o comando: `chmod 777 modules/gateways/efi/ -R`
6. Copie o arquivo **efi.php**, disponível no diretório **callback**, para o diretório **modules/gateways/callback**. Ele deve estar no caminho: *modules/gateways/callback/efi.php*
7. Altere as permissões do arquivo copiado utilizando o comando: `chmod 777 modules/gateways/callback/efi.php`
8. Copie o arquivo **efi.php**, disponível no diretório **hooks**, para o diretório **includes/hooks**. Ele deve estar no caminho *includes/hooks/efi.php*
9. Altere as permissões do arquivo copiado utilizando o comando: `chmod 777 includes/hooks/efi.php`
10. Crie uma pasta na raiz do seu servidor e insira seu certificado na pasta. 

Ao final da instalação, os arquivos do módulo Efí devem estar na seguinte estrutura no WHMCS:

```
includes/hooks/
  |- efi.php
 modules/gateways/
  |- callback/efi.php
  |- efi/
  |- efi.php
```

### Certificado para utilização da API PIX

Todas as requisições devem conter um certificado de segurança que será fornecido pela Efí dentro da sua conta, no formato PFX(.p12). Essa exigência está descrita na integra no [manual de segurança do PIX](https://www.bcb.gov.br/estabilidadefinanceira/comunicacaodados).

Caso ainda não tenha seu certificado, basta seguir o passo a passo do link a seguir para gerar um novo: [Clique Aqui](https://gerencianet.com.br/artigo/como-gerar-o-certificado-para-usar-a-api-pix/)


## Configuração do Módulo

![Tela de Configuração](https://sejaefi.link/BJetA4JvQ3)
1. **Client_Id Produção:** Deve ser preenchido com o client_id de produção de sua conta Efí. Este campo é obrigatório e pode ser encontrado no menu "API" -> "Aplicações". Em seguida, selecione sua aplicação criada, conforme é mostrado no [link](https://gnetbr.com/Ske9THqjrO);
2. **Client_Secret Produção:** Deve ser preenchido com o client_secret de produção de sua conta Efí. Este campo é obrigatório e pode ser encontrado no menu "API" ->  "Aplicações". Em seguida, selecione sua aplicação criada, conforme é mostrado no [link](https://gnetbr.com/Ske9THqjrO);
3. **Client_Id Desenvolvimento:** Deve ser preenchido com o client_id de desenvolvimento de sua conta Efí. Este campo é obrigatório e pode ser encontrado no menu "API" -> "Aplicações". Em seguida, selecione sua aplicação criada, conforme é mostrado no [link](https://gnetbr.com/BJe-vIciHd);
4. **Client_Secret Desenvolvimento:** Deve ser preenchido com o client_secret de desenvolvimento de sua conta Efí. Este campo é obrigatório e pode ser encontrado no menu "API" -> "Aplicações". Em seguida, selecione sua aplicação criada, conforme é mostrado no [link](https://gnetbr.com/BJe-vIciHd);
5. **Identificador da conta:** Deve ser preenchido com o identificador da  sua conta Efí. Este campo é obrigatório e pode ser encontrado no menu "API" -> "Introdução"->"Identificador da Conta", conforme é mostrado no [link](https://gnetbr.com/ryezOK31Qt);
6. **Usuário administrador do WHMCS:** Deve ser preenchido com o usuário administrador do WHMCS. É necessário utilizar o mesmo usuário que o administrador do WHMCS utiliza para fazer login na área administrativa de sua conta. Este campo é de preenchimento obrigatório;
7. **Desconto do Boleto:** Informe o valor desconto que deverá ser aplicado aos boletos gerados exclusivamente pela Efí. Esta informação é opcional;
8. **Tipo de desconto:** Informe o tipo de desconto (porcentagem ou valor fixo) que deverá ser aplicado aos boletos gerados exclusivamente pela Efí. Esta informação é opcional;
9. **Número de dias para o vencimento do Boleto:** Informe o número de dias corridos para o vencimento do boleto Efí após a cobrança ser gerada. Se o campo estiver vazio, o valor será 0;
10. **E-mail de cobrança - Efí:** Caso seja de seu interesse, habilite o envio de emails de cobrança da Efí para o cliente final;
11. **Configuração de Multa:** Caso seja de seu interesse, informe o valor, em porcentagem, cobrado de multa após o vencimento. Por exemplo: se você quiser 2%, você deve informar 2. Mínimo de 0.01 e máximo de 10. Integer;
12. **Configuração de Juros:** valor cobrado de juros por dia após a data de vencimento. Por exemplo: se você quiser 0,033%, você deve informar 0.033. Mínimo de 0.001 e máximo de 0.33;
13. **Observação:** Permite incluir no boleto uma mensagem para o cliente;
14. **Sandbox:** Caso seja de seu interesse, habilite o ambiente de testes da API Efí;
15. **Debug:** Neste campo é possível habilitar os logs de transação e de erros da Efí no painel WHMCS;
16. **Chave PIX:** Se utilizado CNPJ, informar sem pontos e espaços, ex. 11111111111121;
17. **Certificado Pix:** Deve ser preenchido com o caminho do certificado salvo em seu servidor no passo 10 da instalação;
18. **Desconto:** Informe o valor de desconto que deverá ser aplicado ao pix gerado exclusivamente pela Efí;
19. **Validade da Cobrança PIX:** Deve ser informado o período de validade em dias da cobrança PIX;
20. **Validar mTLS:** Entenda os riscos de não configurar o mTLS acessando o link https://gnetbr.com/rke4baDVyd;
21. **PIX:** Selecione essa opção caso deseje deixar a opção PIX como forma de pagamento;
22. **Boleto:** Selecione essa opção caso deseje deixar a opção boleto como forma de pagamento;
23. **Cartão de Crédito:** Selecione essa opção caso deseje deixar a opção de cartão de crédito como forma de pagamento.