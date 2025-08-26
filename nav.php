<nav class="navbar navbar-expand-lg d-flex align-items-center" style="padding: 10px; background-color: #00995D; position: fixed; z-index: 1050; top: 0; width: 100%; height: 70px;">
    <div class="container-fluid">
        <!-- Logo -->
        <div class="d-flex align-items-center">
            <!-- <img src="http://186.209.52.83/tech_health/images/techealth.png" class="img-fluid logoTechealth" alt="Logo"> -->
            <!-- <img src="http://192.168.1.15/tech_health/images/techealth.png" class="img-fluid logoTechealth" alt="Logo"> -->
             <img src="/techHealth/images/techealth.png" class="img-fluid logoTechealth" alt="Logo">

        </div>

        <!-- Texto de boas-vindas -->
        <div class="text-white text-center d-none d-md-block mx-auto">
            <h4 class="m-0" style="font-size: 16px; max-width: 500px;">
                Bem-vindo <?php echo $_SESSION['nomeuser']; ?>
            </h4>
        </div>

        
        <div class="d-flex align-items-center">
            <?php 
                if (isset($_SESSION['adm']) && $_SESSION['adm'] == 'S' && basename($_SERVER['PHP_SELF']) === 'dashboard.php') { 
                ?>
                    <button id="btnCadUser" class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#modalCadastrar" style="margin-right:50px">
                        Cadastrar/Editar Usuário
                    </button>
                <?php 
                } 
            ?>
            <!-- Botão "Sair" -->
            <a class="text-white" href="/techHealth/logout.php" >Sair</a>
        </div>
    </div>
</nav>
