<?php
include ('../../../inc/includes.php');
header("Content-type: application/json; charset=utf-8");

$cpf = null;
$req = $DB->query("SELECT id, registration_number FROM `glpi_users` WHERE id = ".$_SESSION['glpiID']);
$row = $req->fetch_assoc();
if($row) {
    $cpf = $row['registration_number'];
}

if(empty($cpf)) {
    echo '{"message": "Sem CPF no usuario"}';
    die();
}


$ret = [];
try {

    $host = "postgres-homolog.saude-go.net";
    $port = "5432";
    $db   = "homologacao";
    $user = "siapiuser";
    $pwd  = "siapiuser";
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";

    $dbh = new PDO($dsn, $user, $pwd, array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ) );
} catch (PDOException $e) {
    echo json_encode( array('error' => $e->getMessage() ));
    die();
}

$remover = array("'", "-", ";", "-", ".", "/", ",", "(", ")", "*", "%", "$", "@", "#", "!", "&", "_", " ", "=", "+");
$cpf = str_replace($remover, "", $cpf);

$sql = "
SELECT DISTINCT
	od.odep_id,
    ps.pser_cpf as cpf,
	ps.pser_nome as nome,
	ce.cont_contato as email,
	'(' || ct.cont_ddd || ') ' || ct.cont_contato as telefone,
	od.odep_nome departamento
	from siapi.pessoa_servidor ps
	left join siapi.contato ce on ps.pser_id = ce.cont_pser_id and ce.cont_tipo_contato = 5
	left join siapi.contato ct on ps.pser_id = ct.cont_pser_id and ct.cont_tipo_contato = 3
	left join siapi.lotacao lo on ps.pser_id = lo.lota_pser_id and lo.lota_data_fim is null
	left join siapi.orgao_departamento od on lo.lota_odep_id = od.odep_id and od.odep_excluido = 'F'
	left join siapi.vinculo vi on lo.lota_vinc_id = vi.vinc_id and vi.vinc_data_fim is null
	left join siapi.cargo ca on vi.vinc_carg_id = ca.carg_id and ca.carg_excluido = 'F'
where ps.pser_cpf = :cpf
	limit 1";
$sth = $dbh->prepare($sql);
$sth->execute(array(':cpf' => $cpf));
$dados = $sth->fetchAll(PDO::FETCH_ASSOC);

if (count($dados) > 0) {

    $ret = $dados[0];
    $sql = "
WITH RECURSIVE orgaod_superior AS (
        SELECT
        	1 nivel,
        	*
        FROM siapi.orgao_departamento
        WHERE odep_id = :odep_id
        UNION ALL
        SELECT
        	orgaod_superior.nivel + 1 nivel,
			od.*
        FROM siapi.orgao_departamento od
        INNER JOIN orgaod_superior ON od.odep_id = orgaod_superior.odep_departamento_superior_id
)
SELECT
	avaliador.pser_nome chefia,
    od.odep_nome as departamento,
    od.odep_sigla as departamento_sigla
FROM orgaod_superior od
LEFT JOIN siapi.chefia_avaliador cavaliador ON cavaliador.cava_odep_id = od.odep_id AND cavaliador.cava_excluido = 'F'
LEFT JOIN siapi.pessoa_servidor avaliador ON avaliador.pser_id = cavaliador.cava_pser_id
WHERE cavaliador.cava_gerente = 'T'
ORDER BY od.nivel
LIMIT 1";

    $sth = $dbh->prepare($sql);
    $sth->execute(array(':odep_id' => $ret['odep_id']));
    $dados = $sth->fetchAll(PDO::FETCH_ASSOC);
    
    $ret['chefia'] = $dados[0]['chefia'];
    unset($ret['odep_id']);
} else {

   $sql = "
   SELECT
   cpf,
   nome,
   COALESCE(email_oficial, email) AS email,
   CASE
       WHEN telefone_comercial IS NOT NULL THEN telefone_comercial
       WHEN telefone_recado IS NOT NULL THEN telefone_recado
       WHEN telefone_residencial ~* '^\(62\)\s*3201.*$' THEN telefone_residencial
       ELSE NULL
   END AS telefone,
   NULL AS departamento,
   NULL AS chefia
from (
   select
       pfis_iden,
       pfis.pfis_numr_cpf as cpf,
       pfis.pfis_nome as nome,
       email_oficial.ctto_info as email_oficial,
       email.ctto_info as email,
       '(' || telefone_residencial.ctto_info_1 || ')' || telefone_residencial.ctto_info as telefone_residencial,
       '(' || telefone_comercial.ctto_info_1 || ')' || telefone_comercial.ctto_info as telefone_comercial,
       '(' || telefone_recado.ctto_info_1 || ')' || telefone_recado.ctto_info as telefone_recado
          from corp.pessoas_fisicas pfis
   left join corp.contatos_pessoa \"email_oficial\" on email_oficial.ctto_tipo = 1 and email_oficial.ctto_pess_iden = pfis.pfis_iden
   left join corp.contatos_pessoa \"email\" on email.ctto_tipo = 6 and email.ctto_pess_iden = pfis.pfis_iden
   left join corp.contatos_pessoa \"telefone_residencial\" on telefone_residencial.ctto_tipo = 2 and telefone_residencial.ctto_pess_iden = pfis.pfis_iden
   left join corp.contatos_pessoa \"telefone_comercial\" on telefone_comercial.ctto_tipo = 3 and telefone_comercial.ctto_pess_iden = pfis.pfis_iden
   left join corp.contatos_pessoa \"telefone_recado\" on telefone_recado.ctto_tipo = 81 and telefone_recado.ctto_pess_iden = pfis.pfis_iden
   left join corp.vinculo vinc on vinc.vinc_serv_pfis_iden = pfis.pfis_iden
        where pfis.pfis_numr_cpf = :cpf
) as t";

    $sth = $dbh->prepare($sql);
    $sth->execute(array(':cpf' => $cpf));
    $dados = $sth->fetchAll(PDO::FETCH_ASSOC);

    if(!empty($dados) ){
        $ret = $dados[0];
    }
}

echo json_encode($ret);
