

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Estrutura para tabela `asset_statements`
--

CREATE TABLE `asset_statements` (
  `id` int(11) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `header` text,
  `title` text,
  `p1_bfr_list` text,
  `p2_bfr_list` text,
  `p3_bfr_list` text,
  `p1_aft_list` text,
  `p2_aft_list` text,
  `p3_aft_list` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Textos para os termos de responsabilidade';

--
-- Despejando dados para a tabela `asset_statements`
--

INSERT INTO `asset_statements` (`id`, `slug`, `name`, `header`, `title`, `p1_bfr_list`, `p2_bfr_list`, `p3_bfr_list`, `p1_aft_list`, `p2_aft_list`, `p3_aft_list`) VALUES
(1, 'termo-compromisso', 'Termo de Compromisso', 'CENTRO DE INFORMÁTICA - SIGLA / SUPORTE AO USUÁRIO - HELPDESK', 'Termo de Compromisso para Equipamento', 'Por esse termo acuso o recebimento do(s) equipamento(s) abaixo especificado(s), comprometendo-me a mantê-lo(s) sob a minha guarda e responsabilidade, dele(s) fazendo uso adequado, de acordo com a resolução xxx/ano que define políticas, normas e procedimentos que disciplinam a utilização de equipamentos, recursos e serviços de informática da SUA_EMPRESA.', NULL, NULL, 'O suporte para qualquer problema que porventura vier a ocorrer na instalação ou operação do(s) equipamento(s), deverá ser solicitado à área de Suporte, através do telefone/ramal xxxx, pois somente através desde procedimento os chamados poderão ser registrados e atendidos.', 'Em conformidade com o preceituado no art. 1º da Resolução nº xxx/ano, é expressamente vedada a instalação de softwares sem a necessária licença de uso ou em desrespeito aos direitos autorais.', 'A SUA_EMPRESA, através do seu Departamento Responsável (XXXX), em virtude das suas disposições regimentais e regulamentadoras, adota sistema de controle de instalação de softwares em todos os seus equipamentos, impedindo a instalação destes sem prévia autorização do Departamento Competente.'),
(2, 'termo-transito', 'Formulário de Trânsito', 'CENTRO DE INFORMÁTICA - SIGLA / SUPORTE AO USUÁRIO - HELPDESK', 'Formulário de Trânsito de Equipamentos de Informática', 'Informo que o(s) equipamento(s) abaixo descriminado(s) está(ão) autorizado(s) pelo departamento responsável a serem transportados para fora da Unidade pelo portador citado.', NULL, NULL, 'A constatação de inconformidade dos dados aqui descritos no ato de verificação na portaria implica na não autorização de saída dos equipamentos, nesse caso o departamento responsável deve ser contactado.', NULL, NULL);


--
-- Índices de tabela `asset_statements`
--
ALTER TABLE `asset_statements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT de tabela `asset_statements`
--
ALTER TABLE `asset_statements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;



ALTER TABLE `materiais` CHANGE `mat_cod` `mat_cod` INT(6) NOT NULL AUTO_INCREMENT; 
ALTER TABLE `materiais` CHANGE `mat_nome` `mat_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `materiais` CHANGE `mat_caixa` `mat_caixa` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `materiais` CHANGE `mat_obs` `mat_obs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 

ALTER TABLE `ocorrencias_log` CHANGE `log_data` `log_data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; 

CREATE TABLE `email_warranty_equipment` ( `id` INT NOT NULL AUTO_INCREMENT , `equipment_id` INT NOT NULL , `sent_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`equipment_id`)) ENGINE = InnoDB COMMENT = 'Controle de envio de e-mails sobre vencimento garantia'; 


ALTER TABLE `config` ADD `conf_isolate_areas` INT(1) NOT NULL DEFAULT '0' COMMENT 'Visibilidade entre areas para consultas e relatorios' AFTER `conf_sla_tolerance`; 


ALTER TABLE `hw_alter` CHANGE `hwa_item` `hwa_item` INT(4) NULL; 

ALTER TABLE `mailconfig` ADD `mail_send` TINYINT(1) NOT NULL DEFAULT '1' AFTER `mail_from_name`; 

ALTER TABLE `modelos_itens` ADD `mdit_manufacturer` INT(6) NULL AFTER `mdit_cod`, ADD INDEX (`mdit_manufacturer`); 

ALTER TABLE `modelos_itens` CHANGE `mdit_fabricante` `mdit_fabricante` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 

ALTER TABLE `estoque` ADD `estoq_assist` INT(2) NULL DEFAULT NULL AFTER `estoq_partnumber`, ADD `estoq_warranty_type` INT(2) NULL DEFAULT NULL AFTER `estoq_assist`, ADD INDEX (`estoq_assist`), ADD INDEX (`estoq_warranty_type`); 



ALTER TABLE `sistemas` CHANGE `sis_email` `sis_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `config` CHANGE `conf_upld_file_types` `conf_upld_file_types` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '%%IMG%'; 
ALTER TABLE `predios` CHANGE `pred_desc` `pred_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 



ALTER TABLE `usuarios` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 
ALTER TABLE `usuarios` ADD `hash` VARCHAR(255) NULL AFTER `password`; 



CREATE TABLE `channels` ( `id` INT(2) NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci COMMENT = 'Canais disponíveis para abertura de chamados';

INSERT INTO `channels` (`id`, `name`) VALUES (NULL, 'Sistema Web'), (NULL, 'Telefone') ;
INSERT INTO `channels` (`id`, `name`) VALUES (NULL, 'Automático: via Email'), (NULL, 'Email') ;

ALTER TABLE `channels` ADD `is_default` TINYINT(1) NOT NULL DEFAULT '0' AFTER `name`, ADD INDEX (`is_default`); 
UPDATE `channels` SET `is_default` = '1' WHERE `channels`.`id` = 1; 

ALTER TABLE `channels` ADD `only_set_by_system` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Apenas para processos automatizados' AFTER `is_default`; 

UPDATE `channels` SET `only_set_by_system` = '1' WHERE `channels`.`id` = 3; 


ALTER TABLE `configusercall` ADD `conf_scr_channel` TINYINT(1) NOT NULL DEFAULT '0' AFTER `conf_scr_contact_email`; 

ALTER TABLE `ocorrencias` ADD `oco_channel` INT(2) NULL DEFAULT 1 AFTER `oco_prior`, ADD INDEX (`oco_channel`); 



CREATE TABLE `config_keys` ( `id` INT(3) NOT NULL AUTO_INCREMENT , `key_name` VARCHAR(255) NOT NULL , `key_value` VARCHAR(255) NULL DEFAULT NULL , PRIMARY KEY (`id`), UNIQUE (`key_name`)) ENGINE = InnoDB COMMENT = 'Configuracoes relacionadas a API e outras operacoes'; 

ALTER TABLE `config_keys` CHANGE `key_value` `key_value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; 

INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_ADDRESS', NULL), (NULL, 'MAIL_GET_IMAP_ADDRESS', NULL), (NULL, 'MAIL_GET_PORT', NULL) ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_USER', NULL) ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_APP', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_TOKEN', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_CERT', '1');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_PASSWORD', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_MAILBOX', 'INBOX');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_MOVETO', 'OCOMON');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_MARK_SEEN', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_SUBJECT_CONTAINS', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_BODY_CONTAINS', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_DAYS_SINCE', '3');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ALLOW_OPEN_TICKET_BY_EMAIL', '0') ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_CHANNEL', '3');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_AREA', NULL), (NULL, 'API_TICKET_BY_MAIL_STATUS', '1') ;




--
-- Estrutura para tabela `mail_queue`
--

CREATE TABLE `mail_queue` (
  `id` int(11) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `from_email` varchar(255) NOT NULL DEFAULT '',
  `from_name` varchar(255) NOT NULL DEFAULT '',
  `recipient_email` varchar(255) NOT NULL DEFAULT '',
  `recipient_name` varchar(255) NOT NULL DEFAULT '',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índices de tabela `mail_queue`
--
ALTER TABLE `mail_queue`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabela `mail_queue`
--
ALTER TABLE `mail_queue`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `mail_queue` ADD `ticket` INT(11) NULL DEFAULT NULL AFTER `id`, ADD INDEX (`ticket`); 




CREATE TABLE `access_tokens` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `user_id` INT(11) NULL DEFAULT NULL , `app` VARCHAR(255) NULL DEFAULT NULL , `token` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`app`)) ENGINE = InnoDB; 

ALTER TABLE `access_tokens` CHANGE `token` `token` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL; 

ALTER TABLE `access_tokens` ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `token`, ADD `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`; 






CREATE TABLE `apps_register` ( `id` INT NOT NULL AUTO_INCREMENT , `app` VARCHAR(255) NOT NULL , `controller` VARCHAR(255) NOT NULL , `methods` TEXT NOT NULL , PRIMARY KEY (`id`), UNIQUE (`app`, `controller`)) ENGINE = InnoDB COMMENT = 'Registro de apps para controle de acesso pela API'; 

INSERT INTO `apps_register` (`id`, `app`, `controller`, `methods`) VALUES (NULL, 'ticket_by_email', 'OcomonApi\\Controllers\\Tickets', 'create') ;


ALTER TABLE `utmp_usuarios` ADD `utmp_hash` TEXT NULL DEFAULT NULL AFTER `utmp_passwd`; 

ALTER TABLE `usuarios` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 

ALTER TABLE `utmp_usuarios` CHANGE `utmp_passwd` `utmp_passwd` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 







CREATE TABLE `form_fields` ( `id` INT NOT NULL AUTO_INCREMENT , `entity_name` VARCHAR(30) NOT NULL , `field_name` VARCHAR(50) NOT NULL , `action_name` ENUM('new','edit','close') NOT NULL , `not_empty` TINYINT(1) NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`entity_name`), INDEX (`field_name`), INDEX (`action_name`)) ENGINE = InnoDB COMMENT = 'Obrigatoriedade de preenchimento de campos nos formulários';

-- Inicializacao padrao do form_fields
INSERT INTO `form_fields` (`id`, `entity_name`, `field_name`, `action_name`, `not_empty`) VALUES 
(NULL, 'ocorrencias', 'issue', 'new', '1'), 
(NULL, 'ocorrencias', 'asset_tag', 'new', '0'), (NULL, 'ocorrencias', 'area', 'new', '1'), 
(NULL, 'ocorrencias', 'contact', 'new', '1'), (NULL, 'ocorrencias', 'contact_email', 'new', '1'), 
(NULL, 'ocorrencias', 'phone', 'new', '1'), (NULL, 'ocorrencias', 'department', 'new', '1'), 
(NULL, 'ocorrencias', 'operator', 'new', '1'), (NULL, 'ocorrencias', 'unit', 'new', '0'), 
(NULL, 'ocorrencias', 'priority', 'new', '1'), (NULL, 'ocorrencias', 'channel', 'new', '1');

INSERT INTO `form_fields` (`id`, `entity_name`, `field_name`, `action_name`, `not_empty`) VALUES 
(NULL, 'ocorrencias', 'issue', 'edit', '1'), 
(NULL, 'ocorrencias', 'asset_tag', 'edit', '0'), (NULL, 'ocorrencias', 'area', 'edit', '1'), 
(NULL, 'ocorrencias', 'contact', 'edit', '1'), (NULL, 'ocorrencias', 'contact_email', 'edit', '1'), 
(NULL, 'ocorrencias', 'phone', 'edit', '1'), (NULL, 'ocorrencias', 'department', 'edit', '1'), 
(NULL, 'ocorrencias', 'operator', 'edit', '1'), (NULL, 'ocorrencias', 'unit', 'edit', '0'), 
(NULL, 'ocorrencias', 'priority', 'edit', '1'), (NULL, 'ocorrencias', 'channel', 'edit', '1');

INSERT INTO `form_fields` (`id`, `entity_name`, `field_name`, `action_name`, `not_empty`) VALUES 
(NULL, 'ocorrencias', 'issue', 'close', '1'), 
(NULL, 'ocorrencias', 'asset_tag', 'close', '0'), (NULL, 'ocorrencias', 'area', 'close', '1'), 
(NULL, 'ocorrencias', 'contact', 'close', '1'), (NULL, 'ocorrencias', 'contact_email', 'close', '1'), 
(NULL, 'ocorrencias', 'phone', 'close', '1'), (NULL, 'ocorrencias', 'department', 'close', '1'), 
(NULL, 'ocorrencias', 'operator', 'close', '1'), (NULL, 'ocorrencias', 'unit', 'close', '0'), 
(NULL, 'ocorrencias', 'priority', 'close', '1'), (NULL, 'ocorrencias', 'channel', 'close', '1');




ALTER TABLE `prior_atend` ADD `pr_font_color` VARCHAR(7) NULL DEFAULT '#000000' AFTER `pr_color`; 




CREATE TABLE `input_tags` ( `tag_name` VARCHAR(30) NOT NULL , UNIQUE (`tag_name`)) ENGINE = InnoDB COMMENT = 'Tags de referência'; 
ALTER TABLE `input_tags` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`); 

ALTER TABLE `ocorrencias` ADD `oco_tag` TEXT NULL DEFAULT NULL AFTER `oco_channel`, ADD FULLTEXT (`oco_tag`); 

INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_TAG', NULL) ;

ALTER TABLE `mailconfig` ADD `mail_queue` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mail_send`; 

ALTER TABLE `localizacao` CHANGE `local` `local` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 


ALTER TABLE `usuarios` ADD `forget` VARCHAR(255) NULL DEFAULT NULL AFTER `last_logon`; 

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'forget-password', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Esqueceu sua senha?', 'Esqueceu sua senha %usuario%?\r\n\r\nVocê está recebendo esse e-mail porque solicitou a recuperação de senha de acesso ao sistema de suporte.\r\n\r\nCaso não tenha sido você o autor da solicitação, apenas ignore essa mensagem. Seus dados estão protegidos.\r\n\r\nClique aqui para definir uma nova senha de acesso: %forget_link%\r\n\r\nAtte.\r\nEquipe de Suporte.', 'Esqueceu sua senha %usuario%?\r\n\r\nVocê está recebendo esse e-mail porque solicitou a recuperação de senha de acesso ao sistema de suporte.\r\n\r\nCaso não tenha sido você o autor da solicitação, apenas ignore essa mensagem. Seus dados estão protegidos.\r\n\r\nClique aqui para definir uma nova senha de acesso: %forget_link%\r\n\r\nAtte.\r\nEquipe de Suporte.');

ALTER TABLE `mail_templates` CHANGE `tpl_msg_html` `tpl_msg_html` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `mail_templates` CHANGE `tpl_msg_text` `tpl_msg_text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL; 

ALTER TABLE `msgconfig` CHANGE `msg_body` `msg_body` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `scripts` CHANGE `scpt_script` `scpt_script` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `mail_queue` CHANGE `body` `body` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `usuarios` ADD INDEX `AREA` (`AREA`);
ALTER TABLE `usuarios` ADD INDEX `user_admin` (`user_admin`);  

ALTER TABLE `problemas` ADD `prob_not_area` VARCHAR(255) NULL DEFAULT NULL AFTER `prob_descricao`, ADD INDEX (`prob_not_area`); 

ALTER TABLE `problemas` ADD `prob_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `prob_not_area`, ADD INDEX (`prob_active`); 


INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_ALLOW', NULL), (NULL, 'ANON_OPEN_SCREEN_PFL', NULL) ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_USER', NULL), (NULL, 'ANON_OPEN_STATUS', '1') ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_CHANNEL', NULL), (NULL, 'ANON_OPEN_TAGS', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_CAPTCHA_CASE', '1');



ALTER TABLE `sistemas` ADD `sis_months_done` INT(3) NOT NULL DEFAULT '12' COMMENT 'Tempo em meses, para filtro de exibição de encerrados' AFTER `sis_wt_profile`; 





CREATE TABLE `custom_fields` ( `id` INT(3) NOT NULL AUTO_INCREMENT , `field_name` VARCHAR(255) NOT NULL , `field_type` ENUM('text','number','select','select_multi','date','time','datetime','textarea','checkbox') NOT NULL , `field_default_value` TEXT NULL DEFAULT NULL , `field_required` TINYINT(1) NOT NULL DEFAULT '0' , PRIMARY KEY (`id`), UNIQUE (`field_name`)) ENGINE = InnoDB COMMENT = 'Campos customizaveis';

ALTER TABLE `custom_fields` ADD `field_table_to` VARCHAR(255) NOT NULL AFTER `field_required`, ADD `field_label` VARCHAR(255) NOT NULL AFTER `field_table_to`, ADD `field_order` INT NULL AFTER `field_label`, ADD INDEX (`field_table_to`); 



ALTER TABLE `custom_fields` ADD `field_title` VARCHAR(255) NULL AFTER `field_order`, ADD `field_placeholder` VARCHAR(255) NULL AFTER `field_title`, ADD `field_description` VARCHAR(255) NULL AFTER `field_placeholder`; 

ALTER TABLE `custom_fields` ADD `field_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `field_description`, ADD INDEX (`field_active`); 

ALTER TABLE `custom_fields` CHANGE `field_default_value` `field_default_value` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; 

ALTER TABLE `custom_fields` ADD `field_attributes` TEXT NULL DEFAULT NULL AFTER `field_active`; 

ALTER TABLE `custom_fields` CHANGE `field_order` `field_order` VARCHAR(10) NULL DEFAULT NULL COMMENT 'Campo utilizado para ordenação nas telas do sistema'; 


CREATE TABLE `custom_fields_option_values` ( `id` INT NOT NULL AUTO_INCREMENT , `custom_field_id` INT(3) NOT NULL , `option_value` TEXT NOT NULL , PRIMARY KEY (`id`), INDEX (`custom_field_id`)) ENGINE = InnoDB COMMENT = 'Valores para os campos customizados do tipo select '; 


CREATE TABLE `tickets_x_cfields` ( `id` INT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `cfield_id` INT NOT NULL , `cfield_value` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`cfield_id`)) ENGINE = InnoDB COMMENT = 'Registros com campos personalizados'; 

ALTER TABLE `tickets_x_cfields` ADD `cfield_is_key` TINYINT NULL DEFAULT NULL AFTER `cfield_value`; 


ALTER TABLE `configusercall` ADD `conf_scr_custom_ids` TEXT NULL DEFAULT NULL COMMENT 'Ids dos campos personalizados' AFTER `conf_scr_channel`; 


ALTER TABLE `config` ADD `conf_cfield_only_opened` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Define se na edição, os campos personalizados serão limitados aos utilizados na abertura do chamado' AFTER `conf_isolate_areas`; 




ALTER TABLE `config` ADD `conf_updated_issues` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Flag para saber se o update da tabela de tipos de problemas foi realizado.' AFTER `conf_isolate_areas`, ADD INDEX (`conf_updated_issues`); 

ALTER TABLE `config` ADD `conf_allow_op_treat_own_ticket` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Define se o operador pode tratar chamados abertos por ele mesmo' AFTER `conf_isolate_areas`; 

ALTER TABLE `config` ADD `conf_reopen_deadline` INT(2) NOT NULL DEFAULT '0' COMMENT 'Limite de tempo em dias para a reabertura de chamados' AFTER `conf_allow_reopen`; 

CREATE TABLE `areas_x_issues` ( `id` INT NOT NULL AUTO_INCREMENT , `area_id` INT NULL , `prob_id` INT NOT NULL , `old_prob_id` INT NULL, PRIMARY KEY (`id`), INDEX (`area_id`), INDEX (`prob_id`), INDEX (`old_prob_id`)) ENGINE = InnoDB COMMENT = 'NxN Areas x Problemas'; 



CREATE TABLE `screen_field_required` ( `id` INT(6) NOT NULL AUTO_INCREMENT , `profile_id` INT(6) NOT NULL , `field_name` VARCHAR(64) NOT NULL COMMENT 'Nome do campo na tabela configusercall' , `field_required` TINYINT NOT NULL DEFAULT '1' , PRIMARY KEY (`id`), INDEX (`profile_id`), INDEX (`field_name`)) ENGINE = InnoDB COMMENT = 'Obrigatoriedade de preenchim. dos campos nos perfis de tela';


ALTER TABLE `avisos` ADD `is_recurrent` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Indica se o aviso será exibindo novamente no outro dia' AFTER `is_active`, ADD INDEX (`is_recurrent`); 



ALTER TABLE `custom_fields` ADD `field_mask` TEXT NULL DEFAULT NULL COMMENT 'Máscara para campos tipo texto' AFTER `field_attributes`; 

ALTER TABLE `custom_fields` ADD `field_mask_regex` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Se a máscara é uma expressão regular' AFTER `field_mask`; 


-- Versao 5


-- Para evitar problemas com instalacoes antigas onde o valor padrao é 0000-00-00 00:00:00
ALTER TABLE `equipamentos` CHANGE `comp_data_compra` `comp_data_compra` DATETIME NULL DEFAULT NULL;

ALTER TABLE `equipamentos` ADD `comp_part_number` VARCHAR(255) NULL DEFAULT NULL AFTER `comp_assist`, ADD INDEX (`comp_part_number`); 

-- Para evitar problemas com instalacoes antigas onde o valor padrao é 0000-00-00 00:00:00
ALTER TABLE `historico` CHANGE `hist_data` `hist_data` DATETIME NULL DEFAULT CURRENT_TIMESTAMP; 

ALTER TABLE `historico` ADD `asset_id` INT NULL DEFAULT NULL COMMENT 'A partir da versao 5 esse campo referencia o ativo' AFTER `hist_cod`, ADD INDEX (`asset_id`); 

ALTER TABLE `historico` CHANGE `hist_inv` `hist_inv` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'a partir da versão 5 é nulo'; 

ALTER TABLE `historico` CHANGE `hist_inst` `hist_inst` INT(4) NULL DEFAULT NULL COMMENT 'A partir da versão 5 é nulo'; 

ALTER TABLE `historico` CHANGE `hist_data` `hist_data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP; 

ALTER TABLE `historico` ADD `hist_user` INT NULL COMMENT 'Responsável pela ação' AFTER `hist_data`, ADD INDEX (`hist_user`); 


ALTER TABLE `status` ADD `stat_ignored` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'define se o status será ignorado pelo sistema' AFTER `stat_time_freeze`, ADD INDEX (`stat_ignored`); 


ALTER TABLE `problemas` ADD `prob_profile_form` INT NULL DEFAULT NULL COMMENT 'Define o perfil de tela para abertura de chamados desse tipo' AFTER `prob_active`, ADD INDEX (`prob_profile_form`);


ALTER TABLE `configusercall` ADD `conf_is_default` TINYINT(1) NULL DEFAULT NULL AFTER `conf_name`, ADD INDEX (`conf_is_default`); 


ALTER TABLE `sistemas` ADD `sis_opening_mode` ENUM('1','2') NOT NULL DEFAULT '1' COMMENT 'Tipo de abertura de chamados - 1:classica 2:dinâmica' AFTER `sis_months_done`; 


ALTER TABLE `areaxarea_abrechamado` ADD `default_receiver` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Define a área padrão para recebimento de chamados' AFTER `area`; 

ALTER TABLE `problemas` ADD `prob_area_default` INT(4) NULL DEFAULT NULL COMMENT 'Área padrão para recebimento de chamados desse tipo' AFTER `prob_profile_form`, ADD INDEX (`prob_area_default`); 


--
-- Estrutura da tabela `clients`
--

CREATE TABLE `clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `external_id` int(11) DEFAULT NULL COMMENT 'Se o cliente vier de outra base a ser integrada',
  `type` int(11) DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `document_type` enum('cnpj','cpf','outro') DEFAULT 'cnpj',
  `document_value` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `contact_name_2` varchar(255) DEFAULT NULL,
  `contact_email_2` varchar(255) DEFAULT NULL,
  `contact_phone_2` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `area` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL COMMENT 'Referência a tabela de status de clientes',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabela de clientes';

--
-- Índices para tabela `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_value` (`document_value`),
  ADD KEY `contact_email` (`contact_email`),
  ADD KEY `contact_email_2` (`contact_email_2`),
  ADD KEY `area` (`area`),
  ADD KEY `external_id` (`external_id`),
  ADD KEY `type` (`type`),
  ADD KEY `status` (`status`),
  ADD KEY `is_active` (`is_active`);

--
-- AUTO_INCREMENT de tabela `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;



-- Dados iniciais para clientes --

INSERT INTO `clients` (`id`, `fullname`, `nickname`) VALUES
(1, 'Cliente Solucionador Interno', 'Operação'),
(2, 'Cliente solicitante', 'Cliente solicitante');


ALTER TABLE `usuarios` ADD `user_client` INT NULL DEFAULT NULL AFTER `user_id`, ADD INDEX (`user_client`); 


ALTER TABLE `instituicao` ADD `inst_client` INT NULL DEFAULT NULL AFTER `inst_status`, ADD INDEX (`inst_client`); 

ALTER TABLE `localizacao` ADD `loc_unit` INT NULL DEFAULT NULL COMMENT 'ID da unidade' AFTER `loc_status`, ADD INDEX (`loc_unit`); 


ALTER TABLE `custom_fields` DROP INDEX `field_name`, ADD UNIQUE `field_name` (`field_name`, `field_table_to`) USING BTREE; 


ALTER TABLE `ocorrencias` ADD `client` INT NULL DEFAULT NULL COMMENT 'Referencia para o cliente' AFTER `numero`, ADD INDEX (`client`); 

ALTER TABLE `configusercall` ADD `conf_scr_client` TINYINT(1) NOT NULL DEFAULT '0' AFTER `conf_opentoarea`;

ALTER TABLE `configusercall` ADD `conf_scr_auto_client` INT NULL DEFAULT NULL AFTER `conf_scr_custom_ids`; 

UPDATE usuarios SET user_client = 1 WHERE nivel IN (1,2);



ALTER TABLE `ocorrencias_log` ADD `log_cliente` INT NULL DEFAULT NULL AFTER `log_status`, ADD INDEX (`log_cliente`); 



CREATE TABLE `client_types` ( `id` INT NOT NULL AUTO_INCREMENT, `type_name` VARCHAR(255) NOT NULL , `type_description` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Tipos de clientes'; 

INSERT INTO `client_types` (`id`, `type_name`, `type_description`) VALUES (NULL, 'Default', NULL);

CREATE TABLE `client_status` ( `id` INT NOT NULL AUTO_INCREMENT , `status_name` VARCHAR(255) NOT NULL , `status_description` TEXT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Status para clientes'; 

INSERT INTO `client_status` (`id`, `status_name`, `status_description`) VALUES (NULL, 'Default', NULL);



CREATE TABLE `clients_x_cfields` ( `id` INT NOT NULL AUTO_INCREMENT , `client_id` INT NOT NULL , `cfield_id` INT NOT NULL , `cfield_value` TEXT NULL , `cfield_is_key` TINYINT(1) NULL , PRIMARY KEY (`id`), INDEX (`client_id`), INDEX (`cfield_id`), INDEX (`cfield_is_key`)) ENGINE = InnoDB COMMENT = 'Campos customizados da tabela de clientes'; 



CREATE TABLE `measure_types` ( `id` INT NOT NULL AUTO_INCREMENT , `mt_name` VARCHAR(255) NOT NULL , `mt_description` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Características que podem ser medidas e comparadas'; 

CREATE TABLE `measure_units` ( `id` INT NOT NULL AUTO_INCREMENT , `type_id` INT NOT NULL COMMENT 'Referente ao ID do measure_types' , `unit_name` VARCHAR(255) NULL , `unit_abbrev` VARCHAR(10) NOT NULL , `equity_factor` DOUBLE NULL , PRIMARY KEY (`id`), INDEX (`type_id`)) ENGINE = InnoDB COMMENT = 'Unidades de medida para comparação'; 

ALTER TABLE `measure_units` ADD `operation` ENUM('/','*','=') NULL DEFAULT NULL COMMENT 'Define se o valor será multiplicado ou dividido pelo valor base' AFTER `equity_factor`; 

CREATE TABLE `model_x_specs` ( `id` INT NOT NULL AUTO_INCREMENT , `model_id` INT NOT NULL , `measure_unit_id` INT NOT NULL , `spec_value` FLOAT NOT NULL , PRIMARY KEY (`id`), INDEX (`model_id`), INDEX (`measure_unit_id`)) ENGINE = InnoDB COMMENT = 'Caracteristicas mensuraveis de cada modelo';

ALTER TABLE `model_x_specs` ADD `abs_value` DOUBLE NULL DEFAULT NULL COMMENT 'Valor absoluto, utilizado para comparacao' AFTER `spec_value`, ADD INDEX (`abs_value`); 


ALTER TABLE `marcas_comp` ADD `marc_manufacturer` INT NULL AFTER `marc_tipo`, ADD INDEX (`marc_manufacturer`); 


CREATE TABLE `assets_categories` ( `id` INT NOT NULL AUTO_INCREMENT , `cat_name` VARCHAR(255) NOT NULL , `cat_description` TEXT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Categorias para tipos de ativos'; 


ALTER TABLE `assets_categories` ADD `cat_default_profile` INT NULL DEFAULT NULL COMMENT 'Perfil de formulário de cadastro' AFTER `cat_description`, ADD INDEX (`cat_default_profile`); 

ALTER TABLE `tipo_equip` ADD `tipo_categoria` INT NULL DEFAULT NULL AFTER `tipo_nome`, ADD INDEX (`tipo_categoria`); 







CREATE TABLE `assets_fields_profiles` ( `id` INT NOT NULL AUTO_INCREMENT , `profile_name` VARCHAR(255) NOT NULL , `asset_type` TINYINT(1) NULL DEFAULT '1' , `manufacturer` TINYINT(1) NULL DEFAULT '1' , `model` TINYINT(1) NULL DEFAULT '1' , `serial_number` TINYINT(1) NULL DEFAULT '1' , `part_number` TINYINT(1) NULL DEFAULT NULL , `department` TINYINT(1) NULL DEFAULT '1' , `situation` TINYINT(1) NULL DEFAULT '1' , `net_name` TINYINT(1) NULL DEFAULT NULL , `asset_unit` TINYINT(1) NULL DEFAULT '1' , `asset_tag` TINYINT(1) NULL DEFAULT '1' , `invoice_number` TINYINT(1) NULL DEFAULT NULL , `cost_center` TINYINT(1) NULL DEFAULT NULL , `price` TINYINT(1) NULL DEFAULT NULL , `buy_date` TINYINT(1) NULL DEFAULT NULL , `supplier` TINYINT(1) NULL DEFAULT NULL , `assistance_type` TINYINT(1) NULL DEFAULT NULL , `warranty_type` TINYINT(1) NULL DEFAULT NULL , `warranty_time` TINYINT(1) NULL DEFAULT NULL , `extra_info` TINYINT(1) NOT NULL DEFAULT '1' , `field_specs_ids` VARCHAR(255) NULL DEFAULT NULL , `field_custom_ids` VARCHAR(255) NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`asset_type`), INDEX (`manufacturer`), INDEX (`model`), INDEX (`serial_number`), INDEX (`part_number`), INDEX (`department`), INDEX (`situation`), INDEX (`net_name`), INDEX (`asset_unit`), INDEX (`asset_tag`), INDEX (`invoice_number`), INDEX (`cost_center`), INDEX (`price`), INDEX (`buy_date`), INDEX (`supplier`), INDEX (`assistance_type`), INDEX (`warranty_type`), INDEX (`warranty_time`), INDEX (`extra_info`), INDEX (`field_specs_ids`), INDEX (`field_custom_ids`)) ENGINE = InnoDB COMMENT = 'Perfis para os formulários de cadastro dos tipos de ativos';




CREATE TABLE `assets_fields_required` ( `id` INT NOT NULL AUTO_INCREMENT , `profile_id` INT NOT NULL , `field_name` VARCHAR(255) NOT NULL , `field_required` TINYINT(1) NOT NULL DEFAULT '0' , PRIMARY KEY (`id`), INDEX (`profile_id`), INDEX (`field_name`), INDEX (`field_required`)) ENGINE = InnoDB COMMENT = 'Obrigatoriedade preenchimen dos campos no cadastro de ativos';



CREATE TABLE `profiles_x_assets_types` ( `id` INT NOT NULL AUTO_INCREMENT , `profile_id` INT NOT NULL , `asset_type_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`profile_id`), UNIQUE (`asset_type_id`)) ENGINE = InnoDB COMMENT = 'Relação de tipos de ativos e perfis de campos para cadastro';  



CREATE TABLE `assets_types_part_of` ( `id` INT NOT NULL AUTO_INCREMENT , `parent_id` INT NOT NULL COMMENT 'Tipo de ativo pai' , `child_id` INT NOT NULL COMMENT 'tipo de ativo que poderá compor outro tipo de ativo' , PRIMARY KEY (`id`), UNIQUE (`parent_id`, `child_id`)) ENGINE = InnoDB COMMENT = 'Relação de composição entre tipos de ativos';



CREATE TABLE `assets_x_specs` ( `id` INT NOT NULL AUTO_INCREMENT , `asset_id` INT NOT NULL COMMENT 'ID do ativo', `asset_spec_id` INT NOT NULL COMMENT 'ID do modelo de especificação' , PRIMARY KEY (`id`), INDEX (`asset_id`), INDEX (`asset_spec_id`)) ENGINE = InnoDB COMMENT = 'Todas as especificações de um ativo de inventário';



CREATE TABLE `assets_x_cfields` ( `id` INT NOT NULL AUTO_INCREMENT , `asset_id` INT NOT NULL COMMENT 'ID do ativo' , `cfield_id` INT NOT NULL , `cfield_value` TEXT NULL , `cfield_is_key` TINYINT(1) NULL , PRIMARY KEY (`id`), INDEX (`asset_id`), INDEX (`cfield_id`), INDEX (`cfield_is_key`)) ENGINE = InnoDB COMMENT = 'Campos personalizados em cada ativo registrado no sistema';





ALTER TABLE `assets_x_specs` ADD `asset_spec_tagged_id` INT NULL COMMENT 'ID do ativo se especificacao corresponder a um ativo cadastrado' AFTER `asset_spec_id`, ADD UNIQUE (`asset_spec_tagged_id`); 


CREATE TABLE `model_x_child_models` ( `id` INT NOT NULL AUTO_INCREMENT , `model_id` INT NOT NULL , `model_child_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`model_id`), INDEX (`model_child_id`)) ENGINE = InnoDB COMMENT = 'Guarda os modelos de configurações salvos'; 


CREATE TABLE `assets_x_specs_changes` ( `id` INT NOT NULL AUTO_INCREMENT , `asset_id` INT NOT NULL COMMENT 'ID do ativo' , `spec_id` INT NOT NULL COMMENT 'ID da especificacao (id do modelo)' , `user_id` INT NOT NULL COMMENT 'Usuário que executou a ação' , `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da ação' , PRIMARY KEY (`id`), INDEX (`asset_id`), INDEX (`spec_id`), INDEX (`user_id`)) ENGINE = InnoDB COMMENT = 'Registro de modificações de configurações do ativo';

ALTER TABLE `assets_x_specs_changes` ADD `action` ENUM('add','remove') NULL DEFAULT NULL COMMENT 'Flag para saber se a especificação foi add ou removed' AFTER `spec_id`, ADD INDEX (`action`); 




CREATE TABLE `areas_x_units` ( `id` INT NOT NULL AUTO_INCREMENT , `area_id` INT NOT NULL , `unit_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`area_id`), INDEX (`unit_id`)) ENGINE = InnoDB COMMENT = 'Controle de unidades que a area pode acessar no inventário'; 



CREATE TABLE `ticket_x_workers` ( `id` INT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `user_id` INT NOT NULL , `main_worker` TINYINT(1) NOT NULL DEFAULT '0' , `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`user_id`), INDEX (`main_worker`)) ENGINE = InnoDB COMMENT = 'Operadores por chamado';



CREATE TABLE `tickets_extended` ( `ticket` INT NOT NULL , `main_worker` INT NULL DEFAULT NULL , PRIMARY KEY (`ticket`), INDEX (`main_worker`)) ENGINE = InnoDB COMMENT = 'Extenção das tabela de ocorrências'; 

ALTER TABLE `tickets_extended` ADD `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `main_worker`, ADD INDEX (`updated_at`); 


ALTER TABLE `config` ADD `conf_status_scheduled_to_worker` INT NULL DEFAULT '2' COMMENT 'status para chamados agendados para operadores' AFTER `conf_cfield_only_opened`, ADD `conf_status_in_worker_queue` INT NULL DEFAULT 2 COMMENT 'status para chamados que entram na fila direta do operador apos agendamento' AFTER `conf_status_scheduled_to_worker`;



INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES
(NULL, 'agendamento-para-operador', 'SISTEMA OCOMON', 'your_email@your_domain.com', 'Chamado encaminhado para %funcionario%', '<p>Caro %funcionario%,<br>\r\nO chamado <strong>%numero%</strong> foi editado e está direcionado a você.<br>\r\nDescrição: <strong>%descricao%</strong></p><p>Funcionário Responsável:<strong> %funcionario_responsavel%<br></strong>Funcionários alocados:<strong> %funcionarios%<br></strong><br>\r\nAlteração mais recente: <strong>%assentamento%</strong><br>\r\nContato: <strong>%contato%</strong>&nbsp;&nbsp;<br>\r\nTelefone: <strong>%telefone%</strong><br>\r\nOcorrência editada pelo operador: <strong>%editor%</strong><br>\r\n%site%</p>\r\n', 'Caro %funcionario%,\r\nO chamado %numero% foi editado e está direcionado a você.\r\nDescrição: %descricao%\r\n\r\nFuncionários alocados: %funcionarios%\r\n\r\nAlteração mais recente: %assentamento%\r\nContato: %contato%  \r\nTelefone: %telefone%\r\nOcorrência editada pelo operador: %editor%\r\n%site%');



ALTER TABLE `usuarios` ADD `can_route` TINYINT(1) NULL DEFAULT NULL COMMENT 'Operador pode encaminhar chamados' AFTER `forget`, ADD `can_get_routed` TINYINT(1) NULL DEFAULT NULL COMMENT 'Operador pode receber chamados encaminhados' AFTER `can_route`, ADD INDEX (`can_route`), ADD INDEX (`can_get_routed`);



ALTER TABLE `usuarios` ADD `user_bgcolor` VARCHAR(7) NOT NULL DEFAULT '#3A4D56' AFTER `can_get_routed`, ADD `user_textcolor` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF' AFTER `user_bgcolor`;


UPDATE usuarios SET can_route = 1 WHERE nivel IN (1,2);
UPDATE usuarios SET can_route = 0 WHERE nivel NOT IN (1,2);
UPDATE usuarios SET can_get_routed = 1 WHERE nivel IN (1,2);
UPDATE usuarios SET can_get_routed = 0 WHERE nivel NOT IN (1,2);


ALTER TABLE `config` ADD `set_response_at_routing` ENUM('always','never','choice') NOT NULL DEFAULT 'choice' COMMENT 'Primeira resposta ao encaminhar' AFTER `conf_status_in_worker_queue`, ADD INDEX (`set_response_at_routing`); 



ALTER TABLE `instituicao` CHANGE `inst_nome` `inst_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 


--
-- ALTERAÇOES PÓS VERSÃO 5 PREVIEW JÁ INSTALADA EM CLIENTES
--
ALTER TABLE `config` ADD `conf_status_reopen` INT NOT NULL DEFAULT '1' COMMENT 'Status para chamados reabertos' AFTER `set_response_at_routing`, ADD INDEX (`conf_status_reopen`); 



ALTER TABLE `config` ADD `conf_status_done` INT NOT NULL DEFAULT '4' COMMENT 'Status para concluídos pelo operador' AFTER `conf_status_reopen`, ADD `conf_status_done_rejected` INT NOT NULL DEFAULT '1' COMMENT 'Status para solucoes rejeitas pelo solicitante' AFTER `conf_status_done`, ADD `conf_time_to_close_after_done` INT NOT NULL DEFAULT '3' COMMENT 'Tempo em dias para que o chamado seja encerrado após conclusão' AFTER `conf_status_done_rejected`, ADD INDEX (`conf_status_done`), ADD INDEX (`conf_status_done_rejected`);


ALTER TABLE `config` ADD `conf_rate_after_deadline` ENUM("great","good","regular","bad","not_rated") NOT NULL DEFAULT 'great' COMMENT 'Avaliação que o chamado assumirá caso não seja avaliado em tempo' AFTER `conf_time_to_close_after_done`; 



CREATE TABLE `tickets_rated` ( `ticket` INT NOT NULL , `rate` ENUM("great","good","regular","bad","not_rated") NULL DEFAULT 'great' COMMENT 'Avaliacao do atendimento' , `rate_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `automatic_rate` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Se a avaliacao foi feita de forma automatica ou pelo solicitante' , PRIMARY KEY (`ticket`), INDEX (`rate`)) ENGINE = InnoDB;

ALTER TABLE `tickets_rated` CHANGE `rate_date` `rate_date` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; 

ALTER TABLE `tickets_rated` ADD `rejected_count` INT NOT NULL DEFAULT '0' COMMENT 'Contador de vezes que o ticket tiver atendimento rejeitado' AFTER `automatic_rate`; 

ALTER TABLE `tickets_rated` CHANGE `rate_date` `rate_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP; 


INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'rejeitado-para-area', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Atendimento Rejeitado', 'Atenção:\r\n\r\nO Chamado %numero% teve a conclusão do seu atendimento rejeitada pelo solicitante.\r\n\r\nO chamado está retornando para a fila de atendimento.\r\n\r\nAtte.\r\nSistema OcoMon', 'Atenção:\r\n\r\nO Chamado %numero% teve a conclusão do seu atendimento rejeitada pelo solicitante.\r\n\r\nO chamado está retornando para a fila de atendimento.\r\n\r\nAtte.\r\nSistema OcoMon'); 


ALTER TABLE `msgconfig` CHANGE `msg_event` `msg_event` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'evento', CHANGE `msg_fromname` `msg_fromname` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'from', CHANGE `msg_replyto` `msg_replyto` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ocomon@yourdomain.com', CHANGE `msg_subject` `msg_subject` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'subject';


ALTER TABLE `global_tickets` ADD `gt_rating_id` VARCHAR(255) NULL DEFAULT NULL AFTER `gt_id`, ADD INDEX (`gt_rating_id`); 


CREATE TABLE `users_x_area_admin` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `area_id` INT NOT NULL , PRIMARY KEY (`id`), UNIQUE (`user_id`, `area_id`)) ENGINE = InnoDB COMMENT = 'Usuarios x areas gerenciadas por eles'; 

ALTER TABLE `config` ADD `conf_only_weekdays_to_count_after_done` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Define se o prazo para validacao e avaliacao considera apenas dias uteis' AFTER `conf_time_to_close_after_done`; 


ALTER TABLE `configusercall` ADD `cfields_only_edition` TEXT NULL COMMENT 'Ids dos campos que só aparecerao na edicao' AFTER `conf_scr_auto_client`, ADD `cfields_user_hidden` TEXT NULL COMMENT 'Ids dos campos que nunca serao exibidos para o usuario' AFTER `cfields_only_edition`; 


ALTER TABLE `ocorrencias` ADD `profile_id` INT NULL DEFAULT NULL COMMENT 'Perfil de tela utilizado no chamado' AFTER `oco_tag`; 


INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'rejeitado-para-operador', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Seu atendimento %numero% foi rejeitado', '<p>Caro %operador%,&nbsp;</p><p>O seu atendimento ao chamado %numero% foi rejeitado pelo solicitante.Por favor, entre em contato com o solicitante para entender sobre a razão e então concluir o atendimento da forma devida.</p><p>Atte.<br></p><p>Sistema OcoMon</p>', 'Caro operador\r\n\r\nO seu atendimento ao chamado %numero% foi rejeitado pelo solicitante.\r\n\r\nPor favor, entre em contato com o solicitante para entender a razão e então conclua o atendimento.\r\n\r\nSistema OcoMon'), (NULL, 'solicita-avaliacao', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Avalie o atendimento recebido', '<p>Caro %contato%,</p><p>Seu atendimento foi concluído para o chamado %numero% e está aguardando por sua aprovação e avaliação.</p><p>Para aprovar e avaliar basta acessar o seguinte endereço: %rating_url% </p><p>Atte.Sistema OcoMon</p>', 'Caro %contato%\r\n\r\nSeu atendimento foi concluído para o chamado %numero% e está aguardando por sua aprovação e avaliação.\r\n\r\nPara aprovar e avaliar basta acessar o seguinte endereço: %rating_url% \r\n\r\nAtte.\r\nSistema OcoMon');



ALTER TABLE `predios` ADD `pred_unit` INT NULL DEFAULT NULL COMMENT 'Unidade - fará o vínculo com o cliente' AFTER `pred_desc`, ADD INDEX (`pred_unit`); 

ALTER TABLE `reitorias` ADD `reit_unit` INT NULL DEFAULT NULL COMMENT 'Unidade - referencia para clientes' AFTER `reit_nome`, ADD INDEX (`reit_unit`); 

ALTER TABLE `dominios` ADD `dom_unit` INT NULL DEFAULT NULL COMMENT 'Unidade - referência para cliente' AFTER `dom_desc`, ADD INDEX (`dom_unit`); 

ALTER TABLE `ccusto` ADD `client` INT NULL DEFAULT NULL COMMENT 'Referencia para clientes' AFTER `descricao`, ADD INDEX (`client`); 


ALTER TABLE `assets_categories` ADD `cat_is_digital` TINYINT(1) NULL DEFAULT '0' COMMENT 'Define se a categoria é para ativos físicos ou digitais' AFTER `cat_default_profile`, ADD INDEX (`cat_is_digital`); 


ALTER TABLE `assets_categories` ADD `cat_is_product` TINYINT(1) NULL DEFAULT '0' COMMENT 'Define se a categoria é para produtos' AFTER `cat_is_digital`, ADD `cat_is_service` TINYINT(1) NULL DEFAULT '0' COMMENT 'Define se a categoria é para serviços' AFTER `cat_is_product`, ADD `cat_bgcolor` VARCHAR(7) NOT NULL DEFAULT '#17A2B8' AFTER `cat_is_service`, ADD `cat_textcolor` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF' AFTER `cat_bgcolor`, ADD INDEX (`cat_is_product`), ADD INDEX (`cat_is_service`);


ALTER TABLE `marcas_comp` CHANGE `marc_nome` `marc_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'; 



INSERT INTO `measure_types` (`id`, `mt_name`, `mt_description`) VALUES
(1, 'Armazenamento', 'Capacidade de armazenamento de arquivos'),
(4, 'Volume', 'Medidas de volume'),
(5, 'Área', 'Tamanho de área, geralmente em m2'),
(6, 'Taxa de transmissão', NULL),
(7, 'Frequência', 'Utilizado para medição de desempenho em microprocessadores'),
(8, 'Largura', 'Medida de largura'),
(9, 'RPM', 'Rotações por minuto'),
(10, 'Altura', 'Medida da altura de qualquer coisa'),
(11, 'Tamanho de tela', 'Tamanho de tela de dispositivos'),
(12, 'Memória RAM', NULL),
(13, 'Peso', 'Referente a massa de cada item'),
(18, 'Carga elétrica', 'Utilizada para medir autonomia de baterias'),
(19, 'Núcleos', 'Quantidade de núcleos de processador'),
(20, 'Zoom Ótico', 'Zoom ótimo de câmeras'),
(21, 'Zoom Digital', 'Zoom digital para câmeras'),
(22, 'Potência', NULL),
(23, 'Tensão', 'Potencial elétrico');


--
-- Extraindo dados da tabela `measure_units`
--

INSERT INTO `measure_units` (`id`, `type_id`, `unit_name`, `unit_abbrev`, `equity_factor`, `operation`) VALUES
(1, 1, 'Gigabyte', 'GB', 1024, '*'),
(2, 1, 'Kilobyte', 'KB', 1024, '/'),
(3, 1, 'Megabyte', 'MB', 1, '='),
(4, 5, 'Metro quadrado', 'm2', 1, '='),
(5, 1, 'Terabyte', 'TB', 1048575, '*'),
(6, 6, 'Megabit', 'Mbit/s', 1, '='),
(7, 6, 'Gigabit', 'Gbit/s', 1000, '*'),
(8, 6, 'Terabit', 'Tbit/s', 1000000, '*'),
(9, 6, 'Megabyte', 'MB/s', 8, '*'),
(10, 6, 'Gigabyte', 'GB/s', 8000, '*'),
(11, 7, 'Megahertz', 'MHz', 1, '='),
(12, 7, 'Kiloherts', 'kHz', 1000, '/'),
(13, 7, 'Gigahertz', 'GHz', 1000, '*'),
(14, 7, 'Terahertz', 'THz', 1000000, '*'),
(15, 8, 'Metro', 'm', 1, '='),
(16, 8, 'Centrímetro', 'cm', 100, '/'),
(17, 8, 'Milímetro', 'mm', 1000, '/'),
(18, 8, 'Quilômetro', 'km', 1000, '*'),
(19, 9, 'Rotação/minuto', 'rpm', 1, '='),
(20, 10, 'metro', 'm', 1, '='),
(21, 4, 'Litro', 'l', 1, '='),
(22, 11, 'Polegada', '&#34;', 1, '='),
(23, 12, 'Gigabyte', 'GB', 1, '='),
(24, 12, 'Megabyte', 'MB', 1024, '/'),
(25, 13, 'Quilograma', 'kg', 1, '='),
(26, 13, 'grama', 'g', 1000, '/'),
(27, 13, 'miligrama', 'mg', 1000000, '/'),
(28, 13, 'Tonelada', 't', 1000, '*'),
(29, 10, 'Centrímetro', 'cm', 100, '/'),
(32, 10, 'Milímetro', 'mm', 1000, '/'),
(42, 4, 'Centrímetro cúbico', 'cm3', 1000, '/'),
(43, 4, 'Metro cúbico', 'm3', 1000, '*'),
(44, 5, 'Centrímetro quadrado', 'cm2', 10000, '/'),
(45, 18, 'Miliampere-hora', 'mAh', 1, '='),
(46, 18, 'Ampere-hora', 'Ah', 1000, '*'),
(47, 19, 'Núcleo', 'Núcleo', 1, '='),
(48, 20, 'Zoom', 'x', 1, '='),
(49, 21, 'Zoom', 'x', 1, '='),
(50, 22, 'Watt', 'W', 1, '='),
(51, 22, 'Kilowatt', 'kW', 1000, '*'),
(52, 22, 'Horse-power', 'hp', 1.34, '*'),
(53, 23, 'Volt', 'V', 1, '='),
(54, 23, 'Kilovolt', 'kV', 1000, '*');


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
