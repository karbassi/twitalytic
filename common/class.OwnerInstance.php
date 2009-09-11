<?php 
class OwnerInstance {
    var $owner_id;
    var $instance_id;

    
    function OwnerInstance($oid, $iid) {
        $this->owner_id = $oid;
        $this->instance_id = $iid;
    }
    
}

class OwnerInstanceDAO extends MySQLDAO {
	function OwnerInstanceDAO($database, $logger=null) {
		parent::MySQLDAO($database, $logger);
	}

    function doesOwnerHaveAccess($owner, $username) {
        if ($owner->is_admin) {
            return true;
        } else {
            $q = "
				SELECT 
					* 
				FROM 
					%prefix%owner_instances oi
				INNER JOIN
					%prefix%instances i
				ON 
					i.id = oi.instance_id
				WHERE 
					i.twitter_username = '".$username."' AND oi.owner_id = ".$owner->id.";";
            $sql_result = $this->executeSQL($q);
            if (mysql_num_rows($sql_result) == 0) {
                return false;
            } else {
                return true;
            }
        }
    }
    
    function get($owner_id, $instance_id) {
        $q = "
			SELECT 
				* 
			FROM 
				%prefix%owner_instances 
			WHERE 
				owner_id = ".$owner_id." AND instance_id = ".$instance_id.";";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) == 0) {
            $i = null;
        } else {
            $row = mysql_fetch_assoc($sql_result);
            $oid = $row["owner_id"];
            $iid = $row["instance_id"];
            $i = new OwnerInstance($oid, $iid);
        }
        return $i;
    }
    
    function insert($owner_id, $instance_id, $oauth_token, $oauth_token_secret) {
        $q = "
			INSERT INTO 
				%prefix%owner_instances (`owner_id`, `instance_id`, `oauth_access_token`, `oauth_access_token_secret`)
			 VALUES
				(".$owner_id.", ".$instance_id.", '".$oauth_token."', '".$oauth_token_secret."')";
        $sql_result = $this->executeSQL($q);
    }

    
    function getOAuthTokens($id) {
        $q = "
			SELECT 
				oauth_access_token, oauth_access_token_secret 
			FROM 
				%prefix%owner_instances 
			WHERE 
				instance_id = ".$id." ORDER BY id ASC LIMIT 1;";
        $sql_result = $this->executeSQL($q);
        $tokens = mysql_fetch_assoc($sql_result);
        return $tokens;
    }
    
}

?>
