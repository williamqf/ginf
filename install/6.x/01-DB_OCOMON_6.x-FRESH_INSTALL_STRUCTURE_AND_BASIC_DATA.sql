-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 07/07/2020 às 08:16
-- Versão do servidor: 8.0.18
-- Versão do PHP: 7.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE DATABASE /*!32312 IF NOT EXISTS*/`ocomon_6` /*!40100 DEFAULT CHARACTER SET utf8 */;

CREATE USER 'ocomon_6'@'localhost' IDENTIFIED BY 'senha_ocomon_mysql';
GRANT SELECT , INSERT , UPDATE , DELETE ON `ocomon_6` . * TO 'ocomon_6'@'localhost';
GRANT Drop ON ocomon_6.* TO 'ocomon_6'@'localhost';
FLUSH PRIVILEGES;

USE `ocomon_6`;

--
-- Banco de dados: `ocomon_6`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `areaxarea_abrechamado`
--

CREATE TABLE `areaxarea_abrechamado` (
  `area` int(4) UNSIGNED NOT NULL COMMENT 'Área para a qual se quer abrir o chamado.',
  `area_abrechamado` int(4) UNSIGNED NOT NULL COMMENT 'Área que pode abrir chamado.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `areaxarea_abrechamado`
--

INSERT INTO `areaxarea_abrechamado` (`area`, `area_abrechamado`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `assentamentos`
--

CREATE TABLE `assentamentos` (
  `numero` int(11) NOT NULL,
  `ocorrencia` int(11) NOT NULL DEFAULT '0',
  `assentamento` text NOT NULL,
  `data` datetime DEFAULT NULL,
  `responsavel` int(4) NOT NULL DEFAULT '0',
  `asset_privated` tinyint(1) NOT NULL DEFAULT '0',
  `tipo_assentamento` int(1) NOT NULL DEFAULT '0' COMMENT 'Tipo do assentamento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `assistencia`
--

CREATE TABLE `assistencia` (
  `assist_cod` int(4) NOT NULL,
  `assist_desc` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de tipos de assistencia para manutencao';

--
-- Despejando dados para a tabela `assistencia`
--

INSERT INTO `assistencia` (`assist_cod`, `assist_desc`) VALUES
(1, 'Contrato de Manutenção'),
(2, 'Garantia do Fabricante'),
(3, 'Sem Cobertura');

-- --------------------------------------------------------

--
-- Estrutura para tabela `avisos`
--

CREATE TABLE `avisos` (
  `aviso_id` int(11) NOT NULL,
  `avisos` text,
  `data` datetime DEFAULT NULL,
  `origem` int(4) NOT NULL DEFAULT '0',
  `status` varchar(100) DEFAULT NULL,
  `area` int(11) NOT NULL DEFAULT '0',
  `origembkp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `cat_cod` int(4) NOT NULL,
  `cat_desc` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de categoria de softwares';

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`cat_cod`, `cat_desc`) VALUES
(1, 'Escritório'),
(2, 'Browser'),
(3, 'Editor'),
(4, 'Visualizador'),
(5, 'Jogos'),
(6, 'Sistema Operacional'),
(7, 'Antivírus'),
(8, 'E-mail'),
(9, 'Desenvolvimento'),
(10, 'Utilitários'),
(11, 'Compactador');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoriaxproblema_sistemas`
--

CREATE TABLE `categoriaxproblema_sistemas` (
  `prob_id` int(11) NOT NULL DEFAULT '0',
  `ctps_id` int(11) NOT NULL DEFAULT '0',
  `ctps_id_old` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cat_problema_sistemas`
--

CREATE TABLE `cat_problema_sistemas` (
  `ctps_id` int(10) NOT NULL DEFAULT '0',
  `ctps_descricao` varchar(100) NOT NULL DEFAULT '',
  `ctps_peso` decimal(10,2) NOT NULL DEFAULT '1.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ccusto`
--

CREATE TABLE `ccusto` (
  `codigo` int(4) NOT NULL,
  `codccusto` varchar(6) NOT NULL DEFAULT '',
  `descricao` varchar(25) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Centros de Custo';

--
-- Despejando dados para a tabela `ccusto`
--

INSERT INTO `ccusto` (`codigo`, `codccusto`, `descricao`) VALUES
(1, '001', 'Default');

-- --------------------------------------------------------

--
-- Estrutura para tabela `config`
--

CREATE TABLE `config` (
  `conf_cod` int(4) NOT NULL,
  `conf_sql_user` varchar(20) NOT NULL DEFAULT 'ocomon_6',
  `conf_sql_passwd` varchar(50) DEFAULT NULL,
  `conf_sql_server` varchar(40) NOT NULL DEFAULT 'localhost',
  `conf_sql_db` varchar(40) NOT NULL DEFAULT 'ocomon_6',
  `conf_db_ccusto` varchar(40) NOT NULL DEFAULT 'ocomon_6',
  `conf_tb_ccusto` varchar(40) NOT NULL DEFAULT 'ccusto',
  `conf_ccusto_id` varchar(20) NOT NULL DEFAULT 'codigo',
  `conf_ccusto_desc` varchar(20) NOT NULL DEFAULT 'descricao',
  `conf_ccusto_cod` varchar(20) NOT NULL DEFAULT 'codccusto',
  `conf_ocomon_site` varchar(100) NOT NULL DEFAULT 'http://localhost/ocomon',
  `conf_inst_terceira` int(4) NOT NULL DEFAULT '-1',
  `conf_log_path` varchar(50) NOT NULL DEFAULT '../../includes/logs/',
  `conf_logo_path` varchar(50) NOT NULL DEFAULT '../../includes/logos',
  `conf_icons_path` varchar(50) NOT NULL DEFAULT '../../includes/icons/',
  `conf_help_icon` varchar(50) NOT NULL DEFAULT '../../includes/icons/solucoes2.png',
  `conf_help_path` varchar(50) NOT NULL DEFAULT '../../includes/help/',
  `conf_language` varchar(15) NOT NULL DEFAULT 'pt_BR.php',
  `conf_auth_type` varchar(30) NOT NULL DEFAULT 'SYSTEM',
  `conf_upld_size` int(10) NOT NULL DEFAULT '307200',
  `conf_upld_width` int(5) NOT NULL DEFAULT '5000',
  `conf_upld_height` int(5) NOT NULL DEFAULT '5000',
  `conf_formatBar` varchar(40) DEFAULT '%%mural%',
  `conf_page_size` int(3) NOT NULL DEFAULT '50',
  `conf_prob_tipo_1` varchar(30) NOT NULL DEFAULT 'Categoria 1',
  `conf_prob_tipo_2` varchar(30) NOT NULL DEFAULT 'Categoria 2',
  `conf_prob_tipo_3` varchar(30) NOT NULL DEFAULT 'Categoria 3',
  `conf_allow_change_theme` int(1) NOT NULL DEFAULT '0',
  `conf_upld_file_types` varchar(30) NOT NULL DEFAULT '%%IMG%',
  `conf_date_format` varchar(20) NOT NULL DEFAULT 'd/m/Y H:i:s',
  `conf_days_bf` int(3) NOT NULL DEFAULT '30',
  `conf_wrty_area` int(4) NOT NULL DEFAULT '1',
  `conf_allow_reopen` tinyint(1) NOT NULL DEFAULT '1',
  `conf_allow_date_edit` tinyint(1) NOT NULL DEFAULT '0',
  `conf_schedule_status` int(4) NOT NULL DEFAULT '1',
  `conf_schedule_status_2` int(4) NOT NULL DEFAULT '1',
  `conf_foward_when_open` int(4) NOT NULL DEFAULT '1',
  `conf_desc_sla_out` int(1) NOT NULL DEFAULT '0',
  `conf_qtd_max_anexos` int(2) NOT NULL DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de configurações diversas do sistema';

--
-- Despejando dados para a tabela `config`
--

INSERT INTO `config` (`conf_cod`, `conf_sql_user`, `conf_sql_passwd`, `conf_sql_server`, `conf_sql_db`, `conf_db_ccusto`, `conf_tb_ccusto`, `conf_ccusto_id`, `conf_ccusto_desc`, `conf_ccusto_cod`, `conf_ocomon_site`, `conf_inst_terceira`, `conf_log_path`, `conf_logo_path`, `conf_icons_path`, `conf_help_icon`, `conf_help_path`, `conf_language`, `conf_auth_type`, `conf_upld_size`, `conf_upld_width`, `conf_upld_height`, `conf_formatBar`, `conf_page_size`, `conf_prob_tipo_1`, `conf_prob_tipo_2`, `conf_prob_tipo_3`, `conf_allow_change_theme`, `conf_upld_file_types`, `conf_date_format`, `conf_days_bf`, `conf_wrty_area`, `conf_allow_reopen`, `conf_allow_date_edit`, `conf_schedule_status`, `conf_schedule_status_2`, `conf_foward_when_open`, `conf_desc_sla_out`, `conf_qtd_max_anexos`) VALUES
(1, 'ocomon_6', NULL, 'localhost', 'ocomon_6', 'ocomon_6', 'ccusto', 'codigo', 'descricao', 'codccusto', 'http://localhost/ocomon_4.0', -1, '../../includes/logs/', '../../includes/logos', '../../includes/icons/', '../../includes/icons/solucoes2.png', '../../includes/help/', 'pt_BR.php', 'SYSTEM', 10485760, 5000, 5000, '%%mural%', 50, 'Categoria 1', 'Categoria 2', 'Categoria 3', 0, '%%IMG%', 'd/m/Y H:i:s', 30, 1, 1, 0, 1, 1, 1, 0, 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `configusercall`
--

CREATE TABLE `configusercall` (
  `conf_cod` int(4) NOT NULL,
  `conf_name` varchar(50) DEFAULT 'Default',
  `conf_user_opencall` int(1) NOT NULL DEFAULT '1',
  `conf_custom_areas` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `conf_ownarea` int(4) NOT NULL DEFAULT '1',
  `conf_ownarea_2` varchar(200) DEFAULT NULL,
  `conf_opentoarea` int(4) NOT NULL DEFAULT '1',
  `conf_scr_area` int(1) NOT NULL DEFAULT '1',
  `conf_scr_prob` int(1) NOT NULL DEFAULT '1',
  `conf_scr_desc` int(1) NOT NULL DEFAULT '1',
  `conf_scr_unit` int(1) NOT NULL DEFAULT '1',
  `conf_scr_tag` int(1) NOT NULL DEFAULT '1',
  `conf_scr_chktag` int(1) NOT NULL DEFAULT '1',
  `conf_scr_chkhist` int(1) NOT NULL DEFAULT '1',
  `conf_scr_contact` int(1) NOT NULL DEFAULT '1',
  `conf_scr_fone` int(1) NOT NULL DEFAULT '1',
  `conf_scr_local` int(1) NOT NULL DEFAULT '1',
  `conf_scr_btloadlocal` int(1) NOT NULL DEFAULT '1',
  `conf_scr_searchbylocal` int(1) NOT NULL DEFAULT '1',
  `conf_scr_operator` int(1) NOT NULL DEFAULT '1',
  `conf_scr_date` int(1) NOT NULL DEFAULT '1',
  `conf_scr_status` int(1) NOT NULL DEFAULT '1',
  `conf_scr_replicate` int(1) NOT NULL DEFAULT '0',
  `conf_scr_mail` int(1) NOT NULL DEFAULT '1',
  `conf_scr_msg` text NOT NULL,
  `conf_scr_upload` int(1) NOT NULL DEFAULT '0',
  `conf_scr_schedule` tinyint(1) NOT NULL DEFAULT '0',
  `conf_scr_foward` tinyint(1) NOT NULL DEFAULT '0',
  `conf_scr_prior` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='tabela de configuração para usuários de somente abertura de chamados';


--
-- Estrutura para tabela `contatos`
--

CREATE TABLE `contatos` (
  `contact_id` int(5) NOT NULL,
  `contact_login` varchar(15) NOT NULL,
  `contact_name` varchar(40) NOT NULL,
  `contact_email` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Contatos';

-- --------------------------------------------------------

--
-- Estrutura para tabela `doc_time`
--

CREATE TABLE `doc_time` (
  `doc_id` int(6) NOT NULL,
  `doc_oco` int(6) NOT NULL,
  `doc_open` int(10) NOT NULL DEFAULT '0',
  `doc_edit` int(10) NOT NULL DEFAULT '0',
  `doc_close` int(10) NOT NULL DEFAULT '0',
  `doc_user` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela para armazenar o tempo de documentacao de cada chamado';

-- --------------------------------------------------------

--
-- Estrutura para tabela `dominios`
--

CREATE TABLE `dominios` (
  `dom_cod` int(4) NOT NULL,
  `dom_desc` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Domínios de Rede';

--
-- Despejando dados para a tabela `dominios`
--

INSERT INTO `dominios` (`dom_cod`, `dom_desc`) VALUES
(1, 'ARQUIVOS');

-- --------------------------------------------------------

--
-- Estrutura para tabela `email_warranty`
--

CREATE TABLE `email_warranty` (
  `ew_id` int(6) NOT NULL,
  `ew_piece_type` int(1) NOT NULL DEFAULT '0',
  `ew_piece_id` int(6) NOT NULL,
  `ew_sent_first_alert` tinyint(1) NOT NULL DEFAULT '0',
  `ew_sent_last_alert` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de controle para envio de email sobre prazo de garantias';

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimos`
--

CREATE TABLE `emprestimos` (
  `empr_id` int(11) NOT NULL,
  `material` text NOT NULL,
  `responsavel` int(4) NOT NULL DEFAULT '0',
  `data_empr` datetime DEFAULT NULL,
  `data_devol` datetime DEFAULT NULL,
  `quem` varchar(100) DEFAULT NULL,
  `local` varchar(100) DEFAULT NULL,
  `ramal` varchar(20) DEFAULT NULL,
  `responsavelbkp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipamentos`
--

CREATE TABLE `equipamentos` (
  `comp_cod` int(4) UNSIGNED NOT NULL,
  `comp_inv` int(6) NOT NULL DEFAULT '0',
  `comp_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `comp_marca` int(4) UNSIGNED NOT NULL DEFAULT '0',
  `comp_mb` int(4) DEFAULT NULL,
  `comp_proc` int(4) UNSIGNED DEFAULT NULL,
  `comp_memo` int(4) UNSIGNED DEFAULT NULL,
  `comp_video` int(4) UNSIGNED DEFAULT NULL,
  `comp_som` int(4) UNSIGNED DEFAULT NULL,
  `comp_rede` int(4) UNSIGNED DEFAULT NULL,
  `comp_modelohd` int(4) UNSIGNED DEFAULT NULL,
  `comp_modem` int(4) UNSIGNED DEFAULT NULL,
  `comp_cdrom` int(4) UNSIGNED DEFAULT NULL,
  `comp_dvd` int(4) UNSIGNED DEFAULT NULL,
  `comp_grav` int(4) UNSIGNED DEFAULT NULL,
  `comp_nome` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `comp_local` int(4) UNSIGNED NOT NULL DEFAULT '0',
  `comp_fornecedor` int(4) DEFAULT NULL,
  `comp_nf` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `comp_coment` text,
  `comp_data` datetime DEFAULT NULL,
  `comp_valor` float DEFAULT NULL,
  `comp_data_compra` datetime DEFAULT NULL,
  `comp_inst` int(4) NOT NULL DEFAULT '0',
  `comp_ccusto` int(6) DEFAULT NULL,
  `comp_tipo_equip` int(4) NOT NULL DEFAULT '0',
  `comp_tipo_imp` int(4) DEFAULT NULL,
  `comp_resolucao` int(4) DEFAULT NULL,
  `comp_polegada` int(4) DEFAULT NULL,
  `comp_fab` int(4) NOT NULL DEFAULT '0',
  `comp_situac` int(4) DEFAULT NULL,
  `comp_reitoria` int(4) DEFAULT NULL,
  `comp_tipo_garant` int(4) DEFAULT NULL,
  `comp_garant_meses` int(4) DEFAULT NULL,
  `comp_assist` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela principal modulo de inventario de computadores';

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipxpieces`
--

CREATE TABLE `equipxpieces` (
  `eqp_id` int(4) NOT NULL,
  `eqp_equip_inv` int(6) NOT NULL,
  `eqp_equip_inst` int(4) NOT NULL,
  `eqp_piece_id` int(6) NOT NULL,
  `eqp_piece_modelo_id` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de associacao de equipamentos com componentes';

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `estoq_cod` int(4) NOT NULL,
  `estoq_tipo` int(4) NOT NULL DEFAULT '0',
  `estoq_desc` int(4) NOT NULL DEFAULT '0',
  `estoq_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `estoq_local` int(4) NOT NULL DEFAULT '0',
  `estoq_comentario` varchar(250) DEFAULT NULL,
  `estoq_tag_inv` int(6) DEFAULT NULL,
  `estoq_tag_inst` int(6) DEFAULT NULL,
  `estoq_nf` varchar(255) DEFAULT NULL,
  `estoq_warranty` int(3) DEFAULT NULL,
  `estoq_value` float DEFAULT NULL,
  `estoq_situac` int(2) DEFAULT NULL,
  `estoq_data_compra` date DEFAULT NULL,
  `estoq_ccusto` int(6) DEFAULT NULL,
  `estoq_vendor` int(6) DEFAULT NULL,
  `estoq_partnumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de estoque de itens.';

-- --------------------------------------------------------

--
-- Estrutura para tabela `fabricantes`
--

CREATE TABLE `fabricantes` (
  `fab_cod` int(4) NOT NULL,
  `fab_nome` varchar(30) NOT NULL DEFAULT '',
  `fab_tipo` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de fabricantes de equipamentos do Invmon';

--
-- Despejando dados para a tabela `fabricantes`
--

INSERT INTO `fabricantes` (`fab_cod`, `fab_nome`, `fab_tipo`) VALUES
(1, 'Default', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `feriados`
--

CREATE TABLE `feriados` (
  `cod_feriado` int(4) NOT NULL,
  `data_feriado` datetime DEFAULT NULL,
  `desc_feriado` varchar(40) DEFAULT NULL,
  `fixo_feriado` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de feriados';

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `forn_cod` int(4) NOT NULL,
  `forn_nome` varchar(30) NOT NULL DEFAULT '',
  `forn_fone` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de fornecedores de equipamentos';

--
-- Despejando dados para a tabela `fornecedores`
--

INSERT INTO `fornecedores` (`forn_cod`, `forn_nome`, `forn_fone`) VALUES
(1, 'Default', '0800-00-00-00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `global_tickets`
--

CREATE TABLE `global_tickets` (
  `gt_ticket` int(6) NOT NULL,
  `gt_id` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='tabela para permitir acesso global as ocorrencias';

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico`
--

CREATE TABLE `historico` (
  `hist_cod` int(4) NOT NULL,
  `hist_inv` int(6) NOT NULL DEFAULT '0',
  `hist_inst` int(4) NOT NULL DEFAULT '0',
  `hist_local` int(4) NOT NULL DEFAULT '0',
  `hist_data` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de controle de histórico de locais por onde o equipam';

-- --------------------------------------------------------

--
-- Estrutura para tabela `hist_pieces`
--

CREATE TABLE `hist_pieces` (
  `hp_id` int(6) NOT NULL,
  `hp_piece_id` int(6) NOT NULL,
  `hp_piece_local` int(4) DEFAULT NULL,
  `hp_comp_inv` int(6) DEFAULT NULL,
  `hp_comp_inst` int(4) DEFAULT NULL,
  `hp_uid` int(6) NOT NULL,
  `hp_date` datetime NOT NULL,
  `hp_technician` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de histórico de movimentacões de peças avulsas';

-- --------------------------------------------------------

--
-- Estrutura para tabela `hw_alter`
--

CREATE TABLE `hw_alter` (
  `hwa_cod` int(4) NOT NULL,
  `hwa_inst` int(4) NOT NULL,
  `hwa_inv` int(6) NOT NULL,
  `hwa_item` int(4) NOT NULL,
  `hwa_user` int(4) NOT NULL,
  `hwa_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela para armazenar alteracoes de hw';

-- --------------------------------------------------------

--
-- Estrutura para tabela `hw_sw`
--

CREATE TABLE `hw_sw` (
  `hws_cod` int(4) NOT NULL,
  `hws_sw_cod` int(4) NOT NULL DEFAULT '0',
  `hws_hw_cod` int(4) NOT NULL DEFAULT '0',
  `hws_hw_inst` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de relacionamentos entre equipamentos e softwares';

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagens`
--

CREATE TABLE `imagens` (
  `img_cod` int(4) NOT NULL,
  `img_oco` int(4) DEFAULT NULL,
  `img_inst` int(4) DEFAULT NULL,
  `img_inv` int(6) DEFAULT NULL,
  `img_model` int(4) DEFAULT NULL,
  `img_nome` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `img_tipo` varchar(20) NOT NULL,
  `img_bin` longblob NOT NULL,
  `img_largura` int(4) DEFAULT NULL,
  `img_altura` int(4) DEFAULT NULL,
  `img_size` bigint(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de arquivos anexos';

-- --------------------------------------------------------

--
-- Estrutura para tabela `instituicao`
--

CREATE TABLE `instituicao` (
  `inst_cod` int(4) NOT NULL,
  `inst_nome` varchar(30) NOT NULL DEFAULT '',
  `inst_status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Unidades';

--
-- Despejando dados para a tabela `instituicao`
--

INSERT INTO `instituicao` (`inst_cod`, `inst_nome`, `inst_status`) VALUES
(1, '01-DEFAULT', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens`
--

CREATE TABLE `itens` (
  `item_cod` int(4) NOT NULL,
  `item_nome` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de componentes individuais';

--
-- Despejando dados para a tabela `itens`
--

INSERT INTO `itens` (`item_cod`, `item_nome`) VALUES
(5, 'CD-ROM'),
(8, 'DVD'),
(9, 'Gravador'),
(1, 'HD'),
(7, 'Memória'),
(6, 'Modem'),
(3, 'Placa de rede'),
(4, 'Placa de som'),
(2, 'Placa de vídeo'),
(10, 'Placa mãe'),
(11, 'Processador');

-- --------------------------------------------------------

--
-- Estrutura para tabela `licencas`
--

CREATE TABLE `licencas` (
  `lic_cod` int(4) NOT NULL,
  `lic_desc` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de tipos de licenças de softwares';

--
-- Despejando dados para a tabela `licencas`
--

INSERT INTO `licencas` (`lic_cod`, `lic_desc`) VALUES
(1, 'Open Source / livre'),
(2, 'Freeware'),
(3, 'Shareware'),
(4, 'Adware'),
(5, 'Contrato'),
(6, 'Comercial'),
(7, 'OEM');

-- --------------------------------------------------------

--
-- Estrutura para tabela `localizacao`
--

CREATE TABLE `localizacao` (
  `loc_id` int(11) NOT NULL,
  `local` char(200) DEFAULT NULL,
  `loc_reitoria` int(4) DEFAULT '0',
  `loc_prior` int(4) DEFAULT NULL,
  `loc_dominio` int(4) DEFAULT NULL,
  `loc_predio` int(4) DEFAULT NULL,
  `loc_status` int(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `localizacao`
--

INSERT INTO `localizacao` (`loc_id`, `local`, `loc_reitoria`, `loc_prior`, `loc_dominio`, `loc_predio`, `loc_status`) VALUES
(1, 'DEFAULT', NULL, 5, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `lock_oco`
--

CREATE TABLE `lock_oco` (
  `lck_id` int(4) NOT NULL,
  `lck_uid` int(4) NOT NULL,
  `lck_oco` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Lock para chamados em edição';

-- --------------------------------------------------------

--
-- Estrutura para tabela `mailconfig`
--

CREATE TABLE `mailconfig` (
  `mail_cod` int(4) NOT NULL,
  `mail_issmtp` int(1) NOT NULL DEFAULT '1',
  `mail_host` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'mail.smtp.com',
  `mail_port` int(5) NOT NULL DEFAULT '587',
  `mail_secure` varchar(10) NOT NULL DEFAULT 'tls',
  `mail_isauth` int(1) NOT NULL DEFAULT '0',
  `mail_user` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_pass` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_from` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ocomon@yourdomain.com',
  `mail_ishtml` int(1) NOT NULL DEFAULT '1',
  `mail_from_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'SISTEMA_OCOMON'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de configuracao para envio de e-mails';

--
-- Despejando dados para a tabela `mailconfig`
--

INSERT INTO `mailconfig` (`mail_cod`, `mail_issmtp`, `mail_host`, `mail_port`, `mail_secure`, `mail_isauth`, `mail_user`, `mail_pass`, `mail_from`, `mail_ishtml`, `mail_from_name`) VALUES
(1, 1, 'mail.smtp.com', 587, 'tls', 0, NULL, NULL, 'mail@yourdomain.com', 1, 'SISTEMA_OCOMON');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mail_hist`
--

CREATE TABLE `mail_hist` (
  `mhist_cod` int(6) NOT NULL,
  `mhist_oco` int(6) NOT NULL,
  `mhist_listname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mhist_address` text NOT NULL,
  `mhist_address_cc` text,
  `mhist_subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mhist_body` text NOT NULL,
  `mhist_date` datetime NOT NULL,
  `mhist_technician` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de histórico de emails enviados';

-- --------------------------------------------------------

--
-- Estrutura para tabela `mail_list`
--

CREATE TABLE `mail_list` (
  `ml_cod` int(4) NOT NULL,
  `ml_sigla` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ml_desc` text NOT NULL,
  `ml_addr_to` text NOT NULL,
  `ml_addr_cc` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de listas de distribuicao';

-- --------------------------------------------------------

--
-- Estrutura para tabela `mail_templates`
--

CREATE TABLE `mail_templates` (
  `tpl_cod` int(4) NOT NULL,
  `tpl_sigla` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `tpl_subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `tpl_msg_html` text NOT NULL,
  `tpl_msg_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de templates de e-mails';

-- --------------------------------------------------------

--
-- Estrutura para tabela `marcas_comp`
--

CREATE TABLE `marcas_comp` (
  `marc_cod` int(4) UNSIGNED NOT NULL,
  `marc_nome` varchar(30) NOT NULL DEFAULT '0',
  `marc_tipo` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela das marcas de computadores';

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais`
--

CREATE TABLE `materiais` (
  `mat_cod` int(4) NOT NULL,
  `mat_nome` varchar(100) NOT NULL DEFAULT '',
  `mat_qtd` int(11) NOT NULL DEFAULT '0',
  `mat_caixa` varchar(30) DEFAULT '',
  `mat_data` datetime DEFAULT NULL,
  `mat_obs` varchar(200) NOT NULL DEFAULT '',
  `mat_modelo_equip` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de materiais do Helpdesk';

-- --------------------------------------------------------

--
-- Estrutura para tabela `modelos_itens`
--

CREATE TABLE `modelos_itens` (
  `mdit_cod` int(4) NOT NULL,
  `mdit_fabricante` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mdit_desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mdit_desc_capacidade` float DEFAULT NULL,
  `mdit_tipo` int(4) NOT NULL DEFAULT '0',
  `mdit_cod_old` int(4) DEFAULT NULL,
  `mdit_sufixo` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de modelos de componentes';

--
-- Despejando dados para a tabela `modelos_itens`
--

INSERT INTO `modelos_itens` (`mdit_cod`, `mdit_fabricante`, `mdit_desc`, `mdit_desc_capacidade`, `mdit_tipo`, `mdit_cod_old`, `mdit_sufixo`) VALUES
(1, 'Default', 'SATA', 2, 1, 2, 'TB');

-- --------------------------------------------------------

--
-- Estrutura para tabela `modulos`
--

CREATE TABLE `modulos` (
  `modu_cod` int(4) NOT NULL,
  `modu_nome` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de modulos do sistema';

--
-- Despejando dados para a tabela `modulos`
--

INSERT INTO `modulos` (`modu_cod`, `modu_nome`) VALUES
(2, 'inventário'),
(1, 'ocorrências');

-- --------------------------------------------------------

--
-- Estrutura para tabela `moldes`
--

CREATE TABLE `moldes` (
  `mold_cod` int(4) NOT NULL,
  `mold_inv` int(6) DEFAULT NULL,
  `mold_sn` varchar(30) DEFAULT NULL,
  `mold_marca` int(4) NOT NULL DEFAULT '0',
  `mold_mb` int(4) DEFAULT NULL,
  `mold_proc` int(4) DEFAULT NULL,
  `mold_memo` int(4) DEFAULT NULL,
  `mold_video` int(4) DEFAULT NULL,
  `mold_som` int(4) DEFAULT NULL,
  `mold_rede` int(4) DEFAULT NULL,
  `mold_modelohd` int(4) DEFAULT NULL,
  `mold_modem` int(4) DEFAULT NULL,
  `mold_cdrom` int(4) DEFAULT NULL,
  `mold_dvd` int(4) DEFAULT NULL,
  `mold_grav` int(4) DEFAULT NULL,
  `mold_nome` varchar(10) DEFAULT NULL,
  `mold_local` int(4) DEFAULT NULL,
  `mold_fornecedor` int(4) DEFAULT NULL,
  `mold_nf` varchar(30) DEFAULT NULL,
  `mold_coment` varchar(200) DEFAULT NULL,
  `mold_data` datetime DEFAULT NULL,
  `mold_valor` float DEFAULT NULL,
  `mold_data_compra` datetime DEFAULT NULL,
  `mold_inst` int(4) DEFAULT NULL,
  `mold_ccusto` int(4) DEFAULT NULL,
  `mold_tipo_equip` int(4) NOT NULL DEFAULT '0',
  `mold_tipo_imp` int(4) DEFAULT NULL,
  `mold_resolucao` int(4) DEFAULT NULL,
  `mold_polegada` int(4) DEFAULT NULL,
  `mold_fab` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de padrões de configurações';

-- --------------------------------------------------------

--
-- Estrutura para tabela `msgconfig`
--

CREATE TABLE `msgconfig` (
  `msg_cod` int(4) NOT NULL,
  `msg_event` varchar(40) NOT NULL DEFAULT 'evento',
  `msg_fromname` varchar(40) NOT NULL DEFAULT 'from',
  `msg_replyto` varchar(40) NOT NULL DEFAULT 'ocomon@yourdomain.com',
  `msg_subject` varchar(40) NOT NULL DEFAULT 'subject',
  `msg_body` text,
  `msg_altbody` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de configuracao das mensagens de e-mail';

--
-- Despejando dados para a tabela `msgconfig`
--

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES
(1, 'abertura-para-usuario', 'Sistema Ocomon', 'reply-to', 'CHAMADO ABERTO NO SISTEMA', 'Caro %usuario%,<br />Seu chamado foi aberto com sucesso no sistema de atendimento.<br />O n&uacute;mero do chamado &eacute; %numero%<br />Aguarde o atendimento pela equipe de suporte.<br />%site%', 'Caro %usuario%,\r\nSeu chamado foi aberto com sucesso no sistema de atendimento.\r\nO número do chamado é %numero%\r\nAguarde o atendimento pela equipe de suporte.\r\n%site%'),
(2, 'abertura-para-area', 'Sistema Ocomon', 'reply-to', 'CHAMADO ABERTO PARA %area%', 'Sistema Ocomon<br />Foi aberto um novo chamado t&eacute;cnico para ser atendido pela &aacute;rea %area%.<br />O n&uacute;mero do chamado &eacute; %numero%<br />Descri&ccedil;&atilde;o: %descricao%<br />Contato: %contato%<br />Setor: %departamento%<br />Ramal: %telefone%<br />Chamado aberto pelo operador: %operador%<br />%site%', 'Sistema Ocomon\r\nFoi aberto um novo chamado técnico para ser atendido pela área %area%.\r\nO número do chamado é %numero%\r\nDescrição: %descricao%\r\nContato: %contato%\r\nSetor: %departamento%\r\nRamal: %telefone%\r\nChamado aberto pelo operador: %operador%\r\n%site%'),
(3, 'encerra-para-area', 'SISTEMA OCOMON', 'reply-to', 'OCOMON - CHAMADO ENCERRADO', 'Sistema Ocomon<br />O chamado %numero% foi fechado pelo operador %operador%<br />Descri&ccedil;&atilde;o t&eacute;cnica: %descricao%<br />Solu&ccedil;&atilde;o: %solucao%', 'Sistema Ocomon\r\nO chamado %numero% foi fechado pelo operador %operador%\r\nDescrição técnica: %descricao%\r\nSolução: %solucao%'),
(4, 'encerra-para-usuario', 'SISTEMA OCOMON', 'reply-to', 'OCOMON -CHAMADO ENCERRADO NO SISTEMA', 'Caro %contato%<br />Seu chamado foi encerrado no sistema de atendimento.<br />N&uacute;mero do chamado: %numero%<br />Para maiores informa&ccedil;&otilde;es acesso o sistema com seu nome de usu&aacute;rio e senha no endere&ccedil;o abaixo:<br />%site%', 'Caro %contato%\r\nSeu chamado foi encerrado no sistema de atendimento.\r\nNúmero do chamado: %numero%\r\nPara maiores informações acesso o sistema com seu nome de usuário e senha no endereço abaixo:\r\n%site%'),
(5, 'edita-para-area', 'SISTEMA OCOMON', 'reply-to', 'CHAMADO EDITADO PARA %area%', '<span style=\"color: rgb(0, 0, 0);\">Sistema Ocomon</span><br />Foram adicionadas informa&ccedil;&otilde;es ao chamado %numero% para a &aacute;rea %area%<br />Descri&ccedil;&atilde;o: %descricao%<br />Altera&ccedil;&atilde;o mais recente: %assentamento%<br />Contato: %contato%<br />Ramal: %telefone%<br />Ocorr&ecirc;ncia editada pelo operador: %operador%<br />%site%', 'Sistema Ocomon\r\nForam adicionadas informações ao chamado %numero% para a área %area%\r\nDescrição: %descricao%\r\nAlteração mais recente: %assentamento%\r\nContato: %contato%\r\nRamal: %telefone%\r\nOcorrência editada pelo operador: %operador%\r\n%site%'),
(6, 'edita-para-usuario', 'SISTEMA OCOMON', 'reply-to', 'OCOMON - ALTERAÇÕES NO SEU CHAMADO', 'Caro %contato%,<br />O chamado %numero% foi editado no sistema de atendimento.<br />Altera&ccedil;&atilde;o mais recente: %assentamento%<br />Para maiores informa&ccedil;&otilde;es acesse o sistema com seu usu&aacute;rio e senha no endere&ccedil;o abaixo:<br />%site%', 'Caro %contato%,\r\nO chamado %numero% foi editado no sistema de atendimento.\r\nAlteração mais recente: %assentamento%\r\nPara maiores informações acesse o sistema com seu usuário e senha no endereço abaixo:\r\n%site%'),
(7, 'edita-para-operador', 'SISTEMA OCOMON', 'reply-to', 'CHAMADO PARA %operador%', 'Caro %operador%,<br />O chamado %numero% foi editado e est&aacute; direcionado a voc&ecirc;.<br />Descri&ccedil;&atilde;o: %descricao%<br />Altera&ccedil;&atilde;o mais recente: %assentamento%<br />Contato: %contato%&nbsp;&nbsp; <br />Ramal: %telefone%<br />Ocorr&ecirc;ncia editada pelo operador: %editor%<br />%site%', 'Caro %operador%,\r\nO chamado %numero% foi editado e está direcionado a você.\r\nDescrição: %descricao%\r\nAlteração mais recente: %assentamento%\r\nContato: %contato%\r\nRamal: %telefone%\r\nOcorrência editada pelo operador: %editor%\r\n%site%'),
(8, 'cadastro-usuario', 'SISTEMA OCOMON', 'reply-to', 'OCOMON - CONFIRMAÇÃO DE CADASTRO', 'Prezado %usuario%,<br />Sua solicita&ccedil;&atilde;o para cria&ccedil;&atilde;o do login &quot;%login%&quot; foi bem sucedida!<br />Para confirmar sua inscri&ccedil;&atilde;o clique no link abaixo:<br />%linkconfirma%', 'Prezado %usuario%,\r\nSua solicitação para criação do login &quot;%login%&quot; foi bem sucedida!\r\nPara confirmar sua inscrição clique no link abaixo:\r\n%linkconfirma%'),
(9, 'cadastro-usuario-from-admin', 'SISTEMA OCOMON', 'reply-to', 'OCOMON - CONFIRMAÇÃO DE CADASTRO', 'Prezado %usuario%<br />Seu cadastro foi efetuado com sucesso no sistema de chamados do Helpdesk<br />Seu login &eacute;: %login%<br />Para abrir chamados acesse o site %site%<br />Atenciosamente Helpdesk Unilasalle', 'Prezado %usuario%\r\nSeu cadastro foi efetuado com sucesso no sistema de chamados do Helpdesk\r\nSeu login é: %login%\r\nPara abrir chamados acesse o site %site%\r\nAtenciosamente Helpdesk Unilasalle'),
(10, 'mail-about-warranty', 'SISTEMA OCOMON', 'ocomon@yourdomain.com', 'OCOMON - VENCIMENTO DE GARANTIA', 'Aten&ccedil;&atilde;o: <br />Existem equipamentos com o prazo de garantia prestes a expirar.<br /><br />Tipo de equipamento: %tipo%<br />N&uacute;mero de s&eacute;rie: %serial%<br />Partnumber: %partnumber%<br />Modelo: %modelo%<br />Departamento: %local%<br />Fornecedor: %fornecedor%<br />Nota fiscal: %notafiscal%<br />Vencimento: %vencimento%', 'Atenção:\r\nExistem equipamentos com o prazo de garantia prestes a expirar.\r\n\r\nTipo de equipamento: %tipo%\r\nNúmero de série: %serial%\r\nPartnumber: %partnumber%\r\nModelo: %modelo%\r\nDepartamento: %local%\r\nFornecedor: %fornecedor%\r\nNota fiscal: %notafiscal%\r\nVencimento: %vencimento%'),
(11, 'abertura-para-operador', 'SISTEMA OCOMON', 'ocomon@yourdomain.com', 'CHAMADO ABERTO PARA VOCÊ', '<span style=\"font-weight: bold;\">SISTEMA OCOMON %versao%</span><br />Caro %operador%,<br />O chamado <span style=\"font-weight: bold;\">%numero%</span> foi aberto e direcionado a voc&ecirc;.<br /><span style=\"font-weight: bold;\">Descri&ccedil;&atilde;o: </span>%descricao%<br /><span style=\"font-weight: bold;\">Contato: </span>%contato%<br /><span style=\"font-weight: bold;\">Ramal:</span> %telefone%<br />Ocorr&ecirc;ncia aberta pelo operador: %aberto_por%<br />%site%', 'SISTEMA OCOMON %versao%\r\nCaro %operador%,\r\nO chamado %numero% foi aberto e direcionado a você.\r\nDescrição: %descricao%\r\nContato: %contato%\r\nRamal: %telefone%\r\nOcorrência aberta pelo operador: %aberto_por%\r\n%site%');

-- --------------------------------------------------------

--
-- Estrutura para tabela `nivel`
--

CREATE TABLE `nivel` (
  `nivel_cod` int(4) NOT NULL,
  `nivel_nome` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de níveis de acesso ao invmon';

--
-- Despejando dados para a tabela `nivel`
--

INSERT INTO `nivel` (`nivel_cod`, `nivel_nome`) VALUES
(1, 'Administrador'),
(2, 'Operador'),
(3, 'Somente Abertura'),
(5, 'Desabilitado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ocodeps`
--

CREATE TABLE `ocodeps` (
  `dep_pai` int(6) NOT NULL,
  `dep_filho` int(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela para controle de sub-chamados';

-- --------------------------------------------------------

--
-- Estrutura para tabela `ocorrencias`
--

CREATE TABLE `ocorrencias` (
  `numero` int(11) NOT NULL,
  `problema` int(11) NOT NULL DEFAULT '0',
  `descricao` text NOT NULL,
  `equipamento` int(6) DEFAULT NULL,
  `sistema` int(11) NOT NULL DEFAULT '0',
  `contato` varchar(100) NOT NULL DEFAULT '',
  `telefone` varchar(40) DEFAULT NULL,
  `local` int(11) NOT NULL DEFAULT '0',
  `operador` int(4) NOT NULL DEFAULT '0',
  `data_abertura` datetime DEFAULT NULL,
  `data_fechamento` datetime DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `data_atendimento` datetime DEFAULT NULL,
  `instituicao` int(4) DEFAULT NULL,
  `aberto_por` int(4) NOT NULL DEFAULT '0',
  `oco_scheduled` tinyint(1) NOT NULL DEFAULT '0',
  `oco_real_open_date` datetime DEFAULT NULL,
  `oco_script_sol` int(4) DEFAULT NULL,
  `date_first_queued` datetime DEFAULT NULL,
  `oco_prior` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `perm_cod` int(4) NOT NULL,
  `perm_area` int(4) NOT NULL DEFAULT '0',
  `perm_modulo` int(4) NOT NULL DEFAULT '0',
  `perm_flag` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela para permissoes das áreas';

--
-- Despejando dados para a tabela `permissoes`
--

INSERT INTO `permissoes` (`perm_cod`, `perm_area`, `perm_modulo`, `perm_flag`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 2, 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `polegada`
--

CREATE TABLE `polegada` (
  `pole_cod` int(4) NOT NULL,
  `pole_nome` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de polegadas de monitores de vídeo';

--
-- Despejando dados para a tabela `polegada`
--

INSERT INTO `polegada` (`pole_cod`, `pole_nome`) VALUES
(1, '14 polegadas'),
(2, '15 polegadas'),
(3, '17 polegadas');

-- --------------------------------------------------------

--
-- Estrutura para tabela `predios`
--

CREATE TABLE `predios` (
  `pred_cod` int(4) NOT NULL,
  `pred_desc` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de predios - vinculada a tabela de localizaÃ§Ãµes';

--
-- Despejando dados para a tabela `predios`
--

INSERT INTO `predios` (`pred_cod`, `pred_desc`) VALUES
(1, 'DEFAULT');

-- --------------------------------------------------------

--
-- Estrutura para tabela `prioridades`
--

CREATE TABLE `prioridades` (
  `prior_cod` int(4) NOT NULL,
  `prior_nivel` varchar(15) NOT NULL DEFAULT '',
  `prior_sla` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de prioridades para resposta de chamados';

--
-- Despejando dados para a tabela `prioridades`
--

INSERT INTO `prioridades` (`prior_cod`, `prior_nivel`, `prior_sla`) VALUES
(2, 'NíVEL 1', 18),
(3, 'NíVEL 2', 19),
(4, 'NíVEL 3', 20),
(5, 'NíVEL 4', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `prior_atend`
--

CREATE TABLE `prior_atend` (
  `pr_cod` int(2) NOT NULL,
  `pr_nivel` int(2) NOT NULL,
  `pr_default` tinyint(1) NOT NULL DEFAULT '0',
  `pr_desc` varchar(50) NOT NULL DEFAULT '#CCCCCC',
  `pr_color` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de prioridades para atendimento dos chamados';

ALTER TABLE `prior_atend` ADD `pr_font_color` VARCHAR(7) NULL DEFAULT '#000000' AFTER `pr_color`; 

--
-- Despejando dados para a tabela `prior_atend`
--

INSERT INTO `prior_atend` (`pr_cod`, `pr_nivel`, `pr_default`, `pr_desc`, `pr_color`, `pr_font_color`) VALUES
(1, 1, 1, 'Baixa', '#188236', '#FFFFFF'),
(2, 2, 0, 'Media', '#ff6400', '#FFFFFF'),
(3, 3, 0, 'Alta', '#c0461b', '#FFFFFF'),
(4, 4, 0, 'Urgente', '#CC0000', '#FFFFFF');

-- --------------------------------------------------------

--
-- Estrutura para tabela `prior_nivel`
--

CREATE TABLE `prior_nivel` (
  `prn_cod` int(2) NOT NULL,
  `prn_level` int(2) NOT NULL,
  `prn_used` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Niveis sequenciais para ordem de atendimento';

--
-- Despejando dados para a tabela `prior_nivel`
--

INSERT INTO `prior_nivel` (`prn_cod`, `prn_level`, `prn_used`) VALUES
(1, 1, 0),
(2, 2, 0),
(3, 3, 0),
(4, 4, 0),
(5, 5, 0),
(6, 6, 0),
(7, 7, 0),
(8, 8, 0),
(9, 9, 0),
(10, 10, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `problemas`
--

CREATE TABLE `problemas` (
  `prob_id` int(11) NOT NULL,
  `problema` varchar(100) NOT NULL DEFAULT '',
  `prob_area` int(4) DEFAULT NULL,
  `prob_sla` int(4) DEFAULT NULL,
  `prob_tipo_1` int(4) DEFAULT NULL,
  `prob_tipo_2` int(4) DEFAULT NULL,
  `prob_tipo_3` int(4) DEFAULT NULL,
  `prob_alimenta_banco_solucao` int(1) NOT NULL DEFAULT '1' COMMENT 'Flag para gravar a solucao ou nao',
  `prob_descricao` text COMMENT 'Descricao do tipo de problema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `problemas`
--

INSERT INTO `problemas` (`prob_id`, `problema`, `prob_area`, `prob_sla`, `prob_tipo_1`, `prob_tipo_2`, `prob_tipo_3`, `prob_alimenta_banco_solucao`, `prob_descricao`) VALUES
(1, 'Diversos', -1, 7, -1, -1, -1, 1, 'Solicitações diversas - Configure no painel de administração em Admin::Ocorrências::Tipos de Problemas');

-- --------------------------------------------------------

--
-- Estrutura para tabela `prob_tipo_1`
--

CREATE TABLE `prob_tipo_1` (
  `probt1_cod` int(4) NOT NULL,
  `probt1_desc` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `prob_tipo_2`
--

CREATE TABLE `prob_tipo_2` (
  `probt2_cod` int(4) NOT NULL,
  `probt2_desc` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `prob_tipo_3`
--

CREATE TABLE `prob_tipo_3` (
  `probt3_cod` int(4) NOT NULL,
  `probt3_desc` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `prob_x_script`
--

CREATE TABLE `prob_x_script` (
  `prscpt_id` int(4) NOT NULL,
  `prscpt_prob_id` int(4) NOT NULL,
  `prscpt_scpt_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Scripts por problemas';

-- --------------------------------------------------------

--
-- Estrutura para tabela `reitorias`
--

CREATE TABLE `reitorias` (
  `reit_cod` int(4) NOT NULL,
  `reit_nome` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de reitorias';

--
-- Despejando dados para a tabela `reitorias`
--

INSERT INTO `reitorias` (`reit_cod`, `reit_nome`) VALUES
(1, 'DEFAULT');

-- --------------------------------------------------------

--
-- Estrutura para tabela `resolucao`
--

CREATE TABLE `resolucao` (
  `resol_cod` int(4) NOT NULL,
  `resol_nome` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de resoluções para scanners';

--
-- Despejando dados para a tabela `resolucao`
--

INSERT INTO `resolucao` (`resol_cod`, `resol_nome`) VALUES
(1, '9600 DPI');

-- --------------------------------------------------------

--
-- Estrutura para tabela `scripts`
--

CREATE TABLE `scripts` (
  `scpt_id` int(4) NOT NULL,
  `scpt_nome` varchar(35) NOT NULL,
  `scpt_desc` varchar(100) NOT NULL,
  `scpt_script` text NOT NULL,
  `scpt_enduser` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de scripts para solucoes';

-- --------------------------------------------------------

--
-- Estrutura para tabela `script_solution`
--

CREATE TABLE `script_solution` (
  `script_cod` int(4) NOT NULL,
  `script_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de scripts de solucoes';

-- --------------------------------------------------------

--
-- Estrutura para tabela `sistemas`
--

CREATE TABLE `sistemas` (
  `sis_id` int(11) NOT NULL,
  `sistema` varchar(100) DEFAULT NULL,
  `sis_status` int(4) NOT NULL DEFAULT '1',
  `sis_email` varchar(35) DEFAULT NULL,
  `sis_atende` int(1) NOT NULL DEFAULT '1',
  `sis_screen` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `sistemas`
--

INSERT INTO `sistemas` (`sis_id`, `sistema`, `sis_status`, `sis_email`, `sis_atende`, `sis_screen`) VALUES
(1, 'DEFAULT', 1, 'default@yourdomain.com', 1, 2),
(2, 'USUÁRIOS', 1, 'default@yourdomain.com', 0, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `situacao`
--

CREATE TABLE `situacao` (
  `situac_cod` int(4) NOT NULL,
  `situac_nome` varchar(20) NOT NULL DEFAULT '',
  `situac_desc` varchar(200) DEFAULT NULL,
  `situac_destaque` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de situação de computadores quanto ao seu funcionamento';

--
-- Despejando dados para a tabela `situacao`
--

INSERT INTO `situacao` (`situac_cod`, `situac_nome`, `situac_desc`, `situac_destaque`) VALUES
(1, 'Operacional', 'Equipamento sem problemas de funcionamento', 0),
(2, 'Não Operacional', 'Equipamento utilizado apenas para testes de hardware e não funcionando', 0),
(3, 'Em manutenção', 'Equipamento aguardando peça para manutenção', 0),
(4, 'Furtado', 'Equipamentos furtados da empresa.', 0),
(5, 'Trocado', 'Equipamento trocado por outro em função da sua garantia.', 0),
(6, 'Aguardando orçamento', 'Aguardando orçamento para conserto', 0),
(7, 'Sucateado', 'Equipamento não possui condições para conserto', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `sla_out`
--

CREATE TABLE `sla_out` (
  `out_numero` int(5) NOT NULL COMMENT 'ocorrencias',
  `out_sla` int(1) NOT NULL DEFAULT '0' COMMENT 'se o sla estourou'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela temporaria para controle do sla';

-- --------------------------------------------------------

--
-- Estrutura para tabela `sla_solucao`
--

CREATE TABLE `sla_solucao` (
  `slas_cod` int(4) NOT NULL,
  `slas_tempo` int(6) NOT NULL DEFAULT '0',
  `slas_desc` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de SLAs de tempo de solução';

--
-- Despejando dados para a tabela `sla_solucao`
--

INSERT INTO `sla_solucao` (`slas_cod`, `slas_tempo`, `slas_desc`) VALUES
(1, 15, '15 minutos'),
(2, 30, '30 minutos'),
(3, 45, '45 minutos'),
(4, 60, '1 hora'),
(5, 120, '2 horas'),
(6, 180, '3 horas'),
(7, 240, '4 horas'),
(8, 480, '8 horas'),
(9, 720, '12 horas'),
(10, 1440, '24 horas'),
(11, 2880, '2 dias'),
(12, 4320, '3 dias'),
(13, 5760, '4 dias'),
(14, 10080, '1 semana'),
(15, 20160, '2 semanas'),
(16, 30240, '3 semanas'),
(17, 43200, '1 mês'),
(18, 5, '5 minutos'),
(19, 10, '10 minutos'),
(20, 20, '20 minutos'),
(21, 25, '25 minutos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `softwares`
--

CREATE TABLE `softwares` (
  `soft_cod` int(4) NOT NULL,
  `soft_fab` int(4) NOT NULL DEFAULT '0',
  `soft_desc` varchar(30) NOT NULL DEFAULT '',
  `soft_versao` varchar(10) NOT NULL DEFAULT '',
  `soft_cat` int(4) NOT NULL DEFAULT '0',
  `soft_tipo_lic` int(4) NOT NULL DEFAULT '0',
  `soft_qtd_lic` int(4) DEFAULT NULL,
  `soft_forn` int(4) DEFAULT NULL,
  `soft_nf` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela Softwares do sistema';

--
-- Despejando dados para a tabela `softwares`
--

INSERT INTO `softwares` (`soft_cod`, `soft_fab`, `soft_desc`, `soft_versao`, `soft_cat`, `soft_tipo_lic`, `soft_qtd_lic`, `soft_forn`, `soft_nf`) VALUES
(1, 1, 'Default', '1.0', 10, 1, 1, 1, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solucoes`
--

CREATE TABLE `solucoes` (
  `numero` int(11) NOT NULL DEFAULT '0',
  `problema` text NOT NULL,
  `solucao` text NOT NULL,
  `data` datetime DEFAULT NULL,
  `responsavel` int(4) NOT NULL DEFAULT '0',
  `responsavelbkp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `status`
--

CREATE TABLE `status` (
  `stat_id` int(11) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT '',
  `stat_cat` int(4) DEFAULT NULL,
  `stat_painel` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `status`
--

INSERT INTO `status` (`stat_id`, `status`, `stat_cat`, `stat_painel`) VALUES
(1, 'Aguardando atendimento', 2, 2),
(2, 'Em atendimento', 2, 1),
(3, 'Em estudo', 2, 1),
(4, 'Encerrada', 4, 3),
(7, 'Agendado com usuário', 1, 2),
(12, 'Cancelado', 4, 3),
(16, 'Aguardando feedback do usuário', 1, 2),
(19, 'IndisponÍvel para atendimento', 1, 2),
(21, 'Encaminhado para operador', 2, 1),
(22, 'Interrompido para atender outro chamado', 2, 1),
(25, 'Aguardando retorno do fornecedor', 3, 1),
(26, 'Com Backup', 4, 2),
(27, 'Reservado para Operador', 2, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `status_categ`
--

CREATE TABLE `status_categ` (
  `stc_cod` int(4) NOT NULL,
  `stc_desc` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Categorias de Status para Chamados';

--
-- Despejando dados para a tabela `status_categ`
--

INSERT INTO `status_categ` (`stc_cod`, `stc_desc`) VALUES
(1, 'AO USUÁRIO'),
(2, 'À ÀREA TÉCNICA'),
(3, 'À SERVIÇOS DE TERCEIROS'),
(4, 'INDEPENDENTE');

-- --------------------------------------------------------

--
-- Estrutura para tabela `styles`
--

CREATE TABLE `styles` (
  `tm_id` int(2) NOT NULL,
  `tm_color_destaca` varchar(15) NOT NULL DEFAULT '#CCCCCC',
  `tm_color_marca` varchar(15) NOT NULL DEFAULT '#FFFFCC',
  `tm_color_lin_par` varchar(15) NOT NULL DEFAULT '#E3E1E1',
  `tm_color_lin_impar` varchar(15) NOT NULL DEFAULT '#F6F6F6',
  `tm_color_body` varchar(15) NOT NULL DEFAULT '#F6F6F6',
  `tm_color_td` varchar(15) NOT NULL DEFAULT '#DBDBDB',
  `tm_borda_width` int(2) NOT NULL DEFAULT '2',
  `tm_borda_color` varchar(10) NOT NULL DEFAULT '#F6F6F6',
  `tm_tr_header` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_topo` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_topo_font` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `tm_color_barra` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_menu` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_barra_font` varchar(7) NOT NULL DEFAULT '#675E66',
  `tm_color_barra_hover` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `tm_barra_fundo_destaque` varchar(7) NOT NULL DEFAULT '#666666',
  `tm_barra_fonte_destaque` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `tm_color_font_tr_header` varchar(7) NOT NULL DEFAULT '#000000',
  `tm_color_borda_header_centro` varchar(7) NOT NULL DEFAULT '#999999'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `styles`
--

INSERT INTO `styles` (`tm_id`, `tm_color_destaca`, `tm_color_marca`, `tm_color_lin_par`, `tm_color_lin_impar`, `tm_color_body`, `tm_color_td`, `tm_borda_width`, `tm_borda_color`, `tm_tr_header`, `tm_color_topo`, `tm_color_topo_font`, `tm_color_barra`, `tm_color_menu`, `tm_color_barra_font`, `tm_color_barra_hover`, `tm_barra_fundo_destaque`, `tm_barra_fonte_destaque`, `tm_color_font_tr_header`, `tm_color_borda_header_centro`) VALUES
(1, '#CCCCCC', '#FFFFCC', '#E3E1E1', '#F6F6F6', '#F6F6F6', '#DBDBDB', 2, '#F6F6F6', 'IMG_DEFAULT', 'IMG_DEFAULT', '#FFFFFF', 'IMG_DEFAULT', 'IMG_DEFAULT', '#675E66', '#FFFFFF', '#666666', '#FFFFFF', '#000000', '#999999');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sw_padrao`
--

CREATE TABLE `sw_padrao` (
  `swp_cod` int(4) NOT NULL,
  `swp_sw_cod` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de softwares padrao para cada equipamento';

-- --------------------------------------------------------

--
-- Estrutura para tabela `temas`
--

CREATE TABLE `temas` (
  `tm_id` int(2) NOT NULL,
  `tm_nome` varchar(15) NOT NULL DEFAULT 'DEFAULT',
  `tm_color_destaca` varchar(10) NOT NULL DEFAULT '#CCCCCC',
  `tm_color_marca` varchar(10) NOT NULL DEFAULT '#FFFFCC',
  `tm_color_lin_par` varchar(10) NOT NULL DEFAULT '#E3E1E1',
  `tm_color_lin_impar` varchar(10) NOT NULL DEFAULT '#F6F6F6',
  `tm_color_body` varchar(10) NOT NULL DEFAULT '#F6F6F6',
  `tm_color_td` varchar(10) NOT NULL DEFAULT '#DBDBDB',
  `tm_borda_width` int(2) NOT NULL DEFAULT '2',
  `tm_borda_color` varchar(10) NOT NULL DEFAULT '#F6F6F6',
  `tm_tr_header` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_topo` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_topo_font` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `tm_color_barra` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_menu` varchar(11) NOT NULL DEFAULT 'IMG_DEFAULT',
  `tm_color_barra_font` varchar(7) NOT NULL DEFAULT '#675E66',
  `tm_color_barra_hover` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `tm_barra_fundo_destaque` varchar(7) NOT NULL DEFAULT '#666666',
  `tm_barra_fonte_destaque` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `tm_color_font_tr_header` varchar(7) NOT NULL DEFAULT '#000000',
  `tm_color_borda_header_centro` varchar(7) NOT NULL DEFAULT '#999999'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `temas`
--

INSERT INTO `temas` (`tm_id`, `tm_nome`, `tm_color_destaca`, `tm_color_marca`, `tm_color_lin_par`, `tm_color_lin_impar`, `tm_color_body`, `tm_color_td`, `tm_borda_width`, `tm_borda_color`, `tm_tr_header`, `tm_color_topo`, `tm_color_topo_font`, `tm_color_barra`, `tm_color_menu`, `tm_color_barra_font`, `tm_color_barra_hover`, `tm_barra_fundo_destaque`, `tm_barra_fonte_destaque`, `tm_color_font_tr_header`, `tm_color_borda_header_centro`) VALUES
(1, 'GREEN', '#D0DBCE', '#D0DBCE', '#FFFFFF', '#FFFFFF', '#EEEFE9', '#D0DBCE', 1, '#427041', '#427041', '#3B6B39', '#FFFFFF', '#E3E3E3', '#EEEFE9', '#000000', '#FFFFFF', '#427041', '#FFFFFF', '#FFFFFF', '#427041'),
(2, 'OLD_TIMES', '#99CCFF', '#99CCFF', '#CDE5FF', '#FFFFFF', '#CDE5FF', '#92AECC', 0, '#FFFFFF', '#92AECC', '#92AECC', '#FFFFFF', '#CDE5FF', '#CDE5FF', '#0000EE', '#8F6C7F', '#CDE5FF', '#8F6C7F', '#000000', '#92AECC'),
(3, 'GMAIL', '#FFFFCC', '#E8EEF7', '#FFFFFF', '#FFFFFF', '#FFFFFF', '#E0ECFF', 1, '#BBBBBB', '#C3D9FF', '#DFECF5', '#0000CC', '#C3D9FF', '#FFFFFF', '#0000CC', '#000000', '#FFFFFF', '#000000', '#000000', '#C3D9FF'),
(4, 'CLASSICO', '#D5D5D5', '#FFCC99', '#EAE6D0', '#F8F8F1', '#F6F6F6', '#ECECDB', 0, '#F6F6F6', '#DDDCC5', '#5e515b', '#FFFFFF', '#999999', 'IMG_DEFAULT', '#FFFFFF', '#FFFFFF', '#666666', '#FFFFFF', '#000000', '#DDDCC5'),
(5, 'DEFAULT', '#CCCCCC', '#FFFFCC', '#E3E1E1', '#F6F6F6', '#F6F6F6', '#DBDBDB', 2, '#F6F6F6', 'IMG_DEFAULT', 'IMG_DEFAULT', '#FFFFFF', 'IMG_DEFAULT', 'IMG_DEFAULT', '#675E66', '#FFFFFF', '#666666', '#FFFFFF', '#000000', '#999999'),
(6, 'black_edition', '#CCCCCC', '#FFFFCC', '#E3E1E1', '#F6F6F6', '#FFFFFF', '#999999', 2, '#FFFFFF', '#999999', '#000000', '#FFFFFF', '#000000', 'IMG_DEFAULT', '#FFFFFF', '#000000', '#FFFFFF', '#000000', '#000000', '#FF0000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tempo_garantia`
--

CREATE TABLE `tempo_garantia` (
  `tempo_cod` int(4) NOT NULL,
  `tempo_meses` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de tempos de duração das garantias';

--
-- Despejando dados para a tabela `tempo_garantia`
--

INSERT INTO `tempo_garantia` (`tempo_cod`, `tempo_meses`) VALUES
(4, 6),
(1, 12),
(5, 18),
(2, 24),
(3, 36);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tempo_status`
--

CREATE TABLE `tempo_status` (
  `ts_cod` int(6) NOT NULL,
  `ts_ocorrencia` int(5) NOT NULL DEFAULT '0',
  `ts_status` int(4) NOT NULL DEFAULT '0',
  `ts_tempo` int(10) NOT NULL DEFAULT '0',
  `ts_data` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela para armazenar o tempo dos chamados em cada status';

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_equip`
--

CREATE TABLE `tipo_equip` (
  `tipo_cod` int(11) NOT NULL,
  `tipo_nome` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Tipos de Equipamentos de informática';

--
-- Despejando dados para a tabela `tipo_equip`
--

INSERT INTO `tipo_equip` (`tipo_cod`, `tipo_nome`) VALUES
(1, 'Computador PC'),
(2, 'Notebook'),
(3, 'Impressora'),
(4, 'Scanner'),
(5, 'Monitor'),
(6, 'Zip Drive'),
(7, 'Switch'),
(8, 'HUB'),
(9, 'Gravador externo de CD'),
(10, 'Placa externa de captura'),
(11, 'No Break'),
(12, 'Servidor SCSI'),
(13, 'Smartphone');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_garantia`
--

CREATE TABLE `tipo_garantia` (
  `tipo_garant_cod` int(4) NOT NULL,
  `tipo_garant_nome` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de tipos de garantias de equipamentos';

--
-- Despejando dados para a tabela `tipo_garantia`
--

INSERT INTO `tipo_garantia` (`tipo_garant_cod`, `tipo_garant_nome`) VALUES
(1, 'Balcão'),
(2, 'On site');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_imp`
--

CREATE TABLE `tipo_imp` (
  `tipo_imp_cod` int(11) NOT NULL,
  `tipo_imp_nome` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de tipos de impressoras';

--
-- Despejando dados para a tabela `tipo_imp`
--

INSERT INTO `tipo_imp` (`tipo_imp_cod`, `tipo_imp_nome`) VALUES
(1, 'Matricial'),
(2, 'Jato de tinta'),
(3, 'Laser'),
(4, 'Multifuncional'),
(5, 'Copiadora'),
(6, 'Matricial cupom não fiscal');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_item`
--

CREATE TABLE `tipo_item` (
  `tipo_it_cod` int(4) NOT NULL,
  `tipo_it_desc` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tipos de itens - hw ou sw';

--
-- Despejando dados para a tabela `tipo_item`
--

INSERT INTO `tipo_item` (`tipo_it_cod`, `tipo_it_desc`) VALUES
(1, 'HARDWARE'),
(2, 'SOFTWARE'),
(3, 'HARDWARE E SOFTWARE');

-- --------------------------------------------------------

--
-- Estrutura para tabela `uprefs`
--

CREATE TABLE `uprefs` (
  `upref_uid` int(4) NOT NULL,
  `upref_lang` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de preferencias diversas dos usuarios';

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `user_id` int(4) NOT NULL,
  `login` varchar(100) NOT NULL DEFAULT '',
  `nome` varchar(200) NOT NULL DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  `data_inc` date DEFAULT NULL,
  `data_admis` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `nivel` char(2) DEFAULT NULL,
  `AREA` char(3) DEFAULT 'ALL',
  `user_admin` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de operadores do sistema';

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`user_id`, `login`, `nome`, `password`, `data_inc`, `data_admis`, `email`, `fone`, `nivel`, `AREA`, `user_admin`) VALUES
(1, 'admin', 'Administrador do Sistema', '21232f297a57a5a743894a0e4a801fc3', '2020-07-03', '2020-07-03', 'admin@yourdomain.com', '123456', '1', '1', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_areas`
--

CREATE TABLE `usuarios_areas` (
  `uarea_cod` int(4) NOT NULL,
  `uarea_uid` int(4) NOT NULL DEFAULT '0',
  `uarea_sid` varchar(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de areas que o usuario pertence';

--
-- Despejando dados para a tabela `usuarios_areas`
--

INSERT INTO `usuarios_areas` (`uarea_cod`, `uarea_uid`, `uarea_sid`) VALUES
(1, 1, '1');

-- --------------------------------------------------------

--
-- Estrutura para tabela `uthemes`
--

CREATE TABLE `uthemes` (
  `uth_id` int(4) NOT NULL,
  `uth_uid` int(4) NOT NULL,
  `uth_thid` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Temas por usuario';

-- --------------------------------------------------------

--
-- Estrutura para tabela `utmp_usuarios`
--

CREATE TABLE `utmp_usuarios` (
  `utmp_cod` int(4) NOT NULL,
  `utmp_login` varchar(100) NOT NULL,
  `utmp_nome` varchar(40) NOT NULL DEFAULT '',
  `utmp_email` varchar(40) NOT NULL DEFAULT '',
  `utmp_passwd` varchar(40) NOT NULL DEFAULT '',
  `utmp_rand` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de transição para cadastro de usuários';

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `areaxarea_abrechamado`
--
ALTER TABLE `areaxarea_abrechamado`
  ADD PRIMARY KEY (`area`,`area_abrechamado`),
  ADD KEY `fk_area_abrechamado` (`area_abrechamado`);

--
-- Índices de tabela `assentamentos`
--
ALTER TABLE `assentamentos`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `ocorrencia` (`ocorrencia`),
  ADD KEY `tipo_assentamento` (`tipo_assentamento`);

--
-- Índices de tabela `assistencia`
--
ALTER TABLE `assistencia`
  ADD PRIMARY KEY (`assist_cod`);

--
-- Índices de tabela `avisos`
--
ALTER TABLE `avisos`
  ADD PRIMARY KEY (`aviso_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`cat_cod`);

--
-- Índices de tabela `categoriaxproblema_sistemas`
--
ALTER TABLE `categoriaxproblema_sistemas`
  ADD PRIMARY KEY (`prob_id`),
  ADD KEY `ctps_id` (`ctps_id`,`prob_id`);

--
-- Índices de tabela `cat_problema_sistemas`
--
ALTER TABLE `cat_problema_sistemas`
  ADD PRIMARY KEY (`ctps_id`);

--
-- Índices de tabela `ccusto`
--
ALTER TABLE `ccusto`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `codccusto` (`codccusto`);

--
-- Índices de tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`conf_cod`),
  ADD KEY `conf_formatBar` (`conf_formatBar`),
  ADD KEY `conf_prob_tipo_1` (`conf_prob_tipo_1`,`conf_prob_tipo_2`,`conf_prob_tipo_3`);

--
-- Índices de tabela `configusercall`
--
ALTER TABLE `configusercall`
  ADD PRIMARY KEY (`conf_cod`),
  ADD KEY `conf_opentoarea` (`conf_opentoarea`),
  ADD KEY `conf_nivel` (`conf_custom_areas`),
  ADD KEY `conf_ownarea` (`conf_ownarea`);

--
-- Índices de tabela `contatos`
--
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`contact_id`),
  ADD UNIQUE KEY `contact_login` (`contact_login`,`contact_email`);

--
-- Índices de tabela `doc_time`
--
ALTER TABLE `doc_time`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `doc_user` (`doc_user`),
  ADD KEY `doc_oco` (`doc_oco`);

--
-- Índices de tabela `dominios`
--
ALTER TABLE `dominios`
  ADD PRIMARY KEY (`dom_cod`);

--
-- Índices de tabela `email_warranty`
--
ALTER TABLE `email_warranty`
  ADD PRIMARY KEY (`ew_id`),
  ADD KEY `ew_piece_id` (`ew_piece_id`);

--
-- Índices de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD PRIMARY KEY (`empr_id`);

--
-- Índices de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  ADD PRIMARY KEY (`comp_inv`,`comp_inst`),
  ADD KEY `comp_cod` (`comp_cod`),
  ADD KEY `comp_inv` (`comp_inv`),
  ADD KEY `comp_assist` (`comp_assist`);

--
-- Índices de tabela `equipxpieces`
--
ALTER TABLE `equipxpieces`
  ADD PRIMARY KEY (`eqp_id`),
  ADD KEY `eqp_equip_inv` (`eqp_equip_inv`,`eqp_equip_inst`,`eqp_piece_id`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`estoq_cod`),
  ADD KEY `estoq_tipo` (`estoq_tipo`,`estoq_desc`),
  ADD KEY `estoq_local` (`estoq_local`),
  ADD KEY `estoq_tag_inv` (`estoq_tag_inv`,`estoq_tag_inst`),
  ADD KEY `estoq_partnumber` (`estoq_partnumber`);

--
-- Índices de tabela `fabricantes`
--
ALTER TABLE `fabricantes`
  ADD PRIMARY KEY (`fab_cod`),
  ADD KEY `fab_cod` (`fab_cod`),
  ADD KEY `fab_tipo` (`fab_tipo`);

--
-- Índices de tabela `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`cod_feriado`),
  ADD KEY `data_feriado` (`data_feriado`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`forn_cod`),
  ADD KEY `forn_cod` (`forn_cod`);

--
-- Índices de tabela `global_tickets`
--
ALTER TABLE `global_tickets`
  ADD PRIMARY KEY (`gt_ticket`),
  ADD KEY `gt_id` (`gt_id`);

--
-- Índices de tabela `historico`
--
ALTER TABLE `historico`
  ADD PRIMARY KEY (`hist_cod`),
  ADD KEY `hist_inv` (`hist_inv`),
  ADD KEY `hist_inst` (`hist_inst`);

--
-- Índices de tabela `hist_pieces`
--
ALTER TABLE `hist_pieces`
  ADD PRIMARY KEY (`hp_id`),
  ADD KEY `hp_piece_id` (`hp_piece_id`,`hp_piece_local`,`hp_comp_inv`,`hp_comp_inst`),
  ADD KEY `hp_technician` (`hp_technician`);

--
-- Índices de tabela `hw_alter`
--
ALTER TABLE `hw_alter`
  ADD PRIMARY KEY (`hwa_cod`),
  ADD KEY `hwa_inst` (`hwa_inst`,`hwa_inv`,`hwa_item`,`hwa_user`);

--
-- Índices de tabela `hw_sw`
--
ALTER TABLE `hw_sw`
  ADD PRIMARY KEY (`hws_cod`),
  ADD KEY `hws_sw_cod` (`hws_sw_cod`,`hws_hw_cod`),
  ADD KEY `hws_hw_inst` (`hws_hw_inst`);

--
-- Índices de tabela `imagens`
--
ALTER TABLE `imagens`
  ADD PRIMARY KEY (`img_cod`),
  ADD KEY `img_oco` (`img_oco`),
  ADD KEY `img_inv` (`img_inv`,`img_model`),
  ADD KEY `img_inst` (`img_inst`);

--
-- Índices de tabela `instituicao`
--
ALTER TABLE `instituicao`
  ADD PRIMARY KEY (`inst_cod`),
  ADD KEY `inst_cod` (`inst_cod`),
  ADD KEY `inst_status` (`inst_status`);

--
-- Índices de tabela `itens`
--
ALTER TABLE `itens`
  ADD PRIMARY KEY (`item_cod`),
  ADD KEY `item_nome` (`item_nome`);

--
-- Índices de tabela `licencas`
--
ALTER TABLE `licencas`
  ADD PRIMARY KEY (`lic_cod`);

--
-- Índices de tabela `localizacao`
--
ALTER TABLE `localizacao`
  ADD UNIQUE KEY `loc_id` (`loc_id`),
  ADD KEY `loc_sla` (`loc_prior`),
  ADD KEY `loc_dominio` (`loc_dominio`),
  ADD KEY `loc_predio` (`loc_predio`),
  ADD KEY `loc_status` (`loc_status`),
  ADD KEY `loc_prior` (`loc_prior`);

--
-- Índices de tabela `lock_oco`
--
ALTER TABLE `lock_oco`
  ADD PRIMARY KEY (`lck_id`),
  ADD UNIQUE KEY `lck_oco` (`lck_oco`),
  ADD KEY `lck_uid` (`lck_uid`);

--
-- Índices de tabela `mailconfig`
--
ALTER TABLE `mailconfig`
  ADD PRIMARY KEY (`mail_cod`);

--
-- Índices de tabela `mail_hist`
--
ALTER TABLE `mail_hist`
  ADD PRIMARY KEY (`mhist_cod`),
  ADD KEY `mhist_technician` (`mhist_technician`),
  ADD KEY `mhist_oco` (`mhist_oco`);

--
-- Índices de tabela `mail_list`
--
ALTER TABLE `mail_list`
  ADD PRIMARY KEY (`ml_cod`);

--
-- Índices de tabela `mail_templates`
--
ALTER TABLE `mail_templates`
  ADD PRIMARY KEY (`tpl_cod`);

--
-- Índices de tabela `marcas_comp`
--
ALTER TABLE `marcas_comp`
  ADD PRIMARY KEY (`marc_cod`),
  ADD KEY `marc_cod` (`marc_cod`),
  ADD KEY `marc_tipo` (`marc_tipo`);

--
-- Índices de tabela `materiais`
--
ALTER TABLE `materiais`
  ADD PRIMARY KEY (`mat_cod`),
  ADD KEY `mat_cod_2` (`mat_cod`),
  ADD KEY `mat_modelo_equip` (`mat_modelo_equip`);

--
-- Índices de tabela `modelos_itens`
--
ALTER TABLE `modelos_itens`
  ADD PRIMARY KEY (`mdit_cod`),
  ADD KEY `mdit_desc` (`mdit_desc`),
  ADD KEY `mdit_tipo` (`mdit_tipo`),
  ADD KEY `cod_old` (`mdit_cod_old`);

--
-- Índices de tabela `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`modu_cod`),
  ADD KEY `modu_nome` (`modu_nome`);

--
-- Índices de tabela `moldes`
--
ALTER TABLE `moldes`
  ADD PRIMARY KEY (`mold_marca`),
  ADD KEY `mold_cod` (`mold_cod`);

--
-- Índices de tabela `msgconfig`
--
ALTER TABLE `msgconfig`
  ADD PRIMARY KEY (`msg_cod`),
  ADD UNIQUE KEY `msg_event` (`msg_event`);

--
-- Índices de tabela `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`nivel_cod`);

--
-- Índices de tabela `ocodeps`
--
ALTER TABLE `ocodeps`
  ADD KEY `dep_filho` (`dep_filho`),
  ADD KEY `dep_pai` (`dep_pai`);

--
-- Índices de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `data_abertura` (`data_abertura`),
  ADD KEY `data_fechamento` (`data_fechamento`),
  ADD KEY `local` (`local`),
  ADD KEY `aberto_por` (`aberto_por`),
  ADD KEY `oco_scheduled` (`oco_scheduled`),
  ADD KEY `oco_script_sol` (`oco_script_sol`),
  ADD KEY `oco_prior` (`oco_prior`);

--
-- Índices de tabela `permissoes`
--
ALTER TABLE `permissoes`
  ADD PRIMARY KEY (`perm_cod`),
  ADD KEY `perm_area` (`perm_area`,`perm_modulo`,`perm_flag`);

--
-- Índices de tabela `polegada`
--
ALTER TABLE `polegada`
  ADD PRIMARY KEY (`pole_cod`),
  ADD KEY `pole_cod` (`pole_cod`);

--
-- Índices de tabela `predios`
--
ALTER TABLE `predios`
  ADD PRIMARY KEY (`pred_cod`);

--
-- Índices de tabela `prioridades`
--
ALTER TABLE `prioridades`
  ADD PRIMARY KEY (`prior_cod`),
  ADD KEY `prior_nivel` (`prior_nivel`,`prior_sla`),
  ADD KEY `prior_sla` (`prior_sla`);

--
-- Índices de tabela `prior_atend`
--
ALTER TABLE `prior_atend`
  ADD PRIMARY KEY (`pr_cod`),
  ADD UNIQUE KEY `pr_nivel` (`pr_nivel`);

--
-- Índices de tabela `prior_nivel`
--
ALTER TABLE `prior_nivel`
  ADD PRIMARY KEY (`prn_cod`),
  ADD KEY `prn_level` (`prn_level`);

--
-- Índices de tabela `problemas`
--
ALTER TABLE `problemas`
  ADD PRIMARY KEY (`prob_id`),
  ADD KEY `prob_id` (`prob_id`),
  ADD KEY `prob_area` (`prob_area`),
  ADD KEY `prob_sla` (`prob_sla`),
  ADD KEY `prob_tipo_1` (`prob_tipo_1`,`prob_tipo_2`),
  ADD KEY `prob_tipo_3` (`prob_tipo_3`);

--
-- Índices de tabela `prob_tipo_1`
--
ALTER TABLE `prob_tipo_1`
  ADD PRIMARY KEY (`probt1_cod`);

--
-- Índices de tabela `prob_tipo_2`
--
ALTER TABLE `prob_tipo_2`
  ADD PRIMARY KEY (`probt2_cod`);

--
-- Índices de tabela `prob_tipo_3`
--
ALTER TABLE `prob_tipo_3`
  ADD PRIMARY KEY (`probt3_cod`);

--
-- Índices de tabela `prob_x_script`
--
ALTER TABLE `prob_x_script`
  ADD PRIMARY KEY (`prscpt_id`),
  ADD KEY `prscpt_prob_id` (`prscpt_prob_id`,`prscpt_scpt_id`);

--
-- Índices de tabela `reitorias`
--
ALTER TABLE `reitorias`
  ADD PRIMARY KEY (`reit_cod`),
  ADD KEY `reit_nome` (`reit_nome`);

--
-- Índices de tabela `resolucao`
--
ALTER TABLE `resolucao`
  ADD PRIMARY KEY (`resol_cod`),
  ADD KEY `resol_cod` (`resol_cod`);

--
-- Índices de tabela `scripts`
--
ALTER TABLE `scripts`
  ADD PRIMARY KEY (`scpt_id`);

--
-- Índices de tabela `script_solution`
--
ALTER TABLE `script_solution`
  ADD PRIMARY KEY (`script_cod`);

--
-- Índices de tabela `sistemas`
--
ALTER TABLE `sistemas`
  ADD PRIMARY KEY (`sis_id`),
  ADD KEY `sis_status` (`sis_status`),
  ADD KEY `sis_screen` (`sis_screen`);

--
-- Índices de tabela `situacao`
--
ALTER TABLE `situacao`
  ADD PRIMARY KEY (`situac_cod`);

--
-- Índices de tabela `sla_out`
--
ALTER TABLE `sla_out`
  ADD KEY `out_numero` (`out_numero`);

--
-- Índices de tabela `sla_solucao`
--
ALTER TABLE `sla_solucao`
  ADD PRIMARY KEY (`slas_cod`),
  ADD KEY `slas_tempo` (`slas_tempo`),
  ADD KEY `slas_tempo_2` (`slas_tempo`);

--
-- Índices de tabela `softwares`
--
ALTER TABLE `softwares`
  ADD PRIMARY KEY (`soft_cod`),
  ADD KEY `soft_fab` (`soft_fab`,`soft_cat`,`soft_tipo_lic`),
  ADD KEY `soft_versao` (`soft_versao`),
  ADD KEY `soft_nf` (`soft_nf`),
  ADD KEY `soft_forn` (`soft_forn`);

--
-- Índices de tabela `solucoes`
--
ALTER TABLE `solucoes`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `numero` (`numero`);

--
-- Índices de tabela `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`stat_id`),
  ADD KEY `stat_cat` (`stat_cat`),
  ADD KEY `stat_painel` (`stat_painel`);

--
-- Índices de tabela `status_categ`
--
ALTER TABLE `status_categ`
  ADD PRIMARY KEY (`stc_cod`);

--
-- Índices de tabela `styles`
--
ALTER TABLE `styles`
  ADD PRIMARY KEY (`tm_id`);

--
-- Índices de tabela `sw_padrao`
--
ALTER TABLE `sw_padrao`
  ADD PRIMARY KEY (`swp_cod`),
  ADD KEY `swp_sw_cod` (`swp_sw_cod`);

--
-- Índices de tabela `temas`
--
ALTER TABLE `temas`
  ADD PRIMARY KEY (`tm_id`);

--
-- Índices de tabela `tempo_garantia`
--
ALTER TABLE `tempo_garantia`
  ADD PRIMARY KEY (`tempo_cod`),
  ADD KEY `tempo_meses` (`tempo_meses`);

--
-- Índices de tabela `tempo_status`
--
ALTER TABLE `tempo_status`
  ADD PRIMARY KEY (`ts_cod`),
  ADD KEY `ts_ocorrencia` (`ts_ocorrencia`,`ts_status`);

--
-- Índices de tabela `tipo_equip`
--
ALTER TABLE `tipo_equip`
  ADD PRIMARY KEY (`tipo_cod`),
  ADD KEY `tipo_cod` (`tipo_cod`);

--
-- Índices de tabela `tipo_garantia`
--
ALTER TABLE `tipo_garantia`
  ADD PRIMARY KEY (`tipo_garant_cod`);

--
-- Índices de tabela `tipo_imp`
--
ALTER TABLE `tipo_imp`
  ADD PRIMARY KEY (`tipo_imp_cod`),
  ADD KEY `tipo_imp_cod` (`tipo_imp_cod`);

--
-- Índices de tabela `tipo_item`
--
ALTER TABLE `tipo_item`
  ADD PRIMARY KEY (`tipo_it_cod`);

--
-- Índices de tabela `uprefs`
--
ALTER TABLE `uprefs`
  ADD PRIMARY KEY (`upref_uid`),
  ADD KEY `upref_lang` (`upref_lang`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `login` (`login`);

--
-- Índices de tabela `usuarios_areas`
--
ALTER TABLE `usuarios_areas`
  ADD PRIMARY KEY (`uarea_cod`),
  ADD KEY `uarea_uid` (`uarea_uid`,`uarea_sid`);

--
-- Índices de tabela `uthemes`
--
ALTER TABLE `uthemes`
  ADD PRIMARY KEY (`uth_id`),
  ADD KEY `uth_uid` (`uth_uid`,`uth_thid`);

--
-- Índices de tabela `utmp_usuarios`
--
ALTER TABLE `utmp_usuarios`
  ADD PRIMARY KEY (`utmp_cod`),
  ADD UNIQUE KEY `utmp_login` (`utmp_login`,`utmp_email`),
  ADD KEY `utmp_rand` (`utmp_rand`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `assentamentos`
--
ALTER TABLE `assentamentos`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assistencia`
--
ALTER TABLE `assistencia`
  MODIFY `assist_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `avisos`
--
ALTER TABLE `avisos`
  MODIFY `aviso_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `cat_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `ccusto`
--
ALTER TABLE `ccusto`
  MODIFY `codigo` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `conf_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `configusercall`
--
ALTER TABLE `configusercall`
  MODIFY `conf_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `contatos`
--
ALTER TABLE `contatos`
  MODIFY `contact_id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `doc_time`
--
ALTER TABLE `doc_time`
  MODIFY `doc_id` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `dominios`
--
ALTER TABLE `dominios`
  MODIFY `dom_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `email_warranty`
--
ALTER TABLE `email_warranty`
  MODIFY `ew_id` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  MODIFY `empr_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  MODIFY `comp_cod` int(4) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `equipxpieces`
--
ALTER TABLE `equipxpieces`
  MODIFY `eqp_id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `estoq_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fabricantes`
--
ALTER TABLE `fabricantes`
  MODIFY `fab_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `feriados`
--
ALTER TABLE `feriados`
  MODIFY `cod_feriado` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `forn_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `historico`
--
ALTER TABLE `historico`
  MODIFY `hist_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `hist_pieces`
--
ALTER TABLE `hist_pieces`
  MODIFY `hp_id` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `hw_alter`
--
ALTER TABLE `hw_alter`
  MODIFY `hwa_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `hw_sw`
--
ALTER TABLE `hw_sw`
  MODIFY `hws_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `imagens`
--
ALTER TABLE `imagens`
  MODIFY `img_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `instituicao`
--
ALTER TABLE `instituicao`
  MODIFY `inst_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `itens`
--
ALTER TABLE `itens`
  MODIFY `item_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `licencas`
--
ALTER TABLE `licencas`
  MODIFY `lic_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `localizacao`
--
ALTER TABLE `localizacao`
  MODIFY `loc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `lock_oco`
--
ALTER TABLE `lock_oco`
  MODIFY `lck_id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mailconfig`
--
ALTER TABLE `mailconfig`
  MODIFY `mail_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `mail_hist`
--
ALTER TABLE `mail_hist`
  MODIFY `mhist_cod` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mail_list`
--
ALTER TABLE `mail_list`
  MODIFY `ml_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mail_templates`
--
ALTER TABLE `mail_templates`
  MODIFY `tpl_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcas_comp`
--
ALTER TABLE `marcas_comp`
  MODIFY `marc_cod` int(4) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `materiais`
--
ALTER TABLE `materiais`
  MODIFY `mat_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `modelos_itens`
--
ALTER TABLE `modelos_itens`
  MODIFY `mdit_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `modulos`
--
ALTER TABLE `modulos`
  MODIFY `modu_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `moldes`
--
ALTER TABLE `moldes`
  MODIFY `mold_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `msgconfig`
--
ALTER TABLE `msgconfig`
  MODIFY `msg_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `nivel`
--
ALTER TABLE `nivel`
  MODIFY `nivel_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `permissoes`
--
ALTER TABLE `permissoes`
  MODIFY `perm_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `polegada`
--
ALTER TABLE `polegada`
  MODIFY `pole_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `predios`
--
ALTER TABLE `predios`
  MODIFY `pred_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `prioridades`
--
ALTER TABLE `prioridades`
  MODIFY `prior_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `prior_atend`
--
ALTER TABLE `prior_atend`
  MODIFY `pr_cod` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `prior_nivel`
--
ALTER TABLE `prior_nivel`
  MODIFY `prn_cod` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `problemas`
--
ALTER TABLE `problemas`
  MODIFY `prob_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `prob_tipo_1`
--
ALTER TABLE `prob_tipo_1`
  MODIFY `probt1_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prob_tipo_2`
--
ALTER TABLE `prob_tipo_2`
  MODIFY `probt2_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prob_tipo_3`
--
ALTER TABLE `prob_tipo_3`
  MODIFY `probt3_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prob_x_script`
--
ALTER TABLE `prob_x_script`
  MODIFY `prscpt_id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `reitorias`
--
ALTER TABLE `reitorias`
  MODIFY `reit_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `resolucao`
--
ALTER TABLE `resolucao`
  MODIFY `resol_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `scripts`
--
ALTER TABLE `scripts`
  MODIFY `scpt_id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `script_solution`
--
ALTER TABLE `script_solution`
  MODIFY `script_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sistemas`
--
ALTER TABLE `sistemas`
  MODIFY `sis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `situacao`
--
ALTER TABLE `situacao`
  MODIFY `situac_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `sla_solucao`
--
ALTER TABLE `sla_solucao`
  MODIFY `slas_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `softwares`
--
ALTER TABLE `softwares`
  MODIFY `soft_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `status`
--
ALTER TABLE `status`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `status_categ`
--
ALTER TABLE `status_categ`
  MODIFY `stc_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `styles`
--
ALTER TABLE `styles`
  MODIFY `tm_id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `sw_padrao`
--
ALTER TABLE `sw_padrao`
  MODIFY `swp_cod` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `temas`
--
ALTER TABLE `temas`
  MODIFY `tm_id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `tempo_garantia`
--
ALTER TABLE `tempo_garantia`
  MODIFY `tempo_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tempo_status`
--
ALTER TABLE `tempo_status`
  MODIFY `ts_cod` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tipo_equip`
--
ALTER TABLE `tipo_equip`
  MODIFY `tipo_cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `tipo_garantia`
--
ALTER TABLE `tipo_garantia`
  MODIFY `tipo_garant_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tipo_imp`
--
ALTER TABLE `tipo_imp`
  MODIFY `tipo_imp_cod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `tipo_item`
--
ALTER TABLE `tipo_item`
  MODIFY `tipo_it_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `user_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios_areas`
--
ALTER TABLE `usuarios_areas`
  MODIFY `uarea_cod` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `uthemes`
--
ALTER TABLE `uthemes`
  MODIFY `uth_id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `utmp_usuarios`
--
ALTER TABLE `utmp_usuarios`
  MODIFY `utmp_cod` int(4) NOT NULL AUTO_INCREMENT;
  
  
  
  
--
-- VERSAO 3.0
--
  
  
  
--
-- Estrutura para tabela `worktime_profiles`
--

CREATE TABLE `worktime_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_default` tinyint(1) DEFAULT NULL,
  `week_ini_time_hour` varchar(2) NOT NULL,
  `week_ini_time_minute` varchar(2) NOT NULL,
  `week_end_time_hour` varchar(2) NOT NULL,
  `week_end_time_minute` varchar(2) NOT NULL,
  `week_day_full_worktime` int(5) NOT NULL,
  `sat_ini_time_hour` varchar(2) NOT NULL,
  `sat_ini_time_minute` varchar(2) NOT NULL,
  `sat_end_time_hour` varchar(2) NOT NULL,
  `sat_end_time_minute` varchar(2) NOT NULL,
  `sat_day_full_worktime` int(5) NOT NULL,
  `sun_ini_time_hour` varchar(2) NOT NULL,
  `sun_ini_time_minute` varchar(2) NOT NULL,
  `sun_end_time_hour` varchar(2) NOT NULL,
  `sun_end_time_minute` varchar(2) NOT NULL,
  `sun_day_full_worktime` int(5) NOT NULL,
  `off_ini_time_hour` varchar(2) NOT NULL,
  `off_ini_time_minute` varchar(2) NOT NULL,
  `off_end_time_hour` varchar(2) NOT NULL,
  `off_end_time_minute` varchar(2) NOT NULL,
  `off_day_full_worktime` int(5) NOT NULL,
  `247` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cargas horárias para controle de parada de relógio e SLAs';

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `worktime_profiles`
--
ALTER TABLE `worktime_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `is_default` (`is_default`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `worktime_profiles`
--
ALTER TABLE `worktime_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  
INSERT INTO `worktime_profiles` (`id`, `name`, `is_default`, `week_ini_time_hour`, `week_ini_time_minute`, `week_end_time_hour`, `week_end_time_minute`, `week_day_full_worktime`, `sat_ini_time_hour`, `sat_ini_time_minute`, `sat_end_time_hour`, `sat_end_time_minute`, `sat_day_full_worktime`, `sun_ini_time_hour`, `sun_ini_time_minute`, `sun_end_time_hour`, `sun_end_time_minute`, `sun_day_full_worktime`, `off_ini_time_hour`, `off_ini_time_minute`, `off_end_time_hour`, `off_end_time_minute`, `off_day_full_worktime`, `247`) VALUES ('1', 'DEFAULT', '1', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '1');

  
  
ALTER TABLE `sistemas` ADD `sis_wt_profile` INT(2) NOT NULL DEFAULT '1' COMMENT 'id do perfil de jornada de trabalho' AFTER `sis_screen`, ADD INDEX (`sis_wt_profile`); 
  

ALTER TABLE `config` ADD `conf_wt_areas` ENUM('1','2') NOT NULL DEFAULT '2' COMMENT '1: área origem, 2: área destino' AFTER `conf_qtd_max_anexos`, ADD INDEX (`conf_wt_areas`); 
  
  
ALTER TABLE `status` ADD `stat_time_freeze` TINYINT(1) NOT NULL DEFAULT '0' AFTER `stat_painel`, ADD INDEX (`stat_time_freeze`); 

UPDATE `status` SET `stat_time_freeze` = 1 WHERE stat_id IN (4,12,16);

CREATE TABLE `tickets_stages` ( `id` BIGINT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `date_start` DATETIME NOT NULL , `date_stop` DATETIME NOT NULL , `status_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`status_id`)) ENGINE = InnoDB COMMENT = 'Intervalos de tempo para cada status do chamado'; 
  
ALTER TABLE `tickets_stages` CHANGE `date_stop` `date_stop` DATETIME NULL DEFAULT NULL; 

ALTER TABLE `ocorrencias` ADD `oco_scheduled_to` DATETIME NULL DEFAULT NULL AFTER `oco_scheduled`; 
  
  
  
CREATE TABLE `ocorrencias_log` ( `log_id` INT(11) NOT NULL AUTO_INCREMENT , `log_numero` INT(11) NOT NULL , `log_quem` INT(5) NOT NULL , `log_data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `log_prioridade` INT(2) NULL DEFAULT NULL , `log_area` INT(4) NULL DEFAULT NULL , `log_problema` INT(4) NULL DEFAULT NULL , `log_unidade` INT(4) NULL DEFAULT NULL , `log_etiqueta` INT(11) NULL DEFAULT NULL , `log_contato` VARCHAR(255) NULL DEFAULT NULL , `log_telefone` VARCHAR(255) NULL DEFAULT NULL , `log_departamento` INT(4) NULL DEFAULT NULL , `log_responsavel` INT(5) NULL DEFAULT NULL , `log_data_agendamento` DATETIME NULL DEFAULT NULL , `log_status` INT(4) NULL DEFAULT NULL , `log_tipo_edicao` INT(2) NULL DEFAULT NULL , PRIMARY KEY (`log_id`), INDEX (`log_numero`)) ENGINE = InnoDB COMMENT = 'Log de alteracoes nas informacoes dos chamados';

ALTER TABLE `ocorrencias_log` ADD `log_descricao` TEXT NULL DEFAULT NULL AFTER `log_data`;   
  
  
  
  
  
ALTER TABLE `utmp_usuarios` CHANGE `utmp_nome` `utmp_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 
ALTER TABLE `utmp_usuarios` CHANGE `utmp_email` `utmp_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `utmp_passwd` `utmp_passwd` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `utmp_rand` `utmp_rand` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `utmp_usuarios` ADD `utmp_phone` VARCHAR(255) NULL AFTER `utmp_email`; 
ALTER TABLE `utmp_usuarios` ADD `utmp_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `utmp_rand`; 


ALTER TABLE `usuarios` ADD `last_logon` DATETIME NULL AFTER `user_admin`; 
  
  
  
ALTER TABLE `global_tickets` CHANGE `gt_id` `gt_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `imagens` CHANGE `img_tipo` `img_tipo` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 




ALTER TABLE `prob_tipo_1` CHANGE `probt1_desc` `probt1_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `prob_tipo_2` CHANGE `probt2_desc` `probt2_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `prob_tipo_3` CHANGE `probt3_desc` `probt3_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 





ALTER TABLE `ocorrencias` CHANGE `equipamento` `equipamento` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `ocorrencias_log` CHANGE `log_etiqueta` `log_etiqueta` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `imagens` CHANGE `img_inv` `img_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `equipamentos` CHANGE `comp_inv` `comp_inv` VARCHAR(255) NOT NULL; 



ALTER TABLE `estoque` CHANGE `estoq_tag_inv` `estoq_tag_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `historico` CHANGE `hist_inv` `hist_inv` VARCHAR(255) NOT NULL DEFAULT '0'; 
ALTER TABLE `hist_pieces` CHANGE `hp_comp_inv` `hp_comp_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `hw_alter` CHANGE `hwa_inv` `hwa_inv` VARCHAR(255) NOT NULL; 
ALTER TABLE `hw_sw` CHANGE `hws_hw_cod` `hws_hw_cod` VARCHAR(255) NOT NULL DEFAULT '0'; 
ALTER TABLE `moldes` CHANGE `mold_inv` `mold_inv` VARCHAR(255) NULL DEFAULT NULL; 






INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'agendamento-para-area', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Chamado Agendado', 'Caro operador\r\n\r\nO chamado número %numero% foi editado e marcado como agendado para a seguinte data:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO dia e horário marcados indicam quando o chamado entrará novamente na fila de atendimento.\r\n\r\nAtte. Equipe de Suporte', 'Caro operador\r\n\r\nO chamado número %numero% foi editado e marcado como agendado para a seguinte data:\r\nDia: %data_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO dia e horário marcados indicam quando o chamado entrará novamente na fila de atendimento.\r\n\r\nAtte. Equipe de Suporte'); 

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'agendamento-para-usuario', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Chamado Agendado', 'Caro %usuario%,\r\n\r\nSeu chamado foi marcado como agendado para a seguinte data e horário:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO agendamento do chamado indica que ele entrará novamente na fila de atendimento a partir da data informada.\r\n\r\nAtte.\r\nEquipe de Suporte.', 'Caro %usuario%,\r\n\r\nSeu chamado foi marcado como agendado para a seguinte data e horário:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO agendamento do chamado indica que ele entrará novamente na fila de atendimento a partir da data informada.\r\n\r\nAtte.\r\nEquipe de Suporte.'); 



CREATE TABLE `environment_vars` ( `id` INT NOT NULL AUTO_INCREMENT , `vars` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Variáveis de ambiente para e-mails de notificações'; 

INSERT INTO `environment_vars` (`id`, `vars`) VALUES (NULL, '<p><strong>N&uacute;mero do chamado:</strong> %numero%<br />\r\n<strong>Contato:</strong> %usuario%<br />\r\n<strong>Contato: </strong>%contato%<br />\r\n<strong>E-mail do Contato: </strong>%contato_email%<br />\r\n<strong>Descri&ccedil;&atilde;o do chamado:</strong> %descricao%<br />\r\n<strong>Departamento do chamado:</strong> %departamento%<br />\r\n<strong>Telefone:</strong> %telefone%<br />\r\n<strong>Site para acesso ao OcoMon:</strong> %site%<br />\r\n<strong>&Aacute;rea de atendimento:</strong> %area%<br />\r\n<strong>Operador do chamado:</strong> %operador%<br />\r\n<strong>Operador do chamado:</strong> %editor%<br />\r\n<strong>Quem abriu o chamado:</strong> %aberto_por%<br />\r\n<strong>Tipo de problema:</strong> %problema%<br />\r\n<strong>Vers&atilde;o do OcoMon:</strong> %versao%<br />\r\n<strong>Url global para acesso ao chamado:</strong> %url%<br />\r\n<strong>Url global para acesso ao chamado:</strong> %linkglobal%<br />\r\n<strong>Unidade: </strong>%unidade%<br />\r\n<strong>Etiqueta:</strong> %etiqueta%<br />\r\n<strong>Unidade e Etiqueta:</strong> %patrimonio%<br />\r\n<strong>Data de abertura do chamado:</strong> %data_abertura%<br />\r\n<strong>Status do chamado:</strong> %status%<br />\r\n<strong>Data de agendamento do chamado:</strong> %data_agendamento%<br />\r\n<strong>Data de encerramento do chamado:</strong> %data_fechamento%<br />\r\n<strong>Apenas o dia do agendamento:</strong> %dia_agendamento%<br />\r\n<strong>Apenas a hora do agendamento:</strong> %hora_agendamento%<br />\r\n<strong>Descri&ccedil;&atilde;o t&eacute;cnica (para chamados encerrados):</strong> %descricao_tecnica%<br />\r\n<strong>Solu&ccedil;&atilde;o t&eacute;cnica (para chamados encerrados):</strong> %solucao%<br />\r\n<strong>&Uacute;ltimo assentamento do chamado:</strong> %assentamento%</p>');



ALTER TABLE `avisos` ADD `expire_date` DATETIME NULL DEFAULT NULL AFTER `origembkp`, ADD `is_active` TINYINT NULL DEFAULT NULL AFTER `expire_date`, ADD INDEX (`expire_date`), ADD INDEX (`is_active`); 

ALTER TABLE `avisos` ADD `title` VARCHAR(30) NULL DEFAULT NULL AFTER `aviso_id`; 
ALTER TABLE `avisos` CHANGE `area` `area` VARCHAR(255) NULL DEFAULT NULL; 



CREATE TABLE `user_notices` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `notice_id` INT NOT NULL , `last_shown` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`notice_id`), INDEX (`last_shown`)) ENGINE = InnoDB COMMENT = 'Avisos do Mural já exibidos para o usuário'; 


ALTER TABLE `config` ADD `conf_sla_tolerance` INT(2) NOT NULL DEFAULT '20' COMMENT 'Percentual de Tolerância de SLA - entre o verde e o vermelho' AFTER `conf_wt_areas`; 



ALTER TABLE `ocorrencias` ADD `contato_email` VARCHAR(255) NULL DEFAULT NULL AFTER `contato`, ADD INDEX (`contato_email`); 

ALTER TABLE `configusercall` ADD `conf_scr_contact_email` INT(1) NOT NULL DEFAULT '0' AFTER `conf_scr_prior`; 

INSERT INTO `configusercall` (`conf_cod`, `conf_name`, `conf_user_opencall`, `conf_custom_areas`, `conf_ownarea`, `conf_ownarea_2`, `conf_opentoarea`, `conf_scr_area`, `conf_scr_prob`, `conf_scr_desc`, `conf_scr_unit`, `conf_scr_tag`, `conf_scr_chktag`, `conf_scr_chkhist`, `conf_scr_contact`, `conf_scr_fone`, `conf_scr_local`, `conf_scr_btloadlocal`, `conf_scr_searchbylocal`, `conf_scr_operator`, `conf_scr_date`, `conf_scr_status`, `conf_scr_replicate`, `conf_scr_mail`, `conf_scr_msg`, `conf_scr_upload`, `conf_scr_schedule`, `conf_scr_foward`, `conf_scr_prior`, `conf_scr_contact_email`) VALUES
(1, 'Default', 0, '2', 2, '2', 1, 0, 0, 1, 1, 1, 0, 0, 1, 1, 1, 1, 0, 1, 1, 1, 0, 0, 'Seu chamado foi aberto com sucesso no sistema de ocorrências! O número é %numero%. Aguarde o atendimento pela equipe de suporte.', 0, 0, 0, 1, 0);


INSERT INTO `configusercall` (`conf_cod`, `conf_name`, `conf_user_opencall`, `conf_custom_areas`, `conf_ownarea`, `conf_ownarea_2`, `conf_opentoarea`, `conf_scr_area`, `conf_scr_prob`, `conf_scr_desc`, `conf_scr_unit`, `conf_scr_tag`, `conf_scr_chktag`, `conf_scr_chkhist`, `conf_scr_contact`, `conf_scr_fone`, `conf_scr_local`, `conf_scr_btloadlocal`, `conf_scr_searchbylocal`, `conf_scr_operator`, `conf_scr_date`, `conf_scr_status`, `conf_scr_replicate`, `conf_scr_mail`, `conf_scr_msg`, `conf_scr_upload`, `conf_scr_schedule`, `conf_scr_foward`, `conf_scr_prior`, `conf_scr_contact_email`) VALUES
(2, 'Todos os Campos', 1, '2', 2, '2', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Seu chamado foi aberto com sucesso no sistema de ocorrências! O número é %numero%. Aguarde o atendimento pela equipe de suporte.', 1, 1, 1, 1, 1);


ALTER TABLE `ocorrencias_log` ADD `log_contato_email` VARCHAR(255) NULL DEFAULT NULL AFTER `log_contato`; 
  
  
INSERT INTO `avisos` (`aviso_id`, `title`, `avisos`, `data`, `origem`, `status`, `area`, `origembkp`, `expire_date`, `is_active`) VALUES (NULL, 'Bem vindo!', '<p>Seja muito bem vindo ao OcoMon 6.0, o melhor OcoMon de todos os tempos!</p><hr />
<p>N&atilde;o esque&ccedil;a de ajustar as configura&ccedil;&otilde;es do sistema de acordo com suas necessidades.</p><hr />
<p>Acesse o <a href="https://www.youtube.com/c/OcoMonOficial" target="_blank">canal no Youtube</a> para dicas e informa&ccedil;&otilde;es diversas a respeito do sistema.</p>', CURRENT_TIME(), '1', 'success', '1', NULL, CURRENT_TIME(), '1'); 
  
  

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



ALTER TABLE `equipxpieces` CHANGE `eqp_equip_inv` `eqp_equip_inv` VARCHAR(255) NOT NULL;   
  
  
  
  
ALTER TABLE `hw_alter` CHANGE `hwa_item` `hwa_item` INT(4) NULL; 

ALTER TABLE `mailconfig` ADD `mail_send` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mail_from_name`; 

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


ALTER TABLE `configusercall` ADD `conf_scr_channel` TINYINT(1) NOT NULL DEFAULT '1' AFTER `conf_scr_contact_email`; 

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



CREATE TABLE `input_tags` ( `tag_name` VARCHAR(30) NOT NULL , UNIQUE (`tag_name`)) ENGINE = InnoDB COMMENT = 'Tags de referência'; 
ALTER TABLE `input_tags` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`); 

ALTER TABLE `ocorrencias` ADD `oco_tag` TEXT NULL DEFAULT NULL AFTER `oco_channel`, ADD FULLTEXT (`oco_tag`); 

INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_TAG', NULL) ;

ALTER TABLE `mailconfig` ADD `mail_queue` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mail_send`; 

ALTER TABLE `localizacao` CHANGE `local` `local` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 


ALTER TABLE `usuarios` ADD `forget` VARCHAR(255) NULL DEFAULT NULL AFTER `last_logon`; 

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'forget-password', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Esqueceu sua senha?', '<p>Esqueceu sua senha <strong>%usuario%</strong>?</p>
<p>Voc&ecirc; est&aacute; recebendo esse e-mail porque solicitou a recupera&ccedil;&atilde;o de senha de acesso ao sistema de suporte.</p>
<p>Caso n&atilde;o tenha sido voc&ecirc; o autor da solicita&ccedil;&atilde;o, apenas ignore essa mensagem. <strong>Seus dados est&atilde;o protegidos.</strong></p>
<p>Clique abaixo para definir uma nova senha de acesso:</p>
<p>%forget_link%</p>
<p><strong>Atte. Equipe de Suporte</strong></p>
', '');

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




ALTER TABLE `config` ADD `conf_updated_issues` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Flag para saber se o update da tabela de tipos de problemas foi realizado.' AFTER `conf_isolate_areas`, ADD INDEX (`conf_updated_issues`); 

ALTER TABLE `config` ADD `conf_allow_op_treat_own_ticket` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Define se o operador pode tratar chamados abertos por ele mesmo' AFTER `conf_isolate_areas`; 

ALTER TABLE `config` ADD `conf_reopen_deadline` INT(2) NOT NULL DEFAULT '0' COMMENT 'Limite de tempo em dias para a reabertura de chamados' AFTER `conf_allow_reopen`; 

CREATE TABLE `areas_x_issues` ( `id` INT NOT NULL AUTO_INCREMENT , `area_id` INT NULL , `prob_id` INT NOT NULL , `old_prob_id` INT NULL, PRIMARY KEY (`id`), INDEX (`area_id`), INDEX (`prob_id`), INDEX (`old_prob_id`)) ENGINE = InnoDB COMMENT = 'NxN Areas x Problemas'; 

INSERT INTO areas_x_issues (area_id, prob_id, old_prob_id) VALUES (1,1,null);


CREATE TABLE `screen_field_required` ( `id` INT(6) NOT NULL AUTO_INCREMENT , `profile_id` INT(6) NOT NULL , `field_name` VARCHAR(64) NOT NULL COMMENT 'Nome do campo na tabela configusercall' , `field_required` TINYINT NOT NULL DEFAULT '1' , PRIMARY KEY (`id`), INDEX (`profile_id`), INDEX (`field_name`)) ENGINE = InnoDB COMMENT = 'Obrigatoriedade de preenchim. dos campos nos perfis de tela';


ALTER TABLE `avisos` ADD `is_recurrent` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Indica se o aviso será exibindo novamente no outro dia' AFTER `is_active`, ADD INDEX (`is_recurrent`); 
  
  

ALTER TABLE `custom_fields` ADD `field_mask` TEXT NULL DEFAULT NULL COMMENT 'Máscara para campos tipo texto' AFTER `field_attributes`; 

ALTER TABLE `custom_fields` ADD `field_mask_regex` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Se a máscara é uma expressão regular' AFTER `field_mask`; 
  




-- Versao 5


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



-- Versão 6


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
INSERT INTO `config_keys` (key_name, key_value) VALUES ('DB_OCOMON', '6-20240808') ON DUPLICATE KEY UPDATE id = id;
UPDATE `config_keys` SET key_value = '6-20240808' WHERE key_name = 'DB_OCOMON';


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
