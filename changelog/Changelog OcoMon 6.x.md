
# OcoMon 6.x
Lançamento previsto para o final de 2024
Versão anterior 5.0

É possível ter uma ideia sobre algumas das novidades assistindo aos seguintes vídeos:

- Pré-filtros e abertura de chamados em nome de outros usuários: [acesse aqui](https://www.youtube.com/watch?v=6LqXcmNl2mg)
- Alocação de recursos e visão de projetos: [acesse aqui](https://youtu.be/J-vZY8dLWO8)
- Vídeo sobre novidades diversas: [acesse aqui](https://www.youtube.com/watch?v=yyHKpQiW0fQ)


## Requisitos da versão

1. Servidor **Apache**, **MySQL** a partir da versão 5.6 (Ou **MariaDB** a partir da versão 10.2), **PHP** a partir da **versão 8.1** com os seguintes módulos principais:
    a) PDO, pdo_mysql, curl, gd, openssl, ldap, mbstring, iconv e fileinfo.
2. Por ser uma aplicação web, qualquer sistema operacional é compatível. No entanto, a recomendação de utilização é para servidores Linux (independentemente da distribuição).


## Principais macro-novidades

- [Publicação 1](https://ocomon.com.br/site/versao-6-novas-funcionalidades/)
- [Publicaçao 2](https://ocomon.com.br/site/versao-6-novas-funcionalidades-parte-2/)

## Geral

### Interface

Mudança no estilo dos botões dos menus laterais, gerando maior contraste e conforto visual.

Agora as opções visitadas nos menus ficam marcadas, facilitando a identificação da opção corrente. Essa marcação é persistente mesmo alternando entre os menus superiores.

Se a opção marcada estiver dentro de um menu de pasta, o menu permanecerá aberto mesmo após acessar outro menu superior e então retornar.


### Central de notificações

Agora há uma central de notificações para mensagens diversas do sistema. O ícone para as notificações está posicionado na barra principal do sistema ao lado do ícone de acesso às informações do perfil do usuário logado.

São notificados diversos eventos no sistema, como:
- Interações com os chamados (notificação para solicitante e operador responsável);
- Avaliação do atendimento;
- Autorização ou recusa de atendimento em função do custo;
- Agendamento e Encaminhamento de chamados;
- Citação como operador partícipe em atendimentos (citado no ato do encerramento);
- Pendências relacionadas à avaliação de atendimento;
- Pendências relacionadas à assinatura de termo de compromisso;
- Citações ao usuário como autorizador de formulário de trânsito de ativos;
- Encerramento automático de chamados;
- Auto-avaliação de chamados;
- Etc..

Por meio da central de notificações, é possível ter acesso rápido aos chamados envolvidos, proporcionando maior agilidade e praticidade nas operações relacionadas a estes.

Algumas notificações só são marcadas como visualizadas após a pendência que gerou a notificação ser resolvida, como por exemplo:
- Necessidade de validar e avaliar o atendimento recebido (a notificação permanecerá até que o usuário valide e avalie o atendimento recebido);
- Necessidade de assinar um termo de compromisso (a notificação permanecerá até que o usuário assine o termo de compromisso);

Notificações já visualizadas permanecem acessíveis em uma seção específica para esse fim, mantendo o histórico de notificações.


### Perfil do usuário

No menu de acesso às informações do perfil, agora há novas informações disponíveis:
- Departamento: agora usuários podem ser vinculados a departamentos da organização;
- Assinatura: a assinatura não está relacionada a utilização de certificado digital. Na prática, é apenas um controle de aceite sobre as informações contidas nos termos de responsabilidade.
- Listagem de ativos alocados (caso existam);
- Termo de compromisso (caso exista);

Também na tela de perfil, são exibidas informações sobre pendências de assinatura de termo de compromisso, caso existam.

Por meio da tela de perfil o usuário agora pode:
- Definir/redefinir a assinatura:
	- Será aberto um painel para que o usuário “desenhe”, de forma manuscrita, sua assinatura ou importe um arquivo de imagem. A assinatura é armazenada no sistema como uma imagem e poderá ser renderizada nos documentos (termos de responsabilidade) nos quais o usuário “concordar” com o seu conteúdo.
	
- Visualizar/assinar o termo de compromisso;
- Baixar o termo de compromisso


## Módulo de ocorrências

### Menu Home

#### Fila de Chamados
Agora é possível acessar a "Fila de Chamados" diretamente neste menu, não precisando mudar para o menu "Ocorrências" (apenas para usuários com nível de operação/administração);

#### Áreas Geridas
Há uma nova seção "Pendentes de autorização para atendimento" que agrupa todos os chamados das áreas geridas que estão aguardando por autorização de custo. Só serão exibidos chamados cujo o custo é compatível com o limite de autorização de cada gerente de área.

### Menu Ocorrências

#### Painel de custos
Novo *dashboard* específico para controle de custos dos chamados. Esse painel só será exibido se as configurações relacionadas a "Custo dos chamados" (*Configurações gerais -> Confgurações básicas -> Custo dos chamados*) estiverem realizadas;

Os cards superiores do painel são fixos, no entanto, os cards laterais dependem de configuração para definição de que tipos de solicitações devem ser monitoradas. Essas configurações são feitas diretamente na seção de administração de tipos de solicitações.

Ao definir um tipo de solicitação para ter um card no painel de custos, automaticamente também será criado um gráfico para o tipo de solicitação.

#### Abrir Chamado
O processo de abertura de chamados sofreu algumas mudanças importantes como:

- Possibilidade de abrir um chamado em nome de outro usuário;
	Quando o chamado for aberto em nome de outro usuário, o usuário marcado como solicitante terá acesso ao chamado e receberá todas as notificações relacionadas ao chamado como se ele mesmo tivesse registrado o chamado no sistema.

- Possibilidade de solicitar que o solicitante selecione as categorias a fim de filtrar os tipos de solicitações possíveis. Essa configuração deve ser realizada via menu de administração.

- Os dados de identificação do solicitante são automaticamente preenchidos (ou recuperados em memória quando os campos não estão disponíveis no formulário) no formulário de abertura.


#### Filtro avançado
Foram criadas algumas novas opções de filtro:
- Apenas auto-encerrados por inatividade: no filtro "Data máxima de encerramento" agora há a possibilidade de definir que a busca deverá considerar apenas os chamados que foram auto-encerrados em função de inatividade (nova opção de configuração nas configurações básicas);
- Área solicitante: agora é possível filtrar pela área solicitante e não apenas pela área de atendimento;
- Status de autorização: opção relacionada à autorização de custo dos chamados;
- Recursos alocáveis: possibilita que o filtro considere chamados com recursos em específico;

Novas colunas retornadas da consulta:

- Recursos
- Status de autorização

#### Soluções
A busca textual em chamados concluídos foi otimizada (adicionados índices `fulltext`) e agora também possui a possibilidade de considerar chamados ainda não concluídos no sistema. Também é possível definir termos de exclusão para a consulta.


#### Relatórios diversos

- **Atendimentos e participações por operadores (quantitativos)**: esse relatório foi aprimorado para trazer as informações de forma mais clara, separando os contextos de cada agrupamento de situações possíveis para participações em chamados:
	- Operadores diretos
	- Operadores principais via encaminhamento
	- Operadores auxiliares via encaminhamento
	- Operadores participantes com períodos de atendimento
	- Participantes via fila direta
	- Total de participações não cumulativas

- **Categorias de tipos de solicitações**: este relatório agora permite a exportação para PDF, CSV e Excel. Além disso, como o sistema agora suporte até 6 agrupamentos de categorias (antes eram apenas 3) estes são exibidos como opções de filtro e também são segmentados no resultado da consulta.

- **Mudanças de Status**: novo relatório que traz informações sobre o histórico de mudanças de status de todos os chamados considerados na pesquisa.

- **Encerramento por técnico (responsável)**: esse relatório foi **removido** pois a sua função foi absorvida pelo relatório de "Atendimentos e participações por operadores (quantitativos)". 

- **Recursos alocados**: novo relatório que traz quantitativos e agrupamentos por categoria dos tipos de recursos alocados nos chamados do período. É possível filtrar por algum recurso em específico.

- **Relatório por campo customizado**: novo relatório capaz de trazer informações agrupadas por campos customizados. É possível definir um campo específico para ser considerado no agrupamento das informações.

- **Tempos de atendimento por chamados**: novo relatório que traz as informações sobre os tempos de atendimento dos chamados. As informações são calculadas considerando contextos específicos: **tempo fornecido**, **tempo em fila direta** (filtrado e absoluto).

- **Tempos de atendimento por operadores**: novo relatório que traz informações sobre os tempos de atendimento por operadores. São considerados tanto o contexto sobre o tempo em que os chamados ficam na fila direta dos operadores quanto o contexto dos tempos informados manualmente no ato da conclusão do atendimento. É possível filtrar por operadores específicos.

### Tela de detalhes dos chamados

- Opção "**Recursos**": para chamados não concluídos, há a possibilidade de vincular recursos (cadastrados via módulo de inventário) aos chamados. A listagem de recursos de cada chamado aparecerá nas informações sobre o chamado.
- A abertura de **sub-chamados** agora gera vínculos que podem ser visualizados de forma consolidada como um **projeto**, agrupando as informações relacionadas a todos os chamados relacionados entre si.
	- Sempre que for aberto um primeiro sub-chamado, este sub-chamado exibirá a opção "**Projeto**" com a possibilidade de "**Definir projeto**". Ao dar um nome e uma descrição para o projeto será possível visualizar as informações consolidadas de todos os chamados envolvidos. 
- O campo de "**Descrição**" aparece com maior destaque, podendo ser posicionado no topo, no meio ou no final do formulário (definição realizada nas configurações básicas do sistema);
- Os status dos chamados agora aparecem como um *badge*, com o destaque definido nas configurações de tipos de status;
- Para os casos aplicáveis, haverá também a informação sobre o "**Status de autorização**", bem como a opção "**Autorizar ou rejeitar**" (quando aplicável).
- Chamados com status de autorização "**Aguardando autorização**" não poderão ser concluídos no sistema.
- A informação sobre as **categorias do tipo de solicitação** agora aparecem na forma "agrupamento: categoria", facilitando o entendimento sobre as classificações do tipo de solicitação.
- Para os casos aplicáveis, haverá o botão de definição de custo para o chamado.
- Para os casos aplicáveis, haverá o botão "**Solicitar retorno**". Nestes casos, o solicitante será informado que precisar interagir com o chamado, caso contrário, o chamado será encerrado de forma automática no sistema.
- A listagem de comentários agora permite **filtro de busca** e ordenação direta por qualquer das colunas;

### Tela de encerramento dos chamados

Agora há a opção de adicionar operadores e períodos de atendimento relacionados ao chamado que está sendo concluído. Podem ser adicionados **múltiplos períodos** (desde que não sejam sobrepostos entre si para o mesmo operador) de atendimento para um mesmo operador. **Múltiplos operadores** podem ser adicionados. 


### Abertura de chamados por e-mail

A funcionalidade de abertura de chamados por e-mail foi aprimorada recebendo as seguintes melhorias:

- Agora arquivos anexos nas mensagens de e-mail são adicionados aos chamados (apenas os tipos de arquivos permitidos nas configurações de sistema);

- Caso o solicitante responda à própria mensagem que originou o chamado, essa resposta entrará como um novo comentário no chamado (apenas para chamados não concluídos no sistema), inclusive com novos anexos, caso existam.
	- Caso o endereço configurado para receber abertura de chamados seja o mesmo endereço configurado para os envios de mensagens do sistema, será possível adicionar informações ao chamados a partir da resposta às mensagens enviadas pelo sistema (desde que sejam mensagens relacionadas a chamados);

- Agora o sistema é capaz de identificar informações do solicitante, caso seja um endereço cadastrado. As seguintes informações são identificadas e registradas nos chamados:
	- Nome de usuário
	- Departamento;
	- Número de telefone;
	- Cliente
	- Área solicitante;

- Ao abrir chamado por e-mail, caso o domínio esteja relacionado a algum cliente, ele será considerado, de acordo com a seguinte precedência de classificação: cliente do usuário > Cliente do Domínio > cliente da configuração de abertura de chamados por e-mail;

- Via configuração, é possível definir que apenas endereços cadastrados no sistema possam abrir chamados via e-mail;

- Nesta versão, é possível configurar contas do **Office365** com autenticação via **OAuth** para obtenção das mensagens. Verifique a seção de configuração para abertura de chamados por e-mail.




## Módulo de inventário

### Calendário de garantias
Nova opção para exibir os vencimentos de garantias de ativos que tenham cadastro com data da compra e tempo de garantia;

### Menu: Consultas
#### Seção de Filtro avançado para ativos
Adicionados mais dois campos de filtro:
- Usuário: busca considerando o usuário para qual os ativos estejam alocados;
- Ativos ou recursos: busca que distingue entre ativos ou recursos;

Adicionada a coluna "Alocado para" no retorno do filtro avançado;

Agora há a opção de **remoção de ativos em lote**. Essa opção estará disponível para usuários com nível de administração a partir do resultado do filtro avançado.


#### Histórico de alocação por ativo
Nova seção: Traz a listagem de usuários que já tiveram o ativo alocado;

#### Histórico de alocação por usuário
Nova seção: Traz a listagem de ativos que já estiveram alocados para o usuário;

### Menu: Ativos de inventário

#### Árvore de ativos e recursos alocáveis
A árvore de ativos foi ajustada para trazer informações tanto de ativos quanto de recursos alocáveis em chamados:
Foram criados dois novos filtros:
- Disponibilidade: filtra com base na disponibilidade do ativo. Se estiver alocado para algum usuário o ativo será considerando "Em uso";
- Ativos e Recursos: filtra considerando se a pesquisa será por "Apenas ativos", "Apenas recursos" ou "Ativos e Recursos";

#### Cadastro de Ativos / Recursos
O mesmo processo de cadastro é responsável também por cadastrar recursos alocáveis em chamados. 
- Ao inserir um novo registro, há a opção de definir se o cadastro é para um ativo ou para um recurso.
- Caso o cadastro seja para um ativo, haverá a possibilidade de definir a quantidade (desde que habilitado o cadastro em lote nas configurações de sistema);
- Para cadastro em lote as etiquetas dos ativos poderão ser fornecidas via arquivo de texto (txt ou csv), ou digitadas diretamente na caixa de texto para esse fim. Também é possível gerar automaticamente uma numeração virtual para as etiquetas onde é possível definir um prefixo base que será incorporado a todas numerações geradas.
- Ainda para o cadastro em lote, os números de série também podem ser carregados de um arquivo texto (txt ou csv) ou digitados diretamente na caixa de texto para esse fim.

#### Tipos de ativos
Agora há duas novas opções a serem definidas:
- "Pode ser um recurso alocável em chamados": se for marcada como "Sim", o tipo de ativo será considerado para ser alocado como recurso em chamados;
	- **Obs**: Na versão 5, essa opção existia com vínculo nas categorias dos tipos de ativos, no entanto, sem efeito prático;
- "Digital": Se for marcado como "Sim", o tipo de ativo será considerado "não físico", como softwares, licenças, certificados, etc..
	- **Obs**: Na versão 5, essa opção existia com vínculo nas categorias dos tipos de ativos. Caso você tenha criado categorias marcando essa opção, recomendo que revise e porte as ativos correspondentes utilizando a configuração de "Digital" para o tipo de ativo e não mais para a categoria.

### Menu: Softwares
Todos os itens dentro deste menu estão marcados para serem **descontinuados**. A recomendação é utilizar o cadastro de ativos para também cadastrar e gerenciar softwares (criando um tipos marcados como "Digital");


### Menu: Diversos

#### Categorias de ativos
As opções "Digital" e "Recurso alocável" não existem mais. Essas opções agora são definidas diretamente nos "**Tipos de ativos**".


### Tela de detalhes dos ativos

Houve pequenas modificações na tela de informações detalhadas sobre o ativo:

- A opção "**Termo**" (termo de compromisso), não existe mais na barra de opções. Essa funcionalidade agora está relacionada à alocação de ativos a usuários e está acessível diretamente na seção de administração de usuários;

- A opção "Trânsito" foi reformulada, e agora permite a inclusão de múltiplos ativos em um único formulário. Além disso:
	- É possível selecionar o modelo do formulário (de acordo com o cliente e unidade);
	- O usuário que for marcado como autorizador receberá notificação tanto por e-mail quanto pela nova central de notificações do sistema;
	- Agora todos os formulários de trânsito gerados ficam gravados no sistema, mantendo seu histórico de trânsito.

- Há uma nova informação: "**Alocado para**", que indica o nome do usuário para qual o ativo está alocado. Caso o ativo não esteja alocado, haverá um botão para realizar a alocação.

- Há uma nova informação: "**Disponibilidade**", relacionada ao ativo estar alocado ou disponível para alocação;

- A seção "Dados complementares - Sistemas" traz todos os tipos de ativos marcados como "digital" e que estejam vinculados ao ativo visualizado;




### Correções

## Módulo de administração

### Menu de Configurações gerais

#### Seção de Configurações básicas
- Nova opção para ocultar do menu as opções descontinuadas;

- Nova seção de configurações relacionadas à abertura de chamados:

	- Configuração de Pré-filtros que podem ser apresentados no momento da abertura dos chamados: 
		- O funcionamento desta funcionalidade foi explicado aqui: https://www.youtube.com/watch?v=6LqXcmNl2mg
		- **Importante**: quando os pré-filtros estiverem habilitados, apenas tipos de solicitações que possuam categorias definidas em cada um dos pré-filtros ficarão disponíveis para serem selecionadas no ato da abertura do chamado;
		- É possível sobrescrever a configuração geral de pré-filtros via configuração específica nas áreas de atendimento;

	- Definição sobre a possibilidade de usuários de nível "somente-abertura" abrirem chamados em nome de outros usuários: nessa versão é possível que os usuários que registram o chamado no sistema definam/selecionem o usuário solicitante. Por padrão, apenas usuários com nível de operação ou administração podem abrir chamados em nome de outros usuários. Essa configuração permite habilitar essa possibilidade também para usuários básicos, nesses casos no entanto, apenas para usuários do mesmo departamento do usuário que estiver registrando o chamado.

- Nova seção de configurações relacionadas ao "**auto-encerramento**" de chamados por "inatividade":
	Essa funcionalidade possibilita o auto-encerramento de chamados que estejam com determinado status e que não recebam interações com o solicitante dentro de um prazo definido. 
	
	Por meio dessa seção é possível:

	- Definir o(s) status a ser(em) monitorado(s);
	- Definir qual será o status que o chamado assumirá quando a partir de retorno do solicitante;
	- Prazo limite para o retorno do solicitante antes que o chamado seja auto-encerrado;
	- Avaliação automática que o chamado receberá nos casos de auto-encerramento;

	**Importante**: para que essa funcionalidade tenha efeito, é necessário que o serviço esteja configurado no agendador de tarefas do servidor web (vide documentação de instalação/atualização);

- Nova seção para configurações relacionadas a **custos dos chamados**:
	Agora é possível definir custos para chamados. Para esse funcionamento é necessário definir um campo personalizado para esse fim.

	Nessa seção é possível selecionar:
	
	- Campo customizado para definição de custo (criado previamente);
	- Status para chamados aguardando autorização sobre o custo;
	- Status para chamados com custo autorizado;
	- Status para chamados com custo recusado;
	- Status para chamados com custo atualizado;

- O **upload de arquivos** agora aceita configuração até 20MB;

- Adicionado o suporte para upload de arquivos do tipo wav;

- Nova seção "Diversos", com a opção de definir o **posicionamento do campo "descrição"** na tela de detalhes dos chamados;

- Nova seção para configuração da quantidade máxima de ativos que podem ser cadastrados de uma única vez:
	Agora é possível cadastrar múltiplos ativos no módulo de inventário de uma única vez. Essa configuração define a quantidade máxima permitida.
	
- Nova seção para configurações relacionadas a **alocação de ativos a usuários**: 
	- Configuração sobre a possibilidade de alocação cruzada (entre diferentes clientes):
		Essa configuração define se um ativo de um cliente poderá ser alocado para um usuário de outro cliente. É possível permitir esse tipo de alocação entre diferente clientes apenas para usuários com nível de operação.
		
	- Configuração para definição do departamento que será atribuído aos ativos sempre que o usuário vinculado a estes for desabilitado no sistema;

#### Seção de Configurações estendidas

- Nova opção de autenticação SSO via OpenID Connect (experimental - testado apenas com servidor de identidade utilizando o KeyCloak);
	**Importante**: a aplicação precisa estar devidamente configurada no servidor de identidade.

- Nova opção para limitar a abertura de chamados por e-mail a apenas endereços registrados no sistema;

- Nova possibilidade de autenticação OAuth para acesso IMAP em contas do Office365;
	**Importante**: é necessário que as devidas configurações estejam completas no console de administração da plataforma Azure/Entra;

#### E-mail - Mensagens padrão

Novos eventos criados no sistema:
- **Encerramento automático**: mensagem enviada para o solicitante quando o chamado for encerrado de forma automática de acordo com as configurações de encerramento automático por inatividade;
- **Formulário de trânsito**: email para o usuário autorizador quando for gerado novo formulário de trânsito;
- **Serviço não autorizado**: mensagem para o operador responsável pelo chamado (caso o chamado esteja em fila direta) ou para a área responsável (caso o chamado esteja na fila aberta);
- **Solicitação de autorização**: mensagem para os autorizadores de custo solicitando autorização para o atendimento do chamado;
- **Solicitação de retorno do solicitante**: mensagem enviada para o solicitante do chamado durante o processo de solicitação de retorno;
- **Termo de compromisso**: mensagem para o usuário quando for gerado novo termo de compromisso pra ele;
- **Vinculação de ativos**: mensagem para o usuário quando forem vinculados ou removidos ativos sob sua responsabilidade.
#### Seção de Áreas de atendimento

- Nova seção para definição de configurações próprias para a utilização de pré-filtros na abertura de chamados.
	Quando habilitada a utilização de configurações próprias para área, essa configuração de pré-filtros utilizados (ou não utilizados) irá sobrepor a configuração existente no menu de administração.

### Menu de Ocorrências

#### Seção de Tipos de Solicitações

Foi adicionado suporte a mais 3 agrupamentos de categorias. Agora podem existir até 6 agrupamentos (independentes entre si) de categorias para cada tipo de solicitação.

- Nova opção de configuração sobre a necessidade do tipo de solicitação precisar de autorização de custo para ser processada;
- Nova opção de configuração sobre a exibição do tipo de solicitação no novo painel de controle de custos;

#### Seção de Árvore de tipos de solicitações

Nova funcionalidade: Agora é possível ter uma visão agrupada e hierárquica dos tipos de solicitação a partir de definição de filtros de agrupamentos de categorias;

Uma informação relevante na listagem é a coluna referente a roteiros de atendimento, facilitando a visão sobre que tipos de solicitações têm roteiros associados.

É possível limitar a listagem de tipos de solicitações a apenas tipos de solicitações que tenham roteiros associados.

É possível editar as informações dos tipos de solicitações diretamente por meio da exibição em árvore.

#### Seção de Tipos de Status

Agora é possível definir cor de fundo e cor da fonte de cada status. Dessa forma, estes poderão ser exibidos como rótulos, com destaques específicos, nas filas de chamados e na tela de detalhes de cada chamado, criando uma **identidade visual** nas listagens onde os status são exibidos;


### Menu de Inventário

> A opção de Termos de Responsabilidade foi movida para o menu de Clientes.

### Menu de Clientes

#### Seção de Clientes

Agora há a possibilidade de definir a unidade sede. A partir desta definição, o endereço da unidade sede passa a ser exibido como sendo o endereço do cliente, sobrescrevendo as antigas informações de endereço não estruturadas que eram fornecidas diretamente em uma área de texto do formulário.

Novo campo para Domínio do cliente: esse campo poderá ser utilizado como filtro para distribuição de chamados abertos por e-mail. Desta forma o sistema poderá identificar o cliente a partir do domínio do endereço de e-mail que originou o chamado.

#### Seção de Unidades

As unidades agora suportam informações de endereçamento. Os dados podem ser preenchidos manualmente ou carregados a partir do CEP fornecido.

Também há um novo campo para informações complementares.

#### Seção de Termos de Responsabilidade

Os termos de responsabilidade foram redesenhados para permitirem maior flexibilidade tanto no conteúdo quanto na forma de apresentação. Além disso, agora os termos de compromisso são salvos no sistema, mantendo um histórico tanto de alocação quanto de trânsito de ativos;  

Agora podem ser confeccionados termos específicos para cada cliente e unidade cadastrados.

Os termos de compromisso poderão ser gerados sempre que existir uma alocação de ativo(s) a algum usuário;

Os formulários de trânsito agora suportam múltiplos ativos;

### Seção de usuários

- Novo filtro para listagem de usuários com base nos respectivos termos de compromisso;
- Agora é possível vincular os usuários a departamentos;
- Agora é possível alocar ativos a usuários:
	- Por meio da tela de informações(edição) de cada usuário, é possível visualizar todos os ativos alocados para o usuário;
	- Para usuários que tenham ativos alocados, é possível gerar e baixar os termos de compromisso;

- Para usuários marcados como "Gerente de área", é possível definir um valor limite para que estes possam aprovar custos de chamados para as áreas sob sua responsabilidade;
- A edição das informações de usuários agora é feita por meio de uma janela modal, mantendo o filtro de pesquisa na janela principal.

### API

A API agora suporta o envio de arquivos na abertura de chamados;
Também foi desenvolvido um novo *endpoint* para inclusão de comentários nos chamados (com suporte a envio de arquivos).

Agora é possível informar, via API, quem é o solicitante do chamado: `requester`. Antes o sistema definia o solicitante como sendo o usuário da API. Agora o usuário da API aparece como sendo o autor do processo: `registration_operator`;

Consulte a documentação no site, na seção de **Integração**.



## Ajustes diversos e correções


- No retorno do filtro avançado (ocorrências e inventário), no gerenciamento de colunas, agora há uma *tooltip* com o nome inteiro de cada coluna (ao passar o mouse sobre cada coluna da listagem de 4 colunas). Antes as colunas ficavam com o nome truncado e não era possível saber qual era o nome completo;

- No retorno do filtro avançado, a coluna "Aberto por" foi renomeada para "Solicitante";

- Desenvolvido um sistema de logs para auditoria de ações de usuários no sistema: no momento aplicado apenas para eventos de exclusão de ativos;

- Melhorias na estrutura de estilos e no arquivo de versão para facilitar a mudança de identidade visual;

- Otimizações nas consultas relacionadas às listagens de chamados tanto na aba **Home** quanto na aba **Ocorrências**.

- Na tela de agendamento do chamado, a opção de marcar a primeira resposta não estava funcionando quando não se selecionava um operador para o encaminhamento;

- Em alguns contextos o controle de obrigatoriedade para o campo unidade na edição e encerramento não estava funcionando;

- Correções diversas relacionadas a funções marcadas como descontinuadas para as próximas versões do PHP;

- Desenvolvido um controle mais completo e preciso para não permitir a visualização de chamados que não estejam dentro das áreas que o usuário faça parte;

- Havia um bug que inviabilizava a abertura de chamados por e-mail caso a versão do PHP fosse igual ou superior à versão 8.1;

- Em alguns contextos, havia um bug que gerava erro ao tentar vincular um ativo como filho de outro ativo;

- Agora, ao editar as configurações estendidas, o token para o usuário definido só será atualizado caso o usuário seja alterado. Antes, o token sempre era atualizado independentemente do que fosse alterado.

- Ao adicionar operadores auxiliares em um chamado (por meio do Agendamento / encaminhamento), agora o sistema dispara uma notificação para cada operador auxiliar (os operadores responsáveis já recebiam a notificação);

- Otimizações e correções de bugs no painel de controle;

- Adicionada mensagem informativa sobre a descontinuidade de todas as funções do menu "Softwares" no módulo de inventário;

- Correção de bug na edição de departamentos quando não era definida a unidade. Além disso, agora a seleção de unidade traz o *subtext* informando o respectivo cliente;

- Melhorias na tela de seleção de tipo de solicitação na abertura de chamados. Adicionado melhor controle das mensagens possíveis e melhor formatação - também é exibida a informação caso o tipo de solicitação precisar de autorização;

- Operadores adicionados como auxiliares via o menu de agendamento/encaminhamento agora recebem notificação do sistema;

- Agora, ao desabilitar algum usuário, as informações sobre a área primária não são perdidas;

- Correção do bug que permitia que roteiros ficassem orfãos de tipos de solicitações;

- E diversos outros ajustes e correções menores..
