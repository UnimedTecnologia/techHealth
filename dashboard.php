<?php
    session_start();
    if (!isset($_SESSION['idusuario'])) {
        header("Location: index.php");
        exit();
    }else{
        unset($_SESSION['primeiroAcessoTH']); 
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="style.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.24/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.24/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Title personalizado  -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/scale.css">
    <style>
        /* Sidebar Container */
        #sidebar {
            background-color: #00995D;
            color: white;
            width: 60px;
            transition: width 0.3s ease-in-out;
            overflow: scroll;
            height: 90vh;
            margin-top: 70px;
            position: fixed;
        }

        #sidebar.expanded {
            width: 300px;
        }

        /* Sidebar Buttons */
        #sidebar button {
            background: none;
            border: none;
            color: white;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            width: 100%;
            white-space: nowrap;
        }

        #sidebar button:hover {
            background-color: #007c4b;
            position:static;
            /* margin-left:5px; */
        }

        #sidebar i {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        #sidebar span {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        #sidebar.expanded span {
            opacity: 1;
        }

        /* Toggle Button */
        #toggleSidebar {
            /* position: absolute; */
            margin-top: 10px;
            margin-bottom:10px;
            right: -15px;
            background-color: #00995D;
            color: white;
            border: none;
            /* border-radius: 50%; */
            height: 30px;
            width: 30px;
            text-align: center;
        }

        /* //! overflow personalizado */
        * {
            scrollbar-width: thin; /* Para navegadores como Firefox */
            scrollbar-color: #009B63 transparent; /* Cor do scrollbar e da trilha */
        }

        *::-webkit-scrollbar {
            width: 3px; /* largura do scrollbar vertical */
            height: 3px; /* altura do scrollbar horizontal */
        }

        *::-webkit-scrollbar-thumb {
            background-color: black; /* cor do "polegar" do scrollbar */
            border-radius: 5px; /* opcional: bordas arredondadas */
        }

        *::-webkit-scrollbar-track {
            background: transparent; /* fundo da trilha do scrollbar */
        }
    </style>
</head>
<body>
<?php include_once "nav.php";  // CABEÇALHO NAVBAR ?>

    <!--//! loader  -->
    <div id="loadingOverlay">
        <div class="spinner"></div>
        <p id="msgLoader">Carregando</p>
    </div>
    <!-- Sidebar -->
    <div id="sidebar">
        <!-- //!CARREGA DINAMICAMENTE -->
    </div>
    
    <div class="content background-image" id="content"></div>

    <div class="text-center">
    <?php
        // Exibição de erro, se houver
        if (isset($_SESSION['erroRelatorio'])) {
            ?>
            <script>
                //! mensagem de erro sweet alert
                document.addEventListener('DOMContentLoaded', function() {
                    $('#modalRelatorioPrestador').modal('show');
                    Swal.fire({
                        icon: 'error',
                        title: '',
                        text: '<?php echo addslashes($_SESSION['erroRelatorio']); ?>',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                });
            </script>
            <?php
            unset($_SESSION['erroRelatorio']); // Limpar a sessão após exibir a mensagem
        }

        //* Geração e download automático do relatório
        if (isset($_SESSION['dadosRelatorio'])) {

            $dadosRelatorio = $_SESSION['dadosRelatorio'];

            $nomeDoc = "relatorio_" . $dadosRelatorio['nomeDoc'] . "_" . date("d-m-Y") .  ".xlsx"; //* Nome do relatorio para baixar
            // $nomeDoc = "relatorio_" . $dadosRelatorio['nomeDoc'] . "_" . date("d-m-Y") .  ".csv"; //* Nome do relatorio para baixar
            $arquivoPath = "relatorios/" . $dadosRelatorio['arquivoPath'];
            ?>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    //* Iniciar o download automaticamente
                    const link = document.createElement('a');
                    link.href = '<?php echo $arquivoPath; ?>';
                    link.download = '<?php echo $nomeDoc; ?>';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: 'O relatório será baixado em breve!',
                        timer: 4000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                });
            </script>
            <?php
            //! limpa sessão apos baixar
            unset($_SESSION['dadosRelatorio']);
        }

        if (isset($_SESSION['erroCarteira'])) {
            ?>
            <script>
                //! mensagem de erro sweet alert
                document.addEventListener('DOMContentLoaded', function() {
                    $('#modalAlteraCarteiraBeneficiario').modal('show');
                    Swal.fire({
                        icon: 'error',
                        title: '',
                        text: '<?php echo addslashes($_SESSION['erroCarteira']); ?>',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                });
            </script>
            <?php
            unset($_SESSION['erroCarteira']); // Limpar a sessão após exibir a mensagem
        }

        //* Exibição de erro, se houver
        if (isset($_SESSION['erroPrestador'])) {
            ?>
            <script>
                //! mensagem de erro sweet alert
                document.addEventListener('DOMContentLoaded', function() {
                    $('#modalAlteraPrestador').modal('show');
                    Swal.fire({
                        icon: 'error',
                        title: '',
                        text: '<?php echo addslashes($_SESSION['erroPrestador']); ?>',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                });
            </script>
            <?php
            unset($_SESSION['erroPrestador']); // Limpar a sessão após exibir a mensagem
        }
        ?>
    </div>

    <?php
        if(isset($_SESSION['retornoCadUser'])){
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    $('#modalCadastrar').modal('show');
                });
            </script>
            <?php         
        }

        if(isset($_SESSION['retornoEditarPermissoes'])){
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?php echo $_SESSION['retornoEditarPermissoes']['type']; ?>',
                        title: '',
                        text: '<?php echo addslashes($_SESSION['retornoEditarPermissoes']['message']); ?>',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                    $('#modalCadastrar').modal('show');
                    
                });
            </script>
            <?php     
             unset($_SESSION['retornoEditarPermissoes']);    
        }
        //! verificação erro valores porcentagem documento
        if(isset($_SESSION['dadosValoresDoc']) && $_SESSION['dadosValoresDoc']['error'] == true){

            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        //icon: '<?php //echo $_SESSION['dadosValoresDoc']['type']; ?>',
                        title: '',
                        text: '<?php echo addslashes($_SESSION['dadosValoresDoc']['message']); ?>',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                    $('#modalAlteraPorcentagemDocumento').modal('show');
                    
                });
            </script>
            <?php     
             unset($_SESSION['dadosValoresDoc']);    
        }
    ?>

    <!-- //! RELATORIO DIU -->
    <?php
    if (isset($_SESSION['RelatorioDiu'])) {

        $RelatorioDiu = $_SESSION['RelatorioDiu'];

        // $nomeDoc = "relatorio_" . $RelatorioDiu['nomeDoc'] . "_" . date("d-m-Y") .  ".xlsx"; //* Nome do relatorio para baixar
        $nomeDoc = "relatorio_" . $RelatorioDiu['nomeDoc'] . "_" . date("d-m-Y") .  ".csv"; //* Nome do relatorio para baixar
        $arquivoPath = "diu/" . $RelatorioDiu['arquivoPath'];
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                //* Iniciar o download automaticamente
                const link = document.createElement('a');
                link.href = '<?php echo $arquivoPath; ?>';
                link.download = '<?php echo $nomeDoc; ?>';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: 'O relatório será baixado em breve!',
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        </script>
        <?php
        //! limpa sessão apos baixar
        unset($_SESSION['RelatorioDiu']);
        
    //! Sem resultados relatorio DIU
    }else if (isset($_SESSION['erroRelatorioDiu'])){
        ?>
         <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#modalRelatorioDIU').modal('show');
                Swal.fire({
                    icon: 'error',
                    title: '',
                    text: '<?php echo addslashes($_SESSION['erroRelatorioDiu']); ?>',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        </script>
        
        <?php
        unset($_SESSION['erroRelatorioDiu']);
    }
    ?>


    <!-- //* Modais -->
      <?php include_once "modalAutorizacao.php"; ?>
      <?php include_once "modalRelatorioPrestador.php"; ?>
      <?php include_once "modalCorrecaoPrestador.php"; ?>
      <?php include_once "modalTrocaCarteiraBeneficiario.php"; ?>
      <?php include_once "modalTrocaPrestador.php"; ?>
      <?php include_once "modalSituacaoCadastral.php"; ?>
      <?php include_once "modalCadastrar.php"; ?>
      <?php include_once "modalAlterarPorcentDocumento.php"; ?>
      <?php include_once "modalRelatorioDIU.php"; ?>
      <?php include_once "modalAtualizacaoStatusGuia.php"; ?>
      <?php include_once "modalAtualizaFaturamento.php"; ?>
      <?php include_once "modalRelatorioGuias.php"; ?>
      <?php include_once "modalProducaoClinicas.php"; ?>
      <?php include_once "modalTrocaPeriodoCooperado.php"; ?>
      <?php include_once "modalDocPendenteLiberacao.php"; ?>
      <?php include_once "modalTrocaAcomodacao.php"; ?>
      <?php include_once "modalDadosProfissionalExecutante.php"; ?>
      

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script src="prestadores.js"></script>

    <?php 
        if (isset($_SESSION['adm']) && $_SESSION['adm'] == 'S') { 
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                await getTelas("formPermissoes");
                await getTelas("formPermissoesEditar");
                await getUsuariosDoAdm();
            });
        </script>

    <?php 
        } 
    ?>


</body>
</html>
