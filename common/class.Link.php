<?php

class Link {
	var $id;
	var $url;
	var $expanded_url;
	var $title;
	var $clicks;
	var $status_id;
	var $is_image;
	var $img_src; //optional
	var $container_tweet; //optional

	function Link($val) {
		$this->url = $val["url"];
		if (isset($val["expanded_url"]))
			$this->expanded_url = $val["expanded_url"];

		if (isset($val["title"]))
			$this->title = $val["title"];

		if (isset($val["clicks"]))
			$this->clicks = $val["clicks"];

		if (isset($val["status_id"]))
			$this->status_id = $val["status_id"];

		if (isset($val["is_image"]) && $val["is_image"] == 1 )
			$this->is_image = true;
		else
			$this->is_image = false;

		//TODO: Get more image services to work, like yfrog, TwitGoo, Twidroid, img.ly, etc.
		//if ( substr($this->url, 0, strlen('http://yfrog.com/')) == 'http://yfrog.com/' )
			//$this->img_src = 'http://twitpic.com/show/mini/'.substr($this->url, strlen('http://yfrog.com/'));
			//http://img243.yfrog.com/i/xae.jpg/
			//http://img243.yfrog.com/img243/3258/xae.jpg
	}
}

class LinkDAO {
	$cfg = new Config();

	function insert($url, $expanded, $title, $status_id, $is_image=0) {
		$expanded = mysql_real_escape_string($expanded);
		$title = mysql_real_escape_string($title);

		$q = "
			INSERT INTO
				" . $this->cfg->table_prefix . "links (url, expanded_url, title, status_id, is_image)
			VALUES (
					'{$url}', '{$expanded}', '{$title}', ".$status_id.", ".$is_image.");";

		$foo = Database::exec($q);
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}

	function update($url, $expanded, $title, $status_id, $is_image=0) {
		$expanded = mysql_real_escape_string($expanded);
		$title = mysql_real_escape_string($title);

		$q = "
			UPDATE
				" . $this->cfg->table_prefix . "
			SET
				expanded_url = '{$expanded}',
				title = '{$title}',
				status_id=".$status_id.",
				is_image=".$is_image."
			WHERE url = '{$url}';";

		$foo = Database::exec($q);
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}

	function getLinksByFriends($user_id) {
		$q = "
			SELECT
				l.*, t.*, pub_date - interval 8 hour as adj_pub_date
			FROM
				" . $this->cfg->table_prefix . "links l
			INNER JOIN
				" . $this->cfg->table_prefix . "tweets t
			ON
				t.status_id = l.status_id
			WHERE
				t.author_user_id in (SELECT user_id FROM " . $this->cfg->table_prefix . "follows f WHERE f.follower_id = ".$user_id.")
			ORDER BY
				l.status_id DESC
			LIMIT
				15";

		$sql_result = Database::exec($q);
		$links = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $l = new Link($row); $l->container_tweet = new Tweet($row); $links[] = $l; }
		mysql_free_result($sql_result);
		return $links;
	}

	function getPhotosByFriends($user_id) {
		$q = "
			SELECT
				l.*, t.*, pub_date - interval 8 hour as adj_pub_date
			FROM
				" . $this->cfg->table_prefix . "links l
			INNER JOIN
				" . $this->cfg->table_prefix . "tweets t
			ON
				t.status_id = l.status_id
			WHERE
				is_image = 1 and t.author_user_id in (SELECT user_id FROM " . $this->cfg->table_prefix . "follows f WHERE f.follower_id = ".$user_id.")
			ORDER BY
				l.status_id DESC
			LIMIT
				15";

		$sql_result = Database::exec($q);
		$links = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $l = new Link($row); $l->container_tweet = new Tweet($row); $links[] = $l; }
		mysql_free_result($sql_result);
		return $links;
	}

	function getLinksToUpdate() {
		$q = "
			SELECT
				l.*
			FROM
				" . $this->cfg->table_prefix . "links l
			WHERE
				/*l.expanded_url = '' and */(l.url like '%flic.kr%' OR l.url like '%twitpic%') and is_image = 0
			ORDER BY
				l.status_id DESC
			LIMIT
				15";

		$sql_result = Database::exec($q);
		$links = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $links[] = $row; }
		mysql_free_result($sql_result);
		return $links;
	}
}