<?php

class OwnerInstance {
	var $owner_id;
	var $instance_id;

	function OwnerInstance($oid, $iid) {
		$this->owner_id = $oid;
		$this->instance_id = $iid;
	}

}

class OwnerInstanceDAO {
	$cfg = new Config();

	function doesOwnerHaveAccess($owner, $username) {
		if ($owner->is_admin) {
			return true;
		} else {
			$q = "
				SELECT
					*
				FROM
					" . $this->cfg->table_prefix . "owner_instances oi
				INNER JOIN
					" . $this->cfg->table_prefix . "instances i
				ON
					i.id = oi.instance_id
				WHERE
					i.twitter_username = '".$username."' AND oi.owner_id = ".$owner->id. ";";
			$sql_result = Database::exec($q);
			if (mysql_num_rows  ( $sql_result  ) == 0 ) {
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
				" . $this->cfg->table_prefix . "owner_instances
			WHERE
				owner_id = ".$owner_id." AND instance_id = ".$instance_id. ";";
		$sql_result =Database::exec($q);
		if (mysql_num_rows  ( $sql_result  ) == 0 ) {
			$i = null;
		} else {
			$row = mysql_fetch_assoc($sql_result);
			$oid = $row["owner_id"];
			$iid = $row["instance_id"];
			$i = new OwnerInstance($oid, $iid );
		}
		return $i;
	}

	function insert($owner_id, $instance_id, $oauth_token, $oauth_token_secret) {
		$q = "
			INSERT INTO
				" . $this->cfg->table_prefix . "owner_instances (`owner_id`, `instance_id`, `oauth_access_token`, `oauth_access_token_secret`)
			VALUES
				(".$owner_id.", ".$instance_id.", '".$oauth_token."', '". $oauth_token_secret."')";
		$sql_result = Database::exec($q);
	}

	function getOAuthTokens( $id ) {
		$q = "
			SELECT
				oauth_access_token, oauth_access_token_secret
			FROM
				" . $this->cfg->table_prefix . "owner_instances
			WHERE
				instance_id = ".$id." ORDER BY id ASC LIMIT 1;";
		$sql_result = Database::exec($q);
		$tokens = mysql_fetch_assoc($sql_result);
		return $tokens;
	}
}