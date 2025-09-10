<!-- Modal Cadastrar -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-labelledby="modalCadastrarLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastrarLabel">Cadastrar/Editar usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- //* Nav Tabs -->
                <ul class="nav nav-tabs" id="userTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tabCadastrar" data-bs-toggle="tab" data-bs-target="#cadastrar" type="button" role="tab" aria-controls="cadastrar" aria-selected="true">Cadastrar</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tabEditar" data-bs-toggle="tab" data-bs-target="#editar" type="button" role="tab" aria-controls="editar" aria-selected="false">Editar</button>
                    </li>
                </ul>

                 <!-- //*Tab Content -->
                <div class="tab-content" id="userTabContent">
                    <!-- //! Cadastrar Novo Usuário -->
                    <div class="tab-pane fade show active" id="cadastrar" role="tabpanel" aria-labelledby="tabCadastrar">
                        <form id="formCadastrar" action="utils/cadastrar_usuario.php" method="POST">

                            <div class="mb-3 form-floating-label">
                                <input id="nomeCadadastrar" type="text" class="form-control inputback" name="nome" placeholder=" " required maxlength="30">
                                <label for="nomeCadadastrar" >Nome *</label>
                            </div>

                            <div class="mb-3 form-floating-label">
                                <input id="usuarioCadastrar" type="text" class="form-control inputback" name="usuario" placeholder=" " required maxlength="30">
                                <label for="usuarioCadastrar">Usuário*</label>
                            </div>

                            <!-- //! adm - somente TI -->
                             <?php if(isset($_SESSION['idusuario']) && $_SESSION['idusuario'] == 1){
                                ?>
                                    <div class="mb-3 ">
                                    <input id="admCadastro" type="checkbox" name="adm">
                                    <label for="admCadastro">Administrador</label>
                                    </div>
                                <?php 
                                } 
                                ?>
                            

                            <!-- //! GERENCIAMENTO DE PERMISSÕES -->
                            <div id="gerenciamentoPermissoes" class="container mt-4">
                                <h5>Gerenciar Permissões de Telas</h5>
                                <div id="formPermissoes">
                                    <!-- O conteúdo gerado dinamicamente  -->
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-success">Cadastrar</button>
                            </div>
                        </form>
                    </div>

                    <!-- //! Editar Usuário -->
                    <div class="tab-pane fade" id="editar" role="tabpanel" aria-labelledby="tabEditar">
                        <form id="formEditar" action="utils/editar_permissoes_usuario.php" method="POST">
                            <div class="card mb-3 mt-3">
                                <!-- //* Nav Tabs Atualizar 1 por 1 ou todos-->
                                <div class="card-header ">
                                    <!-- <input type="checkbox" id="allUsuarios" class=" cursor-pointer" onchange="toggleCheckUsuario()"> -->
                                    <span class="form-check-label fw-bold cursor-pointer">Usuários</span>
                                </div>
                                <div id="listaUsuarios" class="card-body" style="height: 200px; overflow:auto">
                                    <!-- //! carrega todos os usuarios dinamicamente -->
                                </div>
                            </div>
                            <!-- //! GERENCIAMENTO DE PERMISSÕES - Editar -->
                            <div id="gerenciamentoPermissoesEditar" class="container mt-4">
                                    <h5>Gerenciar Permissões de Telas</h5>
                                    <div id="formPermissoesEditar">
                                        <!-- O conteúdo gerado dinamicamente  -->
                                    </div>
                                </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                            
                        </form>
                            <div class="text-center mt-2">
                                <button id="btnResetSenha"  style="display:none;" class="btn btn-warning"><i class="bi bi-key"></i> Resetar Senha do Usuário</button>
                            </div>
                    </div>
                <!-- //! Retorno insert -->
                <?php
                    if(isset($_SESSION['retornoCadUser'])){
                        $retorno = $_SESSION['retornoCadUser'];
                        ?>
                        <script>
                            //! mensagem de erro sweet alert
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: '<?php echo ($retorno['type']); ?>',
                                    title: '',
                                    text: '<?php echo addslashes($retorno['message']); ?>',
                                    timer: 2500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                            });
                        </script>
                        <?php
                        unset($_SESSION['retornoCadUser']); //* Limpa a variável da sessão após o uso
                    }
                ?>
                </div>
               
            </div>
        </div>
    </div>
</div>

<script>
    //! Limpa o foco quando o modal está prestes a ser fechado
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalCadastrar');
        
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
    });
</script>

