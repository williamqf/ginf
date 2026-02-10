/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;



ALTER TABLE `scripts` CHANGE `scpt_nome` `scpt_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `scripts` CHANGE `scpt_desc` `scpt_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `fornecedores` CHANGE `forn_nome` `forn_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `fornecedores` CHANGE `forn_fone` `forn_fone` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `ocorrencias` ADD INDEX(`problema`), ADD INDEX(`equipamento`), ADD INDEX(`sistema`), ADD INDEX(`operador`), ADD INDEX(`status`), ADD INDEX(`data_atendimento`), ADD INDEX(`instituicao`), ADD INDEX(`oco_real_open_date`), ADD INDEX(`date_first_queued`), ADD INDEX(`oco_scheduled_to`), ADD INDEX(`profile_id`);

ALTER TABLE `equipamentos` ADD INDEX(`comp_marca`), ADD INDEX(`comp_mb`), ADD INDEX(`comp_proc`), ADD INDEX(`comp_memo`), ADD INDEX(`comp_video`), ADD INDEX(`comp_som`), ADD INDEX(`comp_rede`), ADD INDEX(`comp_modelohd`), ADD INDEX(`comp_modem`), ADD INDEX(`comp_cdrom`), ADD INDEX(`comp_dvd`), ADD INDEX(`comp_grav`), ADD INDEX(`comp_local`), ADD INDEX(`comp_fornecedor`), ADD INDEX(`comp_data`), ADD INDEX(`comp_data_compra`), ADD INDEX(`comp_ccusto`), ADD INDEX(`comp_tipo_equip`), ADD INDEX(`comp_tipo_imp`), ADD INDEX(`comp_resolucao`), ADD INDEX(`comp_polegada`), ADD INDEX(`comp_fab`), ADD INDEX(`comp_situac`), ADD INDEX(`comp_reitoria`), ADD INDEX(`comp_tipo_garant`), ADD INDEX(`comp_garant_meses`);


ALTER TABLE `config` ADD `conf_cat_chain_at_opening` VARCHAR(11) NULL DEFAULT NULL COMMENT 'Pré-filtros para tipos de solicitações na abertura de chamados' AFTER `conf_rate_after_deadline`; 

ALTER TABLE `config` ADD `conf_prob_tipo_4` VARCHAR(255) NOT NULL DEFAULT 'Categoria 4' AFTER `conf_prob_tipo_3`; 
ALTER TABLE `config` ADD `conf_prob_tipo_5` VARCHAR(255) NOT NULL DEFAULT 'Categoria 5' AFTER `conf_prob_tipo_4`; 
ALTER TABLE `config` ADD `conf_prob_tipo_6` VARCHAR(255) NOT NULL DEFAULT 'Categoria 6' AFTER `conf_prob_tipo_5`; 

ALTER TABLE `config` CHANGE `conf_prob_tipo_1` `conf_prob_tipo_1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Categoria 1', CHANGE `conf_prob_tipo_2` `conf_prob_tipo_2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Categoria 2', CHANGE `conf_prob_tipo_3` `conf_prob_tipo_3` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Categoria 3';

ALTER TABLE `sistemas` ADD `sis_cat_chain_at_opening` VARCHAR(11) NULL DEFAULT NULL COMMENT 'Pré-filtros para seleção de tipos de solicitações na abertura de chamados' AFTER `sis_opening_mode`; 

ALTER TABLE `sistemas` ADD `use_own_config_cat_chain` TINYINT(1) NULL DEFAULT '0' COMMENT 'Define se a área utilizará configuração própria para pré filtros de categorias na abertura de chamados' AFTER `sis_cat_chain_at_opening`; 

CREATE TABLE `prob_tipo_4` (`probt4_cod` INT(6) NOT NULL AUTO_INCREMENT , `probt4_desc` VARCHAR(255) NOT NULL , PRIMARY KEY (`probt4_cod`)) ENGINE = InnoDB COMMENT = 'Agrupamento de categorias de tipos de solicitações'; 

CREATE TABLE `prob_tipo_5` (`probt5_cod` INT(6) NOT NULL AUTO_INCREMENT , `probt5_desc` VARCHAR(255) NOT NULL , PRIMARY KEY (`probt5_cod`)) ENGINE = InnoDB COMMENT = 'Agrupamento de categorias de tipos de solicitações'; 

CREATE TABLE `prob_tipo_6` (`probt6_cod` INT(6) NOT NULL AUTO_INCREMENT , `probt6_desc` VARCHAR(255) NOT NULL , PRIMARY KEY (`probt6_cod`)) ENGINE = InnoDB COMMENT = 'Agrupamento de categorias de tipos de solicitações'; 


ALTER TABLE `problemas` ADD `prob_tipo_4` INT(4) NULL DEFAULT NULL AFTER `prob_tipo_3`, ADD INDEX (`prob_tipo_4`); 
ALTER TABLE `problemas` ADD `prob_tipo_5` INT(4) NULL DEFAULT NULL AFTER `prob_tipo_4`, ADD INDEX (`prob_tipo_5`); 
ALTER TABLE `problemas` ADD `prob_tipo_6` INT(4) NULL DEFAULT NULL AFTER `prob_tipo_5`, ADD INDEX (`prob_tipo_6`); 



ALTER TABLE `instituicao` ADD `addr_cep` VARCHAR(8) NULL DEFAULT NULL AFTER `inst_client`, ADD `addr_street` VARCHAR(255) NULL DEFAULT NULL AFTER `addr_cep`, ADD `addr_neighborhood` VARCHAR(255) NULL DEFAULT NULL AFTER `addr_street`, ADD `addr_city` VARCHAR(255) NULL DEFAULT NULL AFTER `addr_neighborhood`, ADD `addr_uf` VARCHAR(255) NULL DEFAULT NULL AFTER `addr_city`, ADD `addr_number` VARCHAR(255) NULL DEFAULT NULL AFTER `addr_uf`, ADD `addr_complement` VARCHAR(255) NULL DEFAULT NULL AFTER `addr_number`, ADD `observation` TEXT NULL DEFAULT NULL AFTER `addr_complement`, ADD INDEX (`addr_cep`);



-- Recursos alocáveis

CREATE TABLE `tickets_x_resources` (`id` INT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `model_id` INT NOT NULL , `amount` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`model_id`)) ENGINE = InnoDB COMMENT = 'Recursos alocados para o chamado';

ALTER TABLE `tickets_x_resources` ADD `unitary_price` FLOAT NULL AFTER `amount`, ADD `author` INT(6) NOT NULL AFTER `unitary_price`, ADD `is_current` TINYINT(1) NOT NULL DEFAULT '1' AFTER `author`, ADD `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `is_current`, ADD INDEX (`author`), ADD INDEX (`is_current`);


-- Autorizacao de atendimentos

ALTER TABLE `problemas` ADD `need_authorization` TINYINT(1) NULL DEFAULT '0' COMMENT 'Define se a solicitação precisa de autorização' AFTER `prob_area_default`, ADD INDEX (`need_authorization`); 

ALTER TABLE `problemas` ADD `card_in_costdash` TINYINT(1) NULL DEFAULT NULL COMMENT 'Se o tipo de solicitação será contabilizado no dash de custos' AFTER `need_authorization`, ADD INDEX (`card_in_costdash`); 

ALTER TABLE `ocorrencias` ADD `authorization_status` INT(1) NULL DEFAULT NULL COMMENT '1: Aguardando autorização - 2: Autorizado - 3: Recusado' AFTER `profile_id`, ADD INDEX (`authorization_status`); 

ALTER TABLE `ocorrencias` ADD `authorization_author` INT(6) NULL DEFAULT NULL COMMENT 'Responsável pela autorização ou recusa do chamado' AFTER `authorization_status`, ADD INDEX (`authorization_author`); 

CREATE TABLE `authorization_status` (`id` INT(2) NOT NULL , `name_key` VARCHAR(255) NOT NULL COMMENT 'chave para a real nomenclatura no arquivo de idiomas' , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Status de autorização';

ALTER TABLE `ocorrencias_log` ADD `log_authorization_status` INT(2) NULL DEFAULT NULL COMMENT 'Status de autorização de atendimento' AFTER `log_status`; 

ALTER TABLE `usuarios` ADD `max_cost_authorizing` FLOAT NULL DEFAULT NULL COMMENT 'Custo máximo que pode ser aprovado pelo usuário' AFTER `user_textcolor`, ADD INDEX (`max_cost_authorizing`); 


-- Custo dos chamados


ALTER TABLE `config` ADD `tickets_cost_field` INT(6) NULL DEFAULT NULL COMMENT 'Campo customizado para receber o custo dos chamados' AFTER `conf_rate_after_deadline`, ADD INDEX (`tickets_cost_field`); 

ALTER TABLE `config` ADD `status_waiting_cost_auth` INT(6) NULL DEFAULT NULL COMMENT 'status para chamados aguardando autorização sobre o custo' AFTER `tickets_cost_field`, ADD `status_cost_authorized` INT(6) NULL DEFAULT NULL COMMENT 'status para chamados com custo autorizado' AFTER `status_waiting_cost_auth`, ADD `status_cost_refused` INT(6) NULL DEFAULT NULL COMMENT 'status para chamados com custo recusado' AFTER `status_cost_authorized`;

ALTER TABLE `config` ADD `status_cost_updated` INT(6) NULL DEFAULT NULL COMMENT 'Status que o chamado deve receber ao ter o custo alterado' AFTER `status_cost_refused`; 


-- Modelos de mensagens

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'request-authorization', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Solicitação de autorização de serviço', '<p>Caro %contato%,</p><p>O chamado %numero% está aguardando por sua autorização para poder prosseguir com o atendimento.</p><p>Para autorizar ou rejeitar o atendimento, basta acessar o sistema e ir na seção: Aguardando autorização<p>Atte.Sistema OcoMon</p>', '<p>Caro %contato%,</p><p>O chamado %numero% está aguardando por sua autorização para poder prosseguir com o atendimento.</p><p>Para autorizar ou rejeitar o atendimento, basta acessar o sistema e ir na seção: Aguardando autorização<p>Atte.Sistema OcoMon</p>');

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'request-authorized', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Serviço autorizado', '<p>Caro %operador%,</p><p>O chamado %numero% foi aprovado para atendimento.<p>Atte.Sistema OcoMon</p>', '<p>Caro %operador%,</p><p>O chamado %numero% foi aprovado para atendimento.<p>Atte.Sistema OcoMon</p>');

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'request-denied', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Serviço não autorizado', '<p>Caro %operador%,</p><p>O chamado %numero% teve sua solicitação de autorização negada. <p>Atte.Sistema OcoMon</p>', '<p>Caro %operador%,</p><p>O chamado %numero% teve sua solicitação de autorização negada. <p>Atte.Sistema OcoMon</p>');


-- Projetos
ALTER TABLE `ocodeps` ADD `proj_id` INT(6) NULL DEFAULT NULL COMMENT 'Referencia na tabela de projetos' AFTER `dep_filho`, ADD INDEX (`proj_id`); 

CREATE TABLE `projects` (`id` INT NOT NULL , `name` VARCHAR(255) NOT NULL , `description` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Projetos relacionados à tabela ocodeps'; 

ALTER TABLE `projects` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT; 


-- Usuários vinculados a departamentos
ALTER TABLE `usuarios` ADD `user_department` INT NULL DEFAULT NULL COMMENT 'Departamento do usuario' AFTER `user_client`, ADD INDEX (`user_department`); 



-- Termos de compromisso e vinculacao de ativos a usuarios

CREATE TABLE `commitment_models` (`id` INT(6) NOT NULL AUTO_INCREMENT , `type` INT(2) NOT NULL COMMENT '1: Termo de compromisso, 2: Formulário de trânsito' , `html_content` LONGTEXT NOT NULL , `client_id` INT(6) NULL DEFAULT NULL , `unit_id` INT(6) NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`type`), INDEX (`client_id`), INDEX (`unit_id`)) ENGINE = InnoDB COMMENT = 'Modelos de termos de responsabililidade';

CREATE TABLE `users_x_assets` (`id` INT(7) NOT NULL AUTO_INCREMENT , `user_id` INT(6) NOT NULL , `asset_id` INT(6) NOT NULL , `author_id` INT(6) NOT NULL , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `signed_at` DATETIME NULL , `updated_at` DATETIME on update CURRENT_TIMESTAMP NULL , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`asset_id`), INDEX (`author_id`)) ENGINE = InnoDB COMMENT = 'Relacao usuarios x ativos';

ALTER TABLE `users_x_assets` ADD `is_current` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Define se o ativo ainda está vinculado ao usuário' AFTER `updated_at`, ADD INDEX (`is_current`); 


CREATE TABLE `users_x_files` (`id` INT(4) NOT NULL AUTO_INCREMENT , `user_id` INT(6) NOT NULL , `file_type` INT(2) NULL DEFAULT NULL COMMENT '1: foto de perfil, 2: termo de compromisso, 3: termo de compromisso assinado' , `file` LONGBLOB NOT NULL , `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`file_type`)) ENGINE = InnoDB COMMENT = 'Arquivos vinculados diretamente ao usuário';

ALTER TABLE `users_x_files` ADD `mime_type` VARCHAR(255) NULL DEFAULT NULL AFTER `file`, ADD `file_size` BIGINT NULL DEFAULT NULL AFTER `mime_type`; 

ALTER TABLE `users_x_files` ADD `file_name` VARCHAR(255) NULL AFTER `file`; 


INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'term-to-user', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Ativos sob sua responsabilidade', '<p>Caro %usuario_responsavel%,</p><p>Foi gerado um novo termo de compromisso com ativos sob sua responsabilidade</p>\r\n<p>%tabela_de_ativos%</p><p>Atte.Sistema OcoMon</p>', '<p>Caro %usuario_responsavel%,</p><p>Foi gerado um novo termo de compromisso com ativos sob sua responsabilidade</p>\r\n<p>%tabela_de_ativos%</p><p>Atte.Sistema OcoMon</p>');


ALTER TABLE `usuarios` ADD `term_unit` INT(6) NULL DEFAULT NULL COMMENT 'Unidade para geração do termo de compromisso' AFTER `user_client`, ADD INDEX (`term_unit`); 


ALTER TABLE `usuarios` ADD `term_unit_updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Monitoramento da mudança de valor para unidade' AFTER `term_unit`, ADD INDEX (`term_unit_updated_at`); 


CREATE TABLE `traffic_files` (`id` INT NOT NULL AUTO_INCREMENT , `info_id` INT NOT NULL COMMENT 'referencia para assets_traffic_info' , `file` LONGBLOB NOT NULL , `file_name` VARCHAR(255) NOT NULL , `mime_type` VARCHAR(255) NOT NULL , `file_size` BIGINT NOT NULL , `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`info_id`), INDEX (`uploaded_at`)) ENGINE = InnoDB COMMENT = 'Formulários de trânsito';

CREATE TABLE `assets_x_traffic` (`id` INT NOT NULL AUTO_INCREMENT , `info_id` INT NOT NULL COMMENT 'Referência para assets_traffic_info' , `asset_id` INT NOT NULL , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`info_id`), INDEX (`asset_id`), INDEX (`created_at`)) ENGINE = InnoDB COMMENT = 'Relacao de equipamentos por formulário de trânsito';


CREATE TABLE `assets_traffic_info` (`id` INT NOT NULL AUTO_INCREMENT , `carrier` VARCHAR(255) NOT NULL , `reason` TEXT NOT NULL , `user_authorizer` INT NOT NULL , `responsible_area` INT NOT NULL , `author_id` INT NOT NULL , `valid_until` DATETIME NULL DEFAULT NULL , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `updated_at` DATETIME on update CURRENT_TIMESTAMP NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`user_authorizer`), INDEX (`responsible_area`), INDEX (`author_id`), INDEX (`created_at`), INDEX (`updated_at`)) ENGINE = InnoDB COMMENT = 'Informações sobre cada formulário de trânsito';

ALTER TABLE `assets_traffic_info` ADD `destination` TEXT NULL AFTER `reason`; 


INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'traffic-term-to-authorizer', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Formulário de trânsito gerado', '<p>Caro %autorizado_por%,</p><p>Foi gerado um formulário de trânsito sob sua autorização</p>\r\n<p>%tabela_de_ativos%</p>\r\n<p>Área Responsável: %area_responsavel%</p>\r\n<p>Destino: %destino%</p>\r\n<p>Justificativa: %justificativa%</p>\r\n<p>Formulário gerado por: %autor%</p><p>Atte.Sistema OcoMon</p>', 'Caro %autorizado_por%,\r\n\r\nFoi gerado um formulário de trânsito sob sua autorização\r\n\r\n%tabela_de_ativos%\r\n\r\nÁrea Responsável: %area_responsavel%\r\n\r\nDestino: %destino%\r\n\r\nJustificativa: %justificativa%\r\n\r\nFormulário gerado por: %autor%\r\n\r\nAtte.Sistema OcoMon');



-- Contexto para variáveis de ambiente
ALTER TABLE `environment_vars` ADD `context` INT NULL DEFAULT NULL COMMENT '1: email, 2: termo de compromisso, 3: formulário de trânsito' AFTER `vars`, ADD `event` VARCHAR(255) NULL DEFAULT NULL COMMENT 'eventos específicos podem ter variáveis distintas' AFTER `context`, ADD INDEX (`context`), ADD INDEX (`event`);


INSERT INTO `environment_vars` (`id`, `vars`, `context`, `event`) VALUES (NULL, '<h4>Termo de Compromisso</h4>\r\n<strong>Tabela de ativos: </strong>%tabela_de_ativos%<br />\r\n<strong>Usuário responsável: </strong>%usuario_reponsavel%<br />\r\n<strong>Data: </strong>%data%<br />\r\n<strong>Data com hora: </strong>%data_e_hora%<br />\r\n<strong>Data da assinatura: </strong>%data_assinatura%<br />\r\n<strong>Assinatura: </strong>%assinatura%<br />\r\n<hr/>\r\n<h4>Formulário de Trânsito</h4>\r\n<strong>Tabela de ativos: </strong>%tabela_de_ativos%<br />\r\n<strong>Portador: </strong>%portador%<br />\r\n<strong>Autor: </strong>%autor%<br />\r\n<strong>Área responsável: </strong>%area_responsavel%<br />\r\n<strong>Destino: </strong>%destino%<br />\r\n<strong>Autorizado por: </strong>%autorizado_por%<br />\r\n<strong>Justificativa: </strong>%justificativa%<br />\r\n<strong>Data: </strong>%data%<br />\r\n<strong>Data com hora: </strong>%data_e_hora%', '2', NULL);


INSERT INTO `commitment_models` (`id`, `type`, `html_content`, `client_id`, `unit_id`) VALUES
(1, 1, '<h1>CENTRAL DE PATRIMÔNIO / SUPORTE AO USUÁRIO - SERVICE DESK<br></h1><h2>Termo de Compromisso para Equipamento<br></h2><p><br></p><p>Por esse termo acuso o recebimento do(s) equipamento(s) abaixo especificado(s), comprometendo-me a mantê-lo(s) sob a minha guarda e responsabilidade, dele(s) fazendo uso adequado, de acordo com a resolução xxx/ano que define políticas, normas e procedimentos que disciplinam a utilização de equipamentos, recursos e serviços de informática da SUA_EMPRESA.<br></p><p><br></p><p>%ativos%<br></p><p><br></p><h3><strong>Informações complementares</strong></h3><p>Departamento: %departamento%<br></p><p>Usuário responsável: <br></p><p><br></p><h3>IMPORTANTE:<br></h3><p>O suporte para qualquer problema que porventura vier a ocorrer na instalação ou operação do(s) equipamento(s), deverá ser solicitado à área de Suporte, através do telefone/ramal xxxx, pois somente através desde procedimento os chamados poderão ser registrados e atendidos.<br></p><p>Em conformidade com o preceituado no art. 1º da Resolução nº xxx/ano, é expressamente vedada a instalação de softwares sem a necessária licença de uso ou em desrespeito aos direitos autorais.<br></p><p>A SUA_EMPRESA, através do seu Departamento Responsável (XXXX), em virtude das suas disposições regimentais e regulamentadoras, adota sistema de controle de instalação de softwares em todos os seus equipamentos, impedindo a instalação destes sem prévia autorização do Departamento Competente.<br></p><p><span style="font-family: Arial"><br></span></p><table><tbody><tr><td><div><span style="font-family: Arial">%assinatura%</span><br></div></td><td><div>Data da assinatura: %data_assinatura%<br></div></td></tr><tr><td><div><span style="font-family: Arial">Assinatura do usuário responsável<br></span></div></td><td><div><span style="font-family: Arial">Data do documento: %data_e_hora%<br></span></div></td></tr></tbody></table><hr class=\"__se__solid\">', NULL, NULL),
(2, 2, '<h1>CENTRAL DE PATRIMÔNIO / SUPORTE AO USUÁRIO - SERVICE DESK<br></h1><h2>Formulário de Trânsito para Equipamentos<br></h2><p><br></p><p>Informo que o(s) equipamento(s) abaixo descriminado(s) está(ão) autorizado(s) pelo departamento responsável a serem transportados para fora da Unidade pelo portador citado:<br></p><p><br></p><p>%ativos%<br></p><p><br></p><h3><strong>Informações complementares</strong></h3><p>Portador: <br></p><p>Destino:<br></p><p>Motivo:<br></p><p>Autorizado por:<br></p><p>Departamento responsável: <br></p><p><br></p><h3>IMPORTANTE:<br></h3><p>A constatação de inconformidade dos dados aqui descritos no ato de verificação na portaria implica na não autorização de saída dos equipamentos, nesse caso o departamento responsável deve ser contactado.<br></p><p><br></p><table class=\"se-table-layout-fixed\"><tbody><tr><td><div>Assinatura:<br></div></td><td><div>Data do documento: %data_completa%<em></em><br></div></td></tr><tr><td><div><br></div></td><td><div><br></div></td></tr><tr><td><hr class=\"__se__solid\"></td><td><div><br></div></td></tr></tbody></table><p><br></p><p><br></p>', NULL, NULL);



-- Assinatura dos termos de compromisso

CREATE TABLE `users_x_signatures` (`id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `signature_file` MEDIUMBLOB NOT NULL , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`user_id`)) ENGINE = InnoDB COMMENT = 'Arquivos de assinatura vinculados ao usuário';

ALTER TABLE `users_x_signatures` ADD `file_type` VARCHAR(255) NOT NULL AFTER `signature_file`, ADD `file_size` INT NOT NULL AFTER `file_type`; 


ALTER TABLE `users_x_files` ADD `html_doc` LONGTEXT NULL DEFAULT NULL AFTER `file_type`; 

ALTER TABLE `users_x_files` ADD `signed_at` DATETIME NULL DEFAULT NULL AFTER `uploaded_at`, ADD INDEX (`signed_at`); 

CREATE TABLE `users_terms_pivot` (`id` INT NOT NULL , `user_id` INT NOT NULL , `has_term` TINYINT(1) NULL DEFAULT NULL , `is_term_updated` TINYINT(1) NULL DEFAULT NULL , `is_term_signed` TINYINT(1) NULL DEFAULT NULL , `signed_at` DATETIME NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`has_term`), INDEX (`is_term_updated`), INDEX (`is_term_signed`), INDEX (`signed_at`)) ENGINE = InnoDB COMMENT = 'Pivot para buscar info sobre assinaturas dos termos';

ALTER TABLE `users_terms_pivot` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT; 


-- Lotes de ativos
ALTER TABLE `equipamentos` ADD `batch_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Identificador de lote para quando o cadastro for em lote' AFTER `comp_part_number`, ADD `has_virtual_tag` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Identificador sobre a geração automática de etiquetas' AFTER `batch_id`, ADD INDEX (`batch_id`), ADD INDEX (`has_virtual_tag`);



-- A seguir, alterações para permitir a configuração de auto-encerramento por inatividade

ALTER TABLE `config` ADD `stats_to_close_by_inactivity` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Status que serão monitorados sobre a inatividade dos chamados' AFTER `conf_rate_after_deadline`, ADD `days_to_close_by_inactivity` INT(3) NOT NULL COMMENT 'Quantidade de dias limite para que o chamado seja encerrado por inatividade' AFTER `stats_to_close_by_inactivity`, ADD `rate_after_close_by_inactivity` ENUM('great','good','regular','bad','not_rated') NOT NULL DEFAULT 'great' COMMENT 'Avaliação que o chamado irá receber quando encerrado por inatividade' AFTER `days_to_close_by_inactivity`;

ALTER TABLE `config` ADD `only_weekdays_to_count_inactivity` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Define se serão considerados os finais de semana para a contagem dos dias para encerramento automático' AFTER `days_to_close_by_inactivity`;

ALTER TABLE `config` CHANGE `days_to_close_by_inactivity` `days_to_close_by_inactivity` INT(3) NOT NULL DEFAULT '7' COMMENT 'Quantidade de dias limite para que o chamado seja encerrado por inatividade'; 


INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'closed-by-inactivity', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Chamado encerrado no sistema', '<p>Caro %contato%,</p><p>Seu chamado %numero% foi encerrado de forma automática no sistema em função da falta de retorno de sua parte. </p><p>Atte.Sistema OcoMon</p>', 'Caro %contato%\r\n\r\nSeu chamado %numero% foi encerrado de forma automática no sistema em função da falta de retorno de sua parte. \r\n\r\nAtte.\r\nSistema OcoMon');


INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'request-feedback', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Solicitação de Retorno', '<p>Caro %contato%,</p><p>Nossa equipe de atendimento precisa de um retorno seu para o chamado %numero%.</p><p>Por favor, acesse a plataforma e interaja por meio de um comentário.</p><p>Atte.Sistema OcoMon</p>', 'Caro %contato%\r\n\r\nNossa equipe de atendimento precisa de um retorno seu para o chamado %numero%.\r\n\r\nPor favor, acesse a plataforma e interaja por meio de um comentário.\r\n\r\nAtte.\r\nSistema OcoMon');


ALTER TABLE `tickets_extended` ADD `auto_closed` TINYINT(1) NULL DEFAULT NULL AFTER `updated_at`, ADD INDEX (`auto_closed`); 

ALTER TABLE `config` ADD `stat_out_inactivity` INT(3) NOT NULL DEFAULT '1' COMMENT 'Status que o chamado assumirá nos casos em que o solicitante interagir pós inatividade' AFTER `stats_to_close_by_inactivity`, ADD INDEX (`stat_out_inactivity`); 




-- Abertura de chamados para outros usuários

ALTER TABLE `ocorrencias` ADD `registration_operator` INT NULL DEFAULT NULL COMMENT 'Operador responsável pelo registro no sistema' AFTER `aberto_por`, ADD INDEX (`registration_operator`); 

ALTER TABLE `ocorrencias_log` ADD `log_requester` INT NULL DEFAULT NULL COMMENT 'Solicitante' AFTER `log_quem`, ADD INDEX (`log_requester`); 


-- Usuario para processos automatizados no sistema
INSERT INTO `usuarios` (`user_id`, `user_client`, `user_department`, `login`, `nome`, `password`, `hash`, `data_inc`, `data_admis`, `email`, `fone`, `nivel`, `AREA`, `user_admin`, `last_logon`, `forget`, `can_route`, `can_get_routed`, `user_bgcolor`, `user_textcolor`) VALUES ('-1', NULL, NULL, '__auto__', 'Processo automatizado', NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, '0', NULL, NULL, NULL, NULL, '#3A4D56', '#FFFFFF');

UPDATE `usuarios` SET `user_id` = '0' WHERE `user_id` = '-1'; 



-- Cores nos status
ALTER TABLE `status` ADD `bgcolor` VARCHAR(8) NULL DEFAULT NULL AFTER `stat_ignored`, ADD `textcolor` VARCHAR(8) NOT NULL DEFAULT '#212529' AFTER `bgcolor` ; 



-- Modificações para ativos do tipo recurso
ALTER TABLE `equipamentos` ADD `is_product` TINYINT(1) NULL DEFAULT NULL COMMENT 'Define se o ativo é um produto' AFTER `comp_part_number`, ADD INDEX (`is_product`); 

ALTER TABLE `tipo_equip` ADD `can_be_product` TINYINT(1) NULL DEFAULT NULL COMMENT 'Define se o tipo de ativo pode ser produto' AFTER `tipo_categoria`, ADD INDEX (`can_be_product`); 

ALTER TABLE `tipo_equip` ADD `is_digital` TINYINT(1) NULL DEFAULT NULL COMMENT 'Define se o tipo é para ativos digitais' AFTER `can_be_product`, ADD INDEX (`is_digital`); 


-- Notificações relacionadas aos chamados - não oriundas do mural de avisos
CREATE TABLE `users_tickets_notices` (`id` INT NOT NULL AUTO_INCREMENT , `source_table` VARCHAR(255) NOT NULL , `notice_id` INT NOT NULL , `seen_at` DATETIME NULL , PRIMARY KEY (`id`), INDEX `source_and_row_id` (`source_table`, `notice_id`), INDEX (`seen_at`)) ENGINE = InnoDB COMMENT = 'Controle de notificações por usuários';

ALTER TABLE `users_tickets_notices` ADD `type` INT NOT NULL AFTER `id`, ADD INDEX (`type`); 
ALTER TABLE `users_tickets_notices` CHANGE `type` `type` INT(11) NULL DEFAULT NULL; 

ALTER TABLE `users_tickets_notices` ADD `requester_seen_at` DATETIME NULL DEFAULT NULL AFTER `seen_at`, ADD `treater_seen_at` DATETIME NULL DEFAULT NULL AFTER `requester_seen_at`, ADD INDEX (`requester_seen_at`), ADD INDEX (`treater_seen_at`); 

ALTER TABLE `users_tickets_notices` DROP `seen_at`;


-- Noticações diretas para usuários - não oriundas de chamados ou do mural de avisos
CREATE TABLE `users_notifications` (`id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `type` INT NOT NULL , `text` TEXT NOT NULL , `author` INT NOT NULL , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `seen_at` DATETIME NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`type`), INDEX (`author`), INDEX (`created_at`), INDEX (`seen_at`), FULLTEXT (`text`)) ENGINE = InnoDB COMMENT = 'Tabela de mensagens diversas para os usuários';



ALTER TABLE `msgconfig` ADD `has_specific_env_vars` TINYINT(1) NULL DEFAULT NULL COMMENT 'Define se o evento utilizará variáveis de ambiente específicas' AFTER `msg_altbody`, ADD INDEX (`has_specific_env_vars`); 



INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`, `has_specific_env_vars`) VALUES (NULL, 'alocate-asset-to-user', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Equipamento sob sua responsabilidade', '<p>Caro %usuario%,</p><p>Foram adicionados ou removidos ativos sob sua responsabilidade.</p><p>Responsável pelo processo: %autor% - %autor_departamento%<br></p><p>Por favor, acesse seu perfil de usuário na plataforma para conferir.</p><p>Atte.Sistema OcoMon</p>', 'Caro %usuario%\r\n\r\nForam adicionados ou removidos ativos sob sua responsabilidade.\r\n\r\nPor favor, acesse o seu perfil de usuário na plataforma para conferir.\r\n\r\nAtte.\r\nSistema OcoMon', '1');



INSERT INTO `environment_vars` (`id`, `vars`, `context`, `event`) VALUES (NULL, '<strong>Usuário: </strong>%usuario%<br /><strong>Autor: </strong>%autor%<br /><strong>Departamento do autor: </strong>%autor_departamento%<br />\r\n<strong>Data: </strong>%data%<br />', '2', 'alocate-asset-to-user');


-- Possibilitar a contabilização de tempo por cada operador responsável do chamado
ALTER TABLE `tickets_stages` ADD `treater_id` INT NULL DEFAULT NULL AFTER `status_id`, ADD INDEX (`treater_id`); 



-- Possibilitar o registro manual de operadores e períodos de atendimento
CREATE TABLE `tickets_treaters_stages` (`id` INT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `treater_id` INT NOT NULL , `date_start` DATETIME NOT NULL , `date_stop` DATETIME NOT NULL , `author` INT NOT NULL , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`treater_id`), INDEX (`date_start`), INDEX (`date_stop`), INDEX (`author`), INDEX (`created_at`)) ENGINE = InnoDB COMMENT = 'Inserção manual de operadores e períodos de atendimento';



/* Atualização da tickets_stages adicionando o operador vinculado ao chamado como sendo o tratador */
UPDATE 
    tickets_stages ts
    INNER JOIN ocorrencias o ON o.numero = ts.ticket
    INNER JOIN status st ON st.stat_id = ts.status_id
    INNER JOIN usuarios u ON u.user_id = o.operador
SET
    ts.treater_id = u.user_id
WHERE
    st.stat_painel = 1 AND
    u.nivel < 3 AND
    ts.treater_id IS NULL;



ALTER table `ocorrencias` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `ocorrencias` CHANGE `descricao` `descricao` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL; 

ALTER table `mail_queue` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `mail_queue` CHANGE `body` `body` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL; 

ALTER TABLE `assentamentos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `assentamentos` CHANGE `assentamento` `assentamento` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `solucoes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `solucoes` CHANGE `problema` `problema` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `solucoes` CHANGE `solucao` `solucao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER table `ocorrencias_log` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `ocorrencias_log` CHANGE `log_descricao` `log_descricao` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; 

ALTER table `scripts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;



-- Interações via email --
CREATE TABLE `tickets_email_references` (`id` INT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `references_to` VARCHAR(255) NOT NULL COMMENT 'ID da mensagem' , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`created_at`), UNIQUE (`references_to`)) ENGINE = InnoDB; 

ALTER TABLE `tickets_email_references` ADD `updated_at` DATETIME on update CURRENT_TIMESTAMP NULL DEFAULT NULL AFTER `created_at`; 

ALTER TABLE `tickets_email_references` ADD `md5_references_to` VARCHAR(255) NOT NULL AFTER `references_to`, ADD INDEX (`md5_references_to`); 

ALTER TABLE `mail_queue` ADD `references_to` VARCHAR(255) NULL DEFAULT NULL AFTER `ticket`; 

ALTER TABLE `assentamentos` CHANGE `data` `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP; 

ALTER TABLE `tickets_email_references` CHANGE `references_to` `references_to` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID da mensagem'; 

ALTER TABLE `tickets_email_references` DROP `md5_references_to`;

ALTER TABLE `tickets_email_references` ADD `original_subject` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL AFTER `references_to`, ADD INDEX (`original_subject`); 

ALTER TABLE `tickets_email_references` ADD `started_from` VARCHAR(255) NOT NULL AFTER `references_to`, ADD INDEX (`started_from`); 


ALTER TABLE `usuarios_areas` CHANGE `uarea_sid` `uarea_sid` INT NOT NULL; 


-- Versao estrutura da base do OcoMon
-- DB_CHECKPOINT 6-20240808

-- Conteúdo gerado automaticamente
alter table `status` add column open_queue tinyint unsigned generated always as (
    stat_painel = 2 AND
    stat_ignored <> 1
);
alter table `status` add index (`open_queue`);

-- Conteúdo gerado automaticamente
alter table `status` add column not_done tinyint unsigned generated always as (
    stat_painel <> 3 AND
    stat_ignored <> 1
);
alter table `status` add index (`not_done`);


-- Conteúdo gerado automaticamente
alter table `status` add column in_progress tinyint unsigned generated always as (
    stat_painel = 1 AND
    stat_ignored <> 1
);
alter table `status` add index (`in_progress`);


-- Conteúdo gerado automaticamente
alter table `tickets_treaters_stages` add column `full_seconds` int unsigned generated always as (
    (TIMESTAMPDIFF(SECOND, date_start, date_stop))
);
alter table `tickets_treaters_stages` add index (`full_seconds`);

alter table `tickets_treaters_stages` add column `hours` int unsigned generated always as (
    full_seconds / 3600
);
alter table `tickets_treaters_stages` add index (`hours`);

alter table `tickets_treaters_stages` add column `minutes` int unsigned generated always as (
    (full_seconds % 3600) / 60
);
alter table `tickets_treaters_stages` add index (`minutes`);

alter table `tickets_treaters_stages` add column `seconds` int unsigned generated always as (
    (full_seconds % 3600) % 60
);
alter table `tickets_treaters_stages` add index (`seconds`);

alter table `tickets_treaters_stages` add column `concated_time` varchar(15) generated always as (
    (CONCAT(hours,':',minutes,':',seconds))
);


-- DB_CHECKPOINT 6-20240813


CREATE FULLTEXT INDEX INDEX_ENTRY ON assentamentos (assentamento);
CREATE FULLTEXT INDEX INDEX_DESC ON ocorrencias (descricao);
CREATE FULLTEXT INDEX INDEX_SOLUTIONS ON solucoes (problema,solucao);

-- DB_CHECKPOINT 6-20240818

ALTER TABLE `clients` ADD `domain` VARCHAR(255) NULL DEFAULT NULL AFTER `nickname`, ADD UNIQUE (`domain`); 
ALTER TABLE `clients` ADD `base_unit` INT NULL DEFAULT NULL COMMENT 'Unidade Sede' AFTER `nickname`, ADD UNIQUE (`base_unit`); 

-- Versao estrutura da base do OcoMon
-- DB_CHECKPOINT 6-20240821

CREATE TABLE user_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);

CREATE INDEX idx_user_id ON user_logs(user_id);
CREATE INDEX idx_action_type ON user_logs(action_type);
CREATE INDEX idx_created_at ON user_logs(created_at);


-- Versao anterior:
-- DB_CHECKPOINT 6-20240828


-- Correcao da tabela tickets_treaters_stages
ALTER TABLE `tickets_treaters_stages`
MODIFY COLUMN `full_seconds` INT UNSIGNED GENERATED ALWAYS AS (TIMESTAMPDIFF(SECOND, date_start, date_stop)),
MODIFY COLUMN `hours` INT UNSIGNED GENERATED ALWAYS AS (full_seconds DIV 3600),
MODIFY COLUMN `minutes` INT UNSIGNED GENERATED ALWAYS AS ((full_seconds % 3600) DIV 60),
MODIFY COLUMN `seconds` INT UNSIGNED GENERATED ALWAYS AS ((full_seconds % 3600) % 60),
MODIFY COLUMN `concated_time` VARCHAR(15) GENERATED ALWAYS AS (
    CONCAT(
        hours,
        ':',
        LPAD(minutes, 2, '0'),
        ':',
        LPAD(seconds, 2, '0')
    )
);


-- DB_CHECKPOINT 6-20250404
INSERT INTO `config_keys` (key_name, key_value) VALUES ('DB_CHECKPOINT', '6-20250404') ON DUPLICATE KEY UPDATE id = id;
UPDATE `config_keys` SET key_value = '6-20250404' WHERE key_name = 'DB_CHECKPOINT';

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
