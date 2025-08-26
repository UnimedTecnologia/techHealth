<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="../../style.css" rel="stylesheet"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include_once "../../nav.php"; // CABEÇALHO NAVBAR ?>
    <div id="loadingOverlay">
        <div class="spinner"></div>
        <p id="textoLoad">Carregando</p>
    </div>
    <div class="d-flex align-items-center mt-100">
        <div style="position: absolute; left: 10px;">
            <button class="btn" onclick="location.href='../../dashboard.php'" style="background-color: #008e55; color: white; margin-left: 10px;">
                <i class="bi bi-arrow-left"></i> <!-- Ícone de voltar -->
            </button>
        </div>
        <div class="mx-auto text-center">
            <h3>Prestador</h3>                
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <?php
        if (isset($_SESSION['dadosPrestador']) && is_array($_SESSION['dadosPrestador'])) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-bordered">';

            //* Cabeçalhos colunas
            $headers = [
                "CD_PRESTADOR_PRINCIPAL" => "Código Prestador",
                "CD_TRANSACAO" => "Código Transação",
                "NR_SERIE_DOC_ORIGINAL" => "Número Série",
                "NR_DOC_ORIGINAL" => "Número Documento",
                "NR_PERREF" => "Número Período Ref.",
                "DT_ANOREF" => "Data Ano Ref.",
            ];

            //* Criar cabeçalhos da tabela
            echo '<thead class="table-dark"><tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            // echo '<th>Editar</th>';
            echo '</tr></thead>';

            //* Preencher os dados
            echo '<tbody>';
            if(empty($_SESSION['dadosPrestador'])) {
                echo '<tr><td class="text-center" colspan="' . count($headers) . '">Sem Registros</td></tr>';
            }else{
                foreach ($_SESSION['dadosPrestador'] as $row) {
                    echo '<tr>';
                    foreach (array_keys($headers) as $key) {
                        echo '<td>' . htmlspecialchars($row[$key] ?? '-') . '</td>'; // Exibe "-" se o valor não existir
                    }                                    
                    echo '</tr>';
                }
            }
            echo '</tbody>';

            echo '</table>';
            echo '</div>';


        } else{
            echo '<p class="text-center">Nenhum dado encontrado na sessão.</p>';
        }

        ?>

    </div>

    <!-- //! Modal editar Prestador -->
    <div class="modal fade" id="editarPrestadorModal" tabindex="-1" aria-labelledby="editarPrestadorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editarPrestadorForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarPrestadorModalLabel">Editar Código Prestador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="codigoAtual" name="codigoAtual">
                        <input type="hidden" id="nrDocOriginal" name="nrDocOriginal">
                        <input type="hidden" id="transacao" name="transacao">
                        <input type="hidden" id="nrSerie" name="nrSerie">
                        <input type="hidden" id="nrPeriodo" name="nrPeriodo">
                        <input type="hidden" id="anoRef" name="anoRef">
                        <div class="">
                            <label for="novoCodigo" class="form-label">Prestador*</label>
                            <select id="novoCodigo" name="novoCodigo" class="custom-select" >
                                    <option value="" disabled selected>Prestador </option>
                                <option value="1"></option>
                            </select>
                        </div>
                        <!-- <div class="mb-3">
                            <label for="novoCodigo" class="form-label">Novo Código Prestador</label>
                            <input type="text" class="form-control" id="novoCodigo" name="novoCodigo" required>
                        </div> -->

                    </div>
                    <div class="text-center mb-4">
                        <button type="submit" class="btn btn-success">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="mx-auto text-center mt-5">
        <h3>Insumos</h3>                
    </div>

    <div class="d-flex justify-content-center">
        <?php
        if (isset($_SESSION['dadosInsumos']) && is_array($_SESSION['dadosInsumos'])) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-bordered">';

            //* Cabeçalhos colunas
            $headers = [
                "CD_PRESTADOR" => "Código Prestador",
                "CD_TRANSACAO" => "Código Transação",
                "NR_SERIE_DOC_ORIGINAL" => "Número Série",
                "NR_DOC_ORIGINAL" => "Número Documento Original",
                "NR_DOC_SISTEMA" => "Número Documento Sistema",
                "PROC_INSU" => "Proc Insumo",
                "NR_PERREF" => "Número Período Ref.",
                "DT_ANOREF" => "Data Ano Ref.",
            ];

            //* Criar cabeçalhos da tabela
            echo '<thead class="table-dark"><tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr></thead>';

            //* Preencher os dados
            echo '<tbody>';
            if(empty($_SESSION['dadosInsumos'])) {
                echo '<tr><td class="text-center" colspan="' . count($headers) . '">Sem Registros</td></tr>';
            }else {
                foreach ($_SESSION['dadosInsumos'] as $row) {
                    echo '<tr>';
                    foreach (array_keys($headers) as $key) {
                        echo '<td>' . htmlspecialchars($row[$key] ?? '-') . '</td>'; // Exibe "-" se o valor não existir
                    }
                    echo '</tr>';
                }
            }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';

            } else {
                echo '<p class="text-center">Nenhum dado encontrado na sessão.</p>';
            }
        

        ?>

    </div>

    <div class="mx-auto text-center mt-5">
        <h3>Procedimento</h3>                
    </div>

    <div class="d-flex justify-content-center">
        <?php
        if (isset($_SESSION['dadosProcedimento']) && is_array($_SESSION['dadosProcedimento'])) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-bordered">';

            //* Cabeçalhos colunas
            $headers = [
                "CD_PRESTADOR" => "Código Prestador",
                "CD_TRANSACAO" => "Código Transação",
                "NR_SERIE_DOC_ORIGINAL" => "Número Série",
                "NR_DOC_ORIGINAL" => "Número Documento",
                "CD_TIPO_VINCULO" => "Tipo Vinculo",
                "CD_PRESTADOR_PAGAMENTO" => "Prestador Pagamento",
                "PROCEDIMENTO" => "Procedimento",
                "NR_PERREF" => "Número Período Ref.",
                "DT_ANOREF" => "Data Ano Ref.",
            ];

            //* Criar cabeçalhos da tabela
            echo '<thead class="table-dark"><tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr></thead>';

            //* Preencher os dados
            echo '<tbody>';
            if(empty($_SESSION['dadosProcedimento'])) {
                echo '<tr><td class="text-center" colspan="' . count($headers) . '">Sem Registros</td></tr>';
            }else{
                foreach ($_SESSION['dadosProcedimento'] as $row) {
                    echo '<tr>';
                    foreach (array_keys($headers) as $key) {
                        echo '<td>' . htmlspecialchars($row[$key] ?? '-') . '</td>'; // Exibe "-" se o valor não existir
                    }
                    echo '</tr>';
                }
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';

        } else {
            echo '<p class="text-center">Nenhum dado encontrado na sessão.</p>';
        }

        ?>

    </div>
    
    <!-- BOTÃO criação em runtime-->
    <div id="btnAlterarPrest" class="text-center mt-3">

    </div>
    <div class="text-center">
        <p id="retornoAlteracao">
        <!-- Mensagem de sucesso -->
        </p>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../../prestadores.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            //! preenche prestadores no input select para editar
            initSelect2('#novoCodigo', '#editarPrestadorModal'); 
            getPrestador('../../utils/get_prestador.php', ['#novoCodigo']);


            const dadosPrestador = <?php echo json_encode($_SESSION['dadosPrestador'] ?? []); ?>;
            const dadosInsumos = <?php echo json_encode($_SESSION['dadosInsumos'] ?? []); ?>;
            const dadosProcedimento = <?php echo json_encode($_SESSION['dadosProcedimento'] ?? []); ?>;
            document.getElementById('loadingOverlay').style.display = 'none';
            
            let prestPrincipal;
            let nrDocOriginal;
            let transacao;
            let nrSerie;
            let nrPeriodo;
            let anoRef;
            if (Array.isArray(dadosPrestador) && dadosPrestador.length > 0) {
                // console.log(dadosPrestador);

                // Construir lista de NR_DOC_ORIGINAL separados por vírgula
                const nrDocOriginalList = [];
                dadosPrestador.forEach(row => {
                    prestPrincipal = row['CD_PRESTADOR_PRINCIPAL'] || '-';
                    nrDocOriginal = row['NR_DOC_ORIGINAL'] || '-';
                    transacao = row['CD_TRANSACAO'] || '-';
                    nrSerie = row['NR_SERIE_DOC_ORIGINAL'] || '-';
                    nrPeriodo = row['NR_PERREF'] || '-';
                    anoRef = row['DT_ANOREF'] || '-';
                    if (nrDocOriginal !== '-' && !nrDocOriginalList.includes(nrDocOriginal)) {
                        nrDocOriginalList.push(nrDocOriginal);
                    }
                });

                // Converter para string separada por vírgulas
                const nrDocOriginalString = nrDocOriginalList.join(',');

                // Criar botão para abrir modal e atualizar dados
                const botaoAlterarContainer = document.getElementById('btnAlterarPrest');
                if (botaoAlterarContainer) {
                    const button = document.createElement('button');
                    button.className = 'btn btn-success';
                    // button.setAttribute('data-bs-toggle', 'modal');
                    // button.setAttribute('data-bs-target', '#modalSenha');
                    button.textContent = 'Alterar Prestador';

                    button.addEventListener('click', () => {
                        editarPrestador(prestPrincipal, nrDocOriginalString, transacao, nrSerie, nrPeriodo, anoRef);
                    });

                    botaoAlterarContainer.appendChild(button);
                } else {
                    console.warn('Elemento com ID "btnAlterarPrest" não encontrado.');
                }
            } else {
                console.warn('Nenhum dado encontrado em dadosPrestador.');
            }
            
        })
    </script>
    
    <script>
        
       function editarPrestador(codigo, nrDocOriginal, transacao, nrSerie, nrPeriodo, anoRef) {
            //* Preencher o campo oculto no modal com o código do prestador
            document.getElementById('codigoAtual').value = codigo  || '';
            document.getElementById('nrDocOriginal').value = nrDocOriginal  || '';
            document.getElementById('transacao').value = transacao  || '';
            document.getElementById('nrSerie').value = nrSerie  || '';
            document.getElementById('nrPeriodo').value = nrPeriodo  || '';
            document.getElementById('anoRef').value = anoRef  || '';

            //* Abrir o modal
            const modal = new bootstrap.Modal(document.getElementById('editarPrestadorModal'));
            modal.show();
        }

        // Enviar os dados ao backend
        document.getElementById('editarPrestadorForm').addEventListener('submit', function (e) {
            e.preventDefault(); // Evita o recarregamento da página

            //!LOADER
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('textoLoad').innerText = 'Atualizando registro, aguarde por favor!';

            const codigoAtual   = document.getElementById('codigoAtual').value;
            const novoCodigo    = document.getElementById('novoCodigo').value;
            const nrDocOriginal = document.getElementById('nrDocOriginal').value;
            const transacao     = document.getElementById('transacao').value;
            const nrSerie       = document.getElementById('nrSerie').value;
            const nrPeriodo     = document.getElementById('nrPeriodo').value;
            const anoRef        = document.getElementById('anoRef').value;

            console.log({ codigoAtual, novoCodigo, nrDocOriginal, transacao, nrSerie, nrPeriodo, anoRef});
    
            axios({
                method: 'post',
                url: 'update_prestadorPrincipal.php', //teste.php
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                data: {
                    newPrestador: novoCodigo,
                    oldPrestadorPrincipal: codigoAtual,
                    nrDocOriginal: nrDocOriginal,
                    transacao: transacao,
                    nrSerie: nrSerie,
                    nrPeriodo: nrPeriodo,
                    anoRef: anoRef
                }
            })
            .then(function (response) {
                console.log(response.data);

                if (response.data.error === false) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonText: 'Ok',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // Após o alerta desaparecer, recarregar a página
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: response.data.message || 'Algo deu errado!',
                        icon: 'error',
                    });
                }

                document.getElementById('loadingOverlay').style.display = 'none';
            })
            .catch(function (error) {
                console.error('Erro ao realizar a requisição:', error);

                // Verifica se o erro contém uma resposta do servidor
                if (error.response) {
                    Swal.fire({
                        title: 'Erro!',
                        text: `Erro no servidor: ${error.response.status} - ${error.response.statusText}`,
                        icon: 'error',
                    });
                } else if (error.request) {
                    // Caso o erro ocorra antes do servidor responder
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Nenhuma resposta do servidor. Verifique sua conexão ou entre em contato com o suporte.',
                        icon: 'error',
                    });
                } else {
                    // Erros ao configurar a requisição
                    Swal.fire({
                        title: 'Erro!',
                        text: `Erro inesperado: ${error.message}`,
                        icon: 'error',
                    });
                }

                document.getElementById('loadingOverlay').style.display = 'none';
            });

            // axios({
            //     method: 'post',
            //     url: 'update_prestadorPrincipal.php',
            //     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            //     data: {
            //         newPrestador: novoCodigo,
            //         oldPrestadorPrincipal: codigoAtual,
            //         nrDocOriginal: nrDocOriginal,
            //         transacao: transacao,
            //         nrSerie: nrSerie,
            //         nrPeriodo: nrPeriodo,
            //         anoRef: anoRef
            //     }
            // })
            // .then(function (response) {
            //     console.log(response.data);
            //     if(response.data.error == false){
            //         Swal.fire({
            //             title: 'Sucesso!',
            //             text: response.data.message,
            //             icon: 'success',
            //             confirmButtonText: 'Ok',
            //             timer: 2000,  
            //             timerProgressBar: true  // Exibe a barra de progresso durante o timer
            //         }).then(() => {
            //             // Após o alerta desaparecer, recarregar a página
            //             location.reload();
            //         });


            //     }else{
            //         Swal.fire({
            //             title: 'Erro!',
            //             text: response.data.message || 'Algo deu errado!',
            //             icon: 'error',
            //         });
            //     }
            //     document.getElementById('loadingOverlay').style.display = 'none';
            // })

        });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
    
</body>
</html>
