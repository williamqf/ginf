<?php
session_start();
/*   
	Copyright 2025 Flávio Ribeiro

	This file is part of OCOMON.

	OCOMON is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.
	OCOMON is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Foobar; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
    if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
        $_SESSION['session_expired'] = 1;
        echo "<script>top.window.location = '../../index.php'</script>";
        exit();
    }

    if ($_SESSION['s_nivel'] != 1) {
        exit;
    }
    
    require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";

    $_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= TRANS('SERVER_INFO'); ?></title>

		<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
		<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
		<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
		<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

    <style>
        .card-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .bg-system {
            background-color: #e3f2fd;
        }
        .bg-server {
            background-color: #f1f8e9;
        }
        .bg-php {
            background-color: #fff8e1;
        }
        .bg-db {
            background-color: #f3e5f5;
        }
        .server-info-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .health-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .module-item {
            padding: 8px 12px;
            margin-bottom: 5px;
            border-radius: 4px;
            background-color: #f8f9fa;
        }
        .module-item i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
<input type="hidden" name="system_online_label" id="system_online_label" value="<?= TRANS('SYSTEM_ONLINE'); ?>">
<input type="hidden" name="system_offline_label" id="system_offline_label" value="<?= TRANS('SYSTEM_OFFLINE'); ?>">
<input type="hidden" name="system_checking_label" id="system_checking_label" value="<?= TRANS('CHECKING_CONNECTION'); ?>">
<div class="container-fluid py-4">
    <div id="idLoad" class="loading" style="display:none"></div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-server mr-3 ocomon-color-3" style="font-size: 2rem;"></i>
                    <div>
                        <h1 class="h3 mb-0"><?= TRANS('SERVER_INFO'); ?></h1>
                        <p class="text-muted mb-0"><?= TRANS('HELPER_SERVER_INFO'); ?></p>
                    </div>
                    <div class="ml-auto">
                        <span id="systemStatus" class="badge p-2"><?= TRANS('CHECKING_CONNECTION'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Resumo do Sistema -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-system">
                    <i class="fas fa-desktop mr-2"></i><?= TRANS('SYSTEM'); ?>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-laptop server-info-icon ocomon-color-3"></i>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-server text-muted mr-2"></i><?= TRANS('SERVER_NAME'); ?>:</span> 
                            <span class="font-weight-bold" id="serverName"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-hdd text-muted mr-2"></i><?= TRANS('OPERATING_SYSTEM'); ?>:</span>
                            <span class="font-weight-bold" id="os"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Informações do Servidor Web -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-server">
                    <i class="fas fa-globe mr-2"></i><?= TRANS('WEBSERVER'); ?>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-cloud server-info-icon ocomon-color-3"></i>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-cog text-muted mr-2"></i><?= TRANS('SOFTWARE'); ?>:</span>
                            <span class="font-weight-bold" id="serverSoftware"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-map-marker-alt text-muted mr-2"></i><?= TRANS('SERVER_ADDRESS'); ?>:</span>
                            <span class="font-weight-bold" id="serverAddr"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Informações do PHP -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-php">
                    <i class="fab fa-php mr-2"></i>PHP
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fab fa-php server-info-icon ocomon-color-3"></i>
                        <h3 class="h4 mb-0" id="php_version"></h3>
                    </div>
                    <hr>
                    <h6 class="mb-3"><?= TRANS('REQUIRED_PHP_MODULES'); ?>:</h6>
                    <div id="requiredModules" class="mb-3">
                        <!-- Preenchido via JavaScript -->
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Informações do Banco de Dados -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-db">
                    <i class="fas fa-database mr-2"></i><?= TRANS('DATABASE'); ?>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-database server-info-icon ocomon-color-3"></i>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-code-branch text-muted mr-2"></i><?= TRANS('COL_VERSION'); ?>:</span>
                            <span class="font-weight-bold" id="db_version"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-flag-checkered text-muted mr-2"></i>Checkpoint:</span>
                            <span class="font-weight-bold" id="app_db_checkpoint"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações Detalhadas -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <i class="fas fa-info-circle mr-2"></i><?= TRANS('DETAILED_INFORMATION'); ?>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded" id="rawData" style="max-height: 300px; overflow: auto;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS e dependências -->
<script src="../../includes/components/jquery/jquery.js"></script>
<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
<script>
    
    $(function() {
    
        const serverData = {};

        getServerInfo();
        checkServerConnection();

        setInterval(function() {
            checkServerConnection();
            getServerInfo();
        }, 10000); // 10000 milissegundos = 10 segundos

    });


    function checkServerConnection() {
        checkInternetAccess().then(hasAccess => {
            
            $('#systemStatus').removeClass('badge-success');
            $('#systemStatus').removeClass('badge-danger');
            $('#systemStatus').text($('#system_checking_label').val());
            
            if (hasAccess) {
                $('#systemStatus').addClass('badge-success');
                $('#systemStatus').text($('#system_online_label').val());
            } else {
                $('#systemStatus').addClass('badge-danger');
                $('#systemStatus').text($('#system_offline_label').val());
            }
        });
    }



    function getServerInfo() {
        var loading = $(".loading");
        $(document).ajaxStart(function() {
            loading.show();
        });
        $(document).ajaxStop(function() {
            loading.hide();
        });
        $.ajax({
            url: 'server_info_process.php',
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                updateServerInfo(data);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao obter informações do servidor:', error);
            }
        });
    }


    // Função para atualizar os dados no painel
    function updateServerInfo(data) {
        // Informações básicas
        document.getElementById('serverName').textContent = data.serverName;
        document.getElementById('os').textContent = simplifyOsName(data.os);
        document.getElementById('serverSoftware').textContent = simplifyServerInfo(data.serverSoftware);
        document.getElementById('serverAddr').textContent = data.serverAddr;
        // document.getElementById('uri').textContent = data.uri;
        document.getElementById('php_version').textContent = `Versão ${data.php_version}`;
        document.getElementById('db_version').textContent = data.db_version;
        document.getElementById('app_db_checkpoint').textContent = data.app_db_checkpoint;
        
        // Preencher módulos PHP requeridos
        const requiredModulesDiv = document.getElementById('requiredModules');
        /* Limpar os módulos antigos */
        requiredModulesDiv.innerHTML = '';
        data.php_required_modules.forEach(module => {
            const moduleItem = document.createElement('div');
            moduleItem.className = 'module-item';
            if (data.php_missing_modules.includes(module)) {
                moduleItem.className += ' text-danger';
                moduleItem.innerHTML = `<i class="fas fa-times-circle"></i> ${module}`;
            } else {
                moduleItem.className += ' text-success';
                moduleItem.innerHTML = `<i class="fas fa-check-circle"></i> ${module}`;
            }
            requiredModulesDiv.appendChild(moduleItem);
        });
        
        // Exibir dados brutos em JSON formatado
        document.getElementById('rawData').textContent = JSON.stringify(data, null, 2);
    }
    
    // Função para simplificar a informação do servidor
    function simplifyServerInfo(serverInfo) {
        const parts = serverInfo.split(' ');
        if (parts.length > 0) {
            return parts[0]; // Retorna apenas "Apache/2.4.56"
        }
        return serverInfo;
    }
    
    // Função para simplificar o nome do SO
    function simplifyOsName(osInfo) {
        if (osInfo.includes('Linux')) {
            return 'Linux ' + osInfo.split(' ')[1];
        }
        return osInfo;
    }

    async function checkInternetAccess() {
        try {
            // Tenta fazer uma requisição a um servidor confiável
            const response = await fetch('https://www.google.com', { method: 'HEAD', mode: 'no-cors' });

            // Verifica se a resposta foi bem-sucedida
            if (response.ok || response.type === 'opaque') {
                return true;
            } else {
                return false;
            }
        } catch (error) {
            // Se houver um erro na requisição, assume que não há acesso à internet
            return false;
        }
    }
    
</script>
</body>
</html>
