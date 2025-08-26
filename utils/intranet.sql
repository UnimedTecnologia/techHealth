-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 11/12/2024 às 13:46
-- Versão do servidor: 8.0.40-0ubuntu0.20.04.1
-- Versão do PHP: 7.4.3-4ubuntu2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `intranet`
--
CREATE DATABASE IF NOT EXISTS `intranet` DEFAULT CHARACTER SET utf32 COLLATE utf32_general_ci;
USE `intranet`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--

CREATE TABLE `administrador` (
  `ID` int NOT NULL,
  `USUARIO` varchar(200) NOT NULL,
  `PERFIL` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `administrador`
--

INSERT INTO `administrador` (`ID`, `USUARIO`, `PERFIL`) VALUES
(1, 'sulivan.santos', 100),
(2, 'tecnologia.01', 100),
(3, 'pedro.rodrigues', 100),
(4, 'daniel.souza', 100),
(5, 'leonardo.fernandes', 100),
(6, 'gabriel.freitas', 100),
(7, 'vanessa.fioraso', 100),
(8, 'giovana.souza', 200),
(9, 'karilene.canale', 200),
(10, 'gizele.avila', 200);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamado`
--

CREATE TABLE `chamado` (
  `ID` int NOT NULL,
  `USUARIO` varchar(200) NOT NULL,
  `USUARIO_EMAIL` varchar(200) DEFAULT NULL,
  `TITULO` varchar(200) NOT NULL,
  `DESCRICAO` mediumtext NOT NULL,
  `DATA_CHAMADO` datetime NOT NULL,
  `IP` varchar(200) NOT NULL,
  `STATUS` int NOT NULL,
  `ANALISTA` varchar(200) DEFAULT NULL,
  `ANALISTA_EMAIL` varchar(200) DEFAULT NULL,
  `DATA_FECHAMENTO` datetime DEFAULT NULL,
  `CATEGORIA` varchar(1) NOT NULL,
  `ANEXO` varchar(200) DEFAULT NULL,
  `SOLUCAO_USUARIO` mediumtext,
  `SOLUCAO_TECNICA` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `chamado`
--

INSERT INTO `chamado` (`ID`, `USUARIO`, `USUARIO_EMAIL`, `TITULO`, `DESCRICAO`, `DATA_CHAMADO`, `IP`, `STATUS`, `ANALISTA`, `ANALISTA_EMAIL`, `DATA_FECHAMENTO`, `CATEGORIA`, `ANEXO`, `SOLUCAO_USUARIO`, `SOLUCAO_TECNICA`) VALUES
(1, 'gislaine.morais', 'gislaine.morais@unimeditatiba.com.br', 'Alteração de regra na proposta', 'Por favor para o contrato: 25/722 alterar a proposta para a regra: 5826.', '2021-11-08 12:36:26', '192.168.3.92', 0, NULL, NULL, NULL, 'S', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamado_nota`
--

CREATE TABLE `chamado_nota` (
  `ID_CHAMADO` int NOT NULL,
  `USUARIO` varchar(200) NOT NULL,
  `DATA` datetime NOT NULL,
  `NOTA` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contatos`
--

CREATE TABLE `contatos` (
  `ID` int NOT NULL,
  `NOME` varchar(200) DEFAULT NULL,
  `EMAIL` varchar(200) DEFAULT NULL,
  `TELEFONE` varchar(200) DEFAULT NULL,
  `DESCRICAO` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `contatos`
--

INSERT INTO `contatos` (`ID`, `NOME`, `EMAIL`, `TELEFONE`, `DESCRICAO`) VALUES
(1, 'DANTE PABX', 'danteluizsc23@hotmail.com', '11998687531', 'TECNICO PABX HOSPITAL DIA'),
(2, 'CHRISTIANO ', 'christiano.decarli', '', 'SKYPE'),
(3, 'CLAUDEMIR', 'claudemir.santos@totvs.com.br', '5191517441', 'CONSULTOR TOTVS'),
(4, 'JUNIOR TOTVS', '', '11987705678', 'CONSULTOR (JUNINHO)'),
(5, 'DIALOG', 'ti-canal@dialogcallcenter.com.br', '11985696213', 'SUPORTE PARCEIRO ZENVIA'),
(6, 'RODRIGO BUENO ', 'rodrigo.bueno@thealth.com.br', '11993200842', 'CONSULTOR THEALTH'),
(7, 'TI UNIMED BRASIL', 'experienciasdigitais@unimed.coop.br', ' (11) 3265-4525', 'CHAMADOS UNIMED BRASIL'),
(8, 'Suporte NetTurbo', 'suportecorporativo@netturbo.com.br', '-', 'Suporte Internet'),
(9, 'zenvia', '', '', ''),
(10, 'app', '', '', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `credencial`
--

CREATE TABLE `credencial` (
  `ID` int NOT NULL,
  `TIPO` varchar(200) DEFAULT NULL,
  `IP_SITE` text CHARACTER SET utf32 COLLATE utf32_general_ci,
  `USUARIO` varchar(200) DEFAULT NULL,
  `SENHA` varchar(200) DEFAULT NULL,
  `DESCRICAO` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `credencial`
--

INSERT INTO `credencial` (`ID`, `TIPO`, `IP_SITE`, `USUARIO`, `SENHA`, `DESCRICAO`) VALUES
(1, 'SERVIDOR', '192.168.0.6', 'UNI540/administrator', '#21Habacuque540@', 'Servidor VMWare'),
(2, 'SERVIDOR', '192.168.11.2', 'administrator', '#21Habacuque540@', 'Servidor Save'),
(3, 'SITE', 'https://connect.zenvia360.com/manager/', 'unimeditatiba.web / daniel.souza@unimeditatiba.com.br', 'Unimed540@ ', 'Zenvia SMS'),
(4, 'SONICWALL SEDE', 'https://192.168.1.14/main.html', 'admin', '#21Habacuque540@', 'Firewall Sonicwall'),
(5, 'SERVIDOR', '192.168.0.27', 'root', '2666328ddconn', 'Jboss Prod e HML'),
(6, 'SITE', 'http://192.168.1.18/unimeditatiba/opme/relatorio.php', '-', '-', 'Relatório OPME'),
(7, 'SITE', 'http://barracuda.penso.com.br/cgi-mod/index.cgi', 'admin@unimeditatiba.com.br', 'x1L39*ALl!$@', 'Barracuda Penso'),
(8, 'SITE', 'https://www.office.com/?auth=2', 'tecnologia@unimeditatiba.onmicrosoft.com', 'Unimed540@', 'Office 365'),
(9, 'SITE', 'http://192.168.1.18/unimeditatiba/comercial/proposta/index.html', 'diego', 'diegounimed9', 'Proposta - Setor Comercial'),
(10, 'SITE', 'https://unimedfesp.topdesk.net/tas/public/login/form', 'unimed.itatiba.07', 'Unimed540@', 'ServiceDesk - FESP'),
(11, 'SITE', 'http://192.168.1.9:9090/login.jsp', 'admin', '#21Habacuque540@', 'Spark'),
(12, 'SISTEMA', 'http://192.168.0.28:8081/autorizador/', 'teste / 500', 'teste540@', 'Autorizador Web - Produção'),
(13, 'SISTEMA', 'http://srvjboss:8180/menu-html/', 'super', '#Uni540@@@', 'TOTVS Produção'),
(14, 'SERVIDOR', '192.168.0.39', 'UNI540Administrador', '#21Habacuque540@', 'Save HML - Vmware'),
(15, 'BANCO DE DADOS', '192.168.11.2,1433', 'sa', '7ItatibA729SisteX36', 'BD Save - Produção'),
(16, 'BANCO DE DADOS', '192.168.0.39,1433', 'sa', '7ItatibA729SisteX36', 'BD Save - HML'),
(17, 'SISTEMA', '192.168.1.8', 'root', '#21Asgard540@', 'VMSphere Client'),
(18, 'REPOSITORIO', '192.168.1.147', 'suporte', 'Uni540med', 'REPOSITORIO TI'),
(19, 'ACESSO', 'WIFI UNIMED', 'sem usuário', 'Uni2022540med', 'Unimed Itatiba'),
(20, 'ACESSO', 'WIFI MOBILE', 'sem usuário', 'Uni2022540@', 'Unimed Itatiba Mobile'),
(21, 'SITE', 'https://email.unimedosbandeirantes.com.br:9071/zimbraAdmin/', 'admin@unimedosbandeirantes.com.br', '#21Demiurgos05@', 'Portal Emails Zimbra'),
(22, 'SITE', 'https://192.168.0.20:8443/', 'tecnologia', 'Unimed21$', 'UNIFI CONTROLLER'),
(23, 'SITE', 'https://appunimed.com/umb-web/pages/usuario-situacao/list.jsf', 'leonardo.ridolfi@unimeditatiba.com.br', 'Unimed540@', 'Gestão App Unimed'),
(24, 'SITE', 'https://epacs.com.br/router/login/', 'tecnologia@unimeditatiba.com.br', 'Unimed21$', 'EPACS'),
(25, 'SISTEMA', 'https://serverrds.uni540.local/rdweb', 'usuario dominio', 'senha dominio', 'Servidor RDS'),
(26, 'SITE', 'https://app.zenvia.com/welcome', 'leonardo.ridolfi@unimeditatiba.com.br', 'b96ce3a8', 'Zenvia Portal'),
(27, 'SITE', 'https://giu.unimed.coop.br/home', '30137670818', 'GIUBan540@##', 'GIU - WSD Intercâmbio'),
(28, 'SITE', 'https://app.zenvia.com/welcome', 'leonardo.ridolfi@unimeditatiba.com.br', 'b96ce3a8', 'Zenvia Portal'),
(29, 'SITE', 'http://192.168.11.247:9004', 'admin', '888888', 'Cameras PA'),
(30, 'SITE', 'https://app.zenvia.com/welcome', 'leonardo.ridolfi@unimeditatiba.com.br', 'b96ce3a8', 'Zenvia - Whatsapp'),
(31, 'WEBSTART HOMOLOGAÇÃO', 'https://giuhml.unimed.coop.br/login', 'cmbtestebrasil', 'Portal-001', 'Webstart Homologação'),
(32, 'FTP', 'ftp://ftp.unimeditatiba.com.br', 'paitatibavirtual', 'paitatibavirutal', 'FTP - PA Virtual'),
(33, 'SITE', 'https://vcenter.uni540.local', 'administrator@vmware.local', '@666#@*DDconn', 'VMware VSphere - Web'),
(34, 'SITE', 'http://186.209.52.83/unimeditatiba/cooperado/', '047335', 'unimed', 'Portal Cooperado'),
(35, 'SITE', 'http://192.168.1.18/unimeditatiba/contas/demonstrativos/', 'medicos', 'medicos', 'Portal Demonstrativos'),
(36, 'SITE', 'http://clientepj.unimed.coop.br/login_adm', 'tecnologia@unimeditatiba.com.br', 'unimed2021', 'Portal PJ - Admin'),
(37, 'SITE', 'https://estacao.zendesk.com/hc/pt-br', 'vanessa.fioraso@unimeditatiba.com.br', 'GIUnita540@', 'Portal PJ'),
(38, 'PROGRAMA', 'PTA - Programa Transmissor de Arquivos', '415014', 'Uni@540@', 'PTA - Monitoramento TISS'),
(39, 'CAMERAS', 'http://192.168.11.247:9004', 'admin', '888888', 'CAMERAS PA'),
(40, 'CAMERAS', 'http://192.168.1.58:7001', 'admin', '540999', 'CAMERAS SEDE'),
(41, 'CAMERAS', 'http://192.168.1.132:9001', 'admin', 'unimed540', 'CAMERAS HD'),
(42, 'CAMERAS', 'http://192.168.10.252', 'admin', 'unimed540999', 'CAMERAS ATIBAIA'),
(43, 'CAMERAS', 'http://192.168.12.252:9001', 'admin', 'unimed540', 'CAMERAS CTO'),
(44, 'CAMERAS', 'http://192.168.14.243', 'admin', 'Unimed540@', 'CAMERAS MORUNGABA'),
(45, ' ', ' ', ' ', ' ', ' '),
(46, 'SITE', 'http://177.107.72.12:8080/sac/login/?sys=SAC', '06091170000105', '06091170000105', 'CrossConection / Br Link / Tech Net'),
(47, 'SITE', 'http://192.168.0.28:8081/autorizador/', '500 / medical', 'medical01', 'Admin Autorizador'),
(48, 'Site', 'https://app.amplimed.com.br/login', 'amplimed.unimed13@amplimed.com.br', 'Unimed540@', 'Acesso ao Amplimed'),
(49, 'SITE', 'http://projetobi.unimedfesp.coop.br/qlikview/login.htm', 'fespusr_qlikview_itatiba', 'kAqX8*Fy9M', 'admin - fesp'),
(50, 'SERVIDOR', '192.168.0.49', 'root', '#21Habacuque540@', 'Foundation - Produção'),
(51, 'SERVIDOR', '192.168.0.28', 'root', '2666328ddconn', 'Autorizador TOTVS - SU JBOSS'),
(52, 'SITE', 'https://zchat-admin.zenvia.com/newDashboard', 'leonardo.fernandes@unimedosbandeirantes.com.br', 'Unimed540@#', 'Zenvia Admin'),
(53, 'SITE', 'http://192.168.0.49:8080/htz/pages/welcome/welcome.jsf', 'medical', 'Unimed540@', 'Foundation - Produção'),
(54, 'SITE', 'http://192.168.0.21/intranet/atendimento_pa/', '-', '-', 'Relatório de atendimento PA - Auditoria'),
(55, 'SITE', 'http://192.168.1.18/unimeditatiba//rh/plantonistas/index.html', 'admin', 'unimed', 'Plantonistas PA'),
(56, 'SITE', 'https://unimed.coop.br/', 'daniel.vitellozzi', 'GIUBand540@', 'Portal da Brasil'),
(57, 'SITE', 'http://gnu.unimedcentralrs.com.br/webtickets/', '540_deivid.jesus', 'aFlKcfNz4b', 'PORTAL GNU'),
(58, 'SITE', 'https://login.bitdefender.com/', 'tecnologia@unimeditatiba.com.br', '#21Habacuque540@', 'Acesso painel BitDefender'),
(59, 'SITE', 'https://app.apoiocotacoes.com.br/', 'ti.unimeditatiba', 'Unimed540@', 'Apoio Cotações - Site de Compra Mat/Med'),
(60, 'PHP MY ADMIN', 'http://192.168.0.21/phpmyadmin/index.php', 'admin', '2666328ddconn', 'Administrador PHP '),
(61, 'SERVIDOR', '192.168.0.54', 'painelmv', 'P@inel540@', 'Painel MV'),
(62, 'SITE', 'http://192.168.0.54/Painel_PRD/', 'tecnologia', 'Unimed540@', 'Painel MV - Produção'),
(63, 'SITE', 'http://192.168.0.54/Painel_SML/', 'tecnologia', 'Unimed540@', 'Painel MV - Homologação'),
(64, 'AppUnimed', 'https://appunimed.com/umb-web/pages/singular/form.jsf', 'tecnologia@unimedosbandeirantes.com.br', 'Unimed540@', 'Aplicativo Unimed'),
(65, 'Servidor Matrix', '192.168.11.12', 'administrador', '#22Demiurgos05@', 'Servidor Matrix / Caché / Connect'),
(66, 'Connect - Matrix', '192.168.11.12', '114000255', 'Pastel540$', 'Acesso ao Connect da Matrix'),
(67, 'BANCO DE DADOS', '192.168.1.2', 'root', '#21Asgard540@', 'Oracle 19c'),
(68, ' SISTEMA', 'http://192.168.0.30:8180/autorizador/pages/checkin/checkin.jsf', 'teste / 500', 'teste540@', 'Autorizador Web - Homologacao'),
(69, 'VMWARE', '192.168.11.37', 'root', '@666#@*DDconn', 'VMWARE PA - Matrix - Save - Toten'),
(70, 'Auditoria medica', 'http://unimeditatiba.ddns.me:8180/hau/authorization-audit/#/', '1', '1', 'auditor medico'),
(71, 'Webstart', 'App', 'vanessa540', 'Unimed540@@', 'NovaCMB'),
(72, 'App epac', 'https://app.epacs.com.br/', 'raiox@unimedosbandeirantes.com.br', '540Unimed@', 'epacs web'),
(73, 'App epac', 'https://app.epacs.com.br/', 'tecnologia@unimeditatiba.com.br', 'Unimed21$', 'epacs web ADM'),
(74, 'Servidor', '192.168.0.15', 'root', '#22Demiurgos05@', 'Validador XML / IR'),
(75, 'Sistema EPACS', 'https:https://app.epacs.com.br/', 'crservice', 'Agsrvc2ls', 'Epeople Raio-X'),
(76, 'SITE', 'https://giu.unimed.coop.br/home', '27859570831', 'GIUmed540540@$$', 'GIU - WSD Intercâmbio');

-- --------------------------------------------------------

--
-- Estrutura para tabela `departamento`
--

CREATE TABLE `departamento` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) NOT NULL,
  `EMAIL` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `departamento`
--

INSERT INTO `departamento` (`ID`, `DEPARTAMENTO`, `EMAIL`) VALUES
(1, 'Tecnologia da Informação', 'gabriel.freitas@unimedosbandeirantes.com.br'),
(2, 'Autorizações', 'leticia.lima@unimedosbandeirantes.com.br'),
(3, 'Jurídico', 'alessandra.pavan@unimedosbandeirantes.com.br'),
(4, 'Contas Médicas', 'elaine.silva@unimedosbandeirantes.com.br'),
(5, 'Auditoria', 'auditoria@unimedosbandeirantes.com.br'),
(6, 'Cadastro', 'ana.botelho@unimedosbandeirantes.com.br'),
(7, 'Comercial', 'marcela.ferreira@unimedosbandeirantes.com.br'),
(8, 'Financeiro', 'renato.pessini@unimedosbandeirantes.com.br'),
(10, 'Recursos Humanos', 'karilene.canale@unimedosbandeirantes.com.br'),
(11, 'Intercâmbio', 'liliana.tobias@unimedosbandeirantes.com.br'),
(12, 'Programa de Controle Médico de Saúde Ocupacional', ''),
(13, 'Gerenciamento de Casos Crônicos ', ''),
(14, 'Ouvidoria ', 'alessandra.pavan@unimedosbandeirantes.com.br'),
(15, 'Faturamento', 'graziela.sanches@unimedosbandeirantes.com.br');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ferias`
--

CREATE TABLE `ferias` (
  `ID` int NOT NULL,
  `USUARIO` varchar(200) NOT NULL,
  `NOME` varchar(200) NOT NULL,
  `INICIO` date NOT NULL,
  `TERMINO` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ferias`
--

INSERT INTO `ferias` (`ID`, `USUARIO`, `NOME`, `INICIO`, `TERMINO`) VALUES
(659, 'ariane.silva                                  ', 'ARIANE APARECIDA DE SOUZA SILVA         ', '2024-11-19', '2024-12-18'),
(661, 'evelin.ferrari                                ', 'EVELIN FERRARI                          ', '2024-11-25', '2024-12-24'),
(671, 'vanessa.taffarello                            ', 'VANESSA BUZETTO ROCATELLI TAFFARELLO    ', '2024-11-25', '2024-12-24'),
(673, 'alexandra.machado                             ', 'ALEXANDRA MACHADO                       ', '2024-12-02', '2024-12-16'),
(674, 'anabeatriz.lopes                              ', 'ANA BEATRIZ PASCOAL LOPES               ', '2024-12-29', '2025-01-17'),
(675, 'ANA.MUNIZ                                     ', 'ANA CAROLINE LOPES MUNIZ                ', '2024-12-18', '2025-01-01'),
(676, 'ANACAROLINI.SILVA                             ', 'ANA CAROLINI ALVES DA SILVA             ', '2024-12-11', '2024-12-25'),
(677, 'argemiro.candido                              ', 'ARGEMIRO CANDIDO                        ', '2024-12-23', '2025-01-11'),
(678, 'ariane.rodrigues                              ', 'ARIANE SANTOS RODRIGUES                 ', '2024-12-02', '2024-12-31'),
(679, 'camila.goncalves                              ', 'CAMILA NUNES DE OLIVEIRA GONCALVES      ', '2024-12-02', '2024-12-31'),
(680, 'catia.dreher                                  ', 'CATIA DREHER PEREIRA DOS SANTOS         ', '2024-12-24', '2025-01-22'),
(681, 'danieli.cabrera                               ', 'DANIELI CABRERA                         ', '2024-12-16', '2025-01-04'),
(682, 'EDILAINE.MARTINS                              ', 'EDILAINE FERREIRA SOUZA MARTINS         ', '2024-12-02', '2024-12-31'),
(683, 'ELAINE.SILVA                                  ', 'ELAINE GUIMARAES DA SILVA               ', '2024-12-30', '2025-01-13'),
(684, 'ELIDIANA.AGUIAR                               ', 'ELIDIANA DE OLIVEIRA AGUIAR             ', '2024-12-19', '2025-01-02'),
(685, 'ELIEIDI.SOARES                                ', 'ELIEIDI FERNANDA INACIO SOARES          ', '2024-12-16', '2024-12-30'),
(686, 'fernanda.oliveira                             ', 'FERNANDA FRANCO DE MORAES               ', '2024-12-30', '2025-01-18'),
(687, 'gislaine.morais                               ', 'GISLAINE ANDRADE DE MORAIS              ', '2024-12-30', '2025-01-13'),
(688, 'horacio.junior                                ', 'HORACIO PEREIRA DOS PASSOS JUNIOR       ', '2024-12-18', '2025-01-06'),
(689, 'INGRID.LEITE                                  ', 'INGRID LEITE                            ', '2024-12-11', '2024-12-30'),
(691, 'lucia.falsarela                               ', 'LUCIA FALSARELA DE LIMA                 ', '2024-12-01', '2024-12-20'),
(692, 'ludmilla.vilela                               ', 'LUDMILLA MORAES VILELA ALEXANDRE        ', '2024-12-18', '2025-01-01'),
(693, 'marcia.pauleto                                ', 'MARCIA DANIELA PAULETO                  ', '2024-12-09', '2025-01-07'),
(694, 'neuzeli.benedetti                             ', 'NEUZELI GRACE DOS SANTOS BENEDETTI      ', '2024-12-23', '2025-01-11'),
(695, 'rita.lotsch                                   ', 'RITA JAYMES LOTSCH                      ', '2024-12-23', '2025-01-21'),
(696, 'sandra.silva                                  ', 'SANDRA HELENA DA SILVA                  ', '2024-12-02', '2024-12-31'),
(697, 'tiago.santos                                  ', 'TIAGO DE PAULA DOCHA DOS SANTOS         ', '2024-12-04', '2025-01-02'),
(698, 'vitoria.santos                                ', 'VITORIA FERREIRA DOS SANTOS             ', '2024-12-02', '2024-12-31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramais`
--

CREATE TABLE `ramais` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) DEFAULT NULL,
  `UNIDADE` varchar(200) DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `E-MAIL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramais`
--

INSERT INTO `ramais` (`ID`, `DEPARTAMENTO`, `UNIDADE`, `RAMAL`, `E-MAIL`) VALUES
(1, 'ldfkjg', 'dlfjgk', 'godj', 'fd'),
(2, 'teste3', 'teste3', '9405', 'teste3');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramaisatibaia`
--

CREATE TABLE `ramaisatibaia` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramaisatibaia`
--

INSERT INTO `ramaisatibaia` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(2, 'RECEPÇÃO', '200 / 201'),
(3, 'CONSULTORIO 2', '202'),
(4, 'CONSULTORIO 3', '203'),
(5, 'CONSULTORIO 4', '204'),
(6, 'CONSULTORIO 5', '205'),
(7, 'CONSULTORIO 6', '206'),
(8, 'CONSULTORIO 7', '207'),
(9, 'CONSULTORIO 8', '208'),
(10, 'CONSULTORIO 11', '211'),
(11, 'CONSULTORIO 12', '212'),
(12, 'SALA 1', '400'),
(13, 'COLETA', '501'),
(14, 'RECEPÇÃO LABORATORIO', '500'),
(15, 'ONCOLOGIA', '502');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramaisbp`
--

CREATE TABLE `ramaisbp` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramaisbp`
--

INSERT INTO `ramaisbp` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(1, 'RECEPCAO', '6901 / 6902 / 6903'),
(2, 'TRIAGEM', '6904'),
(3, 'ENFERMAGEM', '6905'),
(4, 'CONSULTORIO 1', '6906'),
(5, 'CONSULTORIO 2', '6907'),
(6, 'CONSULTORIO 3', '6908'),
(8, 'FARMÁCIA', '6909'),
(9, 'RAIO-X', '6910'),
(10, 'COMERCIAL', '6911'),
(11, 'CONFORTO MEDICO', '6912'),
(12, 'LABORATORIO', '6913');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramaisctoamb`
--

CREATE TABLE `ramaisctoamb` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf16 COLLATE utf16_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramaisctoamb`
--

INSERT INTO `ramaisctoamb` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(1, 'QUIMIOTERAPIA', '201'),
(2, 'CONSULTORIO ONCOLOGIA', '202'),
(3, 'ENFERMAGEM', '204'),
(4, 'FARMACIA', '205'),
(5, 'CONSULTORIO VIVERBEM 1', '206'),
(6, 'CONSULTORIO VIVERBEM 2', '207'),
(7, 'RECEPÇÃO VIVERBEM ', '208');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramaishd`
--

CREATE TABLE `ramaishd` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramaishd`
--

INSERT INTO `ramaishd` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(1, 'RECEPÇÃO', '200'),
(2, 'FARMÁCIA', '207'),
(3, 'ENFERMAGEM', '201');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramaislaboratorio`
--

CREATE TABLE `ramaislaboratorio` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramaislaboratorio`
--

INSERT INTO `ramaislaboratorio` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(1, 'RECEPÇÃO', '9800 / 9801'),
(2, 'COLETA', '9804'),
(3, 'TRIAGEM', '9806');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramaismorungaba`
--

CREATE TABLE `ramaismorungaba` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramaismorungaba`
--

INSERT INTO `ramaismorungaba` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(2, 'RECEPCAO  ', '100 / 101'),
(4, 'FARMÁCIA', '103'),
(5, 'ENFERMAGEM ', '102'),
(6, 'BALCAO ENFERMAGEM', '104'),
(7, 'RAIO-X', '105'),
(8, 'CONSULTÓRIO 1', '106'),
(9, 'CONSULTÓRIO 2', '107'),
(10, 'CONSULTÓRIO 3', '108'),
(11, 'CONSULTÓRIO 4', '109');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ramaispaitatiba`
--

CREATE TABLE `ramaispaitatiba` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `ramaispaitatiba`
--

INSERT INTO `ramaispaitatiba` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(1, 'RECEPEÇÃO', '2000 / 2001'),
(2, 'ENFERMAGEM', '2008'),
(3, 'CONSULTORIO 2', '2010'),
(4, 'CONSULTORIO 3', '2009'),
(5, 'CONSULTORIO 4 ', '2015'),
(6, 'CONSULTORIO 5', '2022'),
(7, 'CONSULTORIO 6', '2006'),
(8, 'TRIAGEM', '2016'),
(9, 'RAIO X ', '2014'),
(10, 'ADMINISTRATIVO', '2011'),
(12, 'COLETA', '2024');

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorios`
--

CREATE TABLE `relatorios` (
  `ID` int NOT NULL,
  `RELATORIO` varchar(200) NOT NULL,
  `DEPARTAMENTO` varchar(200) NOT NULL,
  `LINK` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `relatorios`
--

INSERT INTO `relatorios` (`ID`, `RELATORIO`, `DEPARTAMENTO`, `LINK`) VALUES
(4, 'Relatório OPME', 'OPME', 'www.google.comw');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sede`
--

CREATE TABLE `sede` (
  `ID` int NOT NULL,
  `DEPARTAMENTO` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL,
  `RAMAL` varchar(200) CHARACTER SET utf32 COLLATE utf32_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Despejando dados para a tabela `sede`
--

INSERT INTO `sede` (`ID`, `DEPARTAMENTO`, `RAMAL`) VALUES
(1, 'TECNOLOGIA', '9496 / 9495 / 9491'),
(2, 'CONTAS MÉDICAS', '9461 / 9462 / 9463 / 9464'),
(3, 'RECEPÇÃO', '9401 / 9403'),
(4, 'RELACIONAMENTO', '9450 / 9451'),
(5, 'REGULAÇÃO', '9471 / 9472 / 9473 / 9474 '),
(6, 'COMPRAS', '9457'),
(7, 'INTERCÂMBIO', '9415 / 9416 / 9417 / 9418 / 9419'),
(8, 'TERAPIA ESPECIAIS', '9404'),
(9, 'AUTORIZAÇÕES', '9406 / 9407 / 9408 / 9409 / 9410 / 9411 /9412 / 9414'),
(10, 'FINANCEIRO', '9430 / 9431'),
(11, 'COMERCIAL', '9442 / 9443 / 9444'),
(12, 'MARKETING', '9489'),
(13, 'GDC', '9420 / 9421'),
(14, 'JURÍDICO', '9480'),
(15, 'OUVIDORIA', '9482'),
(16, 'RECEPÇÃO DIRETORIA', '9405'),
(17, 'GESTÃO DE PESSOAS', '9425 / 9426 / 9427'),
(19, 'FATURAMENTO', '9436 / 9437'),
(20, 'CADASTRO', '9453 / 9454'),
(21, 'MARKETING', '9489'),
(22, 'DIRETORIA', '9488'),
(23, 'QUALIDADE', '9486'),
(24, 'REDE CREDENCIADA', '9484'),
(25, 'COMPRAS FARMÁCIA', '9458'),
(26, 'REGULAÇÃO GERÊNCIA', '9479');

-- --------------------------------------------------------

--
-- Estrutura para tabela `slider`
--

CREATE TABLE `slider` (
  `ID` int NOT NULL,
  `FRASE_1_1` varchar(200) DEFAULT NULL,
  `FRASE_1_2` varchar(200) DEFAULT NULL,
  `FRASE_2_1` varchar(200) DEFAULT NULL,
  `FRASE_2_2` varchar(200) DEFAULT NULL,
  `FRASE_3_1` varchar(200) DEFAULT NULL,
  `FRASE_3_2` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `chamado`
--
ALTER TABLE `chamado`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `contatos`
--
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `credencial`
--
ALTER TABLE `credencial`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `departamento`
--
ALTER TABLE `departamento`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ferias`
--
ALTER TABLE `ferias`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramais`
--
ALTER TABLE `ramais`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramaisatibaia`
--
ALTER TABLE `ramaisatibaia`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramaisbp`
--
ALTER TABLE `ramaisbp`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramaisctoamb`
--
ALTER TABLE `ramaisctoamb`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramaishd`
--
ALTER TABLE `ramaishd`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramaislaboratorio`
--
ALTER TABLE `ramaislaboratorio`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramaismorungaba`
--
ALTER TABLE `ramaismorungaba`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `ramaispaitatiba`
--
ALTER TABLE `ramaispaitatiba`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `relatorios`
--
ALTER TABLE `relatorios`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `sede`
--
ALTER TABLE `sede`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `administrador`
--
ALTER TABLE `administrador`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `chamado`
--
ALTER TABLE `chamado`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `contatos`
--
ALTER TABLE `contatos`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `credencial`
--
ALTER TABLE `credencial`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de tabela `departamento`
--
ALTER TABLE `departamento`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `ferias`
--
ALTER TABLE `ferias`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=700;

--
-- AUTO_INCREMENT de tabela `ramais`
--
ALTER TABLE `ramais`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `ramaisatibaia`
--
ALTER TABLE `ramaisatibaia`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de tabela `ramaisbp`
--
ALTER TABLE `ramaisbp`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `ramaisctoamb`
--
ALTER TABLE `ramaisctoamb`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `ramaishd`
--
ALTER TABLE `ramaishd`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `ramaislaboratorio`
--
ALTER TABLE `ramaislaboratorio`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `ramaismorungaba`
--
ALTER TABLE `ramaismorungaba`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `ramaispaitatiba`
--
ALTER TABLE `ramaispaitatiba`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `relatorios`
--
ALTER TABLE `relatorios`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `sede`
--
ALTER TABLE `sede`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `slider`
--
ALTER TABLE `slider`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;
--
-- Banco de dados: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int UNSIGNED NOT NULL,
  `dbase` varchar(255) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `user` varchar(255) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `query` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `col_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `col_type` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `col_length` text COLLATE utf8mb3_bin,
  `col_collation` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) COLLATE utf8mb3_bin DEFAULT '',
  `col_default` text COLLATE utf8mb3_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Central list of columns';

--
-- Despejando dados para a tabela `pma__central_columns`
--

INSERT INTO `pma__central_columns` (`db_name`, `col_name`, `col_type`, `col_length`, `col_collation`, `col_isNull`, `col_extra`, `col_default`) VALUES
('intranet', 'RAMAL', 'varchar', '200', 'utf32_general_ci', 1, ',', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int UNSIGNED NOT NULL,
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `column_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `transformation_options` varchar(255) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `input_transformation` varchar(255) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) COLLATE utf8mb3_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `settings_data` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `export_type` varchar(10) COLLATE utf8mb3_bin NOT NULL,
  `template_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `template_data` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `tables` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `db` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sqlquery` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `item_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `item_type` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `table_name` varchar(64) COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Hidden items of navigation tree';

--
-- Despejando dados para a tabela `pma__navigationhiding`
--

INSERT INTO `pma__navigationhiding` (`username`, `item_name`, `item_type`, `db_name`, `table_name`) VALUES
('admin', 'plugin', 'table', 'mysql', ''),
('admin', 'procs_priv', 'table', 'mysql', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `page_nr` int UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `tables` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Recently accessed tables';

--
-- Despejando dados para a tabela `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('admin', '[{\"db\":\"intranet\",\"table\":\"administrador\"},{\"db\":\"mysql\",\"table\":\"columns_priv\"},{\"db\":\"intranet\",\"table\":\"ferias\"},{\"db\":\"intranet\",\"table\":\"credencial\"},{\"db\":\"intranet\",\"table\":\"departamento\"},{\"db\":\"intranet\",\"table\":\"ramais\"},{\"db\":\"intranet\",\"table\":\"slider\"},{\"db\":\"intranet\",\"table\":\"sede\"},{\"db\":\"intranet\",\"table\":\"relatorios\"},{\"db\":\"intranet\",\"table\":\"ramaislaboratorio\"}]');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `master_table` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `master_field` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `foreign_db` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `foreign_table` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `foreign_field` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `search_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `search_data` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `pdf_page_number` int NOT NULL DEFAULT '0',
  `x` float UNSIGNED NOT NULL DEFAULT '0',
  `y` float UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `display_field` varchar(64) COLLATE utf8mb3_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `table_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `prefs` text COLLATE utf8mb3_bin NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Tables'' UI preferences';

--
-- Despejando dados para a tabela `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`) VALUES
('admin', 'information_schema', 'COLUMNS', '{\"sorted_col\":\"`COLUMNS`.`TABLE_NAME` ASC\"}'),
('admin', 'intranet', 'ramais', '[]');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `table_name` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `version` int UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text COLLATE utf8mb3_bin NOT NULL,
  `schema_sql` text COLLATE utf8mb3_bin,
  `data_sql` longtext COLLATE utf8mb3_bin,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') COLLATE utf8mb3_bin DEFAULT NULL,
  `tracking_active` int UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `config_data` text COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Despejando dados para a tabela `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `config_data`) VALUES
('admin', '{\"lang\":\"pt_BR\",\"Console\\/Mode\":\"show\",\"Console\\/Height\":51,\"NavigationWidth\":237}');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `tab` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `allowed` enum('Y','N') COLLATE utf8mb3_bin NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) COLLATE utf8mb3_bin NOT NULL,
  `usergroup` varchar(64) COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Users and their assignments to user groups';

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Índices de tabela `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Índices de tabela `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Índices de tabela `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Índices de tabela `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Índices de tabela `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Índices de tabela `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Índices de tabela `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Índices de tabela `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Índices de tabela `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Índices de tabela `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Índices de tabela `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Índices de tabela `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Índices de tabela `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
