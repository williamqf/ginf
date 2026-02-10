<?php
/* 
Copyright 2023 Flávio Ribeiro

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

/**
 * getConfig
 * Retorna o array com as informações de configuração do sistema
 * @param PDO $conn
 * @return array
 */
function getConfig ($conn): array
{
    $sql = "SELECT * FROM config ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getConfigValue
 * Retorna o valor da chave de configuração informada - Configurações estendidas
 * @param \PDO $conn
 * @param string $key
 * @return null | string
 */
function getConfigValue (\PDO $conn, string $key): ?string
{
    $sql = "SELECT key_value FROM config_keys WHERE key_name = :key_name ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':key_name', $key);
        $res->execute();
        
        if ($res->rowCount()) {
            return $res->fetch()['key_value'];
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}


function configKeyExists (\PDO $conn, string $key): bool
{
    $sql = "SELECT key_name FROM config_keys WHERE key_name = :key_name ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':key_name', $key);
        $res->execute();
        
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}

/**
 * getConfigValues
 * Retorna um array com todas as chaves e valores das Configurações estendidas
 * @param \PDO $conn
 * @return array
 */
function getConfigValues (\PDO $conn): array
{
    $return = [];
    $notReturn = [];
    
    /* Essas chaves não serão retornadas */
    $notReturn[] = 'API_TICKET_BY_MAIL_TOKEN';
    $notReturn[] = 'MAIL_GET_PASSWORD';

    $sql = "SELECT key_name, key_value FROM config_keys ";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                if (!in_array($row['key_name'], $notReturn))
                    $return[$row['key_name']] = $row['key_value'];
            }
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * Sets the value of a configuration key in the database.
 *
 * @param \PDO $conn The PDO connection object.
 * @param string $key The name of the configuration key.
 * @param string $value The value to set for the configuration key.
 * @throws Exception If there is an error executing the database query.
 * @return bool Returns true if the configuration value is successfully set, false otherwise.
 */
function setConfigValue (\PDO $conn, string $key, ?string $value = null): bool
{

    $value = ($value === null ? null : $value);
    
    $sql = "SELECT key_name, key_value FROM config_keys WHERE key_name = :key_name ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':key_name', $key);
        $res->execute();
        
        if ($res->rowCount()) {

            $sql = "UPDATE config_keys SET key_value = :key_value WHERE key_name = :key_name ";
            $res = $conn->prepare($sql);
            $res->bindParam(':key_name', $key, PDO::PARAM_STR);
            $res->bindParam(':key_value', $value, PDO::PARAM_STR);
            $res->execute();
            return true;
        }
        $sql = "INSERT INTO config_keys (key_name, key_value) VALUES (:key_name, :key_value) ";
        $res = $conn->prepare($sql);
        $res->bindParam(':key_name', $key, PDO::PARAM_STR);
        $res->bindParam(':key_value', $value, PDO::PARAM_STR);
        $res->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


/**
 * saveNewTags
 * Checa se há novas tags em um array informado - se existirem novas tags serão gravadas
 * @param \PDO $conn
 * @param array $tags
 * @return bool
 */
function saveNewTags (\PDO $conn, array $tags): bool
{
    if (!is_array($tags)){
        return false;
    }

    $tags = filter_var_array($tags, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    foreach ($tags as $tag) {
        $sql = "SELECT tag_name FROM input_tags WHERE tag_name = :tag ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':tag', $tag);
            $res->execute();
            if (!$res->rowCount()) {
                $sqlIns = "INSERT INTO input_tags (tag_name) VALUES (:tag)";
                try {
                    $resInsert = $conn->prepare($sqlIns);
                    $resInsert->bindParam(':tag', $tag);
                    $resInsert->execute();
                }
                catch (Exception $e) {
                    return false;
                }
            }
        }
        catch (Exception $e) {
            return false;
        }
    }
    return true;
}


/**
 * getTagsList
 * Retorna a listagem de tags existentes ou uma tag específica na tabela de referência
 * @param \PDO $conn
 * @param int $id
 * @return array
 */
function getTagsList(\PDO $conn, ?int $id = null): array
{
    $data = [];
    $terms = "";
    if ($id) {
        $terms = " WHERE id = :id ";
    }

    $sql = "SELECT id, tag_name FROM input_tags {$terms} ORDER BY tag_name";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $tag) {
                $data[] = $tag;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];

    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getTagCount
 * Retorna a quantidade de vezes que a tag informada está sendo utilizada nos chamados
 * @param \PDO $conn
 * @param string $tag
 * 
 * @return int
 */
function getTagCount(\PDO $conn, string $tag, ?string $startDate = null, ?string $endDate = null, ?string $area = null, bool $requesterArea = false, ?string $client = null): int
{

    $terms = "";
    $aliasAreas = ($requesterArea ? "ua.AREA" : "o.sistema");
    
    if ($startDate) {
        $terms .= " AND o.data_abertura >= :startDate ";
    }
    if ($endDate) {
        $terms .= " AND o.data_abertura <= :endDate ";
    }

    if ($area && !empty($area) && $area != -1) {
        // $terms .= " AND o.sistema IN ({$area})";
        $terms .= " AND {$aliasAreas} IN ({$area})";
    }

    if ($client && !empty($client)) {
        $terms .= " AND o.client IN ({$client})";
    }
    
    $sql = "SELECT count(*) total 
            FROM 
                ocorrencias o, sistemas s, usuarios ua, `status` st 
            WHERE 
                o.status = st.stat_id AND st.stat_ignored <> 1 AND 
                o.sistema = s.sis_id AND o.aberto_por = ua.user_id AND 
                MATCH(oco_tag) AGAINST ('\"$tag\"' IN BOOLEAN MODE) 
                {$terms}";
    try {
        $res = $conn->prepare($sql);
        // $res->bindParam(':tag', $tag);
        if ($startDate) 
            $res->bindParam(":startDate", $startDate);
        if ($endDate) 
            $res->bindParam(":endDate", $endDate);
        // if ($area && !empty($area) && $area != -1)
        //     $res->bindParam(":area", $area);

        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['total'];
        }
        return 0;
    }
    catch (Exception $e) {
        echo $sql . "<hr/>" . $e->getMessage();
        // exit;
        return 0;
    }
}


/**
 * getScreenInfo
 * Retorna o array com as informações do perfil de tela de abertura
 * [conf_cod], [conf_name], [conf_user_opencall - permite autocadastro], [conf_custom_areas], 
 * [conf_ownarea - area para usuários que se autocadastram], [conf_ownarea_2], [conf_opentoarea]
 * [conf_screen_area], []... [conf_screen_msg]
 * @param \PDO $conn
 * @param int $screenId
 * @return array
 */
function getScreenInfo (\PDO $conn, int $screenId): array
{
    $sql = "SELECT 
                *
            FROM 
                configusercall
            WHERE 
                conf_cod = :screenID ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':screenID', $screenId);
        $res->execute();

        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getScreenProfiles
 * Retorna a listagem de perfis de tela de abertura
 * Indices: conf_cod, conf_name, etc..
 *
 * @param \PDO $conn
 * 
 * @return array
 */
function getScreenProfiles (\PDO $conn): array
{
    $sql = "SELECT * FROM configusercall ORDER BY conf_name";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getDefaultScreenProfile
 * Retorna o código do perfil de tela padrão ou 0 se não existir
 * @param \PDO $conn
 * 
 * @return int
 */
function getDefaultScreenProfile(\PDO $conn): int
{
    $sql = "SELECT conf_cod FROM configusercall WHERE conf_is_default = 1";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        
        if ($res->rowCount()) {
            return $res->fetch()['conf_cod'];
        }
        return 0;
    }
    catch (Exception $e) {
        return 0;
    }
}


/**
 * getFormRequiredInfo
 * Retorna um array com os valores de obrigariedade para os campos do perfil de campos disponíveis
 * Indices: nome do campo, valor (0|1)
 *
 * @param \PDO $conn
 * @param int $profileId
 * 
 * @return array
 */
function getFormRequiredInfo (\PDO $conn, int $profileId, ?string $table = null): array
{
    
    $fields = [];
    $table = ($table === null ? "screen_field_required" : $table);
    
    
    $sql = "SELECT 
                *
            FROM 
                {$table}
            WHERE 
                profile_id = :profileId ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':profileId', $profileId);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $fields[$row['field_name']] = $row['field_required'];
            }
            return $fields;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * pass
 * Retorna se a combinação de usuário e senha(ou hash:versão 4x) é válida
 * @param \PDO $conn
 * @param string $user
 * @param string $pass (deve vir com md5)
 * @return bool
 */
function pass(\PDO $conn, string $user, string $pass): bool
{
    $user = filter_var($user, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pass = filter_var($pass, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $sql = "SELECT 
                `user_id`, `password`, `hash`
            FROM 
                usuarios 
            WHERE 
                login = :user AND
                nivel < 4
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user', $user);

        $res->execute();
        if ($res->rowCount()) {

            $row = $res->fetch();
            if (!empty($row['hash'])) {
                /* usuário possui hash de senha */
                return password_verify($pass, $row['hash']);
            }

            if ($pass === $row['password'] && !empty($pass)) {
                return true;
            }
            return false;

        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }

    return false;
}


/**
 * Valida usuário e senha quanto a configuração de autenticação for para LDAP
 * É utilizada quando o tipo de autenticação de autenticação configurado em AUTH_TYPE for LDAP
 */
function passLdap (string $username, string $pass, array $ldapConfig): bool
{
    if (empty($username) || empty($pass)) {
        return false;
    }

    $username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $ldapConn = ldap_connect($ldapConfig['LDAP_HOST'], $ldapConfig['LDAP_PORT']);
    if (!$ldapConn) {
        // echo ldap_error($ldapConn);
        return false;
    }

    ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

    $username = $username . "@" . $ldapConfig['LDAP_DOMAIN'];

    if (@ldap_bind($ldapConn, $username, $pass)) {
        // echo ldap_error($ldapConn);
        return true;
    }
    return false;
}


/**
 * getUserLdapData
 *
 * @param string $username
 * @param string $pass
 * @param array $ldapConfig
 * 
 * @return array
 */
function getUserLdapData(string $username, string $pass, array $ldapConfig): ?array
{
    $ldapConn = ldap_connect($ldapConfig['LDAP_HOST'], $ldapConfig['LDAP_PORT']);
    if (!$ldapConn) {
        return null;
    }
    
    ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

    $usernameAtDomain = $username . "@" . $ldapConfig['LDAP_DOMAIN'];

    if (@ldap_bind($ldapConn, $usernameAtDomain, $pass)) {
        // echo ldap_error($ldapConn);

        /* (&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2))) */
        $search_filter = "(|(sAMAccountName=" . $username .")(uid=" . $username ."))";
        $results = ldap_search($ldapConn, $ldapConfig['LDAP_BASEDN'], $search_filter);
        
        if (!$results) {
            return [];
        }

        $datas = ldap_get_entries($ldapConn, $results);

        if (!($datas['count']) || !$datas) {
            return [];
        }

        $data = [];
        $data['username'] = $username;
        $data['password'] = $pass;
        
        $data['LDAP_FIELD_FULLNAME'] = (isset($datas[0][$ldapConfig['LDAP_FIELD_FULLNAME']][0]) ? $datas[0][$ldapConfig['LDAP_FIELD_FULLNAME']][0] : "");
        $data['LDAP_FIELD_EMAIL'] = (isset($datas[0][$ldapConfig['LDAP_FIELD_EMAIL']][0]) ? $datas[0][$ldapConfig['LDAP_FIELD_EMAIL']][0] : "");
        $data['LDAP_FIELD_PHONE'] = (isset($datas[0][$ldapConfig['LDAP_FIELD_PHONE']][0]) ? $datas[0][$ldapConfig['LDAP_FIELD_PHONE']][0] : "");

        return $data;
        
    }
    return [];
}



/**
 * isLocalUser
 * Retorna se existe um usuário com o nome de login informado
 *
 * @param \PDO $conn
 * @param string $user
 * 
 * @return bool
 */
function isLocalUser (\PDO $conn, string $user): bool
{
    $user = filter_var($user, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $sql = "SELECT user_id FROM usuarios WHERE login = :user ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user', $user);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }

    return false;
}


function getUserById (\PDO $conn, int $id): array
{
    $sql = "SELECT * FROM usuarios WHERE user_id = :id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    } catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
}



/**
 * getUsers
 * Retorna um array com a listagem dos usuários ou do usuário específico caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * @param array|null $level
 * @param bool|null $can_route - filtra pelos usuários que podem encaminhar chamados
 * @param bool|null $can_get_routed - filtra pelos usuários que podem receber chamados encaminhados
 * @param array|null $areas
 * @param int|null $department (id do departamento - se for algum valor menor ou igual a 0, filtra por nao nulos)
 * @param int|null $client (id do cliente - se for algum valor menor ou igual a 0, filtra por não nulos)
 * @return array
 */
function getUsers (
    \PDO $conn, 
    ?int $id = null, 
    ?array $level = null, 
    ?bool $can_route = null, 
    ?bool $can_get_routed = null, 
    ?array $areas = null,
    ?int $department = null,
    ?int $client = null
): array
{
    $in = "";
    if ($level) {
        $in = implode(',', array_map('intval', $level));
        // VERSION 2. For strings: apply PDO::quote() function to all elements
        // $in = implode(',', array_map([$conn, 'quote'], $level));
    }
    // $terms = ($id ? " WHERE user_id = :id" : '');
    $terms = ($id ? " AND user_id = :id" : '');
    
    if (!$id) {
        if ($level) {
            // $terms .= " WHERE nivel IN ($in) ";
            $terms .= " AND nivel IN ($in) ";
        }

        if ($can_route !== null) {
            $can_route = ($can_route ? 1 : 0);
            // $terms .= ($terms ? " AND " : " WHERE ") . "can_route = {$can_route} ";
            $terms .= " AND can_route = {$can_route} ";
        }
        if ($can_get_routed !== null) {
            $can_get_routed = ($can_get_routed ? 1 : 0);
            // $terms .= ($terms ? " AND " : " WHERE ") . "can_get_routed = {$can_get_routed} ";
            $terms .= " AND can_get_routed = {$can_get_routed} ";
        }

        if ($areas) {
            $in = implode(',', array_map('intval', $areas));
            // $terms .= ($terms ? " AND " : " WHERE ") . " AREA IN ({$in}) ";
            $terms .= " AND AREA IN ({$in}) ";
        }

        if ($department) {
            $terms .= ($department > 0 ? " AND user_department = '{$department}' " : " AND user_department IS NOT NULL ");
            // $terms .= " AND user_department = '{$department}' ";
        }
    
        if ($client) {
            $terms .= ($client > 0 ? " AND user_client = '{$client}' " : " AND user_client IS NOT NULL ");
            // $terms .= " AND user_client = '{$client}' ";
        }
    }
    
    // $sql = "SELECT * FROM usuarios {$terms} ORDER BY nome, login";
    $sql = "SELECT * FROM usuarios WHERE user_id > 0 {$terms} ORDER BY nome, login";
    try {
        $res = $conn->prepare($sql);
    
        if ($id) {
            $res->bindParam(':id', $id); 
        }
        
        $res->execute();
        /* $res->debugDumpParams() */
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                
                unset($row['password']);
                unset($row['hash']);
                
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
        // return [];
    }
}



function getUserDepartment(\PDO $conn, int $userId): ?int
{
    $sql = "SELECT user_department FROM usuarios WHERE user_id = :id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $userId, \PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            return $row['user_department'];
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}


function getArrayOfUsersNamesByIds (\PDO $conn, array $ids): array
{
    $ids = array_map('intval', $ids);
    $sql = "SELECT nome FROM usuarios WHERE user_id IN (".implode(',', $ids).")";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row['nome'];
            }
            return $data;
        }
        return [];
    } catch (Exception $e) {
        return [];
    }
}




/**
 * getUsersByPrimaryArea
 * Retorna um array com a listagem dos usuários da área informada
 * @param \PDO $conn
 * @param null|int $area
 * @param null|array $level
 * @return array
 */
function getUsersByPrimaryArea (\PDO $conn, ?int $area = null, ?array $level = null): array
{
    $return = [];
    $in = "";
    if ($level) {
        $in = implode(',', array_map('intval', $level));
    }
    $terms = ($area ? "AND u.AREA = :area " : '');
    $terms = (empty($terms) && $level ? "AND nivel IN ({$in})" : $terms);

    $sql = "SELECT u.user_id, u.nome FROM usuarios u, sistemas a 
            WHERE u.AREA = a.sis_id 
            {$terms} ORDER BY nome";
    try {
        $res = $conn->prepare($sql);
        if (!empty($terms)) {
            if ($area)
                $res->bindParam(':area', $area); 
        }
        $res->execute();
        /* $res->debugDumpParams() */
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * getUserByEmail
 * Retorna as informações do usuário pelo email informado;
 * Se maxId = false, só retornará informações caso não existam e-mails repetidos na base de usuários;
 * Se maxId = true, vai retornar o user_id mais alto para o email informado (quando existirem mais de um);
 *
 * @param \PDO $conn
 * @param string $email
 * @param bool $maxId
 * 
 * @return array|null
 */
function getUserByEmail (\PDO $conn, string $email, bool $maxId = false): ?array
{
    /* Confere se o email está formatado */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }

    $terms = "";
    if ($maxId) {
        $terms = " AND user_id = (SELECT (MAX(user_id)) FROM usuarios WHERE email = :email) ";
    }

    $sql = "SELECT 
                user_id, 
                nome as name, 
                user_client, 
                user_department, 
                fone as phone, 
                nivel as level
            FROM 
                usuarios 
            WHERE 
                email = :email 
                {$terms}
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':email', $email);
        $res->execute();
        if ($res->rowCount() == 1) {
            return $res->fetch();
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}




/**
 * getUserInfo
 * Retorna o array com as informações do usuário e da área de atendimento que ele está vinculado
 * [user_id], [login], [nome], [email], [fone], [nivel], [area_id], [user_admin], [last_logon], 
 * [area_nome], [area_status], [area_email], [area_atende], [sis_screen], [sis_wt_profile],
 * [language]
 * @param \PDO $conn: conexao PDO
 * @param int $userId: id do usuário
 * @param string $userName: login do usuário - se for informado, o filtro será por ele
 * @return array
 */
function getUserInfo (\PDO $conn, ?int $userId = null, string $userName = '', ?array $columns = null): array
{
    $defaultColumns = [
        'u.user_id',
        'u.user_client', 
        'u.user_department', 
        'u.term_unit', 
        'u.term_unit_updated_at',
        'u.login', 'u.nome', 
        'u.email', 'u.fone', 
        'u.password', 'u.hash', 
        'u.nivel', 'u.AREA as area_id', 
        'u.user_admin', 'u.last_logon', 
        'u.can_route', 'u.can_get_routed',
        'u.max_cost_authorizing', 
        'a.sistema as area_nome', 
        'a.sis_status as area_status', 
        'a.sis_email as area_email', 
        'a.sis_atende as area_atende', 'a.sis_screen',
        'a.sis_wt_profile',
        'a.sis_opening_mode as opening_mode',
        'p.upref_lang as language',
        'cl.id', 'cl.fullname', 'cl.nickname',
        'l.local as department'
    ];

    if ($columns === null || empty($columns)) {
        $columns = $defaultColumns;
    }

    $columns = implode(',', $columns);
    
    $terms = (empty($userName) ? " user_id = :userId " : " login = :userName ");
    $sql = "SELECT 
                {$columns}
            FROM 
                sistemas a, usuarios u 
                LEFT JOIN uprefs p ON u.user_id = p.upref_uid
                LEFT JOIN clients cl ON u.user_client = cl.id
                LEFT JOIN localizacao l ON u.user_department = l.loc_id
            WHERE 
                u.user_id > 0 AND
                u.AREA = a.sis_id 
                AND 
                {$terms} ";
    try {
        $res = $conn->prepare($sql);

        if (!empty($userName)) {
            $res->bindParam(':userName', $userName); 
        } else
            $res->bindParam(':userId', $userId); 

        $res->execute();

        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getUnitFromUserDepartmentOrUserClient
 * Retorna o id da unidade relacionada ao usuário por meio do departamento vinculado 
 * ao usuário ou então ao cliente vinculado ao usuário
 *
 * @param \PDO $conn
 * @param int $userId
 * 
 * @return int|null
 */
function getUnitFromUserDepartmentOrUserClient(\PDO $conn, int $userId): ?int
{
    $userInfo = getUserInfo($conn, $userId);
    if (empty($userInfo)) {
        return null;
    }
    unset($userInfo['password']);
    unset($userInfo['hash']);

    $userClient = (!empty($userInfo) && !empty($userInfo['user_client']) ? $userInfo['user_client'] : "");
    $userDepartment = (!empty($userInfo) && !empty($userInfo['user_department']) ? $userInfo['user_department'] : "");

    $unitInfo = [];
    $units = [];
    if (!empty($userDepartment)) {
        /* Buscando a unidade com base no departamento do usuário */
        $departmentInfo = getDepartments($conn, null, $userDepartment);
        if (!empty($departmentInfo) && !empty($departmentInfo['loc_unit'])) {
            $unitInfo = getUnits($conn, null , $departmentInfo['loc_unit']);
        }
    }

    $unit = (!empty($unitInfo) && !empty($unitInfo['inst_cod']) ? $unitInfo['inst_cod'] : "");

    if (!empty($unit)) {
        return $unit;
    }

    
    /* Buscar pelo cliente do usuário então */
    if (!empty($userClient)) {
        $units = getUnits($conn, null , null, $userClient);
    }

    if (count($units) == 1) {
        return $units[0]['inst_cod'];
    }

    return null;
}

/**
 * getUserSignatureFileInfo
 * Retorna as informações sobre o arquivo de assinatura mais recente do usuário informado
 *
 * @param \PDO $conn
 * @param int $userId
 * 
 * @return array|null
 */
function getUserSignatureFileInfo (\PDO $conn, int $userId): ?array
{
    $sql = "SELECT 
                * 
            FROM 
                users_x_signatures 
            WHERE 
                user_id = :user_id AND
                id = (
                    SELECT MAX(id) FROM users_x_signatures WHERE user_id = :user_id
                )
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $userId);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            // $row['signature_file'] = base64_encode($row['signature_file']);
            $row['signature_src'] = $row['file_type'] . ',' . base64_encode($row['signature_file']);
            unset($row['signature_file']);
            return $row;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}




/**
 * getUserAreas
 * Retorna uma string com as áreas SECUNDÁRIAS associadas ao usuário
 * @param \PDO $conn: conexao PDO
 * @param int $userId: id do usuário
 * @return string
 *
 */
function getUserAreas (\PDO $conn, int $userId): string
{
    $areas = "";
    $sql = "SELECT uarea_sid FROM usuarios_areas WHERE uarea_uid = '{$userId}' ";
    
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                if (strlen((string)$areas) > 0)
                    $areas .= ",";
                $areas .= $row['uarea_sid'];
            }
            return $areas;
        }
        return $areas;
    }
    catch (Exception $e) {
        return $areas;
    }
}



function getAssetsFromUser (\PDO $conn, int $userId): array
{
    // table: users_x_assets
    $sql = "SELECT * 
            FROM 
                users_x_assets 
            WHERE 
                user_id = :user_id AND
                is_current = 1
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $userId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


function getUserFromAssetId (\PDO $conn, int $assetId): array
{
    // table: users_x_assets
    $sql = "SELECT user_id FROM users_x_assets WHERE asset_id = :asset_id AND is_current = 1";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $assetId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    } catch (exception $e) {
        return [];
    }
}


function getAssetsFromArrayOfUsers (\PDO $conn, array $users): array
{
    // table: users_x_assets
    $users = array_map('intval', $users);

    $sql = "SELECT 
                asset_id
            FROM 
                users_x_assets 
            WHERE 
                user_id IN (" . implode(',', $users) . ") AND
                is_current = 1
    ";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row['asset_id'];
            }
            return $data;
        }
        return [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



function getUserLastCommitmentTermId(\PDO $conn, int $userId): ?int
{
    $sql = "SELECT MAX(id) as id FROM users_x_files 
    
            WHERE
                user_id = :userId AND
                file_type = 1
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['id'];
        }
        return null;
    } catch (Exception $e) {
        return null;
    }
}


function getUserLastCommitmentTermInfo(\PDO $conn, int $userId): array
{

    $sql = "SELECT 
                id,
                html_doc,
                uploaded_at,
                signed_at
            FROM
                users_x_files
            WHERE
                user_id = :user_id AND
                file_type = 1 AND
                id = (
                    SELECT MAX(id) FROM users_x_files WHERE user_id = :user_id AND file_type = 1
                )
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $userId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage()
        ];
    }
}


function isLastUserTermSigned(\PDO $conn, int $userId): bool
{
    $sql = "SELECT 
                signed_at
            FROM
                users_x_files
            WHERE
                user_id = :user_id AND
                file_type = 1 AND
                id = (
                    SELECT MAX(id) FROM users_x_files WHERE user_id = :user_id AND file_type = 1
                )
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $userId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['signed_at'] != null;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}


function lastUserTermSignedDate(\PDO $conn, int $userId): ?string
{
    $sql = "SELECT 
                signed_at
            FROM
                users_x_files
            WHERE
                user_id = :user_id AND
                file_type = 1 AND
                id = (
                    SELECT MAX(id) FROM users_x_files WHERE user_id = :user_id AND file_type = 1
                )
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $userId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['signed_at'];
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}



function isUserTermUpdated(\PDO $conn, int $userId): bool 
{
    $lastTermUpdate = "";
    $lastAssetCreated = "";
    $lastAssetUpdate = "";
    $lastTermUnitUpdate = "";
    
    /* Busca a geração de termo mais recente */
    $sql = "SELECT 
                MAX(uploaded_at) as uploaded_at 
            FROM 
                users_x_files 
            WHERE
                user_id = :userId AND
                file_type = 1
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount()) {
            $lastTermUpdate = $res->fetch()['uploaded_at'];
        } else {
            /* Se não possui nenhum registro, nunca foi gerado */
            return false;
        }
        // return false;
    } catch (Exception $e) {
        return false;
    }


    /* Busca sobre a vinculação de ativos mais recente */
    $sql = "SELECT
                MAX(created_at) AS created_at
            FROM 
                users_x_assets
            WHERE
                user_id = :userId
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount()) {
            $lastAssetCreated = $res->fetch()['created_at'];
        } else {
            /* Se não possui nenhum registro de criação, não é necessário gerar termo */
            return true;
        }
        // return false;
    } catch (Exception $e) {
        return false;
    }

    /* Busca sobre a atualização (remoção) de ativos mais recente */
    $sql = "SELECT
                MAX(updated_at) AS updated_at
            FROM 
                users_x_assets
            WHERE
                user_id = :userId AND
                updated_at IS NOT NULL
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount()) {
            $lastAssetUpdate = $res->fetch()['updated_at'];
        } /* else {
            return true;
        } */
    } catch (Exception $e) {
        return false;
    }


    /* Buscar o term_unit_updated_at na tabela de usuarios */
    $sql = "SELECT 
                term_unit_updated_at 
            FROM 
                usuarios 
            WHERE 
                user_id = :userId AND
                term_unit_updated_at IS NOT NULL
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount()) {
            $lastTermUnitUpdate = $res->fetch()['term_unit_updated_at'];
        }
    } catch (Exception $e) {
        return false;
    }


    $lastAssetUpdate = (!empty($lastAssetUpdate) ? $lastAssetUpdate : $lastAssetCreated);

    $lastAssetUpdate = ($lastAssetUpdate > $lastTermUnitUpdate ? $lastAssetUpdate : $lastTermUnitUpdate);

    $lastAssetUpdate = ($lastAssetUpdate > $lastAssetCreated ? $lastAssetUpdate : $lastAssetCreated);

    if ($lastTermUpdate > $lastAssetUpdate)
        return true;
    return false;

}



function hasToSignTerm(\PDO $conn, int $userId): bool
{
    $commitmentTermId = getUserLastCommitmentTermId($conn, $userId);
    
    if (!$commitmentTermId) {
        return false;
    }
    
    $termUpdated = isUserTermUpdated($conn, $userId);

    if (!$termUpdated) {
        return false;
    }

    return (isLastUserTermSigned($conn, $userId) ? false : true);
}







function insertOrUpdateUsersTermsPivotTable(\PDO $conn, int $userId, bool $isTermUpdated, bool $isTermSigned, ?string $signedAt = null): bool
{
    $table = "users_terms_pivot";
    // $hasTerm = ($hasTerm ? 1 : 0);
    $hasTerm = 0;

    $sql = "SELECT user_id FROM users_x_files WHERE user_id = :userId AND file_type = 1 LIMIT 1";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount() > 0) {
            $hasTerm = 1;
        }
    } catch (\PDOException $e) {
        // return false;
        $hasTerm = 0;
    }


    $isTermUpdated = ($isTermUpdated ? 1 : 0);
    $isTermSigned = ($isTermSigned ? 1 : 0);

    $sql = "SELECT user_id FROM {$table} WHERE user_id = :userId";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount()) {
            $sql = "UPDATE {$table} SET has_term = :has_term, is_term_updated = :is_term_updated, is_term_signed = :is_term_signed, signed_at = :signed_at WHERE user_id = :userId";
            
            try {
                $res = $conn->prepare($sql);
                $res->bindParam(':userId', $userId, \PDO::PARAM_INT);
                $res->bindParam(':has_term', $hasTerm, \PDO::PARAM_INT);
                $res->bindParam(':is_term_updated', $isTermUpdated, \PDO::PARAM_INT);
                $res->bindParam(':is_term_signed', $isTermSigned, \PDO::PARAM_INT);
                $res->bindParam(':signed_at', $signedAt, \PDO::PARAM_STR);
                $res->execute();
                return true;
            } catch (exception $e) {
                echo $e->getMessage();
                return false;
            }
            
        } else {
            $sql = "INSERT INTO {$table} (user_id, has_term, is_term_updated, is_term_signed, signed_at) VALUES (:userId, :has_term, :is_term_updated, :is_term_signed, :signed_at)";

            try {
                $res = $conn->prepare($sql);
                $res->bindParam(':userId', $userId);
                $res->bindParam(':has_term', $hasTerm);
                $res->bindParam(':is_term_updated', $isTermUpdated);
                $res->bindParam(':is_term_signed', $isTermSigned);
                $res->bindParam(':signed_at', $signedAt);
                $res->execute();
            } catch (exception $e) {
                echo $e->getMessage();
                return false;
            }
        }
    } catch (exception $e) {
        echo $e->getMessage();
        return false;
    }
    return true;
}



/**
 * unique_multidim_array
 * Retorna o array multidimensional sem duplicados baseado na chave fornecida
 * @param mixed $array
 * @param mixed $key
 * 
 * @return array | null
 */
function unique_multidim_array($array, $key): ?array
{
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
} 



/**
 * getUsersByArea
 * Retorna todos os usuários de uma determinada área - sendo primária ou secundária
 * Também retorna a quantidade de chamados vinculados (sob responsabilidade) a cada usuário
 * @param \PDO $conn: conexao PDO
 * @param int|null  $area: id da area
 * @param bool|null $getTotalTickets: se true, retorna o total de chamados vinculados ao usuário
 * @param bool|null $canRoute: Filtra por usuários que podem encaminhar chamados
 * @param bool|null $canGetRouted: Filtra por usuários que podem receber encaminhamentos de chamados
 * @return array|null
 *
 */
function getUsersByArea (
    \PDO $conn, 
    ?int $area, 
    ?bool $getTotalTickets = true, 
    ?bool $canRoute = null, 
    ?bool $canGetRouted = null
    ): ?array
{

    if (!$area) {
        return [];
    }

    $terms = "";
    if ($canRoute !== null) {
        $canRoute = ($canRoute ? 1 : 0);
        $terms .= " AND u.can_route = ". $canRoute;
    }

    if ($canGetRouted !== null) {
        $canGetRouted = ($canGetRouted ? 1 : 0);
        $terms .= " AND u.can_get_routed = " . $canGetRouted;
    }
    
    $primaryUsers = [];
    $secondaryUsers = [];
    $totalTickets = [];
    
    /* Checando com a área sendo primária */
    $sql = "SELECT 
                u.user_id, u.nome, u.user_bgcolor, u.user_textcolor, '0' total 
            FROM 
                sistemas a, usuarios u
            WHERE 
                a.sis_id = u.AREA AND
                a.sis_id = :area AND
                u.nivel < 4  
                {$terms} 
            ORDER BY 
                u.nome
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":area", $area, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $primaryUsers[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
        // return [];
    }

    /* Checando com a área sendo secundária */
    $sql = "SELECT 
                u.user_id, u.nome, u.user_bgcolor, u.user_textcolor, '0' total 
            FROM 
                usuarios as u, usuarios_areas as ua 
            WHERE
                u.user_id = ua.uarea_uid AND 
                u.nivel < 4 AND
                ua.uarea_sid = :area 
                {$terms}
            ORDER BY 
                u.nome
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":area", $area, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $secondaryUsers[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }


    /* Quantidade de chamados sob responsabilidade */
    if ($getTotalTickets) {
        $sql = "SELECT 
                    u.user_id, u.nome, u.user_bgcolor, u.user_textcolor, count(*) total 
                FROM 
                    ocorrencias o, status s, usuarios u, sistemas a 
                WHERE 
                    o.status = s.stat_id AND 
                    s.stat_painel = 1 AND 
                    o.operador = u.user_id AND 
                    o.oco_scheduled = 0 AND 
                    u.nivel < 4 AND 
                    a.sis_id = u.AREA AND 
                    a.sis_id = :area 
                    {$terms}
                GROUP BY 
                    user_id, nome, u.user_bgcolor, u.user_textcolor
                ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(":area", $area, PDO::PARAM_INT);
            $res->execute();
            if ($res->rowCount()) {
                foreach ($res->fetchAll() as $row) {
                    $totalTickets[] = $row;
                }
            }
        }
        catch (Exception $e) {
            return ['error' => $e->getMessage()];
            // return [];
        }
    }

    
    if ($getTotalTickets) {
        $output = array_merge($totalTickets, $primaryUsers, $secondaryUsers);
    } else {
        $output = array_merge($primaryUsers, $secondaryUsers);
    }
    
    $output = unique_multidim_array($output, 'user_id');
    
    $keys = array_column($output, 'nome');
    array_multisort($keys, SORT_ASC, $output);

    return $output;
}



/**
 * getOnlyOpenUsers
 * Retorna a listagem de usuários de nível somente abertura
 *
 * @param mixed $conn
 * 
 * @return array
 */
function getOnlyOpenUsers($conn): array
{
    $onlyOpenUsers = [];
    $sql = "SELECT 
                u.user_id, 
                u.nome, 
                u.user_bgcolor, 
                u.user_textcolor, '0' total 
            FROM
                usuarios u
            WHERE
                u.nivel = 3 
            ORDER BY
                u.nome
            ";
        try {
            $res = $conn->query($sql);
            if ($res->rowCount()) {
                foreach ($res->fetchAll() as $row) {
                    $onlyOpenUsers[] = $row;
                }
                return $onlyOpenUsers;
            } else 
                return [];
        }
        catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
}


/**
 * getUsersBySetOfAreas
 * Retorna todos os usuários de uma ou várias áreas informadas - sendo primárias ou secundárias
 * Também retorna a quantidade de chamados vinculados (sob responsabilidade) a cada usuário
 * @param \PDO $conn: conexao PDO
 * @param array  $area: array com id(s) da(s) area(s)
 * @param bool|null $getTotalTickets: se true, retorna o total de chamados vinculados ao usuário
 * @param bool|null $canRoute: Filtra por usuários que podem encaminhar chamados
 * @param bool|null $canGetRouted: Filtra por usuários que podem receber encaminhamentos de chamados
 * @param array|null $level: Filtra pelos níveis dos usuários
 * @return array|null
 *
 */
function getUsersBySetOfAreas (\PDO $conn, array $area = [], ?bool $getTotalTickets = true, ?bool $canRoute = null, ?bool $canGetRouted = null, ?array $level = null): ?array
{

    $terms = "";
    $terms2 = "";
    
    $csvAreas = "";
    if (!empty($area)) {
        $csvAreas = implode(',', $area);
        $terms .= " AND a.sis_id IN ({$csvAreas}) ";
        $terms2 .= " AND ua.uarea_sid IN ({$csvAreas}) ";
    }
    
    
    if ($canRoute !== null) {
        $canRoute = ($canRoute ? 1 : 0);
        $terms .= " AND u.can_route = ". $canRoute;
        $terms2 .= " AND u.can_route = ". $canRoute;
    }

    if ($canGetRouted !== null) {
        $canGetRouted = ($canGetRouted ? 1 : 0);
        $terms .= " AND u.can_get_routed = " . $canGetRouted;
        $terms2 .= " AND u.can_get_routed = " . $canGetRouted;
    }

    if ($level !== null) {
        $csvLevels = implode(',', $level);
        $terms .= " AND u.nivel IN ({$csvLevels}) ";
        $terms2 .= " AND u.nivel IN ({$csvLevels}) ";
    }
    
    $primaryUsers = [];
    $secondaryUsers = [];
    $totalTickets = [];
    
    /* Checando com a área sendo primária */
    $sql = "SELECT 
                u.user_id, u.nome, u.user_bgcolor, u.user_textcolor, '0' total 
            FROM 
                sistemas a, usuarios u
            WHERE 
                a.sis_id = u.AREA AND
                u.nivel < 4  
                {$terms} 
            ORDER BY 
                u.nome
            ";
    try {
        $res = $conn->prepare($sql);
        // $res->bindParam(":area", $area, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $primaryUsers[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return [
                'error' => $e->getMessage(), 
                'sql' => $sql
            ];
    }

    /* Checando com a área sendo secundária */
    $sql = "SELECT 
                u.user_id, u.nome, u.user_bgcolor, u.user_textcolor, '0' total 
            FROM 
                usuarios as u, usuarios_areas as ua 
            WHERE
                u.user_id = ua.uarea_uid AND 
                u.nivel < 4 
                {$terms2}
            ORDER BY 
                u.nome
            ";
    try {
        $res = $conn->prepare($sql);
        // $res->bindParam(":area", $area, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $secondaryUsers[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return [
            'error' => $e->getMessage(), 
            'sql' => $sql
        ];
    }


    /* Quantidade de chamados sob responsabilidade */
    if ($getTotalTickets) {
        $sql = "SELECT 
                    u.user_id, u.nome, u.user_bgcolor, u.user_textcolor, count(*) total 
                FROM 
                    ocorrencias o, status s, usuarios u, sistemas a 
                WHERE 
                    o.status = s.stat_id AND 
                    s.stat_painel = 1 AND 
                    o.operador = u.user_id AND 
                    o.oco_scheduled = 0 AND 
                    u.nivel < 4 AND 
                    a.sis_id = u.AREA  
                    {$terms}
                GROUP BY 
                    user_id, nome, u.user_bgcolor, u.user_textcolor
                ";
        try {
            $res = $conn->prepare($sql);
            // $res->bindParam(":area", $area, PDO::PARAM_INT);
            $res->execute();
            if ($res->rowCount()) {
                foreach ($res->fetchAll() as $row) {
                    $totalTickets[] = $row;
                }
            }
        }
        catch (Exception $e) {
            return [
                'error' => $e->getMessage(), 
                'sql' => $sql
            ];
        }
    }

    
    if ($getTotalTickets) {
        $output = array_merge($totalTickets, $primaryUsers, $secondaryUsers);
    } else {
        $output = array_merge($primaryUsers, $secondaryUsers);
    }
    
    $output = unique_multidim_array($output, 'user_id');
    
    $keys = array_column($output, 'nome');
    array_multisort($keys, SORT_ASC, $output);

    return $output;
}




/**
 * getUserAreasNames
 * Retorna um array com os nomes das áreas cujos ids são informados em string
 * @param PDO $conn
 * @param mixed $areasIds
 * @return array
 */
function getUserAreasNames(\PDO $conn, string $areasIds): array
{
    $names = [];
    $sql = "SELECT sistema FROM sistemas WHERE sis_id IN ({$areasIds}) ORDER BY sistema";
    try {
        $res = $conn->query($sql);
        foreach ($res->fetchAll() as $row) {
            $names[] = $row['sistema'];
        }
        return $names;
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getClients
 * Retorna um array com a listagem de clientes ou do cliente específico caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $operationType (1 - apenas interno atendimento | 2 (ou qualquer outro valor) - apenas externo - usuario final)
 * @return array
 */
function getClients (\PDO $conn, ?int $id = null, ?int $operationType = null, ?string $ids = null): array
{
    $terms = "";
    $terms = ($id ? " WHERE id = :id " : '');
    
    if (!$id) {
        if ($operationType !== null) {
            $terms .= ($operationType == 1 ? " WHERE id = 1" : " WHERE id <> 1 ");
        }

        if ($ids) {
            $terms .= ($terms ? " AND " : " WHERE ") . "id IN ({$ids})";
        }
    }


    $sql = "SELECT * FROM clients {$terms} ORDER BY fullname, nickname";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



function getClientByUnitId(\PDO $conn, int $id): array
{
    $sql = "SELECT
                cl.id,
                cl.nickname
            FROM
                clients cl,
                instituicao u
            WHERE
                cl.id = u.inst_client AND
                u.inst_cod = :id
    ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id); 
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getClientsNamesByIds
 * Retorna um array com os nomes dos clientes cujos ids são informados em string
 * @param PDO $conn
 * @param mixed $ids
 * @return array
 */
function getClientsNamesByIds(\PDO $conn, string $ids, ?bool $nickname = false): array
{
    $arrayIds = explode(',', $ids);
    $arrayIds = array_map('trim', $arrayIds);
    $arrayIds = array_filter($arrayIds);
    $arrayIds = array_unique($arrayIds);
    $arrayIds = array_map('intval', $arrayIds);
    $ids = implode(',', $arrayIds);
    $names = [];
    $alias = ($nickname ? 'nickname' : 'fullname');
    $sql = "SELECT {$alias} FROM clients WHERE id IN ({$ids}) ORDER BY {$alias}";
    try {
        $res = $conn->query($sql);
        foreach ($res->fetchAll() as $row) {
            $names[] = $row[$alias];
        }
        return $names;
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getClientsTypes
 * Retorna a listagem de tipos de clientes ou um cliente específico caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getClientsTypes(\PDO $conn, ?int $id = null): array
{

    $terms = ($id ? " WHERE id = :id " : '');
    
    $sql = "SELECT * FROM client_types {$terms} ORDER BY type_name";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getClientsStatus
 * Retorna a listagem de tipos de status de clientes ou um status específico caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getClientsStatus(\PDO $conn, ?int $id = null): array
{

    $terms = ($id ? " WHERE id = :id " : '');
    
    $sql = "SELECT * FROM client_status {$terms} ORDER BY status_name";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



function getClientByTicket(\PDO $conn, int $ticket): array
{
    $sql = "SELECT 
                cl.id, cl.fullname, cl.nickname 
            FROM
                ocorrencias o
            LEFT JOIN clients cl ON cl.id = o.client
            WHERE
                o.numero = :ticket
            ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * getTableCompat
 * Para manter a compatibilidade com versões antigas
 * Faz o teste com a nomenclatura da tabela areaxarea_abrechamado
 * Em versões antigas essa tabela era areaXarea_abrechamado
 * @param PDO $conn
 * @return string
 */
function getTableCompat(\PDO $conn): string
{
    $table = "areaxarea_abrechamado";
    $sqlTest = "SELECT * FROM {$table}";
    try {
        $conn->query($sqlTest);
        return $table;
    } catch (Exception $e) {
        $table = "areaXarea_abrechamado";
        return $table;
    }
}



/**
 * getMeasureTypes
 * Retorna a listagem dos tipos de caracteríscas que podem ser medidas no inventário ou uma característica especifíca caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * @param bool $onlyHavingUnit : se true retorna apenas os tipos que possuem unidade de medida
 * 
 * @return array
 */
function getMeasureTypes(\PDO $conn, ?int $id = null, bool $onlyHavingUnit = false): array
{

    $terms = ($id ? " WHERE mt.id = :id " : '');
    $groupBy = "";

    if (!$id && $onlyHavingUnit) {
        $terms .= " LEFT JOIN measure_units mu ON mu.type_id = mt.id WHERE mu.id IS NOT NULL ";
        $groupBy = " GROUP BY mt.id, mt.mt_name, mt.mt_description ";
    }
    
    $sql = "SELECT
                mt.id, mt.mt_name, mt.mt_description
            FROM 
                measure_types mt {$terms} 
                {$groupBy}
            ORDER BY mt_name";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getMeasureUnits
 * Retorna a listagem de unidades de medida ou de uma medida específica caso o id seja informado
 * Também pode ser filtrado pelo tipo de medida
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $type : id do tipo de medida
 * @param bool|null $onlyBaseUnit
 * 
 * @return array
 */
function getMeasureUnits(\PDO $conn, ?int $id = null, ?int $type = null, ?bool $onlyBaseUnit = false): array
{

    $terms = ($id ? " WHERE id = :id " : '');

    if (!$id && $type !== null) {
        $terms .= " WHERE type_id = :type ";
    }

    if (!$id && $onlyBaseUnit == true) {

        $terms .= ($terms ? " AND " : " WHERE ");
        $terms .= " equity_factor = 1 ";
    }
    
    $sql = "SELECT * FROM measure_units {$terms} ORDER BY unit_abbrev";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id); 
        } elseif ($type !== null) {
            $res->bindParam(':type', $type); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id || ($type !== null && $onlyBaseUnit == true))
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}




/**
 * renderMeasureUnitsByType
 * Retorna um conjuntos de tags span com as unidades de medida de um tipo informado
 *
 * @param \PDO $conn
 * @param int $unitType
 * 
 * @return string
 */
function renderMeasureUnitsByType(\PDO $conn, int $unitType): string
{
    $html = "";
    $newUnitArray = [];
    $units = getMeasureUnits($conn, null, $unitType);

    foreach ($units as $unit) {
        /* Adcionando uma coluna com o valor para ordenação */    
        if ($unit['operation'] == '/') {
            $unit['pos_value'] = (1 / $unit['equity_factor']);
        } elseif ($unit['operation'] == '*') {
            $unit['pos_value'] = (1 * $unit['equity_factor']);
        } else {
            $unit['pos_value'] = 1;
        }

        /* Novo array com o campo de valor do posicionamento */
        $newUnitArray[] = $unit; 
    }

    /* Ordena o array pela coluna específica */
    $pos = array_column($newUnitArray, 'pos_value');
    array_multisort($pos, SORT_ASC, $newUnitArray);

    $i = 0;
    foreach ($newUnitArray as $unit) {

        $signal = "";
        if ($i < (count($newUnitArray) -1)) {
            $signal = ($unit['pos_value'] < 1 ? '<i class="fas fa-less-than"></i>' : '<i class="fas fa-greater-than"></i>');
        }
        $color = ($unit['equity_factor'] == 1 ? 'warning' : 'info');
        $title = ($unit['equity_factor'] == 1 ? TRANS('REFERENCE_BASE') : "");

        $html .= '<span title="'.$title.'" class="badge badge-'.$color.' p-2 m-2 mb-4">'.$unit['unit_abbrev'].'</span>'. $signal;
        $i++;
    }

    return $html;

}


function calcUnitAbsValue (\PDO $conn, int $unit_id, float $value) : float
{
    $units = getMeasureUnits($conn, $unit_id);

    if ($units['operation'] == '/') {
        return ($value / $units['equity_factor']);
    }
    
    if ($units['operation'] == '*') {
        return ($value * $units['equity_factor']);
    } 
        
    return $value;
}


/**
 * setModelSpecsAbsValues
 * Grava o valor absoluto de cada característica do modelo - campo abs_value
 *
 * @param \PDO $conn
 * @param int $model_id
 * 
 * @return bool
 */
function setModelSpecsAbsValues (\PDO $conn, int $model_id): bool
{
    $specs = getModelSpecs($conn, $model_id);
    
    foreach ($specs as $spec) {
        $units = getMeasureUnits($conn, $spec['unit_id']);

        if ($units['operation'] == '/') {
            $abs_value = ($spec['spec_value'] / $units['equity_factor']);
        }
        elseif ($units['operation'] == '*') {
            $abs_value = ($spec['spec_value'] * $units['equity_factor']);
        } else {
            $abs_value = $spec['spec_value'];
        }

        $sql = "UPDATE model_x_specs
                SET abs_value = :abs_value
                WHERE id = :id";
        
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':abs_value', $abs_value);
            $res->bindParam(':id', $spec['spec_id']);
            $res->execute();
            
        } catch (Exception $e) {
            return false;
        }
    }

    return true;

}


/**
 * getModelsBySpecUnit
 * Retorna todos os modelos que possuem uma determinada unidade de medida
 *
 * @param \PDO $conn
 * @param int $unit_id
 * 
 * @return array
 */
function getModelsBySpecUnit (\PDO $conn, int $unit_id): array
{
    $sql = "SELECT DISTINCT model_id FROM model_x_specs WHERE measure_unit_id = :unit_id";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':unit_id', $unit_id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getMeasureTypeByMeasureUnit
 * Retorna o tipo de medida de uma unidade de medida fornecida
 *
 * @param \PDO $conn
 * @param int $unit_id
 * 
 * @return int
 */
function getMeasureTypeByMeasureUnit (\PDO $conn, int $unit_id): int
{
    $sql = "SELECT DISTINCT(type_id) FROM measure_units WHERE id = :unit_id";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':unit_id', $unit_id);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            return $row['type_id'];
        }
        return 0;
    } catch (Exception $e) {
        echo $e->getMessage();
        return 0;
    }
}



/**
 * modelHasAttribute
 * Retorna se um modelo atende às especificações de um atributo fornecido em um valor para comparação
 *
 * @param \PDO $conn
 * @param int $model_id
 * @param int $measure_unit_id
 * @param string $operation
 * @param float $comparison_value
 * 
 * @return bool
 */
function modelHasAttribute (\PDO $conn, int $model_id, int $measure_unit_id, string $operation, float $comparison_value): bool
{
    
    /* Gerar o valor absoluto a partir a measure_unit_id e do comparison_value */
    $abs_value = calcUnitAbsValue($conn, $measure_unit_id, $comparison_value);
    $measure_type = getMeasureTypeByMeasureUnit($conn, $measure_unit_id);

    $sql = "SELECT m.id FROM model_x_specs m, measure_units u 
            WHERE 
                m.model_id = :model_id AND 
                u.id = m.measure_unit_id AND
                u.type_id = {$measure_type} AND
                m.abs_value {$operation} :abs_value";
    
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':model_id', $model_id);
        $res->bindParam(':abs_value', $abs_value);

        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}



/**
 * getModelSpecs
 * Retorna as características existentes para o tipo de modelo informado
 * @param \PDO $conn
 * @param int $modelId
 * 
 * @return array
 */
function getModelSpecs(\PDO $conn, int $modelId): array
{
    $sql = "SELECT
                spec.id as spec_id, spec.spec_value, spec.abs_value,
                mt.mt_name, mt.id as type_id,
                mu.unit_name, mu.unit_abbrev, mu.id as unit_id
            FROM 
                model_x_specs spec,
                measure_types mt,
                measure_units mu
            WHERE 
                mu.id = spec.measure_unit_id AND
                mu.type_id = mt.id AND
                spec.model_id = :modelId
            ORDER BY mt_name";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':modelId', $modelId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getAssetsCategories
 * Retorna as categorias possíveis para os tipos de ativos ou uma categoria específica caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getAssetsCategories(\PDO $conn, ?int $id = null): array
{
    $terms = ($id ? " WHERE id = :id " : '');
    $sql = "SELECT * FROM assets_categories {$terms} ORDER BY cat_name";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getAssetsTypes
 * Retorna os tipos de ativos cadastrados - ou um tipo específico caso o id seja informado
 * Tipos para filtro: (1 - não são partes de outros tipos de ativos | 2 - podem ser partes de outros tipos de ativos)
 * 
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $type (1 - não são partes de outros tipos de ativos | 2 - podem ser partes de outros tipos de ativos)
 * @param array|null $category Categorias para filtro
 * @param int|null $inProfile Filtra a partir do perfil de campos para cadastro de ativos
 * 
 * @return array
 */
// function getAssetsTypes(\PDO $conn, ?int $id = null, ?int $type = null, ?int $category = null, ?int $inProfile = null, ?bool $andHasProfile = null): array
function getAssetsTypes(
    \PDO $conn, 
    ?int $id = null, 
    ?int $type = null, 
    ?array $category = null, 
    ?int $inProfile = null, 
    ?bool $andHasProfile = null,
    ?bool $isProduct = null,
    ?bool $isDigital = null
): array
{

/**
 * $type 1: ativos que não podem ser agregados a outros
 * $type 2: ativos que podem ser agregados a outros ativos (partes internas)
 * $inProfile null: desconsidera o filtro
 * $inProfile 0: ativos que não estão vinculados a nenhum perfil - não possuem um perfil - is null
 * $inProfile id: ativos que estão vinculados a um perfil específico
 */

$terms = ($id ? " WHERE t.tipo_cod = :id " : '');

if (!$id) {

    if ($category !== null && !empty($category)) {
        $terms .= ($terms ? " AND " : " WHERE ");

        $category = implode(",", $category);
        
        $terms .= " c.id IN (" . $category . ")";
    }

    if ($inProfile !== null) {
        $terms .= ($terms ? " AND " : " WHERE ");
        
        if ($andHasProfile === true) {
            $terms .= " (pt.profile_id = " . $inProfile . "  )";
        } elseif ($andHasProfile === false) {
            $terms .= " (pt.profile_id = " . $inProfile . " OR pt.profile_id IS NULL )";
        } else {
            $terms .= " pt.profile_id = " . $inProfile;
        }

        
    } elseif ($andHasProfile === true) {
        $terms .= ($terms ? " AND " : " WHERE ");
        $terms .= " pt.profile_id IS NOT NULL ";
    } elseif ($andHasProfile === false) {
        $terms .= ($terms ? " AND " : " WHERE ");
        $terms .= " pt.profile_id IS NULL ";
    }


    if ($isProduct !== null && $isProduct != 0) {

        $isProduct = (int) $isProduct;
        $terms .= ($terms ? " AND " : " WHERE ");
        // $terms .= " c.cat_is_product = " . $isProduct;
        $terms .= " t.can_be_product = " . $isProduct;
    }

    if ($isDigital !== null && $isDigital != 0) {

        $isDigital = (int) $isDigital;
        $terms .= ($terms ? " AND " : " WHERE ");
        // $terms .= " c.cat_is_product = " . $isDigital;
        $terms .= " t.is_digital = " . $isDigital;
    }
}

$sql = "SELECT 
            t.tipo_cod, t.tipo_nome, t.tipo_categoria, t.can_be_product, t.is_digital,
            c.cat_name, c.id, c.cat_description, c.cat_is_product, c.cat_is_digital, 
            p.profile_name, p.id as profile_id
            FROM 
                tipo_equip t 
            LEFT JOIN assets_categories c ON c.id = t.tipo_categoria
            LEFT JOIN profiles_x_assets_types pt ON pt.asset_type_id = t.tipo_cod
            LEFT JOIN assets_fields_profiles p ON p.id = pt.profile_id

            -- WHERE c.cat_is_product = 1 

            {$terms}
            ORDER BY 
                t.tipo_nome, c.cat_name
            ";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
        // return ["sql" => $sql];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage(), "sql" => $sql];
    }
}


/**
 * canBeChild
 * Retorna se um ativo qualquer pode ser filho de algum ativo
 * @param \PDO $conn
 * @param int $asset_id
 * 
 * @return bool
 */
function canBeChild (\PDO $conn, int $asset_id): bool
{
    $sql = "SELECT id FROM assets_types_part_of 
            WHERE
                child_id = :asset_id
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $asset_id);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    } 
    catch (Exception $e) {
        // return ['error' => $e->getMessage(), "sql" => $sql];
        return false;
    }

}

/**
 * getAssetsTypesPossibleParents
 * Retorna a listagem de tipos de ativos que podem ser pais do tipo informado
 * @param \PDO $conn
 * @param int $id
 * 
 * @return array
 */
function getAssetsTypesPossibleParents (\PDO $conn, int $id): array
{
    $sql = "SELECT 
                t.tipo_cod, t.tipo_nome,
                p.parent_id, p.child_id 
            FROM 
                assets_types_part_of p
            LEFT JOIN tipo_equip t ON p.parent_id = t.tipo_cod
            WHERE 
                p.child_id = :id
            ORDER BY 
                t.tipo_nome
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id); 
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getAssetsTypesByProfile
 * Retorna a listagem de tipos (asset_type_id) de tipos de ativos que estão vinculados a um perfil específico
 * @param \PDO $conn
 * @param int $profileId
 * 
 * @return array
 */
function getAssetsTypesByProfile (\PDO $conn, int $profileId): array
{
    $sql = "SELECT * FROM profiles_x_assets_types WHERE profile_id = :profileId";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':profileId', $profileId); 
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



function getAssetsTypesPossibleChilds (\PDO $conn, int $id): array
{
    $sql = "SELECT 
                t.tipo_cod, t.tipo_nome
                -- p.parent_id, p.child_id 
            FROM 
                assets_types_part_of p
            LEFT JOIN tipo_equip t ON p.child_id = t.tipo_cod
            WHERE 
                p.parent_id = :id
            ORDER BY 
                t.tipo_nome
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id); 
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getPossibleChildsFromManyAssetsTypes
 * Retorna a listagem de possíveis tipos de ativos filhos, normalizados, a partir de um array de tipos de ativos pais
 * @param \PDO $conn
 * @param array $assetTypes
 * 
 * @return array
 */
function getPossibleChildsFromManyAssetsTypes (\PDO $conn, array $assetTypes): array
{
    if (empty($assetTypes)){
        return [];
    }

    $data = [];
    $dataFiltered = [];

    /* Quantidade de tipos de ativos selecionados para o perfil */
    $countTypes = count($assetTypes);
    /* Definindo as variáveis dinâmicas como arrays */
    for ($i = 1; $i <= $countTypes; $i++){
        /* Será criado um array para cada tipo de ativo selecionado */
        ${'array'.$i} = [];
    }

    $i = 0;
    /* Cada array receberá a listagem de seus possíveis campos de configuração */
    foreach ($assetTypes as $type) {
        ${'array'.$i} = getAssetsTypesPossibleChilds($conn, $type);
        $i++;
    }

    /* Combinando todos os arrays */
    for ($i = 0; $i <= $countTypes; $i++){
        $data = array_merge($data, ${'array'.$i});
    }

    /* Removendo os valores repetidos */
    foreach ($data as $key => $value) {
        $dataFiltered = (in_array($value, $dataFiltered)) ? $dataFiltered : array_merge($dataFiltered, [$value]);
    }

    return $dataFiltered;
}



/**
 * getAssetTypesFromIds
 * Retorna a listagem de tipos de ativos a partir de uma string de IDs
 * @param \PDO $conn
 * @param string $ids
 * 
 * @return array
 */
function getAssetTypesFromIds(\PDO $conn, ?string $ids): array
{

    if (!$ids) {
        return [];
    }
    
    $sql = "SELECT 
                t.tipo_cod, t.tipo_nome, t.tipo_categoria, t.is_part_of, 
                c.cat_name, c.id, c.cat_description
                FROM 
                    tipo_equip t 
                LEFT JOIN assets_categories c ON c.id = t.tipo_categoria 
                WHERE t.tipo_cod IN ({$ids})
                ORDER BY 
                    t.tipo_nome, c.cat_name
                ";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}




/**
 * getAssetsRequiredInfo
 * Retorna a lista dos campos do perfil: 0 para não obrigatório, 1 para obrigatório
 * @param \PDO $conn
 * @param int $profileId
 * 
 * @return array
 */
function getAssetsRequiredInfo (\PDO $conn, int $profileId): array
{
    
    $fields = [];
    
    $sql = "SELECT 
                *
            FROM 
                assets_fields_required
            WHERE 
                profile_id = :profileId ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':profileId', $profileId);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $fields[$row['field_name']] = $row['field_required'];
            }
            return $fields;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getAssetsModels
 * Retorna a listagem de modelos de tipos de ativos com base nos parâmetros fornecidos
 * Pode ser filtrado por tipo de ativo e também pelo fabricante
 * @param \PDO $conn
 * @param int|null $modelId
 * @param int|null $assetTypeId
 * @param int|null $manufacturerId
 * 
 * @return array
 */
function getAssetsModels(
    \PDO $conn, 
    ?int $modelId = null, 
    ?int $assetTypeId = null, 
    ?int $manufacturerId = null,
    ?bool $isProduct = null,
    ?array $orderBy = null, 
    ?bool $hasProductRegistered = null
): array
{

    $defaultOrderBy = "m.marc_nome, t.tipo_nome";

    if ($orderBy !== null) {
        $defaultOrderBy = implode(",", $orderBy);
    }
    
    $termsFrom = "";
    $termsGroupBy = "";
    $terms = ($modelId ? " AND m.marc_cod = :modelId " : '');

    if (!$modelId) {
        $terms .= ($assetTypeId ? " AND t.tipo_cod = :assetTypeId " : '');
        $terms .= ($manufacturerId ? " AND m.marc_manufacturer = :manufacturerId " : '');


        if ($isProduct !== null && $isProduct != 0) {
            $isProduct = (int) $isProduct;
            // $terms .= ($terms ? " AND " : " WHERE ");
            // $terms .= " AND c.cat_is_product = " . $isProduct;
            $terms .= " AND t.can_be_product = " . $isProduct;
        }

        
        if ($hasProductRegistered !== null && $hasProductRegistered != 0) {
            
            $terms .= " AND e.comp_marca = m.marc_cod AND e.is_product = 1 ";
            $termsFrom = ", equipamentos e ";
            $termsGroupBy = "GROUP BY 
                                m.marc_cod, 
                                m.marc_manufacturer, 
                                m.marc_nome, 
                                t.tipo_nome, 
                                t.tipo_cod, 
                                t.can_be_product, 
                                c.cat_name, 
                                c.cat_is_product, 
                                f.fab_nome";
        }
    }



    
    $sql = "SELECT 
                m.marc_cod as codigo, 
                m.marc_manufacturer as fabricante_cod,
                m.marc_nome as modelo, 
                t.tipo_nome as tipo, 
                t.tipo_cod as tipo_cod,
                t.can_be_product,
                c.cat_name, 
                c.cat_is_product,
                f.fab_nome as fabricante
            FROM 
				tipo_equip as t  
                LEFT JOIN assets_categories c ON c.id = t.tipo_categoria, 
                marcas_comp m LEFT JOIN fabricantes f on f.fab_cod = m.marc_manufacturer
                {$termsFrom}
            WHERE 
                m.marc_tipo = t.tipo_cod {$terms}
            {$termsGroupBy}
            ORDER BY $defaultOrderBy
            
            ";

    try {
        $res = $conn->prepare($sql);
        if ($modelId) {
            $res->bindParam(':modelId', $modelId);
        }
        if ($assetTypeId) {
            $res->bindParam(':assetTypeId', $assetTypeId);
        }
        if ($manufacturerId) {
            $res->bindParam(':manufacturerId', $manufacturerId);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($modelId)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['sql' => $sql, 'error' => $e->getMessage()];
    }
}




function getPriceFromAssetModel (\PDO $conn, int $assetModelId): array
{
    $sql = "SELECT 
                MAX(c.comp_cod) AS codigo,
                c.comp_valor
            FROM
                equipamentos c
            WHERE
                c.comp_marca = :assetModelId
            GROUP BY 
                c.comp_cod, c.comp_valor
            ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':assetModelId', $assetModelId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
    
}



/**
 * getResourcesFromTicket
 * Retorna a listagem de recursos alocados para o ticket informado
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getResourcesFromTicket (\PDO $conn, int $ticket): array
{
    // table: tickets_x_resources
    $sql = "SELECT * 
            FROM 
                tickets_x_resources 
            WHERE 
                ticket = :ticket AND
                is_current = 1
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * hasResources
 * Retorna se o chamado informado possui o(s) recurso(s) informado(s)
 * @param \PDO $conn
 * @param int $ticket
 * @param array $resourceModelIds
 * 
 * @return bool
 */
function hasResources(\PDO $conn, int $ticket, array $resourceModelIds = []): bool
{
    /* Se forem informados recursos */
    if (!empty($resourceModelIds)) {
        $resourceModelIds = array_map('intval', $resourceModelIds);
        $stringResourcesIds = implode(",", $resourceModelIds);

        $sql = "SELECT 
                    id
                FROM 
                    tickets_x_resources 
                WHERE 
                    ticket = :ticket AND
                    model_id IN ({$stringResourcesIds}) AND
                    is_current = 1
                    ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
            $res->execute();
            if ($res->rowCount()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /* Caso nenhum recurso seja informado, entao apenas verifico se possui algum recurso para o chamado */
    $sql = "SELECT
                id
            FROM
                tickets_x_resources
            WHERE
                ticket = :ticket AND
                is_current = 1
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}


/**
 * getTotalPricesFromTicket
 * Retorna o valor total de recursos alocados no chamado
 * O valor retornado não é formatado
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return float
 */
function getTotalPricesFromTicket(\PDO $conn, int $ticket): float
{
    $sql = "SELECT COALESCE(SUM(unitary_price * amount), 0) AS total_price 
            FROM 
                tickets_x_resources 
            WHERE 
                ticket = :ticket AND
                is_current = 1
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['total_price'];
        }
        return 0;
    } catch (\PDOException $e) {
        echo $e->getMessage();
        return 0;
    }
}



/**
 * getAreasToOpen
 * Retorna um array com as informacoes das areas possiveis de receberem chamados do usuario logado
 * sis_id , sistema
 * @param PDO $conn
 * @return array
 */
function getAreasToOpen(\PDO $conn, string $userAreas): array
{
    if (empty($userAreas))
        return [];
    $userAreas = filter_var($userAreas, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $table = getTableCompat($conn);
    $sql = "SELECT s.sis_id, s.sistema 
            FROM sistemas s, {$table} a 
            WHERE
                s.sis_status = 1  AND
                s.sis_atende = 1  AND 
                s.sis_id = a.area AND 
                a.area_abrechamado IN (:userAreas) 
            GROUP BY 
                sis_id, sistema 
            ORDER BY sistema";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userAreas', $userAreas, PDO::PARAM_STR);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * setBasicProfile
 * Retorna um array com as informacoes padrão do perfil básico para cadastro de ativos
 *
 * @return array
 */
function setBasicProfile(): array
{
    return [
        'id' => '0',
        'profile_name' => 'Basic',
        'asset_type' => '1',
        'manufacturer' => '1',
        'model' => '1',
        'department' => '1',
        'asset_unit' => '1',
        'asset_tag' => '1',
        'serial_number' => '1',
        'part_number' => '0',
        'situation' => '1',
        'net_name' => '0',
        'invoice_number' => '1',
        'cost_center' => '0',
        'price' => '1',
        'buy_date' => '1',
        'supplier' => '1',
        'assistance_type' => '0',
        'warranty_type' => '1',
        'warranty_time' => '1',
        'extra_info' => '1',
        'field_specs_ids' => '',
        'field_custom_ids' => ''
    ];
}


/* Define os campos básicos obrigatórios para cadastro de ativos */
function setBasicRequired(): array
{
    return [
        'asset_type' => 1,
        'manufacturer' => 1,
        'model' => 1,
        'asset_unit' => '1',
        'department' => 1,
        'asset_tag' => '1'
    ];
}


/**
 * getAssetsProfiles
 * Retorna a listagem de perfis de ativos ou um perfil específico caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $assetTypeId : filtra para encontrar o perfil vinculado ao tipo de ativo
 * 
 * @return array
 */
function getAssetsProfiles (\PDO $conn, ?int $id = null, ?int $assetTypeId = null): array
{
    $terms = ($id ? " WHERE id = :id " : '');

    if (!$id) {

        if ($assetTypeId) {
            $terms .= "LEFT JOIN profiles_x_assets_types pa ON 
                        pa.profile_id = p.id 
                        WHERE 
                            pa.asset_type_id = :assetTypeId 
                            ";
        }
    }

    $sql = "SELECT p.* 
            FROM assets_fields_profiles p 
             
            {$terms} ORDER BY profile_name";
    $res = $conn->prepare($sql);
    
    try {
        if ($id) {
            $res->bindParam(':id', $id); 
        } elseif ($assetTypeId) {
            $res->bindParam(':assetTypeId', $assetTypeId); 
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}




/**
 * getOperationalStates
 * Retorna a listagem de situações operacionais ou uma situação específica caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getOperationalStates(\PDO $conn, ?int $id = null): array
{
    
    $terms = ($id ? " WHERE situac_cod = :id " : '');

    $sql = "SELECT * FROM situacao {$terms} ORDER BY situac_nome";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getSuppliers
 * Retorna a listagem de fornecedores ou um fornecedor específico
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getSuppliers(\PDO $conn, ?int $id = null): array
{
    $terms = ($id ? " WHERE forn_cod = :id " : '');

    $sql = "SELECT * FROM fornecedores {$terms} ORDER BY forn_nome";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getAssistances
 * Retorna a listagem de assistências ou uma assistência em específico
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getAssistancesTypes(\PDO $conn, ?int $id = null): array
{
    $terms = ($id ? " WHERE assist_cod = :id " : '');

    $sql = "SELECT * FROM assistencia {$terms} ORDER BY assist_desc";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getWarrantiesTypes
 * Retorna a listagem de tipos de garantias ou um tipo de garantia específico
 *
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getWarrantiesTypes(\PDO $conn, ?int $id = null): array
{
    $terms = ($id ? " WHERE tipo_garant_cod = :id " : '');

    $sql = "SELECT * FROM tipo_garantia {$terms} ORDER BY tipo_garant_nome";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getWarrantiesTimes
 * Retorna os tempos possíveis para garantias ou um registro específico
 * @param \PDO $conn
 * @param int|null $id
 * 
 * @return array
 */
function getWarrantiesTimes(\PDO $conn, ?int $id = null): array
{
    $terms = ($id ? " WHERE tempo_cod = :id " : '');

    $sql = "SELECT * FROM tempo_garantia {$terms} ORDER BY tempo_meses";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getAssetSpecs
 * Retorna um array com os IDs e outras informações dos modelos de todos os tipos de ativos que o compõe
 * @param \PDO $conn
 * @param int $assetId
 * @param bool|null $isDigital
 * @param bool|null $isProduct
 * 
 * @return array
 */
function getAssetSpecs(
    \PDO $conn, 
    int $assetId, 
    ?bool $hasTag = null, 
    ?bool $isDigital = null, 
    ?bool $isProduct = null
): array
{
    $terms = '';
    if ($hasTag !== null) {
        $terms = ($hasTag ? " a.asset_spec_tagged_id IS NOT NULL AND" : " a.asset_spec_tagged_id IS NULL AND");
    }

    if ($isDigital !== null) {

        $isDigital = ($isDigital == true || $isDigital == 1 ? 1 : 0);
        
        if ($isDigital == 0) {
            $terms.= (strlen((string)$terms) ? " AND (t.is_digital = 0 OR t.is_digital IS NULL) AND " : " (t.is_digital = 0 OR t.is_digital IS NULL) AND ");
        } else {
            $terms.= (strlen((string)$terms) ? " AND t.is_digital = 1 AND " : " t.is_digital = 1 AND ");
        }
        
    }

    if ($isProduct !== null) {

        $isProduct = ($isProduct == true || $isProduct == 1 ? 1 : 0);
        
        // $terms.= (strlen((string)$terms) ? " AND cat.cat_is_product = {$isProduct} AND " : " cat.cat_is_product = {$isProduct} AND ");
        $terms.= (strlen((string)$terms) ? " AND e.is_product = {$isProduct} AND " : " e.is_product = {$isProduct} AND ");
    }
    
    
    $sql = "SELECT t.*, m.*, a.*, e.comp_inst, e.comp_inv, cat.*, 
                    fab.fab_nome
            FROM 
                ((tipo_equip t, marcas_comp m, fabricantes fab, 
                assets_x_specs a
            LEFT JOIN 
                equipamentos e on e.comp_cod = a.asset_spec_tagged_id) 
            LEFT JOIN 
                assets_categories cat ON cat.id = t.tipo_categoria) 
            WHERE 
                {$terms}
                a.asset_id = :id AND
                a.asset_spec_id = m.marc_cod AND
                m.marc_tipo = t.tipo_cod AND
                m.marc_manufacturer = fab.fab_cod
            ORDER BY
                t.tipo_nome, m.marc_nome    
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $assetId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        // return [$sql => $sql];
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getAssetCategoryInfo
 * Retorna array com as informações sobre a categoria do ativo informado
 *
 * @param \PDO $conn
 * @param int $assetId
 * 
 * @return array
 */
function getAssetCategoryInfo(\PDO $conn, int $assetId): array
{
    $sql = "SELECT 
                cat.*
            FROM
                equipamentos a, tipo_equip t
                LEFT JOIN assets_categories cat ON cat.id = t.tipo_categoria

            WHERE
                a.comp_tipo_equip = t.tipo_cod AND 
                a.comp_cod = :id
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $assetId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getAssetParentId
 * Retorna o registro com ID, etiqueta e codigo de unidade do ativo pai de um ativo informado - caso não exista retorna vazio
 * @param \PDO $conn
 * @param int $assetId
 * 
 * @return array
 */
function getAssetParentId(\PDO $conn, int $assetId) :array
{
    $sql = "SELECT 
                a.asset_id, e.comp_inv, e.comp_inst
            FROM 
                assets_x_specs a, equipamentos e
            WHERE 
                a.asset_spec_tagged_id = :id AND a.asset_id = e.comp_cod ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $assetId);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * isAssetModelFreeToLink
 * Retorna se existem ativos com o modelo informado e se estão disponíveis para serem vinculados a outro ativo pai
 *
 * @param \PDO $conn
 * @param int $modelId
 * 
 * @return bool
 */
function isAssetModelFreeToLink(\PDO $conn, int $modelId) :bool
{
    $sql = "SELECT 
                comp_cod
            FROM 
                equipamentos
            WHERE 
                comp_marca = :id AND
                (is_product = 0 OR is_product IS NULL)
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $modelId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $sql = "SELECT 
                            asset_id
                        FROM 
                            assets_x_specs
                        WHERE 
                            asset_spec_tagged_id = :id ";
                $res = $conn->prepare($sql);
                $res->bindParam(':id', $row['comp_cod']);
                $res->execute();
                
                if (!$res->rowCount()) {
                    /* Se pelo menos um dos ativos nao está vinculado, então retorna true */
                    return true;
                }
            }
        }
        /* Não existe ativo cadastrado com o modelo informado */
        return false;
    }
    catch (Exception $e) {
        // return ['error' => $e->getMessage()];
        return false;
    }
}



/**
 * getAssetIdFromTag
 * Retorna o ID do ativo a partir do código da unidade e da etiqueta
 *
 * @param \PDO $conn
 * @param int $unit
 * @param string $tag
 * 
 * @return int|null
 */
function getAssetIdFromTag(\PDO $conn, int $unit, string $tag): ?int
{
    $sql = "SELECT 
                comp_cod
            FROM 
                equipamentos
            WHERE 
                comp_inst = :unit AND comp_inv = :tag ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':unit', $unit);
        $res->bindParam(':tag', $tag);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['comp_cod'];
        }
        return null;
    }
    catch (Exception $e) {
        // return ['error' => $e->getMessage()];
        return null;
    }
}



/**
 * assetHasParent
 * Retorna se o ativo informado possui ativo pai
 * Indica que o ativo é filho de outro ativo
 *
 * @param \PDO $conn
 * @param int $asset_id
 * 
 * @return bool
 */
function assetHasParent(\PDO $conn, int $asset_id): bool
{
    $sql = "SELECT 
                asset_id
            FROM 
                assets_x_specs
            WHERE 
                asset_spec_tagged_id = :id ";
    
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $asset_id);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
        // return ['error' => $e->getMessage()];
    }
}



/**
 * getAssetDescendants
 * Retorna um array com os ativos descendentes do ativo principal - até a quinta geração
 * @param \PDO $conn
 * @param int $asset_id
 * 
 * @return array
 */
function getAssetDescendants (\PDO $conn, int $asset_id) :array
{
    /* Colocar em um array flat todos os ativos filhos e filhos de filhos do ativo principal - até a quinta geração */

    /* Primeira etapa - obter os primeiros filhos */
    $sql = "SELECT * FROM assets_x_specs WHERE asset_id = :id AND asset_spec_tagged_id IS NOT NULL";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $asset_id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }

            /* Segunda etapa - obter os filhos dos filhos */
            foreach ($data as $row) {
                $sql = "SELECT * FROM assets_x_specs WHERE asset_id = :id AND asset_spec_tagged_id IS NOT NULL";
                $res = $conn->prepare($sql);
                $res->bindParam(':id', $row['asset_spec_tagged_id']);
                $res->execute();
                if ($res->rowCount()) {
                    foreach ($res->fetchAll() as $row) {
                        $data[] = $row;
                    }

                    /* Terceira etapa - obter os filhos dos filhos dos filhos */
                    foreach ($data as $row) {
                        $sql = "SELECT * FROM assets_x_specs WHERE asset_id = :id AND asset_spec_tagged_id IS NOT NULL";
                        $res = $conn->prepare($sql);
                        $res->bindParam(':id', $row['asset_spec_tagged_id']);
                        $res->execute();
                        if ($res->rowCount()) {
                            foreach ($res->fetchAll() as $row) {
                                $data[] = $row;
                            }

                            /* Quarto etapa - obter os filhos dos filhos dos filhos dos filhos */
                            foreach ($data as $row) {
                                $sql = "SELECT * FROM assets_x_specs WHERE asset_id = :id AND asset_spec_tagged_id IS NOT NULL";
                                $res = $conn->prepare($sql);
                                $res->bindParam(':id', $row['asset_spec_tagged_id']);
                                $res->execute();
                                if ($res->rowCount()) {
                                    foreach ($res->fetchAll() as $row) {
                                        $data[] = $row;
                                    }

                                    /* Quinta etapa - obter os filhos dos filhos dos filhos dos filhos dos filhos */
                                    foreach ($data as $row) {
                                        $sql = "SELECT * FROM assets_x_specs WHERE asset_id = :id AND asset_spec_tagged_id IS NOT NULL";
                                        $res = $conn->prepare($sql);
                                        $res->bindParam(':id', $row['asset_spec_tagged_id']);
                                        $res->execute();
                                        if ($res->rowCount()) {
                                            foreach ($res->fetchAll() as $row) {
                                                $data[] = $row;
                                            }
                                        }
                                    }

                                }
                            }

                        }
                    }
                }
            }

            /* Retorna o array final com os filhos até a quinta geração (se existirem) */
            return array_unique($data, SORT_REGULAR);

        }
        else {
            return [];
        }
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}






/**
 * modelHasSavedSpecs
 * Retorna se o modelo informado possui especificações salvas
 *
 * @param \PDO $conn
 * @param int $modelId
 * 
 * @return bool
 */
function modelHasSavedSpecs (\PDO $conn, int $modelId): bool 
{
    $sql = "SELECT id FROM model_x_child_models WHERE model_id = :id";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $modelId);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
        // return ['error' => $e->getMessage()];
    }
}


/**
 * getSavedSpecs
 * Retorna a listagem de modelos filhos de um modelo informado
 *
 * @param \PDO $conn
 * @param int $modelId
 * 
 * @return array
 */
function getSavedSpecs (\PDO $conn, int $modelId): array
{
    $sql = "SELECT 
                id,
                model_id,
                model_child_id
            FROM 
                model_x_child_models
            WHERE 
                model_id = :id";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $modelId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}




/**
 * updateAssetDepartment
 * Faz o update do departamento para o ativo informado - 
 * Desconsidera a unidade do ativo e unidade do departamento visto que é possível que 
 * um ativo possa estar registrado em uma unidade mas estar localizado em outra.
 *
 * @param \PDO $conn
 * @param int $assetId
 * @param int $departmentId
 * 
 * @return bool
 */
function updateAssetDepartment(\PDO $conn, int $assetId, int $departmentId): bool
{
    $sql = "UPDATE 
                equipamentos 
            SET 
                comp_local = :dep 
            WHERE 
                comp_cod = :id";
    try {
        $res = $conn->prepare($sql);
        
        $res->bindParam(':dep', $departmentId);
        $res->bindParam(':id', $assetId);
        $res->execute();
        return true;
    }
    catch (Exception $e) {
        // return ['error' => $e->getMessage()];
        return false;
    }
}




/**
 * insertNewDepartmentInHistory
 * Insere um novo departamento na tabela de histórico de departamentos para o ativo informado
 * Primeiro faz a checagem se o departamento atual já o departamento informado
 *
 * @param \PDO $conn
 * @param int $assetId
 * @param int $departmentId
 * @param int $userId
 * 
 * @return bool
 */
function insertNewDepartmentInHistory(\PDO $conn, int $assetId, int $departmentId, int $userId) :bool
{
    
    $sql = "SELECT 
                hist_cod 
            FROM 
                historico 
            WHERE 
                asset_id = :asset_id AND 
                hist_local = :department_id AND
                hist_cod = (SELECT MAX(hist_cod) FROM historico WHERE asset_id = :asset_id)";
    
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $assetId);
        $res->bindParam(':department_id', $departmentId);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        $sql = "INSERT INTO 
                        historico
                    (
                        asset_id,
                        hist_local,
                        hist_user
                    )
                    VALUES
                    (
                        :asset_id,
                        :department_id,
                        :user_id
                    )";
            try {
                $res = $conn->prepare($sql);
                $res->bindParam(':asset_id', $assetId);
                $res->bindParam(':department_id', $departmentId);
                $res->bindParam(':user_id', $userId);
                $res->execute();
                if ($res->rowCount()) {
                    return true;
                }
                return false;
            }
            catch (Exception $e) {
                // return ['error' => $e->getMessage()];
                return false;
            }
    }
    catch (Exception $e) {
        // return ['error' => $e->getMessage()];
        return false;
    }
    
}


/**
 * updateAssetDepartamentAndHistory
 * Atualiza o departamento e o histórico de localização de um ativo individual, bem como seus filhos
 *
 * @param \PDO $conn
 * @param int $assetId
 * @param int $author
 * @param int|null $departmentId
 * 
 * @return bool
 */
function updateAssetDepartamentAndHistory (\PDO $conn, int $assetId, int $author, ?int $departmentId = null): bool
{
    $updateAssetDepartment = updateAssetDepartment($conn, $assetId, $departmentId);

    $newDepartmentInHistory = insertNewDepartmentInHistory($conn, $assetId, $departmentId, $author);
            
    /* Fazer a atualização do departamento também para os ativos filhos e também gravar a modificação no histórico*/
    $children = getAssetDescendants($conn, $assetId);
    foreach ($children as $child) {
        updateAssetDepartment($conn, $child['asset_spec_tagged_id'], $departmentId);
        $newDepartmentInHistory = insertNewDepartmentInHistory($conn, $child['asset_spec_tagged_id'], $departmentId, $author);
    }

    return $updateAssetDepartment && $newDepartmentInHistory;
}


/**
 * updateUserAssetsDepartment
 * Atualiza o departamento de todos os ativos e ativos filhos alocados para um usuário
 * Também atualiza o histórico tanto dos ativos quanto dos ativos filhos
 *
 * @param \PDO $conn
 * @param int $userId
 * @param int $departmentId
 * @param int $author
 * 
 * @return bool
 */
function updateUserAssetsDepartment(\PDO $conn, int $userId, int $author, ?int $departmentId = null): bool
{
    $updateAssetDepartment = true;
    $user_assets = getAssetsFromUser($conn, $userId);

    if (!empty($user_assets) && !empty($departmentId)) {
        foreach ($user_assets as $asset) {
            $updateAssetDepartment = updateAssetDepartamentAndHistory($conn, $asset['asset_id'], $author, $departmentId);
        }
    }
    return ($updateAssetDepartment);

}



/**
 * updateAssetsTookOffUser
 * Função a ser utilizada para atualizar ativos vinculados a usuários que forem desativados ou excluídos do sistema
 * @param \PDO $conn
 * @param int $user_id
 * @param int $author
 * @param int|null $newDepartment
 * 
 * @return bool
 */
function updateAssetsTookOffUser(\PDO $conn, int $user_id, int $author, ?int $newDepartment = null): bool
{
    $userAssetsInfo = getAssetsFromUser($conn, $user_id);
    $count_userAssetsInfo = count($userAssetsInfo);
    $assets_ids = [];   
    $textAssetIds = "";

    if ($count_userAssetsInfo > 0) {
        foreach ($userAssetsInfo as $key => $value) {
            $assets_ids[] = $value['asset_id'];
        }
        $textAssetIds = implode(',', $assets_ids);

        if (empty($textAssetIds)) 
            return false;

        $sql = "UPDATE users_x_assets SET is_current = 0 WHERE user_id = :user_id AND asset_id IN ({$textAssetIds})";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':user_id', $user_id);
            $res->execute();
            
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return false;
        }

        if ($newDepartment) {
            foreach ($assets_ids as $asset_id) {
                $updateAssetsDepartment = updateAssetDepartamentAndHistory($conn, $asset_id, $author, $newDepartment);
            }
        }
        return true;
    }
    return true;
}



/**
 * insertNewAssetSpecChange
 * Insere um novo registro de log de alteração de especificação para o ativo informado
 *
 * @param \PDO $conn
 * @param int $asset_id
 * @param int $spec_id
 * @param int $user_id
 * 
 * @return bool
 */
function insertNewAssetSpecChange (\PDO $conn, int $asset_id, int $spec_id, string $action, int $user_id) :bool
{
    
    $actions = [
        'add',
        'remove'
    ];

    if (!in_array($action, $actions)) {
        echo "Only add or remove are allowed";
        return false;
    }

    $sql = "INSERT INTO 
                assets_x_specs_changes
            (
                asset_id,
                spec_id,
                action,
                user_id
            )
            VALUES
            (
                :asset_id,
                :spec_id,
                :action,
                :user_id
            )";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $asset_id);
        $res->bindParam(':spec_id', $spec_id);
        $res->bindParam(':action', $action);
        $res->bindParam(':user_id', $user_id);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
        // return ['error' => $e->getMessage()];
    }
}



/**
 * getAssetSpecsChanges
 * Retorna a listagem de regitros de modificações de especificações de um ativo informado
 *
 * @param \PDO $conn
 * @param int $asset_id
 * 
 * @return array
 */
function getAssetSpecsChanges (\PDO $conn, int $asset_id) :array
{
    $sql = "SELECT 
                a.id,
                a.asset_id,
                a.spec_id,
                a.updated_at,
                a.action,
                t.tipo_nome,
                f.fab_nome,
                m.marc_nome,
                u.nome 
            FROM 
                assets_x_specs_changes a, usuarios u, tipo_equip t, marcas_comp m, fabricantes f
            WHERE 
                a.asset_id = :asset_id AND 
                a.spec_id = m.marc_cod AND
                a.user_id = u.user_id AND 
                m.marc_tipo = t.tipo_cod AND 
                m.marc_manufacturer = f.fab_cod

            ORDER BY 
                a.updated_at DESC
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $asset_id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


function getAssetDepartmentsChanges (\PDO $conn, int $asset_id): array
{
    $asset_info = getEquipmentInfo($conn, null, null, $asset_id);
    $asset_tag = $asset_info['comp_inv'];
    $asset_unit = $asset_info['comp_inst'];

    $data = [];

    /* A partir da versão 5 a consulta é apenas pelo asset_id */
    $sql = "SELECT 
                l.local,
                i.inst_nome,
                COALESCE (i.inst_nome, 'N/A') AS unidade,
                h.hist_data,
                u.nome
            FROM 
                historico h, usuarios u, localizacao l
                LEFT JOIN instituicao i ON i.inst_cod = l.loc_unit
            WHERE 
                h.asset_id = :asset_id AND
                h.hist_user = u.user_id AND
                h.hist_local = l.loc_id
            ORDER BY 
                h.hist_data DESC
        ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $asset_id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            // return $data;
        }
        // return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }


    /* Até a versão 4 a consulta utiliza a etiqueta e a unidade do ativo */
    $sql = "SELECT 
                l.local,
                COALESCE (i.inst_nome, 'N/A') AS unidade,
                h.hist_data,
                u.nome
            FROM 
            localizacao l 
                LEFT JOIN instituicao i ON i.inst_cod = l.loc_unit,
            historico h 
                LEFT JOIN usuarios u ON u.user_id = h.hist_user
            WHERE 
                h.hist_inv = :asset_tag AND
                h.hist_inst = :asset_unit AND
                h.hist_local = l.loc_id
            ORDER BY 
                h.hist_data DESC
        ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_tag', $asset_tag);
        $res->bindParam(':asset_unit', $asset_unit);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            // return $data;
        }
        // return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }

    return $data;
}



function getUsersFromAssetChanges(\PDO $conn, int $asset_id) :array
{
    $sql = "SELECT 
                u.user_id, 
                u.nome as usuario,
                cl.nickname as cliente,
                aut.nome as autor,
                e.comp_inv,
                e.comp_inst,
                ua.created_at,
                ua.updated_at,
                ua.author_id,
                ua.is_current
            FROM
                usuarios as aut,
                equipamentos as e,
                users_x_assets as ua,
                usuarios as u
                LEFT JOIN clients cl ON cl.id = u.user_client
            WHERE
                u.user_id = ua.user_id AND
                aut.user_id = ua.author_id AND
                ua.asset_id = e.comp_cod AND
                e.comp_cod = :asset_id
            ORDER BY
                ua.created_at DESC";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
    return [];
}




function getAssetsFromUserChanges(\PDO $conn, int $user_id) :array
{
    $sql = "SELECT 
                u.user_id, 
                u.nome as usuario,
                aut.nome as autor,
                e.comp_inv,
                e.comp_inst,
                t.tipo_nome,
                f.fab_nome,
                m.marc_nome,
                cl.nickname as cliente,
                i.inst_nome,
                ua.created_at,
                ua.updated_at,
                ua.author_id,
                ua.is_current
            FROM
                usuarios as u,
                usuarios as aut,
                equipamentos as e,
                users_x_assets as ua,
                tipo_equip as t,
                fabricantes as f,
                marcas_comp as m,
                instituicao as i
                LEFT JOIN clients as cl ON cl.id = i.inst_client
            WHERE
                u.user_id = ua.user_id AND
                aut.user_id = ua.author_id AND
                ua.asset_id = e.comp_cod AND
                e.comp_tipo_equip = t.tipo_cod AND
                e.comp_fab = f.fab_cod AND
                e.comp_marca = m.marc_cod AND
                i.inst_cod = e.comp_inst AND
                u.user_id = :user_id

            ORDER BY
                ua.created_at DESC";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
    return [];
}



function setUserNotification(
    \PDO $conn, 
    int $user_id,
    int $type,
    string $text,
    int $author_id) :bool
{
    $sql = "INSERT INTO users_notifications
                (user_id, type, text, author)
            VALUES
                (:user_id, :type, :text, :author)
    ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $res->bindParam(':type', $type, PDO::PARAM_INT);
        $res->bindParam(':text', $text, PDO::PARAM_STR);
        $res->bindParam(':author', $author_id, PDO::PARAM_INT);
        
        $res->execute();
        return true;
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return false;
    }
}

function getUnreadUserNotifications(\PDO $conn, int $user_id) :array
{
    $sql = "SELECT  
                n.id,
                n.user_id,
                n.type,
                n.text,
                u.nome as author,
                n.created_at
            FROM 
                users_notifications n, 
                usuarios u 
            WHERE 
                n.author = u.user_id AND
                n.user_id = :user_id AND 
                seen_at IS NULL
            ORDER BY
                n.created_at DESC
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

function getSeenUserNotifications(\PDO $conn, int $user_id) :array
{
    $sql = "SELECT  
                n.id,
                n.user_id,
                n.type,
                n.text,
                u.nome as author,
                n.created_at
            FROM 
                users_notifications n, 
                usuarios u 
            WHERE 
                n.author = u.user_id AND
                n.user_id = :user_id AND 
                seen_at IS NOT NULL
            ORDER BY
                n.created_at DESC
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

function setUserNotificationSeen(\PDO $conn, int $notification_id) :bool
{
    $now = date('Y-m-d H:i:s');
    $sql = "UPDATE users_notifications
            SET
                seen_at = :now
            WHERE
                id = :notification_id";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':now', $now, PDO::PARAM_STR);
        $res->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
        
        $res->execute();
        return true;
    }
    catch (Exception $e) {
        return false;
    }
}



function setUserTicketNotice(\PDO $conn, string $sourceTable, int $notice_id, ?int $type = null) :bool
{

    $sourceTable = noHtml($sourceTable);

    $sql = "INSERT INTO users_tickets_notices
                (`type`,source_table, notice_id)
            VALUES
                (:type, :source_table, :notice_id)";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':source_table', $sourceTable, PDO::PARAM_STR);
        $res->bindParam(':notice_id', $notice_id, PDO::PARAM_INT);
        $res->bindParam(':type', $type, PDO::PARAM_INT);
        
        $res->execute();
        return true;
    }
    catch (Exception $e) {
        return false;
    }
}


function getUnreadNoticesIDsFromTicket (\PDO $conn, int $ticket, ?bool $isTreater = false): ?array 
{
    $terms = ($isTreater ? " n.treater_seen_at IS NULL" : " n.requester_seen_at IS NULL");

    $sql = "SELECT
                n.notice_id
            FROM
                users_tickets_notices AS n,
                ocorrencias AS o,
                assentamentos AS a
            WHERE
                a.ocorrencia = o.numero AND
                o.numero = :ticket AND
                n.source_table = 'assentamentos' AND 
                n.notice_id = a.numero AND 
                {$terms}
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, \PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount() > 0) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row['notice_id'];
            }
            return $data;
        }
        return null;
    } catch (\PDOException $e) {
        echo $e->getMessage();
        return null;
    }
}

function setUserTicketNoticeSeen(\PDO $conn, string $sourceTable, array $notice_ids, ?bool $isTreater = false) :bool
{
    
    if (empty($notice_ids)) {
        return false;
    }
    
    $notice_ids = array_map(function ($value) {
        return (int)$value;
    }, $notice_ids);

    $text_ids = implode(', ', $notice_ids);
    
    $terms = " requester_seen_at = :now ";
    $now = date('Y-m-d H:i:s');
    if ($isTreater) {
        $terms = " treater_seen_at = :now ";
    }
    
    
    $sql = "UPDATE users_tickets_notices
            SET
                {$terms}
            WHERE
                source_table = :source_table AND
                notice_id IN ({$text_ids})";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':now', $now, PDO::PARAM_STR);
        $res->bindParam(':source_table', $sourceTable, PDO::PARAM_STR);
        // $res->bindParam(':notice_id', $notice_id, PDO::PARAM_INT);
        $res->execute();
        return true;
    }
    catch (Exception $e) {
        return false;
    }   
}


function getUserTicketsNotices(\PDO $conn, int $user_id, ?bool $onlyCount = false, ?bool $seen = false) :array
{
    $data = [];
    $totalMyOpened = 0;
    $totalMyTreated = 0;
    $terms = "a.tipo_assentamento as type,
                a.ocorrencia,
                a.numero as notice_id,
                a.assentamento,
                a.created_at,
                u.nome AS author";

    $termsTicketsIopened = ",'TICKETS_YOU_OPENED' AS type_name ";
    $termsTicketsITreat = ",'TICKETS_YOU_TREAT' AS type_name ";


    $orderOrGroup = "ORDER BY a.created_at DESC";


    if ($onlyCount) {
        $terms = "COUNT(*) AS notices_count ";
        $termsTicketsIopened = "";
        $termsTicketsITreat = "";
        $orderOrGroup = "";
    }

    // $whereTerms = "udn.seen_at IS NULL";
    $whereRequesterTerms = "udn.requester_seen_at IS NULL";
    $whereTreaterTerms = "udn.treater_seen_at IS NULL";
    if ($seen == true) {
        // $whereTerms = "udn.seen_at IS NOT NULL";
        $whereRequesterTerms = "udn.requester_seen_at IS NOT NULL";
        $whereTreaterTerms = "udn.treater_seen_at IS NOT NULL";
    }
    
    /* Notificacoes para os solicitantes */
    $sql = "SELECT
            {$terms}
            {$termsTicketsIopened}
        FROM
            users_tickets_notices as udn,
            assentamentos as a,
            ocorrencias as o,
            usuarios as u
        WHERE
            udn.notice_id = a.numero AND
            udn.source_table = 'assentamentos' AND
            a.ocorrencia = o.numero AND
            a.asset_privated = 0 AND
            u.user_id = a.responsavel AND
            o.aberto_por = :user_id AND
            a.responsavel <> :user_id AND
            {$whereRequesterTerms} 
            {$orderOrGroup}
        
            ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            if ($onlyCount) {
                $totalMyOpened = $res->fetch()['notices_count'];
            }
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }

    /* Notificacoes para os responsaveis */
    $sql = "SELECT
            {$terms}
            {$termsTicketsITreat}
            FROM
                users_tickets_notices as udn,
                assentamentos as a,
                ocorrencias as o,
                usuarios as u,
                `status` st
            WHERE
                udn.notice_id = a.numero AND
                udn.source_table = 'assentamentos' AND
                a.ocorrencia = o.numero AND
                u.user_id = a.responsavel AND
                a.responsavel <> o.operador AND 
                o.operador = :user_id AND
                o.status = st.stat_id AND
                st.stat_painel IN (1,3) AND
                {$whereTreaterTerms} 
                {$orderOrGroup}
            
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            
            if ($onlyCount) {
                $totalMyTreated = $res->fetch()['notices_count'];
            }
            
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }

    if ($onlyCount) {
        return ['notices_count' => $totalMyOpened + $totalMyTreated];
    }

    /* Remover duplicados */
    $data = array_unique($data, SORT_REGULAR);

    return $data;

}




/**
 * getSpecsIdsFromAsset
 * Retona um array com apenas os IDs da especificação do ativo informado
 * Será utilizado para comparar as mudanças com base no array gerado antes e depois de qualquer edição
 *
 * @param \PDO $conn
 * @param int $assetId
 * 
 * @return array
 */
function getSpecsIdsFromAsset(\PDO $conn, int $assetId) :array
{
    $sql = "SELECT 
                asset_spec_id
            FROM 
                assets_x_specs
            WHERE 
                asset_id = :asset_id";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $assetId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row['asset_spec_id'];
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}




/**
 * getAreaAllowedUnits
 * Retorna um array com a listagem de unidades que a área informada pode visualizar no módulo de inventário
 * @param \PDO $conn
 * @param int $area_id
 * @param int|null $client_id
 * 
 * @return array
 */
function getAreaAllowedUnits (\PDO $conn, int $area_id, ?int $client_id = null): array 
{
    
    $terms = "";
    if ($client_id) {
        $terms = " AND u.inst_client = :client_id ";
    }
    
    $sql = "SELECT 
                a.unit_id, u.inst_client
            FROM 
                areas_x_units a, instituicao u
            WHERE 
                a.area_id = :area_id AND 
                u.inst_cod = a.unit_id
                {$terms}
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':area_id', $area_id);
        if ($client_id) {
            $res->bindParam(':client_id', $client_id);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getAreaAllowedUnitsNames
 * Retona a listagem com os nomes dos clientes e nomes das unidades permitidas para serem visualizadas pela a área informada
 * @param \PDO $conn
 * @param int $area_id
 * @param int|null $client_id
 * 
 * @return array
 */
function getAreaAllowedUnitsNames (\PDO $conn, int $area_id, ?int $client_id = null): array 
{
    
    $terms = "";
    if ($client_id) {
        $terms = " AND u.inst_client = :client_id ";
    }
    
    $sql = "SELECT 
                c.nickname, u.inst_nome
            FROM 
                areas_x_units a, instituicao u, clients c
            WHERE 
                a.area_id = :area_id AND 
                u.inst_cod = a.unit_id AND 
                c.id = u.inst_client
                {$terms}
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':area_id', $area_id);
        if ($client_id) {
            $res->bindParam(':client_id', $client_id);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getAreaAllowedClients
 * Retorna um array com a listagem de clientes que a área informada pode visualizar no módulo de inventário
 * @param \PDO $conn
 * @param int $area_id
 * 
 * @return array
 */
function getAreaAllowedClients (\PDO $conn, int $area_id): array
{
    $sql = "SELECT 
            DISTINCT(u.inst_client), c.nickname
        FROM 
            areas_x_units a, instituicao u, clients c
        WHERE 
            a.area_id = :area_id AND 
            u.inst_cod = a.unit_id AND 
            u.inst_client = c.id
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':area_id', $area_id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * ticketHasExtendedInfo
 * Retorna se existem informações extendidas para o ticket informado
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function ticketHasExtendedInfo(\PDO $conn, int $ticket): bool 
{
    $sql = "SELECT ticket FROM tickets_extended WHERE ticket = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * getTicketExtendedInfo
 * Retorna as informações extendidas para o ticket informado
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getTicketExtendedInfo(\PDO $conn, int $ticket): array
{
    $sql = "SELECT * FROM tickets_extended WHERE ticket = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * Sets or updates ticket extended info by specific columns.
 * Tabela tickets_extended
 *
 * @param \PDO $conn The PDO connection object.
 * @param int $ticket The ticket number.
 * @param array $columns An array of column names.
 * @param array $values An array of column values.
 * @return bool Returns true if the operation is successful, false otherwise.
 */
function setOrUpdateTicketExtendedInfoByCols(\PDO $conn, int $ticket, array $columns, array $values): bool
{
    
    if (count($columns) != count($values)) {
        echo "Number of columns must be equal to number of values";
        return false;
    }

    array_unshift( $columns, 'ticket' );
    array_unshift( $values, $ticket );

    $columns = array_map('noHtml', $columns);
    $values = array_map('noHtml', $values);

    $isUpdate = ticketHasExtendedInfo($conn, $ticket);

    $sqlTerms = "";
    $sqlColumns = "";
    $sqlValues = "";
    if ($isUpdate) {
        /* Update */
        foreach ($columns as $key => $column) {
            if (strlen($sqlTerms)) 
                $sqlTerms .= ", ";
            $sqlTerms .= " {$column} = {$values[$key]} ";
        }

        $sql = "UPDATE tickets_extended SET {$sqlTerms} WHERE ticket = :ticket";

    } else {
        /* Insert */
        foreach ($columns as $column) {
            if (strlen($sqlColumns)) 
                $sqlColumns .= ", ";
            $sqlColumns .= " {$column} "; 
        }

        foreach ($values as $key => $value) {
            if (strlen($sqlValues)) 
                $sqlValues .= ", ";
            $sqlValues .= $value;
        }

        $sql = "INSERT INTO tickets_extended ({$sqlColumns}) VALUES ({$sqlValues})";
    }

    try {
        $res = $conn->prepare($sql);

        if ($isUpdate) {
            $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        }
        
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        // echo $e->getMessage();
        // echo '<hr />' .$e->getTraceAsString();
        // echo '<hr />' . $sql;
        // var_dump([
        //     'columns' => $columns,
        //     'values' => $values,
        //     '$sqlColumns' => $sqlColumns,
        //     '$sqlValues' => $sqlValues
        // ]);
        return false;
    }
}



/**
 * getTicketWorkers
 * Retorna um array com os funcionários vinculados à ocorrência
 * Índices retornados: user_id|main_worker(0/1)|nome|email
 * @param \PDO $conn
 * @param int $ticket
 * @param int|null $type (1 - main worker, 2 - auxiliar - null todos)
 * 
 * @return array
 */
function getTicketWorkers(\PDO $conn, int $ticket, ?int $type = null): array
{
    $types = [
        '1' => '1',
        '2' => '0'
    ];

    $terms = '';
    if (array_key_exists($type, $types)) {
        $terms = "AND txw.main_worker = {$types[$type]} ";
    }
    
    $sql = "SELECT 
                txw.user_id, txw.main_worker, u.nome, u.email
            FROM 
                ticket_x_workers txw, usuarios u
            WHERE 
                txw.user_id = u.user_id AND
                txw.ticket = :ticket
                {$terms}";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($type && $type == 1) {
                return $data[0];
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getScheduledTicketsByWorker
 * Retorna os números dos chamados que o operador informado está vinculado - não importa se ele é o principal ou auxiliar
 * O padrão é retornar apenas os agendados
 * @param \PDO $conn
 * @param int $user_id
 * @param bool $scheduled : se for falso, retorna apenas os não agendados, se for nulo retorna todos
 * 
 * @return array
 */
function getScheduledTicketsByWorker(\PDO $conn, int $user_id, ?bool $scheduled = true): array
{

    $terms = "AND o.oco_scheduled = 1";

    if ($scheduled !== null && !$scheduled) {
        $terms = "AND o.oco_scheduled = 0";
    } else {
        $terms = "";
    }
    
    
    
    $sql = "SELECT 
                txw.ticket
            FROM 
                ticket_x_workers txw, ocorrencias o
            WHERE 
                txw.user_id = :user_id AND
                txw.ticket = o.numero 
                {$terms}
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id);
        $res->execute();

        if ($res->rowCount()) {
            
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getDefaultAreaToOpen
 * Retorna o id da area padrão para receber chamados da área informada por $areaId
 * @param \PDO $conn
 * @param int $areaId
 * 
 * @return int|null
 */
function getDefaultAreaToOpen(\PDO $conn, int $areaId): ?int
{
    $sql = "SELECT 
                area 
            FROM 
                areaxarea_abrechamado 
            WHERE 
                area_abrechamado = :areaId AND 
                default_receiver = 1 
            ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            return $row['area'];
        }
        return null;
    }
    catch (Exception $e) {
        //$exception .= "<hr>" . $e->getMessage();
        return null;
    }
}


/**
 * getIssuesByArea
 * Retorna um array com as informacoes dos tipos de problemas - listagem
 * keys: prob_id | problema | prob_area | prob_sla | prob_tipo_1, prob_tipo_2 | prob_tipo_3 | prob_descricao
 * @param PDO $conn
 * @param bool $all
 * @param int|null $areaId
 * @param int|null $showHidden : se estiver marcado como "0" não exibirá os tipos de problemas marcados como ocultos para a área
 * @param int|null $hasProfileForm
 * @return array
 */
function getIssuesByArea(\PDO $conn, bool $all = false, ?int $areaId = null, ?int $showHidden = 1, ?int $hasProfileForm = null): array
{
    $areaId = (isset($areaId) && filter_var($areaId, FILTER_VALIDATE_INT) ? $areaId : "");
    
    $terms = "";
    if (!empty($areaId)) {
        $terms = " (prob_area = :areaId OR prob_area IS NULL OR prob_area = '-1') AND prob_active = 1 ";
        if (!$showHidden) {
            $terms .= " AND (FIND_IN_SET('{$areaId}', prob_not_area) < 1 OR FIND_IN_SET('{$areaId}', prob_not_area) IS NULL ) ";
        }
    } else {
        if ($all) {
            $terms = " 1 = 1 ";
        } else {
            $terms = " (prob_area IS NULL OR prob_area = '-1') AND prob_active = 1 ";
        }
    }


    if ($hasProfileForm != null && $hasProfileForm == 1) {
        if (strlen((string)$terms)) {
            $terms .= " AND ";
        }
        $terms .= " prob_profile_form IS NOT NULL ";
    }

    $sql = "SELECT 
                MIN(prob_id) as prob_id, 
                MIN(prob_area) as prob_area, 
                MIN(prob_descricao) as prob_descricao, 
                problema, 
                prob_profile_form  
            FROM 
                problemas
            WHERE 
                {$terms}
            GROUP BY 
                problema, prob_profile_form
            ORDER BY
                problema";


    try {
        $res = $conn->prepare($sql);
        
        if (!empty($areaId)) {
            $res->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        }
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


function getIssuesCategoriesByType(\PDO $conn, int $type): array
{
    $type = (string)$type;
    $table = "prob_tipo_" . $type;
    $sql = "SELECT * FROM {$table} ";
    $res = $conn->prepare($sql);
    $res->execute();
    if ($res->rowCount()) {
        foreach ($res->fetchAll() as $row) {
            $data[] = $row;
        }
        return $data;
    }
    return [];
}



/**
 * getIssuesByArea4
 * Retorna array com as informações dos tipos de problemas
 * Função específica para a nova versão de relacionamento NxN entre areas e tipos de problemas
 * keys: prob_id | problema | prob_area | prob_sla | prob_tipo_1, prob_tipo_2 | prob_tipo_3 | prob_descricao | prob_profile_form
 *
 * @param \PDO $conn
 * @param bool $all
 * @param int|null $areaId
 * @param int|null $showHidden : para exibir ou não os tipos de problemas marcados como exceção para a área informada
 * @param string|null $areasFromUser : Limita o retorno à tipos de problemas vinculados às áreas para as quais o usuário pode abrir chamados
 * @param int|null $hasProfileForm
 * 
 * @return array
 */
function getIssuesByArea4(
    \PDO $conn, 
    bool $all = false, 
    ?int $areaId = null, 
    ?int $showHidden = 1, 
    ?string $areasFromUser = null,
    ?int $hasProfileForm = null,
    ?array $categories = null,
    ?array $catsNotNull = null
): array
{
    $areaId = (isset($areaId) && $areaId != '-1' && filter_var($areaId, FILTER_VALIDATE_INT) ? $areaId : "");
    $areasToOpen = [];

    /* Categorias de tipos de solicitação chave => valor, onde: 
    chave é o número sufixo do campo de categoria e o valor é o código da categoria na tabela correspondente */
    $termsCategories = "";
    if ($categories !== null && !empty($categories)) {
        foreach ($categories as $cat => $value) {
            $termsCategories .= " AND prob_tipo_" . $cat . " = '{$value}' ";
        }
    }

    if ($catsNotNull !== null && !empty($catsNotNull)) {
        foreach ($catsNotNull as $cat) {
            $termsCategories .= " AND prob_tipo_" . $cat . " IS NOT NULL ";
        }
    }

    $terms = "";
    if (!empty($areaId)) {
        $terms = " (a.area_id = :areaId OR a.area_id IS NULL) AND p.prob_active = 1 ";
        if (!$showHidden) {
            $terms .= " AND (FIND_IN_SET('{$areaId}', prob_not_area) < 1 OR FIND_IN_SET('{$areaId}', prob_not_area) IS NULL ) ";
        }
    } else {
        if ($all) {
            $terms = " 1 = 1 ";
        } else {
            
            if ($areasFromUser) {
                $areasToOpen = getAreasToOpen($conn, $areasFromUser);
                if (count($areasToOpen)) {
                    $areas_ids = [];
                    foreach ($areasToOpen as $area) {
                        $areas_ids[] = $area['sis_id'];
                    }
                    $areas_ids = implode(',', $areas_ids);
                    
                    $terms = " (a.area_id IS NULL OR  a.area_id IN ({$areas_ids}) ) AND p.prob_active = 1 ";
                } else
                    $terms = " (a.area_id IS NULL) AND p.prob_active = 1 ";
            } else
                $terms = " (a.area_id IS NULL) AND p.prob_active = 1 ";
        }
    }

    if ($hasProfileForm != null && $hasProfileForm == 1) {
        if (strlen((string)$terms)) {
            $terms .= " AND ";
        }
        $terms .= " p.prob_profile_form IS NOT NULL ";
    }

    // -- p.prob_tipo_1, p.prob_tipo_2, p.prob_tipo_3
    $sql = "SELECT 
                
                a.prob_id, p.prob_descricao, p.problema, p.prob_profile_form, 
                cat1.probt1_cod, cat2.probt2_cod, cat3.probt3_cod, 
                cat4.probt4_cod, 
                cat5.probt5_cod, 
                cat6.probt6_cod, 
                cat1.probt1_desc, cat2.probt2_desc, cat3.probt3_desc, 
                cat4.probt4_desc,
                cat5.probt5_desc,
                cat6.probt6_desc
            FROM 
                -- problemas p, areas_x_issues a,
                -- prob_tipo_1 cat1, prob_tipo_2 cat2, prob_tipo_3 cat3, prob_tipo_4 cat4

                areas_x_issues a, 
                problemas p
                
                LEFT JOIN prob_tipo_1 AS cat1 ON cat1.probt1_cod = p.prob_tipo_1
                LEFT JOIN prob_tipo_2 AS cat2 ON cat2.probt2_cod = p.prob_tipo_2
                LEFT JOIN prob_tipo_3 AS cat3 ON cat3.probt3_cod = p.prob_tipo_3
                LEFT JOIN prob_tipo_4 AS cat4 ON cat4.probt4_cod = p.prob_tipo_4
                LEFT JOIN prob_tipo_5 AS cat5 ON cat5.probt5_cod = p.prob_tipo_5
                LEFT JOIN prob_tipo_6 AS cat6 ON cat6.probt6_cod = p.prob_tipo_6
            WHERE 
                p.prob_id = a.prob_id AND 
                -- p.prob_tipo_1 = cat1.probt1_cod AND
                -- p.prob_tipo_2 = cat2.probt2_cod AND
                -- p.prob_tipo_3 = cat3.probt3_cod AND
                -- p.prob_tipo_4 = cat4.probt4_cod AND 
                {$terms}
                {$termsCategories}

            GROUP BY 
                
                a.prob_id, p.prob_descricao, p.problema, p.prob_profile_form, 
                cat1.probt1_cod, cat2.probt2_cod, cat3.probt3_cod, 
                cat4.probt4_cod,
                cat5.probt5_cod,
                cat6.probt6_cod,
                cat1.probt1_desc, cat2.probt2_desc, cat3.probt3_desc, 
                cat4.probt4_desc,
                cat5.probt5_desc,
                cat6.probt6_desc
            
            ORDER BY
                problema";
    try {
        $res = $conn->prepare($sql);
        
        if (!empty($areaId)) {
            $res->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        }
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        // return [
        //     "sql" => $sql
        // ];
        return [];
    }
    catch (Exception $e) {
        echo $sql . "<hr>" . $e->getMessage();
        return [];
    }
}



function getUsedCatsFromPossibleIssues(int $catSufix, array $possibleIssues): array
{
    // Sufixos possíveis para compor as categorias: prob_tipo_1, prob_tipo_2, prob_tipo_3, prob_tipo_4, prob_tipo_5, prob_tipo_6
    $acceptSufixes = [1, 2, 3, 4, 5, 6];
    if (!in_array($catSufix, $acceptSufixes)) {
        return [];
    }

    $usedCats = [];
    
    if (!empty($possibleIssues)) {
        foreach ($possibleIssues as $issue) {
            
            if ($issue['probt' . $catSufix . '_cod']) {
                $usedCats[$issue['probt' . $catSufix . '_cod']] = ['id' => $issue['probt' . $catSufix . '_cod'], 'name' => $issue['probt' . $catSufix . '_desc']];
            }
            
        }
        $usedCats = arraySortByColumn($usedCats, 'name');
        return $usedCats;
    }

    return [];
}




/**
 * hiddenAreasByIssue
 * Retorna a listagem de areas que possuem o tipo de problema como oculto para utilização em chamados
 * @param \PDO $conn
 * @param int $issueId
 * 
 * @return array
 */
function hiddenAreasByIssue(\PDO $conn, int $issueId): array
{
    $areasArray = [];
    $data = [];
    $sql = "SELECT prob_not_area FROM problemas WHERE prob_id = :issueId AND prob_not_area IS NOT NULL ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':issueId', $issueId, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            $areasArray = explode(',', (string)$res->fetch()['prob_not_area']);

            foreach ($areasArray as $areaId) {
                $data[] = getAreaInfo($conn, $areaId);
            }
            return $data;
            
            // return $areasArray;
        }
        return [];
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return [];
    }
}


/**
 * getIssueDetailed
 * Retorna um array com as informacoes de Sla e categorias do tipo de problema informado - 
 * nomenclauras parecidas também são buscadas
 * keys: prob_id | problema | 
 * @param PDO $conn
 * @param int $id
 * @param ?int $areaId
 * @return array
 */
function getIssueDetailed(\PDO $conn, int $id, ?int $areaId = null): array
{
    $areaId = (isset($areaId) && $areaId != '-1' && filter_var($areaId, FILTER_VALIDATE_INT) ? $areaId : "");
    $termsIssueName = "";
    $terms = "";
    
    if (empty($id))
        return [] ;

    $sqlName = "SELECT problema FROM problemas WHERE prob_id = :id ";
    try {
        $resName = $conn->prepare($sqlName);
        $resName->bindParam(":id", $id, PDO::PARAM_INT);
        $resName->execute();
        if ($resName->rowCount()) {
            $issueName = $resName->fetch()['problema'];
            $termsIssueName = " AND lower(p.problema) LIKE (lower(:issueName)) ";
        } else {
            return [];
        }
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
   
    if (!empty($areaId)) {
        $terms = " AND (ai.area_id = :areaId OR ai.area_id IS NULL) ";
    }
    
    $sql = "SELECT 
                p.prob_id, p.problema, sl.slas_desc, 
                pt1.probt1_desc, 
                pt2.probt2_desc, 
                pt3.probt3_desc,
                pt4.probt4_desc,
                pt5.probt5_desc,
                pt6.probt6_desc
            FROM areas_x_issues ai, problemas as p 
            
            LEFT JOIN sla_solucao as sl on sl.slas_cod = p.prob_sla 
            LEFT JOIN prob_tipo_1 as pt1 on pt1.probt1_cod = p.prob_tipo_1 
            LEFT JOIN prob_tipo_2 as pt2 on pt2.probt2_cod = p.prob_tipo_2 
            LEFT JOIN prob_tipo_3 as pt3 on pt3.probt3_cod = p.prob_tipo_3 
            LEFT JOIN prob_tipo_4 as pt4 on pt4.probt4_cod = p.prob_tipo_4 
            LEFT JOIN prob_tipo_5 as pt5 on pt5.probt5_cod = p.prob_tipo_5 
            LEFT JOIN prob_tipo_6 as pt6 on pt6.probt6_cod = p.prob_tipo_6 

            WHERE p.prob_id = ai.prob_id 
                {$termsIssueName} {$terms} 

            GROUP BY
                prob_id, problema, slas_desc, probt1_desc, probt2_desc, 
                probt3_desc, probt4_desc, probt5_desc, probt6_desc

            ORDER BY p.problema";
    try {
        $res = $conn->prepare($sql);
        
        if ((!empty($areaId))) {
            $res->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        }
        $res->bindParam(':issueName', $issueName, PDO::PARAM_STR);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        echo $sql . "<hr>" . $e->getMessage();
        return [];
    }
}


/**
 * Retorna as informações do tipo de problema informado
 * @param PDO $conn
 * @param int $id
 * @return array
 */
function getIssueById(\PDO $conn, int $id):array
{
    $sql = "SELECT * FROM problemas WHERE prob_id =:id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id',$id);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getIssueCategoryNameBySufixAndId
 * Retorna o nome da categoria correspondente à tabela do sufixo informado e o id da categoria
 * @param \PDO $conn
 * @param mixed $tableSufix
 * @param mixed $id
 * 
 * @return string
 */
function getIssueCategoryNameBySufixAndId (\PDO $conn, $tableSufix, $id): string
{
    $allowedSufixes = ['1', '2', '3', '4', '5', '6'];
    if (in_array($tableSufix, $allowedSufixes)) {
        $sql = "SELECT probt{$tableSufix}_desc FROM prob_tipo_{$tableSufix} WHERE probt{$tableSufix}_cod = :id ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':id',$id);
            $res->execute();
            if ($res->rowCount()) {
                return $res->fetch()['probt'.$tableSufix.'_desc'];
            }
            return '';
        }
        catch (Exception $e) {
            return '';
        }
    }
    return '';
}





/**
 * getIssuesToCostcards
 * Retorna as informações sobre os tipos de solicitações que estão marcadas para serem
 * exibidas nos cards do dashboard de custos
 *
 * @param \PDO $conn
 * 
 * @return array
 */
function getIssuesToCostcards(\PDO $conn):array
{
    $sql = "SELECT 
                * 
            FROM 
                problemas 
            WHERE 
                prob_active = 1 AND 
                card_in_costdash = 1
            ORDER BY 
                problema    
            ";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        return [];
    }
}





/**
 * Retorna se a área informada possui o tipo de problema com a nomenclatura do id informado
 * @param PDO $conn
 * @param int $areaId
 * @param int $probID
 * @return bool
 */
function areaHasIssueName(\PDO $conn, int $areaId, int $probId):bool
{
    $issueName = "";
    $sql = "SELECT problema FROM problemas WHERE prob_id =:probId ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':probId',$probId);
        $res->execute();
        if ($res->rowCount()) {
            $issueName = $res->fetch()['problema'];

            // $sql = "SELECT * FROM problemas WHERE problema = '{$issueName}' AND prob_area = :areaId ";
            $sql = "SELECT * FROM 
                        problemas p, areas_x_issues ai 
                    WHERE 
                        p.problema = '{$issueName}' AND ai.area_id = :areaId AND 
                        p.prob_id = ai.prob_id ";
            
            
            $res = $conn->prepare($sql);
            $res->bindParam(':areaId', $areaId);
            $res->execute();
            if ($res->rowCount()) {
                return true;
            }
            return false;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}



/**
 * Retorna se o tipo de problema informado (de acordo com sua nomenclatura) existe desvinculado de áreas de atendimento
 * @param PDO $conn
 * @param int $probID
 * @return bool
 */
function issueFreeFromArea(\PDO $conn, int $probId):bool
{
    $issueName = "";
    $sql = "SELECT problema FROM problemas WHERE prob_id =:probId ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':probId',$probId);
        $res->execute();
        if ($res->rowCount()) {
            $issueName = $res->fetch()['problema'];

            $sql = "SELECT * FROM problemas p, areas_x_issues ai 
                    WHERE 
                        p.problema = '{$issueName}' AND (ai.area_id IS NULL)
                        AND p.prob_id = ai.prob_id ";
            $res = $conn->prepare($sql);
            $res->execute();
            if ($res->rowCount()) {
                return true;
            }
            return false;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}



/**
 * Retorna a descrição do tipo de problema
 * @param PDO $conn
 * @param int $id
 * @return string
 */
function issueDescription(\PDO $conn, int $id):string
{
    $sql = "SELECT prob_descricao FROM problemas WHERE prob_id =:id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id',$id);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['prob_descricao'];
        }
        return '';
    }
    catch (Exception $e) {
        return '';
    }
}



/**
 * getAreasByIssue
 * Retorna um array com as informações das áreas de atendimento
 * vinculadas ao tipo de problema informado (via id) - Nova arquitetura NxN para 
 * areas x tipos de problemas: areas_x_issues
 *
 * @param \PDO $conn
 * @param int $id : id do tipo de problema
 * 
 * @return array
 */
function getAreasByIssue (\PDO $conn, int $id, ?string $labelAll = "Todas"): array
{
    $data = [];

    /* Só retornará registro se existir com area_id = null */
    $sqlAllAreas = "SELECT 
                       area_id as sis_id, 
                       '{$labelAll}' as sistema
                    FROM areas_x_issues
                    WHERE 
                        area_id IS NULL AND 
                        prob_id = :id ";
    try {
        $res = $conn->prepare($sqlAllAreas);
        $res->bindParam(':id', $id);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
            
        }
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return [];
    }


    $sql = "SELECT * FROM sistemas s, areas_x_issues ap 
            WHERE 
                (s.sis_id = ap.area_id OR ap.area_id IS NULL) AND 
                ap.prob_id = :id 
            ORDER BY s.sistema";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id',$id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getAreasMinusExceptionsByIssue
 * Retorna um array com todas as áreas que podem receber chamados do tipo de solicitação informado
 * Exclui as áreas que estão marcadas como exceção (quanto o tipo de solicitação for para todas as áreas)
 *
 * @param \PDO $conn
 * @param int $issueId
 * 
 * @return array
 */
function getAreasMinusExceptionsByIssue (\PDO $conn, int $issueId): array
{
    
    /*** Areas que são exceção para o tipo de solicitação */
    $areaExceptions = hiddenAreasByIssue($conn, $issueId);

    /*** Separando apenas a coluna com os IDs */
    $exceptionIDs = (!empty($areaExceptions) ? array_column($areaExceptions, 'area_id') : []);

    /*** Todas as áreas para qual o tipo de solicitação está vinculado */
    $allTreatingAreas = getAreasByIssue($conn, $issueId);

    
    /*** Caso nenhuma área em específico estiver definida, então todas as áreas podem ser */
    if ($allTreatingAreas[0]['sis_id'] == null) {
        /*** Array com todas as áreas ativas que prestam atendimento */
        $allTreatingAreas = getAreas($conn, 0, 1, 1);
    }

    /*** Caso não existam exceções, retorna apenas o array com todas as áreas elegíveis */
    if (empty($exceptionIDs)) {
        return $allTreatingAreas;
    }
    
    /*** Caso existam exceções */
    $newAllAreas = [];
    foreach ($allTreatingAreas as $area) {
        if (!in_array($area['sis_id'], $exceptionIDs)) {
            $newAllAreas[] = $area;
        }
    }

    return $newAllAreas;

}



/**
 * getAreaInDynamicMode
 * Retorna um array com os id (area_receiver) da área de atendimento a ser definida a partir da seleção de um tipo de solicitação
 * Indices: common_areas_btw_issue_and_user | area_receiver | many_options:bool
 * Regras:
 * 1 - Se o tipo de problema não tiver área associada - Será aberto para a área definida como padrão da área primária do usuário
 * 2 - Se o usuário só puder abrir chamados para uma única área, esta será a área destino do chamado
 * 3 - Se existir apenas uma área comum entre as áreas do tipo de problema e as áreas para as quais o usuário pode abrir, esta 
 * será a área destino do chamado
 * 4 - Se o usuário só puder abrir chamado para uma única área, essa será a área do chamado
 * 5 - Se existirem mais de uma área para o tipo de problema, a área destino será a área padrão do tipo de problema (se existir)
 * 5.1 - caso não exista área padrão para o tipo de problema, será checado se a área padrão do usuário está entre as áreas 
 * associadas ao tipo de problema, caso positivo, será a área destino
 * 5.2 - Se ao final de todas as checagens ainda existir mais de uma área possível (significa que o tipo de problema está 
 * vinculado a mais de uma área e não possui definição de área padrão, da mesma forma, a área padrão do usuário não está 
 * entre as áreas possíveis): será escolhida a primeira área do array e retornado o indice "many_options" como true
 * 
 * @param \PDO $conn
 * @param int $issueType
 * @param int $userPrimaryArea
 * @param string $userAllAreas
 * 
 * @return array
 */
function getAreaInDynamicMode(\PDO $conn, int $issueType, int $userPrimaryArea, string $userAllAreas): array
{
    $data = [];

    $data['issue_id'] = $issueType;
    $issueInfo = getIssueById($conn, $issueType);
    $data['many_options'] = false;
    $data['area_receiver'] = [];
    $data['areas_by_issue'] = [];


    /* Áreas vinculadas ao tipo de problema - removendo as áreas para as quais o tipo de problema é oculto*/
    // $possibleAreas = getAreasByIssue($conn, $issueType);
    $possibleAreas = getAreasMinusExceptionsByIssue($conn, $issueType);
    foreach ($possibleAreas as $area) {
        if (!empty($area['sis_id']))
            $data['areas_by_issue'][] = $area['sis_id'];
    }


    /* Áreas para as quais o usuário logado pode abrir chamado */
    $areasToOpen = getAreasToOpen($conn, $userAllAreas);
    foreach ($areasToOpen as $area) {
        $data['user_areas_to_open'][] = $area['sis_id'];
    }


    /* Áreas comuns entre as áreas do tipo de problema e as áreas para as quais o usuário pode abrir chamado */
    $commonAreas = array_intersect($data['areas_by_issue'],$data['user_areas_to_open']);
    foreach ($commonAreas as $area) {
        $data['common_areas_btw_issue_and_user'][] = $area;
    }

    /* Se existir apenas uma área comum entre as vinculadas ao tipo de problema e as que o usuário pode abrir, 
    então essa será a área de atendimento do chamado */
    if (isset($data['common_areas_btw_issue_and_user']) && count($data['common_areas_btw_issue_and_user']) == 1) {
        $data['debug'] = "Linha " . __LINE__;
        $data['area_receiver'] = $data['common_areas_btw_issue_and_user'];
        return $data;
    }


    $data['issue_area_default'] = "";
    if (!empty($issueInfo['prob_area_default'])) {
        $data['issue_area_default'] = $issueInfo['prob_area_default'];
        
        /* Se a área padrão do tipo de problema estiver entre as áreas para as quais o usuário puder abrir, 
        então esta será a área de atendimento do chamado */
        if (in_array($data['issue_area_default'], $data['user_areas_to_open'])) {
            
            // $data['debug'] = "Linha " . __LINE__;
            $data['area_receiver'][] = $data['issue_area_default'];
            return $data;
        }
    }


    /* Se existir apenas uma área associada para o tipo de problema essa será a área destino */
    if (isset($data['areas_by_issue']) && count($data['areas_by_issue']) == 1) {
        // $data['debug'] = "Linha " . __LINE__;
        $data['area_receiver'] = $data['areas_by_issue'];
        return $data;
    }

    /* Área padrão para receber chamados a partir da área primária do usuário logado */
    /* Se a área está configurada para modo dinâmico de abertura, obrigatoriamente tem definida a área padrão para abertura */
    $data['user_default_area_to_open'] = getDefaultAreaToOpen($conn, $userPrimaryArea);


    // if (!isset($data['common_areas_btw_issue_and_user']) && count($data['user_areas_to_open']) > 1) {
    if (empty($data['issue_area_default']) && count($data['user_areas_to_open']) > 1) {
        /* Significa que o tipo de problema não possui área definida (serve para todas) */
        /* Nesse caso a área destino será a área definida como padrão para a área primária do usuário */
        $data['debug'] = "Linha " . __LINE__;
        $data['area_receiver'][] = $data['user_default_area_to_open'];
        $data['message'] = "Será aberto para a área padrão de abertura da área primária do usuário.";

        return $data;
        
    } elseif (count($data['user_areas_to_open']) == 1) {
        
        // $data['debug'] = "Linha " . __LINE__;
        // $data['area_receiver'][] = $data['user_areas_to_open'];
        $data['area_receiver'] = $data['user_areas_to_open'];
        return $data;
    }


    if (isset($data['common_areas_btw_issue_and_user']) && count($data['common_areas_btw_issue_and_user']) > 1) {

        // $data['many_options'] = true;
        
        if (!empty($data['issue_area_default']) && in_array($data['issue_area_default'], $data['user_areas_to_open'])) {
            
            // $data['debug'] = "Linha " . __LINE__;
            $data['area_receiver'][] = $data['issue_area_default'];
            return $data;
        } else {
    
            if (!empty($data['user_default_area_to_open']) && in_array($data['user_default_area_to_open'], $data['common_areas_btw_issue_and_user'])) {


                // $data['debug'] = "Linha " . __LINE__;
                $data['area_receiver'][] = $data['user_default_area_to_open'];
                $data['area_receiver'][] = array_unique($data['area_receiver']); 
    
                $data['message'] = "Será aberto para a área padrão de abertura da área primária do usuário.";
                return $data;
            } else {
                // $data['debug'] = "Linha " . __LINE__;
                $data['message'] = "Nem a área padrão do problema nem a área padrão da área primária estão entre as áreas possíveis para o tipo de problema - desenvolver a regra";
    
                $data['many_options'] = true;
                /* Temporariamente estou definindo a primeira area comum ao tipo de problema e áreas áreas do usuaŕio para receber o chamado */
                $data['area_receiver'][] = $data['common_areas_btw_issue_and_user'][0];
                return $data;
            }
        }
    }
    return $data;
}



/**
 * getCommitmentModels
 * Retorna a listagem de modelos de termos de compromisso cadastrados no sistema
 * ou o termo especificado pelo id
 *
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $unit
 * @param int|null $client
 * @param int|null $type
 * 
 * @return array
 */
function getCommitmentModels(\PDO $conn, ?int $id = null, ?int $unit = null, ?int $client = null, ?int $type = null): array
{

    $filterType = false;
    $terms = "";

    if ($id !== null) {
        $terms = " WHERE cm.id = :id "; 
                
    } elseif ($unit !== null) {
        $terms = " WHERE cm.unit_id = :unit ";
        
    } elseif ($client !== null) {
        $terms = " WHERE cm.client_id = :client ";
    }

    if (!$id && $type !== null) {
        $filterType = true;
        $terms .= (strlen($terms) > 0 ? " AND " : " WHERE ") . " cm.type = :type ";
    }

    $sql = "SELECT
                cm.*, c.nickname, u.inst_nome 
            FROM 
                commitment_models cm
                LEFT JOIN clients c ON c.id = cm.client_id
                LEFT JOIN instituicao u ON u.inst_cod = cm.unit_id 
                $terms
                ORDER BY
                    cm.type, c.nickname, u.inst_nome
            ";

    try {
        $res = $conn->prepare($sql);
        if ($id !== null) {
            $res->bindParam(':id', $id);
        } elseif ($unit !== null) {
            $res->bindParam(':unit', $unit);
        } elseif ($client !== null) {
            $res->bindParam(':client', $client);
        }
        if ($filterType) {
            $res->bindParam(':type', $type);
        }
        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            // if ($id)
            //     return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}






/**
 * Retorna o departamento de uma tag (com unidade) informada
 * @param PDO $conn
 * @param int $unit
 * @param int $tag
 * @return null|int
 */
function getDepartmentByUnitAndTag(\PDO $conn, int $unit, int $tag):?int
{

    $sql = "SELECT comp_local FROM equipamentos WHERE comp_inst = :unit AND comp_inv = :tag ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':unit',$unit);
        $res->bindParam(':tag',$tag);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['comp_local'];
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}


/**
 * Retorna se um tipo de problema possui roteiros relacionados
 * @param PDO $conn
 * @param int $id
 * @return bool
 */
function issueHasScript(\PDO $conn, int $id):bool
{
    $sql = "SELECT prscpt_id FROM prob_x_script WHERE prscpt_prob_id = :id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * Retorna se um tipo de problema possui roteiros para usuário final
 * @param PDO $conn
 * @param int $id
 * @return bool
 */
function issueHasEnduserScript(\PDO $conn, int $id):bool
{
    $sql = "SELECT 
                sr.scpt_nome, pr.prob_id
            FROM 
                problemas as pr, 
                scripts AS sr, 
                prob_x_script as prsc
            WHERE 
                pr.prob_id = prsc.prscpt_prob_id AND 
                prsc.prscpt_scpt_id = sr.scpt_id AND 
                pr.prob_id = :id AND
                sr.scpt_enduser = 1
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}


function getScripts(\PDO $conn, ?int $scpt_id = null, ?int $prob_id = null, ?bool $enduser = null):array
{

    $terms = "";

    if ($scpt_id !== null) {
        $terms .= " AND sr.scpt_id = :scpt_id ";
    } elseif ($prob_id !== null) {
        $terms .= " AND pr.prob_id = :prob_id ";
    }

    if ($scpt_id === null && $enduser !== null) {
        $terms .= " AND sr.scpt_enduser = :enduser ";
    }

    
    $sql = "SELECT 
                sr.*
            FROM 
                problemas AS pr,
                scripts AS sr, 
                -- LEFT JOIN prob_x_script as prsc on prsc.prscpt_scpt_id = sr.scpt_id 
                prob_x_script as prsc
            WHERE 
                pr.prob_id = prsc.prscpt_prob_id AND 
                prsc.prscpt_scpt_id = sr.scpt_id 
                {$terms}
            GROUP BY 
                sr.scpt_id, sr.scpt_nome, sr.scpt_desc, sr.scpt_script, sr.scpt_enduser 
            ORDER BY sr.scpt_nome ";

    try {
        $res = $conn->prepare($sql);
        if ($scpt_id !== null) {
            $res->bindParam(':scpt_id', $scpt_id);
        } elseif ($prob_id !== null) {
            $res->bindParam(':prob_id', $prob_id);
        }

        if ($scpt_id === null && $enduser !== null) {
            $res->bindParam(':enduser', $enduser);
        }
        
        $res->execute();
        if ($res->rowCount()) {
            if ($scpt_id !== null) {
                return $res->fetch();
            }
            return $res->fetchAll();
        }
        return [];
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
}


function getIssuesByScript(\PDO $conn, int $scpt_id):array
{
    $sql = "SELECT 
                pr.prob_id, pr.problema
            FROM 
                problemas AS pr,
                scripts AS sr, 
                prob_x_script as prsc 
            WHERE 
                pr.prob_id = prsc.prscpt_prob_id AND
                prsc.prscpt_scpt_id = sr.scpt_id AND
                sr.scpt_id = :scpt_id
            GROUP BY 
                pr.prob_id, pr.problema
            ORDER BY pr.problema ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':scpt_id', $scpt_id);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
}


/**
 * getOpenerLevel
 * Retorna o código do nível do usuário que abriu o chamado
 * @param \PDO $conn
 * @param int $ticket
 * @return int
 */
function getOpenerLevel (\PDO $conn, int $ticket): int
{
    $sql = "SELECT u.nivel FROM usuarios u, ocorrencias o WHERE o.numero = :ticket AND o.aberto_por = u.user_id ";
    $result = $conn->prepare($sql);
    $result->bindParam(':ticket', $ticket);
    $result->execute();

    return $result->fetch()['nivel'];
}


function getUserLevel (\PDO $conn, int $user_id): int
{
    $sql = "SELECT u.nivel FROM usuarios u WHERE u.user_id = :user_id ";
    try {
        $result = $conn->prepare($sql);
        $result->bindParam(':user_id', $user_id);
        $result->execute();

        return $result->fetch()['nivel'];
    }
    catch (Exception $e) {
        return 0;
    }
}


/**
 * getOpenerEmail
 * Retorna o endereço de e-mail do usuário que abriu o chamado
 * @param \PDO $conn
 * @param int $ticket
 * @return string
 */
function getOpenerEmail (\PDO $conn, int $ticket): string
{
    $sql = "SELECT u.email FROM usuarios u, ocorrencias o WHERE o.numero = :ticket AND o.aberto_por = u.user_id ";
    $result = $conn->prepare($sql);
    $result->bindParam(':ticket', $ticket);
    $result->execute();

    return $result->fetch()['email'];
}


function getOpenerInfo (\PDO $conn, int $ticket): array
{
    $data = [];
    $sql = "SELECT u.* FROM usuarios u, ocorrencias o WHERE o.numero = :ticket AND o.aberto_por = u.user_id ";
    $result = $conn->prepare($sql);
    $result->bindParam(':ticket', $ticket);
    $result->execute();

    $data = $result->fetch();
    unset($data['password']);
    unset($data['hash']);
    return $data;
}


function getOpenerIdByEntry (\PDO $conn, int $entryId): ?int
{
    $sql = "SELECT 
                aberto_por
            FROM 
                ocorrencias o,
                assentamentos a
            WHERE
                o.numero = a.ocorrencia AND
                a.numero = :entryId
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':entryId', $entryId);
        $res->execute();

        if ($res->rowCount()) {
            return $res->fetch()['aberto_por'];
        }
        return null;
    } catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }
}


/**
 * getRegistrationOperatorInfo
 * Retorna as informações sobre o usuário autor do registro do chamado no sistema
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getRegistrationOperatorInfo (\PDO $conn, int $ticket): array
{
    $data = [];

    $sql = "SELECT 
                aberto_por, 
                registration_operator 
            FROM 
                ocorrencias 
            WHERE 
                numero = :ticket ";
    try {
        
        $result = $conn->prepare($sql);
        $result->bindParam(':ticket', $ticket);
        $result->execute();
        if ($result->rowCount()) {
            $data = $result->fetch();

            $userId = $data['aberto_por'];

            if (!empty($data['registration_operator'])) {
                $userId = $data['registration_operator'];
            }

            $userInfo = getUserInfo($conn, $userId);
            
            unset($userInfo['password']);
            unset($userInfo['hash']);
            
            return $userInfo; 
        }

        return [];
    } catch (Exception $e) {
        return [];
    }
}


/**
 * isAreasIsolated
 * Retorna se a configuração atual está marcada para isolamento de visibilidade entre áreas
 * @param PDO $conn
 * @return bool
 */
function isAreasIsolated($conn): bool
{
    $config = getConfig($conn);
    if ($config['conf_isolate_areas'] == 1)
        return true;
    return false;
}


/**
 * getTicketData
 * Busca as informacoes do ticket na tabela de ocorrencias
 * @param \PDO $conn
 * @param int $ticket
 * @param array $columns array de colunas para serem retornadas
 * @return array
 */
function getTicketData (\PDO $conn, int $ticket, ?array $columns = []): array
{
    $terms = "*";

    if ($columns) {
        $terms = implode(',', $columns);
    }

    $sql = "SELECT {$terms}
            FROM ocorrencias  
            WHERE 
                numero = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * setTicketEntry
 * Grava um comentário em um chamado
 *
 * @param \PDO $conn
 * @param int $ticket
 * @param array $entryData: chaves do array: 'text', 'created_at' 'author', 'privated', 'type'
 * 
 * @return int|null Retorna o ID do comentário
 */
function setTicketEntry(\PDO $conn, int $ticket, array $entryData): ?int
{
    $mandatoyKeys = ['text', 'author', 'type'];
    
    foreach ($mandatoyKeys as $key) {
        if (!isset($entryData[$key])) {
            return null;
        }
    }
    
    $text = noHtml($entryData['text']);
    $created_at = $entryData['created_at'] ?? date('Y-m-d H:i:s');
    $author = (int)$entryData['author'];
    $privated = (array_key_exists('privated', $entryData) && $entryData['privated']) ? 1 : 0;
    $type = $entryData['type'];

    $sql = "INSERT 
            INTO 
                assentamentos
            (
                ocorrencia, assentamento, created_at, responsavel, asset_privated, tipo_assentamento
            )
            VALUES
            (
                :ticket, :text, :created_at, :author, :privated, :type
            ) 
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->bindParam(':text', $text, PDO::PARAM_STR);
        $res->bindParam(':created_at', $created_at, PDO::PARAM_STR);
        $res->bindParam(':author', $author, PDO::PARAM_INT);
        $res->bindParam(':privated', $privated, PDO::PARAM_INT);
        $res->bindParam(':type', $type, PDO::PARAM_INT);
        $res->execute();
        return $conn->lastInsertId();
    } catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }
}




function getTicketByEntryId (\PDO $conn, int $entryId): ?array
{
    $sql = "SELECT o.* 
            FROM 
                ocorrencias o, 
                assentamentos a 
            WHERE 
                o.numero = a.ocorrencia AND 
                a.numero = :entryId";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':entryId', $entryId);
        $res->execute();
        
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}



/**
 * isTicketTreater
 * Retorna se o usuário informado é o responsável pelo atendimento do chamado também informado
 *
 * @param \PDO $conn
 * @param int $ticket
 * @param int $user
 * 
 * @return bool
 */
function isTicketTreater (\PDO $conn, int $ticket, int $user): bool
{
    $sql = "SELECT 
                o.numero
            FROM
                ocorrencias o,
                usuarios u,
                `status` st
            WHERE
                o.numero = :ticket AND
                o.operador = u.user_id AND
                u.nivel < 3 AND
                o.status = st.stat_id AND
                st.stat_painel IN (1,3) AND 
                u.user_id = :user
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->bindParam(':user', $user, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}


function getTicketTreater (\PDO $conn, int $ticket): array
{
    $sql = "SELECT 
                u.user_id, u.nome
            FROM
                ocorrencias o,
                usuarios u,
                `status` st
            WHERE
                o.numero = :ticket AND
                o.operador = u.user_id AND
                u.nivel < 3 AND
                o.status = st.stat_id AND
                st.stat_painel IN (1,3)
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }

}


function userHasTicketWaitingToBeRated(\PDO $conn, int $userId): array
{
    $sql = "SELECT 
        o.numero
    FROM 
        ocorrencias o, 
        tickets_rated tr
    WHERE
        o.numero = tr.ticket AND
        o.`aberto_por` = :userId AND
        o.`operador` <> o.`aberto_por` AND
        -- o.`status` = 39 AND
        o.`status` <> 4 AND
        o.`data_fechamento` IS NOT NULL AND
        tr.rate IS NULL";
    
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            $data = [];
            foreach ($res->fetchAll() as $row) {
                $data[] = $row['numero'];
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * isRated
 * Retorna se um dado ticket já teve o atendimento validado e consequentemente avaliado
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function isRated (\PDO $conn, int $ticket): bool 
{
    $sql = "SELECT * FROM tickets_rated WHERE ticket = :ticket AND rate IS NOT NULL";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return false;
    }
}


/**
 * hasRatingRow
 * Retorna se existe regitro em tickets_rated para o ticket informado
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function hasRatingRow (\PDO $conn, int $ticket): bool
{
    $sql = "SELECT * FROM tickets_rated WHERE ticket = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return false;
    }
}

/**
 * getRatedInfo
 * Retorna um array com as informações de avaliação do chamado
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getRatedInfo (\PDO $conn, int $ticket): array
{
    $sql = "SELECT * FROM tickets_rated WHERE ticket = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return [];
    }
}


/**
 * getTicketRate
 * Retorna a avaliacao do chamado
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return string|null
 */
function getTicketRate(\PDO $conn, int $ticket): ?string
{
    $sql = "SELECT * FROM tickets_rated WHERE ticket = :ticket AND rate IS NOT NULL";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return $res->fetch()['rate'];
        }
        return null;
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return null;
    }
}


function isWaitingRate(\PDO $conn, int $ticket, int $statusDone, string $baseDate): bool
{

    $sql = "SELECT 
            o.numero
        FROM 
            ocorrencias o
            LEFT JOIN tickets_rated tr ON tr.ticket = o.numero
        WHERE
            o.`numero` = {$ticket} AND 
            o.`operador` <> o.`aberto_por` AND
            o.`status` = {$statusDone} AND
            o.`data_fechamento` IS NOT NULL AND
            o.`data_fechamento` >= '{$baseDate}' AND
            tr.rate IS NULL
        ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        echo "<hr>" . $e->getMessage();
        return false;
    }
}


/**
 * isRejected
 * Retorna se a conclusão do atendimento de um ticket foi rejeitada
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function isRejected(\PDO $conn, int $ticket): bool
{
    $sql = "SELECT * FROM 
                tickets_rated 
            WHERE 
                ticket = :ticket AND 
                rate IS NULL AND 
                rejected_count > 0";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return false;
    }
}


/**
 * hasBeenRejected
 * Retorna se o ticket foi rejeitado em algum momento
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function hasBeenRejected (\PDO $conn, int $ticket): bool
{
    $sql = "SELECT 
                tr.ticket
            FROM
                tickets_rated tr
            WHERE
                tr.ticket = :ticket AND 
                tr.rejected_count > 0
            ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return false;
    }
}


/**
 * getRejectedCount
 * Retorna a quantidade de rejeições do ticket informado
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return int
 */
function getRejectedCount(\PDO $conn, int $ticket): int
{
    $sql = "SELECT 
                rejected_count
            FROM
                tickets_rated
            WHERE
                ticket = :ticket
                ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return $res->fetch()['rejected_count'];
        }
        return 0;
    }
    catch (Exception $e) {
        echo '<hr>' . $e->getMessage();
        return 0;
    }
}

/**
 * ratingLevels
 * Retorna o array dos tipos de avaliação que um atendimento pode receber
 * @return array
 */
function ratingLabels () :array
{
    return [
        'great' => TRANS('ASSESSMENT_GREAT'),
        'good' => TRANS('ASSESSMENT_GOOD'),
        'regular' => TRANS('ASSESSMENT_REGULAR'),
        'bad' => TRANS('ASSESSMENT_BAD'),
        'not_rated' => TRANS('NOT_RATED_IN_TIME')
    ];
}


function ratingLabelsStates () :array
{
    return [
        'rejected' => TRANS('SERVICE_REJECTED'),
        // 'not_rated' => TRANS('SERVICE_NOT_RATED'),
        'evaluate' => TRANS('APPROVE_AND_CLOSE'),
        'pending' => TRANS('PENDING')
    ];
}

/**
 * ratingClasses
 * Retorna as classes para formatação das etiquetas dos tipos de avaliação - 
 * As classes estão definidas em switch_radio.css
 * @return array
 */
function ratingClasses () :array
{
    return [
        'great' => 'color-great',
        'good' => 'color-good',
        'regular' => 'color-regular',
        'bad' => 'color-bad',
        'rejected' => 'color-bad',
        'not_rated' => 'color-not-rated',
        'pending' => 'color-to-rate',
        'evaluate' => 'color-to-rate'
    ];
}




/**
 * renderRate
 * Renderiza a avaliação do atendimento em um badge
 *
 * @param string|null $rate
 * @param bool|null $isDone
 * @param bool|null $isRequester
 * @param string|null $id
 * 
 * @return string
 */
function renderRate (
            ?string $rate, 
            ?bool $isDone = false , 
            ?bool $isRequester = false, 
            ?bool $isRejected = false, 
            ?string $id = null): string
{

    $rate_key = ($rate ? $rate : '');
    
    if (!$rate && $isDone) {
        $rate_key = ($isRequester ? 'evaluate' : 'pending');
    }

    if ($isRejected && !$isDone) {
        $rate_key = "rejected";
    }


    // $label = TRANS('SERVICE_NOT_RATED');
    // $class = "badge-info";
    $label = '';
    $class = '';
    $typeLabels = array_merge(ratingLabels(), ratingLabelsStates());

    $typeClasses = ratingClasses();

    foreach ($typeLabels as $key => $value) {
        if ($rate_key == $key) {
            $label = $value;
            break;
        }
    }
    foreach ($typeClasses as $key => $value) {
        if ($rate_key == $key) {
            $class = $value;
            break;
        }
    }

    $tagId = ($id ? "id=" . $id : "");

    $html = '<span class="btn btn-sm cursor-no-event ' . $class . ' text-white align-middle" '. $tagId .'>'. $label .'</span>'; /* p-2 m-2 mb-2 */

    return $html;
}



/**
 * isFatherOk
 * Testa se o ticket informado pode ser um chamado pai
 * @param \PDO $conn
 * @param int $ticket
 * @return bool
 */
function isFatherOk (\PDO $conn, ?int $ticket): bool
{
    $sql = "SELECT o.numero 
            FROM ocorrencias o, `status` s 
            WHERE 
                o.`status` = s.stat_id AND 
                s.stat_painel NOT IN (3) AND 
                o.numero = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * getCustomFields
 * Retorna a listagem de campos personalizados de acordo com os filtros $tableTo e $type. 
 * Possíveis $type para filtro: text, number, select, select_multi, date, time, datetime, textarea, checkbox 
 * Ou retorna um registro específico caso o $id seja fornecido
 *
 * @param \PDO $conn
 * @param int|null $id
 * @param string|null $tableTo
 * @param array|null $type
 * @param int|null $active
 * 
 * @return array
 */
function getCustomFields (\PDO $conn, ?int $id = null, ?string $tableTo = null, ?array $type = null, ?int $active = 1): array
{

    $terms = ' WHERE 1 = 1 ';
    $typeList = ["text", "number", "select", "select_multi", "date", "time", "datetime", "textarea", "checkbox"];
    
    
    if (!$id) {
        if ($type && is_array($type)) {

            $typesOk = array_intersect($type,$typeList);
            
            if (count($typesOk)) {
                $typesOk = implode("','", $typesOk);
                $terms .= " AND field_type IN ('$typesOk') ";
            } else {
                return [];
            }
            
        }

        if (!empty($tableTo)) {
            $terms .= " AND field_table_to = :tableTo ";
        }

        if (!empty($active)) {
            $terms .= " AND field_active = :active ";
        }
    } else {
        /* Se tiver $id não importa o $type nem o $tableTo*/
        $terms = "WHERE id = :id ";
    }
    

    $sql = "SELECT * FROM custom_fields {$terms} ORDER BY field_active, field_order, field_label";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        else {
            if ($tableTo)
                $res->bindParam(':tableTo', $tableTo, PDO::PARAM_STR);
            // if ($type)
            //     $res->bindParam(':type', $type, PDO::PARAM_STR);
            if ($active)
                $res->bindParam(':active', $active, PDO::PARAM_STR);
        }
        
        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        dump($sql);
        echo $e->getMessage();
        return [];
    }
}


/**
 * getCustomFieldOptionValues
 * Retorna o array com a listagem de opções de seleção para o custom Field ID $fieldId informado
 *
 * @param \PDO $conn
 * @param int $fieldId
 * 
 * @return array
 */
function getCustomFieldOptionValues(\PDO $conn, int $fieldId): array
{
    $sql = "SELECT * FROM custom_fields_option_values WHERE custom_field_id = :fieldId ORDER BY option_value ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':fieldId', $fieldId, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return [];
    }
}



/**
 * getCustomFieldValue
 * Retorna o valor de um option em um campo personalizado do tipo 'select'.
 * Se o campo for do tipo 'select_multi' então retorna a lista de valores
 *
 * @param \PDO $conn
 * @param string $id
 * 
 * @return string|null
 */
function getCustomFieldValue(\PDO $conn, ?string $id = null): ?string
{

    if (!$id) {
        return null;
    }

    $values = "";
    $ids = explode(',', (string)$id);

    foreach ($ids as $id) {
        $sql = "SELECT option_value FROM custom_fields_option_values WHERE id = :id";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':id', $id, PDO::PARAM_INT);
            $res->execute();
            if ($res->rowCount()) {
                if (strlen((string)$values)) $values .= ", ";
                $values .= $res->fetch()['option_value'];
            }
            // return null;
        }
        catch (Exception $e) {
            // $exception .= "<hr>" . $e->getMessage();
            $values = "";
        }
    }
    return $values;
    
}



/**
 * hasCustomFields
 * Retorna se o ticket informado possui informações em campos extras
 *
 * @param \PDO $conn
 * @param int $key (número do ticket ou id do ativos ou ID de outras tabelas envolvidas)
 * @param string $table : o padrão é a busca no tabela tickets_x_cfields
 * 
 * @return bool
 */
function hasCustomFields(\PDO $conn, int $key, ?string $table = null) : bool
{
    if (!$table) {
        $table = "tickets_x_cfields";
        $fieldId = "ticket";
    } elseif ($table == "assets_x_cfields") {
        $fieldId = "asset_id";
    } elseif ($table == "clients_x_cfields") {
        $fieldId = "client_id";
    }

    $sql = "SELECT id FROM {$table} WHERE {$fieldId} = :id";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $key, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
        // return ['error' => $e->getMessage()];
    }
}



function insertCfieldCaseNotExists(\PDO $conn, int $ticket, int $cfield, ?bool $isKey = false): bool
{
    $sql = "SELECT id FROM tickets_x_cfields WHERE ticket = :ticket AND cfield_id = :cfield";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->bindParam(':cfield', $cfield, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        
        $sql = "INSERT INTO tickets_x_cfields (ticket, cfield_id, cfield_is_key) VALUES (:ticket, :cfield, :isKey)";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
            $res->bindParam(':cfield', $cfield, PDO::PARAM_INT);
            $res->bindParam(':isKey', $isKey, PDO::PARAM_INT);
            $res->execute();
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
} 


/**
 * updateOrSetTicketCustomFieldValue
 * Define ou atualiza o valor de um campo personalizado em um ticket a partir de seu ID
 * Se o campo não existir para o chamado, ele é adicionado.
 * @param \PDO $conn
 * @param int $ticket
 * @param int $field_id
 * @param string $field_value
 * 
 * @return bool
 */
function updateOrSetTicketCustomFieldValue(\PDO $conn, int $ticket, int $field_id, string $field_value): bool
{
    $sql = "SELECT 
                field_type 
            FROM 
                custom_fields 
            WHERE 
                id = {$field_id} AND 
                field_table_to = 'ocorrencias'
                ";
    $res = $conn->query($sql);
    if (!$res->rowCount()) {
        return false;
    }
    $row = $res->fetch();

    $isKey = null;
    $typesInt = [
        'select',
        'select_multi'
    ];
    if (in_array($row['field_type'], $typesInt)) {
        $field_value = (int)$field_value;
        $isKey = true;
    }

    insertCfieldCaseNotExists($conn, $ticket, $field_id, $isKey);

    $sql = "UPDATE 
                tickets_x_cfields 
            SET 
                cfield_value = :field_value 
            WHERE 
                ticket = :ticket AND 
                cfield_id = :field_id
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, \PDO::PARAM_INT);
        $res->bindParam(':field_value', $field_value, \PDO::PARAM_STR);
        $res->bindParam(':field_id', $field_id, \PDO::PARAM_INT);
        $res->execute();
        return true;
    } catch (\PDOException $e) {
        echo $e->getMessage();
        return false;
    }
}

/**
 * getTicketCustomFields
 * Retorna um array com todas as informações dos campos extras (campos personalizados) de um ticket informado
 * Índices: field_name, field_label, field_type, field_title, field_placeholder, field_description, 
 * field_value_idx, field_value
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getTicketCustomFields(\PDO $conn, int $ticket, ?int $fieldId = null): array
{
    $ticketExtraInfo = [];
    $empty = [];
    $empty['field_id'] = "";
    $empty['field_name'] = "";
    $empty['field_type'] = "";
    $empty['field_label'] = "";
    $empty['field_title'] = "";
    $empty['field_placeholder'] = "";
    $empty['field_description'] = "";
    $empty['field_attributes'] = "";
    $empty['field_value_idx'] = "";
    $empty['field_value'] = "";
    $empty['field_is_key'] = "";
    $empty['field_order'] = "";


    $terms = "";
    if ($fieldId) {
        $terms = " AND c.id = :fieldId ";
    }

    $sql = "SELECT 
                c.id field_id, c.field_name, c.field_type, c.field_label, c.field_title, c.field_placeholder, 
                c.field_description, c.field_attributes, c.field_order, t.cfield_value as field_value_idx,
                t.cfield_value as field_value, t.cfield_is_key as field_is_key
            FROM 
                custom_fields c, tickets_x_cfields t WHERE t.cfield_id = c.id AND ticket = :ticket 
                {$terms}
            ORDER BY field_order, field_label";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        if ($fieldId) {
            $res->bindParam(':fieldId', $fieldId, PDO::PARAM_INT);
        }
        $res->execute();
        if ($res->rowCount()) {
            $idx = 0;
            foreach ($res->fetchAll() as $row) {
                
                if ($row['field_is_key']) {
                    /* Buscar valor correspondente ao cfield_value */
                    $ticketExtraInfo[$idx]['field_id'] = $row['field_id'];
                    $ticketExtraInfo[$idx]['field_name'] = $row['field_name'];
                    $ticketExtraInfo[$idx]['field_type'] = $row['field_type'];
                    $ticketExtraInfo[$idx]['field_label'] = $row['field_label'];
                    $ticketExtraInfo[$idx]['field_title'] = $row['field_title'];
                    $ticketExtraInfo[$idx]['field_placeholder'] = $row['field_placeholder'];
                    $ticketExtraInfo[$idx]['field_description'] = $row['field_description'];
                    $ticketExtraInfo[$idx]['field_attributes'] = $row['field_attributes'];
                    $ticketExtraInfo[$idx]['field_value_idx'] = $row['field_value'];
                    $ticketExtraInfo[$idx]['field_value'] = getCustomFieldValue($conn, $row['field_value']);
                    $ticketExtraInfo[$idx]['field_is_key'] = $row['field_is_key'];
                    $ticketExtraInfo[$idx]['field_order'] = $row['field_order'];
                } else {
                    $ticketExtraInfo[] = $row;
                }
                $idx++;
            }
            if ($fieldId) {
                /* Único registro retornado */
                return $ticketExtraInfo[0];
            }
            return $ticketExtraInfo;
        }
        return $empty;
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getOlderTicketInProgress
 * Retorna o número do chamado mais antigo ainda em aberto no sistema
 * @param \PDO $conn
 * 
 * @return int
 */
function getOlderTicketInProgress(\PDO $conn): int
{
    $sql = "SELECT 
            MIN(numero) AS ticket_start 
        FROM 
            ocorrencias o, `status` s 
        WHERE 
            -- s.stat_painel NOT IN (3) AND 
            -- s.stat_ignored <> 1 AND
            s.not_done = 1 AND
            o.status = s.stat_id 
            ";
    $res = $conn->query($sql);
    return $res->fetch()['ticket_start'] ?? 1;
}



/**
 * getAssetCustomFields
 * Retorna um array com todas as informações dos campos extras (campos personalizados) de um ativo informado
 * Índices: field_name, field_label, field_type, field_title, field_placeholder, field_description, 
 * field_value_idx, field_value
 * @param \PDO $conn
 * @param int $assetId
 * 
 * @return array
 */
function getAssetCustomFields(\PDO $conn, int $assetId, ?int $fieldId = null): array
{
    $ticketExtraInfo = [];
    $empty = [];
    $empty['field_id'] = "";
    $empty['field_name'] = "";
    $empty['field_type'] = "";
    $empty['field_label'] = "";
    $empty['field_title'] = "";
    $empty['field_placeholder'] = "";
    $empty['field_description'] = "";
    $empty['field_attributes'] = "";
    $empty['field_value_idx'] = "";
    $empty['field_value'] = "";
    $empty['field_is_key'] = "";
    $empty['field_order'] = "";


    $terms = "";
    if ($fieldId) {
        $terms = " AND c.id = :fieldId ";
    }

    $sql = "SELECT 
                c.id field_id, c.field_name, c.field_type, c.field_label, c.field_title, c.field_placeholder, 
                c.field_description, c.field_attributes, c.field_order, a.cfield_value as field_value_idx,
                a.cfield_value as field_value, a.cfield_is_key as field_is_key
            FROM 
                custom_fields c, assets_x_cfields a WHERE a.cfield_id = c.id AND a.asset_id = :asset_id 
                {$terms}
            ORDER BY field_order, field_label";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $assetId, PDO::PARAM_INT);
        if ($fieldId) {
            $res->bindParam(':fieldId', $fieldId, PDO::PARAM_INT);
        }
        $res->execute();
        if ($res->rowCount()) {
            $idx = 0;
            foreach ($res->fetchAll() as $row) {
                
                if ($row['field_is_key']) {
                    /* Buscar valor correspondente ao cfield_value */
                    $ticketExtraInfo[$idx]['field_id'] = $row['field_id'];
                    $ticketExtraInfo[$idx]['field_name'] = $row['field_name'];
                    $ticketExtraInfo[$idx]['field_type'] = $row['field_type'];
                    $ticketExtraInfo[$idx]['field_label'] = $row['field_label'];
                    $ticketExtraInfo[$idx]['field_title'] = $row['field_title'];
                    $ticketExtraInfo[$idx]['field_placeholder'] = $row['field_placeholder'];
                    $ticketExtraInfo[$idx]['field_description'] = $row['field_description'];
                    $ticketExtraInfo[$idx]['field_attributes'] = $row['field_attributes'];
                    $ticketExtraInfo[$idx]['field_value_idx'] = $row['field_value'];
                    $ticketExtraInfo[$idx]['field_value'] = getCustomFieldValue($conn, $row['field_value']);
                    $ticketExtraInfo[$idx]['field_is_key'] = $row['field_is_key'];
                    $ticketExtraInfo[$idx]['field_order'] = $row['field_order'];
                } else {
                    $ticketExtraInfo[] = $row;
                }
                $idx++;
            }
            if ($fieldId) {
                /* Único registro retornado */
                return $ticketExtraInfo[0];
            }
            return $ticketExtraInfo;
        }
        return $empty;
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * getClientCustomFields
 * Retorna um array com todas as informações dos campos extras (campos personalizados) de um ativo informado
 * Índices: field_name, field_label, field_type, field_title, field_placeholder, field_description, 
 * field_value_idx, field_value
 * @param \PDO $conn
 * @param int $clientId
 * 
 * @return array
 */
function getClientCustomFields(\PDO $conn, int $clientId, ?int $fieldId = null): array
{
    $ticketExtraInfo = [];
    $empty = [];
    $empty['field_id'] = "";
    $empty['field_name'] = "";
    $empty['field_type'] = "";
    $empty['field_label'] = "";
    $empty['field_title'] = "";
    $empty['field_placeholder'] = "";
    $empty['field_description'] = "";
    $empty['field_attributes'] = "";
    $empty['field_value_idx'] = "";
    $empty['field_value'] = "";
    $empty['field_is_key'] = "";
    $empty['field_order'] = "";


    $terms = "";
    if ($fieldId) {
        $terms = " AND c.id = :fieldId ";
    }

    $sql = "SELECT 
                c.id field_id, c.field_name, c.field_type, c.field_label, c.field_title, c.field_placeholder, 
                c.field_description, c.field_attributes, c.field_order, a.cfield_value as field_value_idx,
                a.cfield_value as field_value, a.cfield_is_key as field_is_key
            FROM 
                custom_fields c, clients_x_cfields a WHERE a.cfield_id = c.id AND a.client_id = :client_id 
                {$terms}
            ORDER BY field_order, field_label";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':client_id', $clientId, PDO::PARAM_INT);
        if ($fieldId) {
            $res->bindParam(':fieldId', $fieldId, PDO::PARAM_INT);
        }
        $res->execute();
        if ($res->rowCount()) {
            $idx = 0;
            foreach ($res->fetchAll() as $row) {
                
                if ($row['field_is_key']) {
                    /* Buscar valor correspondente ao cfield_value */
                    $ticketExtraInfo[$idx]['field_id'] = $row['field_id'];
                    $ticketExtraInfo[$idx]['field_name'] = $row['field_name'];
                    $ticketExtraInfo[$idx]['field_type'] = $row['field_type'];
                    $ticketExtraInfo[$idx]['field_label'] = $row['field_label'];
                    $ticketExtraInfo[$idx]['field_title'] = $row['field_title'];
                    $ticketExtraInfo[$idx]['field_placeholder'] = $row['field_placeholder'];
                    $ticketExtraInfo[$idx]['field_description'] = $row['field_description'];
                    $ticketExtraInfo[$idx]['field_attributes'] = $row['field_attributes'];
                    $ticketExtraInfo[$idx]['field_value_idx'] = $row['field_value'];
                    $ticketExtraInfo[$idx]['field_value'] = getCustomFieldValue($conn, $row['field_value']);
                    $ticketExtraInfo[$idx]['field_is_key'] = $row['field_is_key'];
                    $ticketExtraInfo[$idx]['field_order'] = $row['field_order'];
                } else {
                    $ticketExtraInfo[] = $row;
                }
                $idx++;
            }
            if ($fieldId) {
                /* Único registro retornado */
                return $ticketExtraInfo[0];
            }
            return $ticketExtraInfo;
        }
        return $empty;
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/**
 * getChannels
 * Retorna um array com a listagem dos canais de entrada ou do canal específico caso o id seja informado
 * O $type filtra se os canais exibidos estão marcados como only_set_by_system:0|1 (de utilização por meios automatizados)
 * @param \PDO $conn
 * @param null|int $id
 * @param null|string $type : restrict|open| null:todos => Tipos de canais
 * @return array
 */
function getChannels (\PDO $conn, ?int $id = null, ?string $type = null): array
{
    $return = [];

    $terms = '';
    $typeList = ["restrict", "open"];
    
    if (!$id && !empty($type)) {
        if (in_array($type, $typeList)) {
            $terms = "WHERE only_set_by_system = :type ";
        } else {
            $return[] = "Invalid type for channel";
            return $return;
        }
        $filter = ($type == "restrict" ? 1 : 0);
    }

    $terms = ($id ? "WHERE id = :id " : $terms); /* Se tiver $id não importa o $type */

    $sql = "SELECT * FROM channels {$terms} ORDER BY name";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        elseif ($type) 
            $res->bindParam(':type', $filter, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}

/**
 * getDefaultChannel
 * Retorna o canal padrão
 * @param PDO $conn
 * @return array
 */
function getDefaultChannel (\PDO $conn): array
{
    $return = [];
    
    $sql = "SELECT * FROM channels WHERE is_default = 1 ";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            return $row;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * isSystemChannel
 * Retorna se o canal informado pelo $id é de utilização interna do sistema ou não
 * @param \PDO $conn
 * @param int $id
 * @return bool
 */
function isSystemChannel (\PDO $conn, int $id): bool
{
    $sql = "SELECT id FROM channels WHERE id = :id AND only_set_by_system = 1 ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * getWorktime
 * Retorna um array com as informações de horarios da jornada de trabalho
 * @param \PDO $conn
 * @param int $profileId
 * @return array
 */
function getWorktime ($conn, $profileId): array
{
    $empty = [];
    
    if (empty($profileId)) {
        return $empty;
    }
        
    $sql = "SELECT * FROM worktime_profiles WHERE id = '{$profileId}'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        return $empty;
    }
}

/**
 * getStatementsInfo
 * Retorna um array com os textos do termo de responsabilidade informado
 * @param \PDO $conn
 * @param string $slug
 * @return array
 */
function getStatementsInfo (\PDO $conn, string $slug): array
{
    $empty = [];
    $empty['header'] = "";
    $empty['title'] = "";
    $empty['p1_bfr_list'] = "";
    $empty['p2_bfr_list'] = "";
    $empty['p3_bfr_list'] = "";
    $empty['p1_aft_list'] = "";
    $empty['p2_aft_list'] = "";
    $empty['p3_aft_list'] = "";
    
    if (empty($slug)) {
        return $empty;
    }
        
    $sql = "SELECT * FROM asset_statements WHERE slug = '{$slug}'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        return $empty;
    }
}


/**
 * getUnits
 * Retorna um array com a listagem com as unidades/instituicoes ou de uma unidade específica caso o id seja informado
 * @param \PDO $conn
 * @param int|null $status : 0 - inactive | 1 - active
 * @param int|null $id
 * @param int|null $client 
 * @return array
 * keys: inst_cod | inst_nome | inst_status | id (client) | fullname (client) | nickname (client)
 */
function getUnits (\PDO $conn, ?int $status = 1, ?int $id = null, ?int $client = null, ?string $allowedUnits = null ): array
{
    $return = [];

    $terms = "";
    
    if (!$id) {

        if ($status != null) {
            $terms = "WHERE un.inst_status = :status ";
        }

        if ($client != null) {
            $terms .= (!empty($terms) ? "AND " : "WHERE ");
            $terms .= " (un.inst_client = :client OR un.inst_client IS NULL)";
        }

        if ($allowedUnits != null) {
            $terms .= (!empty($terms) ? "AND " : "WHERE ");
            $terms .= " un.inst_cod IN ({$allowedUnits})";
        }
    }
    
    $terms = ($id ? "WHERE un.inst_cod = :id " : $terms); /* Se tiver $id não importa os demais critérios */

    $sql = "SELECT 
                un.*, cl.id, cl.fullname, cl.nickname 
            FROM 
                instituicao un
            LEFT JOIN
                clients cl ON cl.id = un.inst_client
                {$terms} 
                ORDER BY inst_nome";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        else {
            if ($status != null)
                $res->bindParam(':status', $status, PDO::PARAM_INT);
            if ($client)
                $res->bindParam(':client', $client, PDO::PARAM_INT);
        }

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        $return['error'] = $e->getMessage();
        return $return;
    }
}


/**
 * getOrphansUnits
 * Retorna listagem de unidades que não possuem clientes associados
 *
 * @param \PDO $conn
 * 
 * @return array
 */
function getOrphansUnits (\PDO $conn): array
{
    $sql = "SELECT 
                * 
            FROM 
                instituicao 
            WHERE 
                inst_client IS NULL
            ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
}


function canAccessAssetInfo (\PDO $conn, int $assetId, string $allowedClients, string $allowedUnits): bool
{
    
    if (empty($allowedClients) && empty($allowedUnits))
        return true;

    $sql = "SELECT 
                e.comp_inst, 
                i.inst_client 
            FROM 
                equipamentos e
                LEFT JOIN instituicao i ON i.inst_cod = e.comp_inst
            WHERE comp_cod = :assetId";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':assetId', $assetId, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            $data = $res->fetch();
            if (!empty($allowedClients) && !in_array($data['inst_client'], explode(',', $allowedClients)))
                return false;
            if (!empty($allowedUnits) && !in_array($data['comp_inst'], explode(',', $allowedUnits)))
                return false;
            return true;
        }
        return false;
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }

}


/**
 * getDepartments
 * Retorna um array com a listagem com os departamentos (com prédio) ou de uma departamento específico caso o id seja informado
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $status : 0 - inactive | 1 - active
 * @param int|null $unit
 * @param int|null $client
 * @return array
 * keys: l.*, reit_nome, prioridade, dominio, pred_desc, tempo_resposta, unidade
 */
function getDepartments (\PDO $conn, ?int $status = 1, ?int $id = null, ?int $unit = null, ?int $client = null): array
{
    $return = [];

    $terms = '';
    
    if (!$id) {
        if ($status !== null)
            $terms .= " WHERE l.loc_status = :status ";
        
        if ($unit !== null) {
            $terms .= (!empty($terms) ? " AND " : " WHERE ");
            $terms .= " (l.loc_unit = :unit OR l.loc_unit IS NULL)";
        }

        if ($client !== null) {
            $terms .= (!empty($terms) ? " AND " : " WHERE ");
            $terms .= " (cl.id = :client OR cl.id IS NULL)";
        }
    }

    $terms = ($id ? "WHERE l.loc_id = :id " : $terms); /* Se tiver $id não importa o $status */

    $sql = "SELECT 
                l.* , r.reit_nome, pr.prior_nivel AS prioridade, d.dom_desc AS dominio, 
                pred.pred_desc, 
                sla.slas_desc as tempo_resposta, 
                un.inst_nome as unidade, 
                cl.id as client_id, cl.fullname, cl.nickname
            FROM 
                localizacao AS l
                LEFT  JOIN reitorias AS r ON r.reit_cod = l.loc_reitoria
                LEFT  JOIN prioridades AS pr ON pr.prior_cod = l.loc_prior
                LEFT  JOIN dominios AS d ON d.dom_cod = l.loc_dominio
                LEFT JOIN predios as pred on pred.pred_cod = l.loc_predio 
                LEFT JOIN sla_solucao as sla on sla.slas_cod = pr.prior_sla
                LEFT JOIN instituicao as un on un.inst_cod = l.loc_unit
                LEFT JOIN clients as cl on cl.id = un.inst_client
                {$terms}
                ORDER BY local";

    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        }
        else {
            if ($unit !== null)
                $res->bindParam(':unit', $unit, PDO::PARAM_INT);
            if ($status !== null)
                $res->bindParam(':status', $status, PDO::PARAM_INT);
            if ($client !== null)
                $res->bindParam(':client', $client, PDO::PARAM_INT);
        }

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


function getUnitFromDepartment (\PDO $conn, int $id): array
{
    $sql = "SELECT
                u.inst_nome,
                u.inst_cod
            FROM
                localizacao l,
                instituicao u
            WHERE
                l.loc_unit = u.inst_cod AND
                l.loc_id = :id
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * getBuildings
 * Retorna a listagem de prédios com unidades e clientes
 *
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $unit
 * @param int|null $client
 * 
 * @return array
 */
function getBuildings (\PDO $conn, ?int $id = null, ?int $unit = null, ?int $client = null): array
{
    $terms = "";
    if ($id) {
        $terms = " WHERE p.pred_cod = :id ";
    } elseif ($unit) {
        $terms = " WHERE u.inst_cod = :unit OR u.inst_cod IS NULL ";
    } elseif ($client) {
        $terms = " WHERE c.id = :client OR c.id IS NULL ";
    }

    $sql = "SELECT
                p.pred_cod,
                p.pred_desc,
                u.inst_cod,
                u.inst_nome,
                c.id, 
                c.nickname
            FROM
                predios p
                LEFT JOIN instituicao u ON u.inst_cod = p.pred_unit 
                LEFT JOIN clients c ON c.id = u.inst_client 
                {$terms} 
            ORDER BY    
                p.pred_desc, c.nickname, u.inst_nome
            ";

    $res = $conn->prepare($sql);
    if ($id) {
        $res->bindParam(':id', $id, PDO::PARAM_INT);
    } elseif ($unit){
        $res->bindParam(':unit', $unit, PDO::PARAM_INT);
    } elseif ($client){
        $res->bindParam(':client', $client, PDO::PARAM_INT);
    }

    $res->execute();
        
    if ($res->rowCount()) {
        foreach ($res->fetchAll() as $row) {
            $data[] = $row;
        }
        if ($id)
            return $data[0];
        return $data;
    }
    return [];
}


/**
 * getRectories
 * Retorna a listagem de reitorias
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $unit
 * @param int|null $client
 * 
 * @return array
 */
function getRectories (\PDO $conn, ?int $id = null, ?int $unit = null, ?int $client = null): array
{
    $terms = "";
    if ($id) {
        $terms = " WHERE r.reit_cod = :id ";
    } elseif ($unit) {
        $terms = " WHERE u.inst_cod = :unit OR u.inst_cod IS NULL ";
    } elseif ($client) {
        $terms = " WHERE c.id = :client OR c.id IS NULL ";
    }

    $sql = "SELECT
                r.reit_cod,
                r.reit_nome,
                u.inst_cod,
                u.inst_nome,
                c.id, 
                c.nickname
            FROM
                reitorias r
                LEFT JOIN instituicao u ON u.inst_cod = r.reit_unit 
                LEFT JOIN clients c ON c.id = u.inst_client 
                {$terms} 
            ORDER BY    
                r.reit_nome, c.nickname, u.inst_nome
            ";

    $res = $conn->prepare($sql);
    if ($id) {
        $res->bindParam(':id', $id, PDO::PARAM_INT);
    } elseif ($unit){
        $res->bindParam(':unit', $unit, PDO::PARAM_INT);
    } elseif ($client){
        $res->bindParam(':client', $client, PDO::PARAM_INT);
    }

    $res->execute();
        
    if ($res->rowCount()) {
        foreach ($res->fetchAll() as $row) {
            $data[] = $row;
        }
        if ($id)
            return $data[0];
        return $data;
    }
    return [];
}


/**
 * getDomains
 * Retorna a listagem de domínios
 *
 * @param \PDO $conn
 * @param int|null $id
 * @param int|null $unit
 * @param int|null $client
 * 
 * @return array
 */
function getDomains (\PDO $conn, ?int $id = null, ?int $unit = null, ?int $client = null): array
{
    $terms = "";
    if ($id) {
        $terms = " WHERE d.dom_cod = :id ";
    } elseif ($unit) {
        $terms = " WHERE u.inst_cod = :unit OR u.inst_cod IS NULL ";
    } elseif ($client) {
        $terms = " WHERE c.id = :client OR c.id IS NULL ";
    }

    $sql = "SELECT
                d.dom_cod,
                d.dom_desc,
                u.inst_cod,
                u.inst_nome,
                c.id, 
                c.nickname
            FROM
                dominios d
                LEFT JOIN instituicao u ON u.inst_cod = d.dom_unit 
                LEFT JOIN clients c ON c.id = u.inst_client 
                {$terms} 
            ORDER BY    
                d.dom_desc, c.nickname, u.inst_nome
            ";

    $res = $conn->prepare($sql);
    if ($id) {
        $res->bindParam(':id', $id, PDO::PARAM_INT);
    } elseif ($unit){
        $res->bindParam(':unit', $unit, PDO::PARAM_INT);
    } elseif ($client){
        $res->bindParam(':client', $client, PDO::PARAM_INT);
    }

    $res->execute();
        
    if ($res->rowCount()) {
        foreach ($res->fetchAll() as $row) {
            $data[] = $row;
        }
        if ($id)
            return $data[0];
        return $data;
    }
    return [];
}



/**
 * getPriorities
 * Retorna um array com a listagem de prioridades de atendimento ou uma prioridade específica caso o id seja informado
 * @param \PDO $conn
 * @param null|int $id
 * @return array
 * keys: pr_cod | pr_nivel | pr_default | pr_desc | pr_color 
 */
function getPriorities (\PDO $conn, ?int $id = null ): array
{
    $return = [];

    $terms = '';
    
    $terms = ($id ? "WHERE pr_cod = :id " : $terms); /* Se tiver $id não importa o $status */

    $sql = "SELECT * FROM prior_atend {$terms} ORDER BY pr_desc";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * getDefaultPriority
 * Retorna um array com a prioridade padrão de atendimento
 * @param \PDO $conn
 * @return array
 * keys: pr_cod | pr_nivel | pr_default | pr_desc | pr_color 
 */
function getDefaultPriority (\PDO $conn): array
{
    $default = 1;
    $sql = "SELECT * FROM prior_atend WHERE pr_default = :default ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':default', $default, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            // $data[] = $res->fetch();
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * updateLastLogon
 * Atualiza a informação sobre a data do último logon do usuário
 * @param \PDO $conn
 * @param int $userId
 * @return void
 */
function updateLastLogon (\PDO $conn, int $userId): void
{
    $sql = "UPDATE usuarios SET last_logon = '" . date("Y-m-d H:i:s") . "', forget = NULL WHERE user_id = '{$userId}' ";
    try {
        $conn->exec($sql);
    }
    catch (Exception $e) {
        return ;
    }
}


function getTicketEmailReferences (\PDO $conn, int $ticket): ?array
{
    $sql = "SELECT * FROM tickets_email_references WHERE ticket = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            $row['references_to'] = htmlspecialchars_decode($row['references_to'], ENT_QUOTES);
            return $row;
        }
        return null;
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }
}


/**
 * setTicketEmailReferences
 * Grava, caso ainda não exista, uma referência de email para um ticket
 * Data indexes to provided: ticket, references_to
 * @param \PDO $conn
 * @param array $data
 * 
 * @return bool
 */
function setTicketEmailReferences (\PDO $conn, array $data): bool
{
    // $data = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $ticket = (isset($data['ticket']) ? (int)$data['ticket'] : "");
    $references_to = (isset($data['references_to']) && !empty($data['references_to']) ? htmlspecialchars($data['references_to'], ENT_QUOTES) : "");
    $original_subject = (isset($data['original_subject']) && !empty($data['original_subject']) ? htmlspecialchars($data['original_subject'], ENT_QUOTES) : "");
    $started_from = (isset($data['started_from']) && !empty($data['started_from']) ? htmlspecialchars($data['started_from'], ENT_QUOTES) : "");

    if (empty($ticket) || empty($references_to)) {
        echo "Dados incompletos";
        return false;
    }

    if (empty(getTicketEmailReferences($conn, $ticket))) {
        $sql = "INSERT INTO tickets_email_references 
                    (ticket, references_to, started_from, original_subject) 
                VALUES 
                    (:ticket, :references_to, :started_from, :original_subject)";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
            $res->bindParam(':references_to', $references_to, PDO::PARAM_STR);
            $res->bindParam(':started_from', $started_from, PDO::PARAM_STR);
            $res->bindParam(':original_subject', $original_subject, PDO::PARAM_STR);
            $res->execute();
            return true;
        }
        catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
    return false;
}

function findTicketByEmailReferences (\PDO $conn, string $references): ?array
{
    $references = htmlspecialchars($references);
    
    $sql = "SELECT * FROM tickets_email_references WHERE references_to = :references_to";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':references_to', $references, PDO::PARAM_STR);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            $row['references_to'] = htmlspecialchars_decode($row['references_to'], ENT_QUOTES);
            $row['original_subject'] = htmlspecialchars_decode($row['original_subject'], ENT_QUOTES);
            $row['started_from'] = htmlspecialchars_decode($row['started_from'], ENT_QUOTES);

            return $row;
        }
        return null;
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }
}


/**
 * getClientInfoFromDomain
 * Retorna as informações do cliente de acordo com o domínio fornecido - caso existam
 * @param mixed $conn
 * @param mixed $domain
 * 
 * @return array|null
 */
function getClientInfoFromDomain($conn, $domain): ?array
{
    $sql = "SELECT * FROM clients WHERE domain = :domain";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':domain', $domain, PDO::PARAM_STR);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    } catch (PDOException $e) {
        return null;
    }
}


/**
 * getMailConfig
 * Retorna o array com as informações de configuração de e-mail
 * @param \PDO $conn
 * @return array
 */
function getMailConfig (\PDO $conn): array
{
    $sql = "SELECT * FROM mailconfig";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}
/**
 * getEventMailConfig
 * Retorna o array com as informações dos templates de mensagens de e-mail para cada evento
 * @param \PDO $conn
 * @param string $event
 * @return array
 */
function getEventMailConfig (\PDO $conn, string $event): array
{
    $sql = "SELECT * FROM msgconfig WHERE msg_event like (:event)";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':event', $event, PDO::PARAM_STR);
        $res->execute();

        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getStatusInfo
 * Retorna o array com as informações do status filtrado
 * [stat_id], [status], [stat_cat], [stat_painel], [stat_time_freeze], [stat_ignored]
 * @param \PDO $conn
 * @param int $statusId
 * @return array
 */
function getStatusInfo ($conn, ?int $statusId): array
{
    if (!$statusId)
        return array("status" => "", "stat_cat" => "", "stat_painel" => "", "stat_time_freeze" => "");
    
    $sql = "SELECT * FROM `status` WHERE stat_id = '" . $statusId . "'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getOperatorTickets
 * Retorna o total de chamados vinculados a um determinado operador
 * @param \PDO $conn
 * @param int $userId
 * @return int
 */
function getOperatorTickets (\PDO $conn, int $userId): int
{
    $sql = "SELECT 
                count(*) AS total 
            FROM 
                ocorrencias o, `status` s 
            WHERE 
                o.operador = {$userId} AND 
                o.status = s.stat_id AND 
                s.stat_painel = 1  AND 
                o.oco_scheduled = 0
            ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch()['total'];
        return 0;
    }
    catch (Exception $e) {
        return 0;
    }
}


/**
 * getAreaInfo
 * Retorna o array com as informações da área de atendimento:
 * [area_id], [area_name], [status], [email], [atende], [screen], [wt_profile], [sis_months_done ]
 * @param \PDO $conn
 * @param int $areaId
 * @return array
 */
function getAreaInfo (\PDO $conn, int $areaId): array
{
    $sql = "SELECT 
                sis_id as area_id, 
                sistema as area_name, 
                sis_status as status, 
                sis_email as email, 
                sis_atende as atende, 
                sis_screen as screen, 
                sis_wt_profile as wt_profile, 
                sis_months_done, 
                sis_opening_mode,
                use_own_config_cat_chain,
                sis_cat_chain_at_opening
            FROM 
                sistemas 
            WHERE 
                sis_id = '" . $areaId . "'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getAreaAdminsOld
 * Retorna os admins da área informada (apenas área primária) por $areaId ou vazio
 * Indices retornados: user_id | nome | email
 * @param \PDO $conn
 * @param int $areaId
 * 
 * @return array
 */
function getAreaAdminsOld (\PDO $conn, int $areaId):array
{
    $data = [];
    $sql = "SELECT user_id, nome, email FROM usuarios WHERE AREA = :areaId AND user_admin = 1 ORDER BY nome";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':areaId', $areaId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getAreaAdmins
 * Retorna os admins da área informada (primária ou secundárias) por $areaId ou vazio
 *
 * @param \PDO $conn
 * @param int $areaId
 * @param float $maxCost - Valor máximo que o gerente pode aprovar
 * 
 * @return array
 */
function getAreaAdmins (\PDO $conn, int $areaId, ?float $maxCost = null):array
{
    $dataPrimary = [];
    $dataSecundary = [];

    $terms = '';
    if ($maxCost !== null) {
        $terms = " AND u.max_cost_authorizing >= :maxCost ";
    }

    /**
     * Checagem na tabela sobre as áreas secundárias
     */
    $sql = "SELECT 
                u.user_id, 
                u.nome, 
                u.email,
                u.max_cost_authorizing
            FROM
                usuarios u, users_x_area_admin uadmin
            WHERE
                u.user_id = uadmin.user_id AND
                u.user_admin = 1 AND
                uadmin.area_id = :areaId 
                {$terms}
            ORDER BY
                u.nome
            ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":areaId", $areaId, PDO::PARAM_INT);
        if ($maxCost !== null) {
            $res->bindParam(":maxCost", $maxCost, PDO::PARAM_STR);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $dataSecundary[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return [
                'error' => $e->getMessage(),
                'sql'   => $sql
            ];
    }

    /**
     * Checagem sobre as áreas primárias
     */
    $sql = "SELECT 
                u.user_id, 
                u.nome, 
                u.email, 
                u.max_cost_authorizing
            FROM 
                usuarios u 
            WHERE 
                u.AREA = :areaId AND 
                u.user_admin = 1 
                {$terms}
            ORDER BY 
                u.nome";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':areaId', $areaId);
        if ($maxCost !== null) {
            $res->bindParam(":maxCost", $maxCost, PDO::PARAM_STR);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $dataPrimary[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
            'sql'   => $sql
        ];
    }


    $output = array_merge($dataPrimary, $dataSecundary);
    $output = unique_multidim_array($output, 'user_id');
    
    $keys = array_column($output, 'nome');
    array_multisort($keys, SORT_ASC, $output);

    return $output;

}


/**
 * getManagedAreasByUser
 * Retorna array com a listagem das áreas que o usuário informado é gerente (área primária e secundárias)
 * @param \PDO $conn
 * @param int $userId
 * 
 * @return array
 */
function getManagedAreasByUser (\PDO $conn, int $userId):array
{
    $dataPrimary = [];
    $dataSecundary = [];

    /**
     * Checagem na tabela sobre as áreas não primárias
     */
    $sql = "SELECT 
                s.sis_id, 
                s.sistema, 
                s.sis_email
            FROM
                -- sistemas s, usuarios u, usuarios_areas uareas, users_x_area_admin uadmin
                sistemas s, usuarios u, users_x_area_admin uadmin
            WHERE
                -- s.sis_id = uareas.uarea_sid AND 
                -- uareas.uarea_sid = uadmin.area_id AND 
                -- u.user_id = uareas.uarea_uid AND 
                s.sis_id = uadmin.area_id AND 
                uadmin.user_id = u.user_id AND 
                u.user_admin = 1 AND 
                u.user_id = :userId
            ORDER BY
                s.sistema
            ";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":userId", $userId, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $dataSecundary[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return [
                'error' => $e->getMessage(),
                'sql'   => $sql
            ];
    }

    /**
     * Checagem sobre as áreas primárias
     */
    $sql = "SELECT 
                s.sis_id, 
                s.sistema, 
                s.sis_email 
            FROM 
                sistemas s, 
                usuarios u
            WHERE 
                u.AREA = s.sis_id AND
                u.user_id = :userId AND 
                u.user_admin = 1 
            ORDER BY s.sistema";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userId', $userId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $dataPrimary[] = $row;
            }
        }
    }
    catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
            'sql'   => $sql
        ];
    }

    $output = array_merge($dataPrimary, $dataSecundary);
    $output = unique_multidim_array($output, 'sis_id');
    
    $keys = array_column($output, 'sistema');
    array_multisort($keys, SORT_ASC, $output);

    return $output;

}


/**
 * getAreas
 * Retorna o array de registros das áreas cadastradas:
 * [sis_id], [sistema], [status], [sis_email], [sis_atende], [sis_screen], [sis_wt_profile]
 * @param \PDO $conn
 * @param int $all |1: todos os registros| 0: checará os outros parametros de filtro
 * @param int|null $status |0: inativas| 1: ativas | null: qualquer
 * @param int|null $atende |0: somente abertura| 1: atende chamados | null: qualquer
 * @param array|null $ids: caso sejam informados IDS, a consulta retornará apenas o registros correspondentes
 * @return array
 */
function getAreas (\PDO $conn, int $all = 1, ?int $status = 1, ?int $atende = 1, ?array $ids = null): array
{
    $terms = "";
    
    if ($ids !== null && !empty($ids)) {
        $stringIds = implode(',', $ids);
        $terms = " AND sis_id IN ({$stringIds})";
    } elseif ($all == 0) {
        // $terms .= ($status == 1 ? " AND sis_status = 1 " : " AND sis_status = 0 ");
        $terms .= (isset($status) && $status == 1 ? " AND sis_status = 1 " : (isset($status) && $status == 0 ? " AND sis_status = 0 " : ""));
        // $terms .= ($atende == 1 ? " AND sis_atende = 1 " : " AND sis_atende = 0 ");
        $terms .= (isset($atende) && $atende == 1 ? " AND sis_atende = 1 " : (isset($atende) && $atende == 0 ? " AND sis_atende = 0 " : ""));
    }
    
    $data = [];
    $sql = "SELECT 
                *
            FROM 
                sistemas 
            WHERE 
                1 = 1 
                {$terms}
            ORDER BY sistema";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ["error" => $e->getMessage()];
    }
}

/**
 * getModuleAccess
 * Retorna se a área tem permissão de acesso ao módulo do sistema:
 * [perm_area], [perm_modulo]
 * @param \PDO $conn: conexão PDO
 * @param int $module - 1: ocorrências - 2: inventário
 * @param mixed $areaId - id da área de atendimento - podem ser várias áreas (secundárias) 
 * @return bool
 */
function getModuleAccess (\PDO $conn, int $module, $areaId): bool
{
    $sql = "SELECT 
                perm_area, perm_modulo
            FROM 
                permissoes 
            WHERE 
                perm_modulo = '" . $module . "' 
            AND
                perm_area IN ('" . $areaId . "') ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return true;
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * getStatus
 * Retorna o array de registros dos status cadastradas:
 * [stat_id], [status], [stat_cat], [stat_painel], [stat_time_freeze], [stat_ignored]
 * @param \PDO $conn
 * @param int $all 1: todos os registros | 0: checará os outros parametros de filtro
 * @param string $painel 1: vinculado ao operador, 2: principal  3: oculto
 * @param string $timeFreeze 0: status sem parada 1: status de parada
 * @param array | null $except : array com ids de status para não serem listados
 * @return array
 */
function getStatus (\PDO $conn, int $all = 1, string $painel = '1,2,3', string $timeFreeze = '0,1', ?array $except = null): array
{
    $terms = "";
    $excluding = "";
    if ($all == 0) {
        $terms .= " AND stat_painel in ({$painel}) ";
        $terms .= " AND stat_time_freeze in ({$timeFreeze}) ";

        if ($except && !empty($except)) {
            $treatedExcept = array_map('intval', $except);
            foreach ($treatedExcept as $exclude) {
                if (strlen((string)$excluding)) $excluding .= ",";
                $excluding .= $exclude;
            }
            $terms .= " AND stat_id NOT IN ({$excluding}) ";
        }
    }
    
    $data = [];
    $sql = "SELECT 
                s.*, stc.*
            FROM 
                status s LEFT JOIN status_categ stc ON s.stat_cat = stc.stc_cod
            WHERE 
                1 = 1 
                {$terms}
            ORDER BY status";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getStatusById
 * Retorna o array com o registro pesquisado
 * @param \PDO $conn
 * @param int $id
 * @return array
 */
function getStatusById(\PDO $conn, int $id): array
{
    $empty = [];

    $sql = "SELECT * FROM `status` WHERE stat_id = {$id} ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        // return $e->getMessage();
        return $empty;
    }
}



/**
 * getTicketEntries
 * Retorna os assentamentos do chamado informado
 * Fields: 
 * @param \PDO $conn
 * @param int $ticket
 * @param bool|null $private
 * 
 * @return array|null
 */
function getTicketEntries(\PDO $conn, int $ticket, ?bool $private = false): ?array
{

    $terms = "";
    if (!$private) {
        $terms = " AND a.asset_privated = 0 ";
    }

    $data = [];
    /* $sql = "SELECT 
                a.*, u.*
            FROM 
                assentamentos a, usuarios u 
            WHERE 
                a.responsavel = u.user_id AND
                a.ocorrencia = :ticket 
                {$terms}
            ORDER BY numero"; */

    $sql = "SELECT 
                a.*, u.* 
            FROM 
                assentamentos a LEFT JOIN usuarios u ON u.user_id = a.responsavel 
            WHERE 
                a.ocorrencia = :ticket 
                {$terms}
            ORDER BY numero";
            
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getLastEntry
 * Retorna o array com as informações do último assentamento do chamado:
 * [numero], [ocorrencia], [assentamento], [created_at], [responsavel], [asset_privated], [tipo_assentamento]
 * @param \PDO $conn
 * @param int $ticket
 * @param bool $onlyPublic Define se também será considerado assentamento privado
 * @return array
 */
function getLastEntry (\PDO $conn, int $ticket, bool $onlyPublic = true): array
{
    $empty = [];
    $empty['numero'] = "";
    $empty['ocorrencia'] = "";
    $empty['assentamento'] = "";
    $empty['created_at'] = "";
    $empty['responsavel'] = "";
    $empty['asset_privated'] = "";
    $empty['tipo_assentamento'] = "";

    $terms = ($onlyPublic ? " AND asset_privated = 0 " : "");
    
    $sql = "SELECT 
                numero,
                ocorrencia,
                assentamento,
                created_at as `data`,
                responsavel,
                asset_privated,
                tipo_assentamento
            FROM 
                assentamentos 
            WHERE 
                ocorrencia = '{$ticket}' 
                AND
                numero = (SELECT MAX(numero) FROM assentamentos WHERE ocorrencia = '{$ticket}' {$terms} )
            ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $row = $res->fetch();
        $row['assentamento'] = str_replace(["'", "\""], "", $row['assentamento']);
        return $row;
        // return $res->fetch();
    }
    return $empty;
}


/**
 * getLastScheduledDate
 * Retorna a última data de agendamento do chamado
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return string|null
 */
function getLastScheduledDate (\PDO $conn, int $ticket): ?string
{
    
    $sql = "SELECT oco_scheduled_to 
            FROM ocorrencias 
            WHERE 
                numero = :ticket AND 
                oco_scheduled_to IS NOT NULL";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['oco_scheduled_to'];
        }

        /* ocorrencias_log.log_data_agendamento */
        $sql = "SELECT
                log_data_agendamento
            FROM
                ocorrencias_log
            WHERE 
                log_numero = :ticket AND
                log_id = (
                            SELECT MAX(log_id) 
                            FROM ocorrencias_log 
                            WHERE 
                                log_numero = :ticket AND 
                                log_data_agendamento IS NOT NULL
                        )
        ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
            $res->execute();
            if ($res->rowCount()) {
                return $res->fetch()["log_data_agendamento"];
            }
            return null;
        } catch (Exception $e) {
            // echo $e->getMessage();
            return null;
        }

    } catch (Exception $e) {
        // echo $e->getMessage();
        return null;
    }
}



/**
 * getTicketFiles
 * Retorna um array com as informações dos arquivos anexos ao chamado informado
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array|null
 */
function getTicketFiles(\PDO $conn, int $ticket): ?array
{
    $sql = "SELECT * FROM imagens WHERE img_oco = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getTicketRelatives
 * Retorna um array com os números dos chamados relacionados (pai e filhos)
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array|null
 */
function getTicketRelatives(\PDO $conn, int $ticket): ?array
{
    $sql = "SELECT * FROM ocodeps WHERE dep_pai = :ticket OR dep_filho = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * Retrieves the first father of a given ticket number and populates the $store array with the relationships.
 *
 * @param PDO $conn the database connection object
 * @param int $ticketNumber the ticket number to search for
 * @param array &$store the array to store the relationships
 * @throws Exception if an error occurs while executing the SQL query
 * @return int the ticket number of the first father
 */
function getFirstFather(\PDO $conn, int $ticketNumber, array &$store): ?int
{
    $data = [];
    $parent = [];
    $sons = [];
    
    // primeiro checo se possui qualquer relacionamento
    $sql = "SELECT * FROM ocodeps WHERE dep_pai = :ticket OR dep_filho = :ticket ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticketNumber, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
                $parent[] = $row['dep_pai'];
                $sons[] = $row['dep_filho'];
            }
        
        
            /* Como só pode existir um pai - reduzo o array em que ele aparece */
            $parent = array_unique($parent);
            if (in_array($ticketNumber, $parent)) {
                unset($parent[array_search($ticketNumber, $parent)]);
            }

            $relation['parent'] = $parent;
            
            $sons = array_unique($sons);
            if (in_array($ticketNumber, $sons)) {
                unset($sons[array_search($ticketNumber, $sons)]);
            }
            $relation['sons'] = $sons;

            $store[] = $ticketNumber;

            /* Recursividade para subir na hierarquia */
            foreach ($relation['parent'] as $parent) {
                getFirstFather($conn, $parent, $store);
            }

            return min($store);
        }
        return null;
    }
    catch (Exception $e) {
        return null;
        // return ['error' => $e->getMessage()];
    }
}

/**
 * Retrieves the ticket's down relations from the database.
 *
 * @param PDO $conn The database connection.
 * @param int $ticketNumber The ticket number to retrieve the relations for.
 * @param array &$store The reference to the array to store the relations in.
 * @throws Exception When an error occurs during the retrieval process.
 * @return array The array of relations for the given ticket number.
 */
function getTicketDownRelations(\PDO $conn, ?int $ticketNumber, array &$store): array
{
    $data = [];
    $parent = [];
    $sons = [];

    if (!$ticketNumber) {
        return [];
    }
    
    // primeiro checo se possui qualquer relacionamento
    $sql = "SELECT * FROM ocodeps WHERE dep_pai = :ticket OR dep_filho = :ticket ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticketNumber, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
                $parent[] = $row['dep_pai'];
                $sons[] = $row['dep_filho'];
            }
            $parent = array_unique($parent);
        
            if (in_array($ticketNumber, $parent)) {
                unset($parent[array_search($ticketNumber, $parent)]);
            }

            /* Cada chamado só terá um único pai */
            $relation['parent'] = (!empty($parent[0]) ? $parent[0] : null);
            
            $sons = array_unique($sons);
            if (in_array($ticketNumber, $sons)) {
                unset($sons[array_search($ticketNumber, $sons)]);
            }
            $relation['sons'] = $sons;

            $store[$ticketNumber] = $relation;

            /* Recursividade para percorrer os filhos */
            foreach ($relation['sons'] as $son) {
                getTicketDownRelations($conn, $son, $store);
            }

            return $store;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * Generates an HTML tree structure using a given array.
 * Receberá o array resultante da função getTicketDownRelations()
 *
 * @param array $relations The array representing the family tree.
 * @param int $parentId The ID of the parent node.
 * @return string The HTML tree structure.
 */
function generateFamilyTreeGeneric(array $relations, ?int $parentId = null) {
    $html = '<ul>';
    foreach ($relations as $key => $ticket) {
        if ($ticket['parent'] == $parentId) {
            $html .= '<li><div>' . $key;
            if (isset($ticket['sons'])) {
                $html .= generateFamilyTreeGeneric($relations, $key);
            }
            $html .= '</div></li>';
        }
    }
    $html .= '</ul>';
    return $html;
}


function generateFamilyTree(array $relations, ?int $parentId = null, string $nodeIdPrefix = 'tree_') {
    
    $html = '';
    $firstRound = ($parentId === null);
    if ($firstRound) {
        $html = '<ul class="ocomon-tree"><li>';
    } else {
        $html .= '<ul>';
    }
    
    foreach ($relations as $key => $ticket) {
        if ($ticket['parent'] == $parentId) {
            
            if ($firstRound) {
                $html .= '<div class="sticky tree-nodes" data-ticket="' . $key . '" id="' . $nodeIdPrefix . $key . '">' . $key . '<div id="badge_' . $key . '" class="badge-light"></div></div>';
            } else {
                $html .= '<li><div class="tree-nodes" data-ticket="' . $key . '" id="' . $nodeIdPrefix . $key . '">' . $key . '<div id="badge_' . $key . '" class="badge-light"></div></div>';
            }
            
            if (isset($ticket['sons'])) {
                $html .= generateFamilyTree($relations, $key);
            }
            if (!$firstRound) {
                $html .= '</li>';
            }
        }
    }

    if ($firstRound) {
        $html .= '</li></ul>';
    } else {
        $html .= '</ul>';
    }

    return $html;
}


/**
 * getTicketProjectId
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return int|null
 */
function getTicketProjectId(\PDO $conn, int $ticket): ?int
{
    $sql = "SELECT * FROM ocodeps WHERE dep_pai = :ticket OR dep_filho = :ticket AND proj_id IS NOT NULL";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['proj_id'];
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}


/**
 * getProjectDefinition
 * Retorna um array com as informações sobre o projeto: id, name e description
 *
 * @param \PDO $conn
 * @param int $projectID
 * 
 * @return array
 */
function getProjectDefinition(\PDO $conn, int $projectID): array
{
    $sql = "SELECT * FROM projects WHERE id = :projectID";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":projectID", $projectID, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    } catch (Exception $e) {
        return [];
    }
}


/**
 * getTicketsFromProject
 * Retorna a listagem de todos os chamados de um projeto - ordenados pelo número
 *
 * @param \PDO $conn
 * @param int $projectID
 * 
 * @return array
 */
function getTicketsFromProject (\PDO $conn, int $projectID): array
{
    $data = [];
    $data['fathers'] = [];
    $data['sons'] = [];
    $sql = "SELECT * FROM ocodeps WHERE proj_id = :projectID";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":projectID", $projectID, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data['fathers'][] = $row['dep_pai'];
                $data['sons'][] = $row['dep_filho'];
            }
            // Combinar os dois arrays para retornar apenas um array com números únicos
            $data['tickets'] = array_unique(array_merge($data['fathers'], $data['sons']));
            sort($data['tickets']);

            return $data['tickets'];
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}




/**
 * hasDependency
 * Retorna se um dado chamado possui dependências em subchamados
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function hasDependency(\PDO $conn, int $ticket): bool
{
    $sql = "SELECT * FROM ocodeps WHERE dep_pai = :ticket ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()){
            foreach ($res->fetchAll() as $row) {
                $sql = "SELECT o.numero FROM ocorrencias o, `status` s 
                        WHERE
                            o.numero = :ticket AND 
                            o.`status` = s.stat_id AND 
                            s.stat_painel NOT IN (3)
                ";
                try {
                    $result = $conn->prepare($sql);
                    $result->bindParam(':ticket', $row['dep_filho'], PDO::PARAM_INT);
                    $result->execute();
                    if ($result->rowCount()) {
                        return true;
                    }
                }
                catch (Exception $e) {
                    return true;
                }
            }
        }
        return false;
    }
    catch (Exception $e) {
        return true;
    }
    return false;
}




/**
 * getSolutionInfo
 * Retorna o array com as informações de descrição técnica e solução para o chamado ou vazio caso nao tenha registro:
 * [numero], [problema], [solucao], [data], [responsavel]
 * @param \PDO $conn
 * @param int $ticket
 * @return array
 */
function getSolutionInfo (\PDO $conn, int $ticket): array
{
    $sql = "SELECT 
                * 
            FROM 
                solucoes 
            WHERE 
                numero = :ticket 
            ";
    
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }

}


/**
 * getGlobalUri
 * Retorna a url de acesso global da ocorrencia
 * @param \PDO $conn
 * @param int $ticket
 * @return string
 */
function getGlobalUri (\PDO $conn, int $ticket): string
{
    $config = getConfig($conn);

    $sql = "SELECT * FROM global_tickets WHERE gt_ticket = '" . $ticket . "' ";
    $res = $conn->query($sql);
    if ( $res->rowCount() ) {
        $row = $res->fetch();
        return $config['conf_ocomon_site'] . "/ocomon/geral/ticket_show.php?numero=" . $ticket . "&id=" . $row['gt_id'];
    }

    $rand = random64();
    $rand = str_replace(" ", "+", $rand);
    $rand = noHtml($rand);
    $sql = "INSERT INTO global_tickets (gt_ticket, gt_id) VALUES ({$ticket}, '" . $rand . "')";
    $conn->exec($sql);
    
    return $config['conf_ocomon_site'] . "/ocomon/geral/ticket_show.php?numero=" . $ticket . "&id=" . $rand;
}


/**
 * getGlobalTicketId
 * Retorna o id global da ocorrência para acesso por qualquer usuário
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return string|null
 */
function getGlobalTicketId (\PDO $conn, int $ticket): ?string
{
    $sql = "SELECT gt_id FROM global_tickets WHERE gt_ticket = :ticket ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {
            // return $res->fetch()['gt_id'];
            return str_replace(" ", "+", $res->fetch()['gt_id']);
        }
        return null;
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return null;
    }
}


/**
 * getGlobalTicketRatingId
 * Retorna o id random para avaliação do atendimento - Caso o ID não exista, é criado e então retornado
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return string|null
 */
function getGlobalTicketRatingId (\PDO $conn, int $ticket): ?string
{
    $sql = "SELECT gt_rating_id FROM global_tickets WHERE gt_ticket = :ticket ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {

            $row = $res->fetch();

            if (!empty($row['gt_rating_id'])) {
                return str_replace(" ", "+", $row['gt_rating_id']);
            }

            $rand = random64();
            $rand = str_replace(" ", "+", $rand);
            $sql = "UPDATE global_tickets SET gt_rating_id = '{$rand}' WHERE gt_ticket = :ticket";
            try {
                $res = $conn->prepare($sql);
                $res->bindParam(':ticket', $ticket);
                $res->execute();

                return $rand;
            }
            catch (Exception $e) {
                // $exception .= "<hr>" . $e->getMessage();
                // echo $e->getMessage();
                return null;
            }
        }
        return null;
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return null;
    }
}

/**
 * getEnvVarsValues
 * Retorna um array com os valores das variáveis de ambiente para serem utilizadas nos templates de envio de e-mail
 * @param \PDO $conn
 * @param int $ticket
 * @return array
 */
function getEnvVarsValues (\PDO $conn, int $ticket, ?array $row = null): array
{
    
    if (!$row) {
        include ("../../includes/queries/queries.php");
        $sql = $QRY["ocorrencias_full_ini"] . " WHERE o.numero = {$ticket} ";
        $res = $conn->query($sql);
        $row = $res->fetch();
    }
    
    $config = getConfig($conn);
    $lastEntry = getLastEntry($conn, $ticket);
    $solution = getSolutionInfo($conn, $ticket);
    $workers = getTicketWorkers($conn, $ticket);

    /* Variáveis de ambiente para os e-mails */
    $vars = array();

    $vars = array();
    $vars['%numero%'] = $row['numero'];
    $vars['%usuario%'] = $row['contato'];
    $vars['%contato%'] = $row['contato'];
    $vars['%contato_email%'] = $row['contato_email'];
    $vars['%descricao%'] = nl2br($row['descricao']);
    $vars['%departamento%'] = $row['setor'];
    $vars['%telefone%'] = $row['telefone'];
    $vars['%site%'] = "<a href='" . $config['conf_ocomon_site'] . "'>" . $config['conf_ocomon_site'] . "</a>";
    $vars['%area%'] = $row['area'];
    $vars['%area_email%'] = $row['area_email'];
    $vars['%operador%'] = $row['nome'];
    $vars['%editor%'] = $row['nome'];
    $vars['%aberto_por%'] = $row['aberto_por'];
    $vars['%problema%'] = $row['problema'];
    $vars['%versao%'] = VERSAO;
    $vars['%url%'] = getGlobalUri($conn, $ticket);
    $vars['%url%'] = str_replace(" ", "+", $vars['%url%']);
    $vars['%linkglobal%'] = $vars['%url%'];

    $vars['%unidade%'] = $row['unidade'];
    $vars['%etiqueta%'] = $row['etiqueta'];
    $vars['%patrimonio%'] = $row['unidade']."&nbsp;".$row['etiqueta'];
    $vars['%data_abertura%'] = dateScreen($row['oco_real_open_date']);
    $vars['%status%'] = $row['chamado_status'];
    $vars['%data_agendamento%'] = (!empty($row['oco_scheduled_to']) ? dateScreen($row['oco_scheduled_to']) : "");
    $vars['%data_fechamento%'] = (!empty($row['data_fechamento']) ? dateScreen($row['data_fechamento']) : "");

    $vars['%dia_agendamento%'] = (!empty($vars['%data_agendamento%']) ? explode(" ", $vars['%data_agendamento%'])[0] : '');
    $vars['%hora_agendamento%'] = (!empty($vars['%data_agendamento%']) ? explode(" ", $vars['%data_agendamento%'])[1] : '');

    $vars['%descricao_tecnica%'] = $solution['problema'] ?? "";
    $vars['%solucao%'] = $solution['solucao'] ?? "";
    $vars['%assentamento%'] = nl2br($lastEntry['assentamento']);

    $vars['%funcionario_responsavel%'] = "";
    $vars['%funcionario%'] = [];
    $vars['%funcionario_email%'] = [];
    $vars['%funcionarios%'] = "";
    $func = "";
    if (!empty($workers)) {
        // $i = 0;
        foreach ($workers as $worker) {
            if (strlen((string)$func) > 0) {
                $func .= ", ";
            }

            if ($worker['main_worker'] == 1) {
                $vars['%funcionario_responsavel%'] = $worker['nome'];
            }

            $func .= $worker['nome'];
            $vars['%funcionario%'][] = $worker['nome'];
            $vars['%funcionario_email%'][] = $worker['email'];
            // $i++;
        }
        $vars['%funcionarios%'] .= $func;
    }
    return $vars;
}

/**
 * getEnvVars
 * Retorna o registro gravado com as variáveis de ambiente disponíveis
 * @param \PDO $conn
 * @param int|null $context: null = padrão para tickets, 1 = nao definido, 2 = termo de compromisso, 4 = usuários
 * @param string|null $event
 * @return string |null
 */
function getEnvVars (\PDO $conn, ?int $context = null, ?string $event = null): ?string
{
    
    $terms = "";
    if (empty($context) && empty($event)) {
        $terms = " WHERE id = 1 ";
    } 
    
    if (!empty($context)) {
        $terms .= " WHERE context = '{$context}' ";
    } 

    if (!empty($event)) {
        $terms = (!empty($terms) ? $terms . " AND event = '{$event}' " : " WHERE event = '{$event}' ");
    }
    
    $sql = "SELECT vars FROM environment_vars {$terms} ";
    try {
        $res = $conn->query($sql);
        if (!$res->rowCount()) {
            return null;
        }
        return $res->fetch()['vars'];
    }
    catch (Exception $e) {
        return null;
    }
}


/** 
 * insert_ticket_stage
 * Realiza a inserção das informações de período de tempo para o chamado
 * @param \PDO $conn
 * @param int $ticket: número do chamado
 * @param string $stage_type: start|stop
 * @param int $tk_status: status do chamado - só será gravado quando o $stage_type for 'start'
 * @param string $specificDate: data específica para gravar - para os casos de chamados saindo 
 *  da fila de agendamento por meio de processos automatizados
 * @return bool
 * 
*/
function insert_ticket_stage (
    \PDO $conn, 
    int $ticket, 
    string $stageType, 
    int $tkStatus, 
    ?int $treaterId = null, 
    string $specificDate = ''): bool
{

    $date = (!empty($specificDate) ? $specificDate : date("Y-m-d H:i:s"));
    
    $sqlTkt = "SELECT * FROM `tickets_stages` 
                WHERE ticket = {$ticket} AND id = (SELECT max(id) FROM tickets_stages WHERE ticket = {$ticket}) ";
    $resultTkt = $conn->query($sqlTkt);
    $recordsTkt = $resultTkt->rowCount();

    /* Nenhum registro do chamado na tabela. Nesse caso posso apenas inserir um novo */
    if (!$recordsTkt && $stageType == 'start') {
        
        $sql = "INSERT INTO tickets_stages (id, ticket, date_start, status_id, treater_id) 
        values (null, {$ticket}, '" . $date . "', {$tkStatus}, {$treaterId}) ";
    
    } elseif (!$recordsTkt && $stageType == 'stop') {
        
        /* Para chamados existentes anteriormente à implementação da tickets_stages */
        $sqlDateTicket = "SELECT data_abertura, oco_real_open_date FROM ocorrencias WHERE numero = {$ticket} ";
        $resDateTicket = $conn->query($sqlDateTicket);

        $rowDateTicket = $resDateTicket->fetch();

        $openDate = $rowDateTicket['data_abertura'];
        $realOpenDate = $rowDateTicket['oco_real_open_date'];

        $recordDate = (!empty($realOpenDate) ? $realOpenDate : $openDate);

        /* Chamado já existia - nesse caso adiciono um período de start e stop com data de abertura registrada para o chamado*/
        /* o Status zero será para identificar que o período foi inserido nessa condição especial */
        $sql = "INSERT INTO tickets_stages (id, ticket, date_start, date_stop, status_id, treater_id) 
        values (null, {$ticket}, '" . $recordDate . "', '" . $date . "', 0, {$treaterId}) ";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            return false;
        }
        
        //Não posso iniciar um estágio de tempo sem ter primeiro um registro de 'start'
        // return false;
        return true;
    }

    /* Já há registro para esse chamado na tabela de estágios de tempo */
    if ($recordsTkt) {
        $row = $resultTkt->fetch();

        /* há uma data de parada no último registro */
        if (!empty($row['date_stop'])) {
            /* Então preciso inserir novo registro de start */
            if ($stageType == 'start') {
                $sql = "INSERT INTO tickets_stages (id, ticket, date_start, status_id, treater_id) 
                        values (null, {$ticket}, '" . $date . "', {$tkStatus}, {$treaterId}) ";
            } elseif ($stageType == 'stop') {
                return false;
            }
        } else {
            /* Preciso atualizar o registro com a parada (stop) */
            if ($stageType == 'stop') {
                $sql = "UPDATE tickets_stages SET date_stop = '" . $date . "' WHERE id = " . $row['id'] . " ";
            } elseif ($stageType == 'start') {
                return false;
            }
        }
    }
    try {
        $conn->exec($sql);
    }
    catch (Exception $e) {
        return false;
    }

    return true;
}



/**
 * getTicketTreaters
 * Retorna a listagem de ID e nome dos operadores do chamado informado.
 * A busca é realizada na tickets_stages com base em status de fila direta (stat_painel = 1) e
 * usuários com nível de operação e administração.
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getTicketTreaters(\PDO $conn, int $ticket): array
{
    $sql = "SELECT
                u.user_id,
                u.nome
            FROM 
                tickets_stages ts,
                status st,
                usuarios u
            WHERE 
                ts.status_id = st.stat_id AND
                ts.treater_id = u.user_id AND
                st.stat_painel = 1 AND
                u.nivel < 3 AND
                ts.ticket = :ticket
            GROUP BY 
                u.user_id, u.nome";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
}


/**
 * getStagesFromTicket
 * Retorna todos os stages de um ticket fornecido
 * Pode ser filtrado pelos options informados: 
 *  - panel: 1(fila direta), 2(fila aberta), 3(oculto) 
 *  - treater: id do operador
 *  - freeze: se é status de parada do relógio - 0|1
 * @param \PDO $conn
 * @param int $ticket
 * @param array|null $options
 * 
 * @return array
 */
function getStagesFromTicket(\PDO $conn, int $ticket, ?array $options = null): array
{
    $defaults = [
        'panel' => '*',
        'treater' => null,
        'freeze' => null
    ];

    $allowed_panels = ['*', 1, 2, 3];
    $allowed_freeze_values = [0, 1];
    $whereTerms = "";
    $fromTerms = " LEFT JOIN usuarios u ON u.user_id = ts.treater_id ";

    $options = (!is_null($options) ? array_merge($defaults, $options) : $defaults);

    if (!in_array($options['panel'], $allowed_panels)) {
        echo "Panel not allowed! Accepted panels: " . implode(", ", $allowed_panels);
        return [];
    }

    if (!is_null($options['freeze']) && !in_array($options['freeze'], $allowed_freeze_values)) {
        echo "Freeze value not allowed! Accepted freeze values: " . implode(", ", $allowed_freeze_values);
        return [];
    }

    if (!is_null($options['treater'])) {
        $fromTerms = ", usuarios u ";
        $whereTerms = " AND ts.treater_id = u.user_id AND ts.treater_id = :treater ";
    }

    if ($options['panel'] != '*') {
        $whereTerms .= " AND st.stat_painel = :panel ";
    }

    if (!is_null($options['freeze'])) {
        $whereTerms .= " AND st.stat_time_freeze = :freeze ";
    }

    $sql = "SELECT 
                ts.date_start,
                ts.date_stop,
                st.status,
                u.user_id
            FROM 
                status st,
                tickets_stages ts
                
                {$fromTerms}
            WHERE 
                ts.status_id = st.stat_id AND
                ts.ticket = :ticket 
                {$whereTerms}
            ORDER BY id";

    try {
        $res = $conn->prepare($sql);
        
        $res->bindValue(':ticket', $ticket, PDO::PARAM_INT);

        if (!is_null($options['treater'])) {
            $res->bindValue(':treater', $options['treater'], PDO::PARAM_INT);
        }

        if ($options['panel'] != '*') {
            $res->bindValue(':panel', $options['panel'], PDO::PARAM_INT);
        }

        if (!is_null($options['freeze'])) {
            $res->bindValue(':freeze', $options['freeze'], PDO::PARAM_INT);
        }
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        echo 'Erro: ', $e->getMessage(), "<br/>";
        echo $sql . "<br/>";
        return [];
    }
}


/**
 * insertTreaterManualStageInTicket
 * Realiza a inserção de períodos de atendimento fornecidos manualmente
 * @param \PDO $conn
 * @param int $ticket
 * @param array $treaters
 * @param array $startDates
 * @param array $stopDates
 * @param int $author
 * @return bool
 */
function insertTreaterManualStageInTicket(
    \PDO $conn, 
    int $ticket, 
    array $treaters, 
    array $startDates, 
    array $stopDates, 
    int $author): bool
{

    $treaters = array_filter($treaters);
    $startDates = array_filter($startDates);
    $stopDates = array_filter($stopDates);

    if (count($treaters) == 0) {
        return false;
    }

    if (count($treaters) != count($startDates) || count($treaters) != count($stopDates)) {
        return false;
    }

    $now = date("Y-m-d H:i:s");

    /* 
    $time1 = strtotime($startTime);
    $time2 = strtotime($endTime);
    $inSeconds = $time2 - $time1;
    */

    $sql = "INSERT INTO tickets_treaters_stages (ticket, treater_id, date_start, date_stop, author, created_at) VALUES ";
    for ($i = 0; $i < count($treaters); $i++) {

        // $seconds = strtotime($stopDates[$i]) - strtotime($startDates[$i]);
        $sql .= "({$ticket}, {$treaters[$i]}, '{$startDates[$i]}', '{$stopDates[$i]}', {$author}, '{$now}'), ";
    }
    $sql = rtrim($sql, ", ");
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        return true;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

/**
 * getTreatersManualStagesByTicket
 * Retorna os tratadores e os respectivos períodos de tempo em que atuaram em um ticket.
 * Essas informações foram informadas manualmente no sistema. 
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getTreatersManualStagesByTicket (\PDO $conn, int $ticket): array
{
    $sql = "SELECT 
                u.nome, u.user_id,
                ttr.date_start,
                ttr.date_stop,
                ua.nome AS author
            FROM 
                tickets_treaters_stages ttr,
                usuarios u,
                usuarios ua
            WHERE
                ttr.ticket = :ticket AND
                ttr.treater_id = u.user_id AND
                ttr.author = ua.user_id
            ORDER BY
                ttr.date_start
    ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount() > 0) {
            return $res->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
}




/**
 * Registra um log de ação do usuário no banco de dados.
 *
 * @param \PDO $conn Conexão PDO para o banco de dados.
 * @param int $user_id Código do usuário que realizou a ação.
 * @param string $action_type Tipo de açao realizada pelo usuario. 
 * Alguns tipos de $action_type: LOGIN | CREATE_USER | DELETE_USER | UPDATE_USER | DELETE_ASSET | UPDATE_ASSET
 * @param string $action_details [optional] Detalhes sobre a açao realizada.
 * @param string $ip_address [optional] Endere o IP do usuário que realizou a açao.
 *
 * @return bool Verdadeiro se o registro foi bem sucedido, falso caso contrário.
 */
function recordUserLog(
    \PDO $conn, 
    int $user_id, 
    string $action_type, 
    string|null $action_details, 
    string|null $ip_address
): bool
{
    $sql = "INSERT 
            INTO 
                user_logs 
                (
                    user_id, 
                    action_type, 
                    action_details, 
                    ip_address
                ) 
                VALUES 
                (
                    :user_id, 
                    :action_type, 
                    :action_details, 
                    :ip_address
                )";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $res->bindParam(':action_type', $action_type, \PDO::PARAM_STR);
        $res->bindParam(':action_details', $action_details, \PDO::PARAM_STR);
        $res->bindParam(':ip_address', $ip_address, \PDO::PARAM_STR);
        $res->execute();
        return true;
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}






/**
 * firstLog
 * Insere um registro em ocorrencias_log com o estado atual do chamado caso esse registro não exista
 * @param \PDO $conn
 * @param int $numero: número do chamado
 * @param mixed $tipo_edicao: código do tipo de edição - (0: abertura, 1: edição, ...)
 * @param mixed $auto_record
 * @return bool
 */
function firstLog(\PDO $conn, int $numero, $tipo_edicao='NULL', $auto_record = ''): bool
{
       
    /* $tipo_edicao='NULL' */
    include ("../../includes/queries/queries.php");
    
    //Checando se já existe um registro para o chamado
    $sql_log_base = "SELECT * FROM ocorrencias_log WHERE log_numero = '".$numero."' ";
    $qry = $conn->query($sql_log_base);
    $existe_log = $qry->rowCount();

    if (!$existe_log){//AINDA NAO EXISTE REGISTRO - NESSE CASO ADICIONO UM REGISTRO COMPLETO COM O ESTADO ATUAL DO CHAMADO
    
        $qryfull = $QRY["ocorrencias_full_ini"]." WHERE o.numero = " . $numero;
        $qFull = $conn->query($qryfull);
        $rowfull = $qFull->fetch(PDO::FETCH_OBJ);
        
        $base_descricao = $rowfull->descricao;
        $base_departamento = $rowfull->setor_cod;
        $base_area = $rowfull->area_cod;
        $base_cliente = $rowfull->client_id;
        $base_prioridade = $rowfull->oco_prior;
        $base_problema = $rowfull->prob_cod;
        $base_unidade = $rowfull->unidade_cod;
        $base_etiqueta = $rowfull->etiqueta;
        $base_contato = $rowfull->contato;
        $base_contato_email = $rowfull->contato_email;
        $base_telefone = $rowfull->telefone;
        $base_operador = $rowfull->operador_cod;
        $base_data_agendamento = $rowfull->oco_scheduled_to;
        $base_status = $rowfull->status_cod;
        $base_authorization_status = $rowfull->authorization_status;
        $base_requester = $rowfull->aberto_por_cod;
        
        $val = array();
        $val['log_numero'] = $rowfull->numero;
        
        if ($auto_record == ''){
            $val['log_quem'] = $_SESSION['s_uid'];
        } else
            $val['log_quem'] = $base_operador;            
        
        $val['log_requester'] = $base_requester;
        
        // $val['log_data'] = date("Y-m-d H:i:s");            
        $val['log_data'] = $rowfull->oco_real_open_date;            
        $val['log_prioridade'] = ($rowfull->oco_prior == "" || $rowfull->oco_prior == "-1" )?'NULL':"'$base_prioridade'";  
        $val['log_descricao'] = $rowfull->descricao == ""?'NULL':"'$base_descricao'";  
        $val['log_area'] = ($rowfull->area_cod == "" || $rowfull->area_cod =="-1")?'NULL':"'$base_area'";  
        $val['log_cliente'] = ($rowfull->client_id == "" || $rowfull->client_id =="-1")?'NULL':"'$base_cliente'";  
        $val['log_problema'] = ($rowfull->prob_cod == "" || $rowfull->prob_cod =="-1")?'NULL':"'$base_problema'";  
        $val['log_unidade'] = ($rowfull->unidade_cod == "" || $rowfull->unidade_cod =="-1" || $rowfull->unidade_cod =="0")?'NULL':"'$base_unidade'";  
        $val['log_etiqueta'] = ($rowfull->etiqueta == "" || $rowfull->etiqueta =="-1" || $rowfull->etiqueta =="0")?'NULL':"'$base_etiqueta'";  
        $val['log_contato'] = ($rowfull->contato == "")?'NULL':"'$base_contato'";  
        $val['log_contato_email'] = ($rowfull->contato_email == "")?'NULL':"'$base_contato_email'";  
        $val['log_telefone'] = ($rowfull->telefone == "")?'NULL':"'$base_telefone'";  
        $val['log_departamento'] = ($rowfull->setor_cod == "" || $rowfull->setor_cod =="-1")?'NULL':"'$base_departamento'";  
        $val['log_responsavel'] = ($rowfull->operador_cod == "" || $rowfull->operador_cod =="-1")?'NULL':"'$base_operador'";  
        $val['log_data_agendamento'] = ($rowfull->oco_scheduled_to == "")?'NULL':"'$base_data_agendamento'";  
        $val['log_status'] = ($rowfull->status_cod == "" || $rowfull->status_cod =="-1")?'NULL':"'$base_status'";  
        $val['log_authorization_status'] = ($rowfull->authorization_status == "" || $rowfull->authorization_status =="-1")?'NULL':"'$base_authorization_status'";  
        $val['log_tipo_edicao'] = $tipo_edicao;
        
    
        //GRAVA O REGISTRO DE LOG DO ESTADO ANTERIOR A EDICAO
        $sql_base = "INSERT INTO `ocorrencias_log` ".
            "\n\t(`log_numero`, `log_quem`, `log_requester`, `log_data`, `log_descricao`, `log_prioridade`, ".
            "\n\t`log_area`, `log_problema`, `log_unidade`, `log_etiqueta`, ".
            "\n\t`log_contato`, `log_contato_email`, `log_telefone`, `log_departamento`, `log_responsavel`, `log_data_agendamento`, ".
            "\n\t`log_status`, ".
            "\n\t`log_authorization_status`, ".
            "\n\t`log_cliente`, ".
            "\n\t`log_tipo_edicao`) ".
            "\nVALUES ".
            "\n\t('".$val['log_numero']."', '".$val['log_quem']."', '".$val['log_requester']."', '".$val['log_data']."', ".$val['log_descricao'].", ".$val['log_prioridade'].", ".
            "\n\t".$val['log_area'].", ".$val['log_problema'].", ".$val['log_unidade'].", ".$val['log_etiqueta'].", ".
            "\n\t".$val['log_contato'].", ".$val['log_contato_email'].", ".$val['log_telefone'].", ".$val['log_departamento'].", ".$val['log_responsavel'].", ".$val['log_data_agendamento'].", ".
            "\n\t".$val['log_status'].", ".
            "\n\t".$val['log_authorization_status'].", ".
            "\n\t".$val['log_cliente'].", ".
            "\n\t".$val['log_tipo_edicao']." ".
            "\n\t )";
        
        try {
            $conn->exec($sql_base);
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }
    return false;
}

/**
 * recordLog
 * Grava o registro de modificações do chamado na tabela ocorrencias_log
 * @param \PDO $conn: conexão
 * @param int $ticket: número do chamado
 * @param array $beforePost: array de informações do chamado antes de sofrer modificações
 * @param array $afterPost: array das informações postadas para modificar o chamado
 * @param int $operationType: código do tipo de operação - retornado pelo functions::getOperationType()
 * @return bool: true se conseguir realizar a inserção e false em caso de falha
 */
function recordLog(\PDO $conn, int $ticket, array $beforePost, array $afterPost, int $operationType, ?int $author = null): bool
{
    $logCliente = (array_key_exists("cliente", $afterPost) ? $afterPost['cliente'] : "dontCheck");
    $logPrioridade = (array_key_exists("prioridade", $afterPost) ? $afterPost['prioridade'] : "dontCheck");
    $logArea = (array_key_exists("area", $afterPost) ? $afterPost['area'] : "dontCheck");
    $logProblema = (array_key_exists("problema", $afterPost) ? $afterPost['problema'] : "dontCheck");
    $logUnidade = (array_key_exists("unidade", $afterPost) ? $afterPost['unidade'] : "dontCheck");
    $logEtiqueta = (array_key_exists("etiqueta", $afterPost) ? $afterPost['etiqueta'] : "dontCheck");
    $logContato = (array_key_exists("contato", $afterPost) ? $afterPost['contato'] : "dontCheck");
    $logContatoEmail = (array_key_exists("contato_email", $afterPost) ? $afterPost['contato_email'] : "dontCheck");
    $logTelefone = (array_key_exists("telefone", $afterPost) ? $afterPost['telefone'] : "dontCheck");
    $logDepartamento = (array_key_exists("departamento", $afterPost) ? $afterPost['departamento'] : "dontCheck");
    $logOperador = (array_key_exists("operador", $afterPost) ? $afterPost['operador'] : "dontCheck");
    // $logLastEditor = (array_key_exists("last_editor", $afterPost) ? $afterPost['last_editor'] : "dontCheck");


    $logStatus = (array_key_exists("status", $afterPost) ? $afterPost['status'] : "dontCheck");
    $logAuthorizationStatus = (array_key_exists("authorization_status", $afterPost) ? $afterPost['authorization_status'] : "dontCheck");
    $logAgendadoPara = (array_key_exists("agendadoPara", $afterPost) ? $afterPost['agendadoPara'] : "dontCheck");

    $val = array();
    $val['log_numero'] = $ticket;
    $val['log_quem'] = $_SESSION['s_uid'] ?? $author;            
    $val['log_data'] = date("Y-m-d H:i:s");            

    if ($logPrioridade == "dontCheck") $val['log_prioridade'] = 'NULL'; else
        $val['log_prioridade'] = (($beforePost['oco_prior'] == $logPrioridade) || ((empty($beforePost['oco_prior']) || $beforePost['oco_prior']=="-1" || $beforePost['oco_prior']==NULL)  && ($logPrioridade == "" || $logPrioridade == "-1" || $logPrioridade == NULL)))?'NULL': "'$logPrioridade'"; 
    
    if ($logCliente == "dontCheck") $val['log_cliente'] = 'NULL'; else
        $val['log_cliente'] = ($beforePost['client_id'] == $logCliente)?'NULL':"'$logCliente'";    
    
    if ($logArea == "dontCheck") $val['log_area'] = 'NULL'; else
        $val['log_area'] = ($beforePost['area_cod'] == $logArea)?'NULL':"'$logArea'";
    
    if ($logProblema == "dontCheck") $val['log_problema'] = 'NULL'; else
        $val['log_problema'] = ($beforePost['prob_cod'] == $logProblema)?'NULL':"'$logProblema'";
    
    if ($logUnidade == "dontCheck") $val['log_unidade'] = 'NULL'; else
        $val['log_unidade'] = (($beforePost['unidade_cod'] == $logUnidade) || ((empty($beforePost['unidade_cod']) || $beforePost['unidade_cod']=="-1" || $beforePost['unidade_cod']==NULL)  && ($logUnidade == "" || $logUnidade == "-1" || $logUnidade == NULL)))?'NULL':"'$logUnidade'";  

    if ($logEtiqueta == "dontCheck") $val['log_etiqueta'] = 'NULL'; else
        $val['log_etiqueta'] = ($beforePost['etiqueta'] == $logEtiqueta)?'NULL':"'".noHtml($logEtiqueta)."'";

    if ($logContato == "dontCheck") $val['log_contato'] = 'NULL'; else
        $val['log_contato'] = ($beforePost['contato'] == $logContato)?'NULL':"'".noHtml($logContato)."'";
    
    if ($logContatoEmail == "dontCheck") $val['log_contato_email'] = 'NULL'; else
        $val['log_contato_email'] = ($beforePost['contato_email'] == $logContatoEmail)?'NULL':"'".noHtml($logContatoEmail)."'";

    if ($logTelefone == "dontCheck") $val['log_telefone'] = 'NULL'; else
        $val['log_telefone'] = ($beforePost['telefone'] == $logTelefone)?'NULL':"'$logTelefone'";

    if ($logDepartamento == "dontCheck") $val['log_departamento'] = 'NULL'; else    
        $val['log_departamento'] = (($beforePost['setor_cod'] == $logDepartamento) || ((empty($beforePost['setor_cod']) || $beforePost['setor_cod']=="-1" || $beforePost['setor_cod']==NULL)  && ($logDepartamento == "" || $logDepartamento == "-1" || $logDepartamento == NULL)))?'NULL':"'$logDepartamento'"; 

    if ($logOperador == "dontCheck") $val['log_responsavel'] = 'NULL'; else
        $val['log_responsavel'] = ($beforePost['operador_cod'] == $logOperador)?'NULL':"'$logOperador'";

    if ($logStatus == "dontCheck") $val['log_status'] = 'NULL'; else
        $val['log_status'] = ($beforePost['status_cod'] == $logStatus)?'NULL':"'$logStatus'";

    if ($logAuthorizationStatus == "dontCheck" || $logAuthorizationStatus == "") $val['log_authorization_status'] = 'NULL'; else
        $val['log_authorization_status'] = ($beforePost['authorization_status'] == $logAuthorizationStatus)?'NULL':"'$logAuthorizationStatus'";

    if ($logAgendadoPara == "dontCheck") $val['log_data_agendamento'] = 'NULL'; else
        $val['log_data_agendamento'] = ($beforePost['oco_scheduled_to'] == $logAgendadoPara || $logAgendadoPara == "")?'NULL':"'$logAgendadoPara'";

    $val['log_tipo_edicao'] = $operationType; //Edição     


    //GRAVA O REGISTRO DE LOG DA ALTERACAO REALIZADA
    $sqlLog = "INSERT INTO `ocorrencias_log` 
    (`log_numero`, `log_quem`, `log_data`, `log_prioridade`, 
    `log_area`, `log_problema`, `log_unidade`, `log_etiqueta`, `log_departamento`, 
    `log_contato`, `log_contato_email`, `log_telefone`, `log_responsavel`, 
    `log_data_agendamento`, `log_status`, `log_authorization_status`, `log_cliente`,
    `log_tipo_edicao`) 
    VALUES 
    ('".$val['log_numero']."', '".$val['log_quem']."', '".$val['log_data']."', ".$val['log_prioridade'].", 
    ".$val['log_area'].", ".$val['log_problema'].", ".$val['log_unidade'].", ".$val['log_etiqueta'].", 
    ".$val['log_departamento'].",
    ".$val['log_contato'].", ".$val['log_contato_email'].", ".$val['log_telefone'].", ".$val['log_responsavel'].", ". $val['log_data_agendamento'].", 
    ".$val['log_status'].", ".$val['log_authorization_status'].",
    ".$val['log_cliente'].", ".$val['log_tipo_edicao'].")";

    try {
        $conn->exec($sqlLog);
        return true;
    }
    catch (Exception $e) {
        echo $e->getMessage() . "<br/>" . $sqlLog . "<br/>";
        return false;
    }
}


function getTicketLastChangeDateByLogKey(\PDO $conn, int $ticket, string $logKey): string
{
    $sql = "SELECT
                MAX(log_data) AS log_data
            FROM
                ocorrencias_log
            WHERE 
                log_numero = :ticket AND 
                {$logKey} IS NOT NULL
            ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ticket', $ticket, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch()['log_data'];
}



/*************************
 * ****** INVENTÁRIO *****
 ************************/


/**
 * Retorna o array com as informações da tabela de equipamentos
 * Podem ser passados os dados de etiqueta (unidade e etiqueta) ou o código da tabela de equipamentos
 * Retorna o array vazio se não localizar o registro
 * @param PDO $conn variável de conexão
 * @param int|null $unit código da unidade
 * @param varchar|null $tag etiqueta do equipamento
 * @param int|null $cod código do equipamento na tabela de equipamentos
 */
function getEquipmentInfo (\PDO $conn, ?int $unit, ?string $tag, ?int $cod = null): array
{

    $terms = "";
    if (!empty($cod)) {
        $terms .= " AND comp_cod = '{$cod}' ";
    } elseif (empty($unit) || empty($tag)) {
        return [];
    }
    
    if (empty($cod)) {
        $terms .= " AND comp_inv = '{$tag}' AND comp_inst = '{$unit}' ";
    }

    $sql = "SELECT * FROM equipamentos WHERE 1 = 1 {$terms} ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        // return ['error' => $e->getMessage()];
        echo $e->getMessage();
        return [];
    }
}




/**
 * getAssetBasicInfo
 *
 * @param \PDO $conn
 * @param int $assetID
 * 
 * @return array
 */
function getAssetBasicInfo (
    \PDO $conn, 
    ?int $assetID = null, 
    ?string $assetTag = null, 
    ?int $unitID = null, 
    ?bool $exceptResource = null
    ): array
{
    $terms = "";

    if (!$assetID && (!$assetTag || !$unitID)) {
        return [];
    }
    
    
    if ($assetID !== null) {
        $terms = " AND c.comp_cod = {$assetID} ";
    } else {
        $assetTag = noHtml($assetTag);
        $unitID = (int)$unitID;
        $terms = " AND c.comp_inv = '{$assetTag}' AND c.comp_inst = '{$unitID}' ";
    }
    
    if ($exceptResource) {
        $terms .= " AND (c.is_product IS NULL OR c.is_product = 0 )";
    }

    $sql = "SELECT
                c.comp_cod,
                c.comp_inv,
                c.comp_sn,
                COALESCE (cl.nickname, 'N/A') AS 
                            'cliente',
                i.inst_cod,
                i.inst_nome,
                l.local,
                l.loc_id,
                t.tipo_nome,
                f.fab_nome,
                m.marc_nome
            FROM
                equipamentos c, tipo_equip t, marcas_comp m, fabricantes f, localizacao l, instituicao i
                LEFT JOIN clients cl ON cl.id = i.inst_client
            WHERE
                c.comp_inst = i.inst_cod AND
                c.comp_tipo_equip = t.tipo_cod AND
                c.comp_marca = m.marc_cod AND 
                c.comp_fab = f.fab_cod AND
                c.comp_local = l.loc_id 
                {$terms} 
            ORDER BY
                t.tipo_nome, f.fab_nome, m.marc_nome
            ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}




function getUnitsByAssetTag (\PDO $conn, string $tag, ?int $client = null, ?bool $except_resource = null): array
{
    $tag = noHtml($tag);

    $terms = ($client !== null ? " AND i.inst_client = {$client} " : "");

    $terms2 = ($except_resource !== null ? " AND (c.is_product IS NULL OR c.is_product = 0 ) " : "");

    $sql = "SELECT 
                i.inst_cod,
                i.inst_nome,
                COALESCE (cl.nickname, 'N/A') AS 
                'cliente'
            FROM
                equipamentos c, instituicao i
                LEFT JOIN clients cl ON cl.id = i.inst_client
            WHERE
                c.comp_inst = i.inst_cod AND
                c.comp_inv = :tag
                {$terms}
                {$terms2}
            ORDER BY
                i.inst_nome
        ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':tag', $tag);
        $res->execute();
        if ($res->rowCount())
            return $res->fetchAll();
        return [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



function getTrafficInfoFilesFromAsset($conn, int $assetID): array
{
    /* Tabelas envolvidas: assets_traffic_info, assets_x_traffic, traffic_files */
    $sql = "SELECT
                info.*, 
                axt.*, 
                f.id as file_id, 
                f.file_name
            FROM
                assets_traffic_info as info,
                assets_x_traffic as axt,
                traffic_files as f
            WHERE
                info.id = axt.info_id AND
                axt.info_id = f.info_id AND
                axt.asset_id = :asset_id
        ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':asset_id', $assetID);
        $res->execute();
        if ($res->rowCount())
            return $res->fetchAll();
        return [];
    } catch (Exception $e) {
        return [];
    }
    return [];
}



/**
 * getManufacturers
 * Retorna um array com a listagem de fabricantes ou um fabricante específico caso o id seja informado
 * @param PDO $conn
 * @param int|null $id
 * @param int|null $type: 1: hw | 2: sw | 0: any(default)
 * @return array
 */
function getManufacturers (\PDO $conn, ?int $id, ?int $type = 0): array
{
    $data = [];

    $terms = ($id !== null ? " WHERE fab_cod = :id " : "");

    if (!$id) {
        $terms = ($type !== null && $type != 0 ? " WHERE fab_tipo IN ({$type},3) OR fab_tipo IS NULL " : '');
    }
    
    $sql = "SELECT * FROM fabricantes {$terms} ORDER BY fab_nome";
    try {
        $res = $conn->prepare($sql);
        
        if ($id) {
            $res->bindParam(':id', $id);
        }
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * getPeripheralInfo
 * Retorna um array com as informações do componente interno (não avulso)
 * @param \PDO $conn
 * @param mixed $peripheralCod
 * @return array
 */
function getPeripheralInfo (\PDO $conn, $peripheralCod): array
{
    $empty = [];
    $empty['mdit_cod'] = "";
    $empty['mdit_manufacturer'] = "";
    $empty['mdit_fabricante'] = "";
    $empty['mdit_desc'] = "";
    $empty['mdit_desc_capacidade'] = "";
    $empty['mdit_sufixo'] = "";
    
    if (empty($peripheralCod)) {
        return $empty;
    }
        
    $sql = "SELECT * FROM modelos_itens WHERE mdit_cod = '{$peripheralCod}'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        return $empty;
    }
}

/**
 * getCostCenters
 * Retorna o array com as informações da tabela de Centros de Custos
 * Retorna o array vazio se não localizar o registro
 * Campos de retorno (se não vazio): ccusto_id, ccusto_name, ccusto_cod
 * @param \PDO $conn
 * @param int $ccId
 * @return array
 */
function getCostCenters (\PDO $conn, ?int $ccId = null, ?int $client = null): array
{
    $terms = "";
    
    if ($ccId) {
        $terms = "WHERE cc.`". CCUSTO_ID . "` = '{$ccId}' ";
    } elseif ($client) {
        $terms = "WHERE cc.client = '{$client}' OR cc.client IS NULL";
    }
    
    $sql = "SELECT 
                cc." . CCUSTO_ID . " AS ccusto_id, 
                cc." . CCUSTO_DESC . " AS ccusto_name, 
                cc." . CCUSTO_COD . " AS ccusto_cod,
                cl.nickname, cl.id
            FROM 
                `" . DB_CCUSTO . "`.`" . TB_CCUSTO . "` cc
                LEFT JOIN clients cl ON cl.id = cc.client 
                {$terms}
            ORDER BY cc." . CCUSTO_DESC . "
        ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($ccId)
                return $data[0];
            return $data;
        }   
    return [];
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage(),
                'sql' => $sql];
    }
}



/**
 * isValidTicketArea
 * Verifica se o ticket informado é válido para a lista de áreas informadas
 * Retorna true caso seja válido, false caso contrário
 * @param \PDO $conn
 * @param int $ticket
 * @param string $uAreas: lista de áreas separadas por vírgula
 * @return bool
 */
function isValidTicketArea (\PDO $conn, int $ticket, string $uAreas): bool
{
    $ticketArea = null;
    
    $sql = "SELECT sistema FROM ocorrencias WHERE numero = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        $ticketArea = $res->fetch()['sistema'];
    }
    catch (Exception $e) {
        return false;
    }

    /* Se o chamado não possuir área, qualquer área é válida */
    if (empty($ticketArea)) {
        return true;
    }

    $uareas = explode(',', (string)$uAreas);
    return in_array($ticketArea, $uareas);
}



/**
 * Checks if a given custom field is formatted as a currency.
 *
 * This function verifies if the custom field associated with the provided 
 * field ID is of type 'text' and validates it against a set of predefined 
 * currency patterns. The field must have a valid mask and regex pattern 
 * to be considered a currency field.
 *
 * @param \PDO $conn Database connection object.
 * @param mixed $fieldId The ID of the custom field to check.
 *
 * @return bool Returns true if the field is a currency field; false otherwise.
 */

function isCurrencyField(\PDO $conn, $fieldId) {
    $fieldInfo = getCustomFields($conn, $fieldId);
    if ($fieldInfo['field_type'] != 'text') {
        return false;
    }

    $valuesToTest = [
        '1,00',
        '1,55',
        '10,00',
        '100,00',
        '1.000,00',
        '10.000,00',
        '100.000,00',
        '1.000.000,00',
        '10.000.000,00'
    ];

    if ($fieldInfo['field_mask'] && $fieldInfo['field_mask_regex']) {
        foreach ($valuesToTest as $value) {
            if (!preg_match('/' . $fieldInfo['field_mask'] . '/i', $value)) {
                return false;
                break;
            }
        }
        return true;
    }

    return false;
}



/**
 * Cria ou atualiza um projeto
 * 
 * Caso o parametro $id seja nulo, cria um novo projeto com os dados fornecidos em $data.
 * Caso o parâmetro $id seja um valor número diferente de nulo, atualiza o projeto com os dados fornecidos em $data.
 * 
 * @param \PDO $conn Conexao com o banco de dados
 * @param int|null $id Id do projeto a ser atualizado. Se nulo, cria um novo projeto.
 * @param array $data Dados do projeto a ser criado ou atualizado. Se vazio, grava um nome e descrição automáticos para o projeto
 * Chaves: 
 * - name: Nome do projeto
 * - description: Descrição do projeto
 * 
 * @return int|null Id do projeto recém criado ou atualizado
 */
function createOrUpdateProject(\PDO $conn, ?int $id = null, array $data = []):?int
{
    $now_microtime = DateTime::createFromFormat('U.u', microtime(true));
    $autoName = $now_microtime->format("YmdHis.u");
    
    if (array_key_exists('name', $data) && !empty($data['name'])) {
        $data['name'] = noHtml($data['name']);
    } else {
        $data['name'] = $autoName;
    }
    if (array_key_exists('description', $data) && !empty($data['description'])) {
        $data['description'] = noHtml($data['description']);
    } else {
        $data['description'] = $autoName;
    }

    if ($id !== null) {
        $sql = "UPDATE 
                    projects 
                SET 
                    `name` = :name, 
                    `description` = :description 
                WHERE 
                    id = :id";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':name', $data['name']);
            $res->bindParam(':description', $data['description']);
            $res->bindParam(':id', $id);
            $res->execute();
            return $id;
        }
        catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    $sql = "INSERT INTO projects (`name`, `description`) VALUES (:name, :description)";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':name', $data['name']);
        $res->bindParam(':description', $data['description']);
        $res->execute();
        return $conn->lastInsertId();
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }
}


/**
 * Adiciona um ticket a um projeto
 * 
 * Atualiza a tabela ocodeps com o id do projeto em todos os registros que possuem o ticket como pai ou filho 
 * e que nao possuam um projeto associado.
 * 
 * @param \PDO $conn Conexao o com o banco de dados
 * @param int $ticket Número do ticket a ser adicionado ao projeto
 * @param int $project ID do projeto a ser adicionado o ticket
 * 
 * @return bool true se a operação for realizada com sucesso, false caso contrário
 */
function addTicketToProject(\PDO $conn, int $ticket, int $project):bool
{
    $sql = "UPDATE 
                ocodeps 
            SET 
                proj_id = :project 
            WHERE 
                (
                    dep_pai = :ticket OR 
                    dep_filho = :ticket
                ) AND 
                proj_id IS NULL";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':project', $project);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        return true;
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}


/**
 * Fetches all or one specific lending from database.
 * 
 * @param \PDO $conn PDO connection instance
 * @param int $lendingId ID of the lending to be fetched
 * @param int $status Status of the lending: 0 = open, 1 = closed
 * 
 * @return array Associative array with lending data
 * 
 * @throws PDOException
 */
function getLendings(\PDO $conn, ?int $lendingId = null, ?int $status = null): array
{
    $termsStatus = "";
    $statusTypes = [
        0 => " AND (e.data_devol IS NULL OR e.data_devol = '' OR e.data_devol = '0000-00-00')",
        1 => " AND (e.data_devol IS NOT NULL AND e.data_devol != '' AND e.data_devol != '0000-00-00')",
    ];

    if ($status !== null && isset($statusTypes[$status])) {
        $termsStatus = $statusTypes[$status];
    }
    
    
    $terms = "";
    if ($lendingId) {
        $terms = "empr_id = :empr_id AND ";
    }

    $sql = "SELECT 
                e.*, u.nome, l.local as local_nome
            FROM 
                emprestimos AS e, usuarios AS u, localizacao AS l 
            WHERE 
                {$terms}
                e.responsavel = u.user_id AND e.local = l.loc_id 
                {$termsStatus}
            ORDER BY 
                data_devol
            ";
    try {
        $res = $conn->prepare($sql);
        if ($lendingId) {
            $res->bindParam(':empr_id', $lendingId);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($lendingId) {
                return $data[0];
            }
            return $data;
        }
        return [];
        
    } catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
}
