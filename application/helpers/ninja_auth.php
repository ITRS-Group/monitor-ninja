<?php

/**
 * This helper class provides various routines for authenticating
 * users against a database that stores passwords with multiple
 * different hash-types
 */
class ninja_auth_Core
{
	/**
	 * Generates a password hash
	 * @param $pass Plaintext password
	 * @return Password hash
	 */
	public function hash_password($pass)
	{
		return base64_encode(sha1($pass, true));
	}

	/**
	 * Validates a password using apr's md5 hash algorithm
	 */
	private function apr_md5_validate($pass, $hash)
	{
		$pass = escapeshellarg($pass);
		$hash = escapeshellarg($hash);
		$cmd = realpath(APPPATH.'/../cli-helpers')."/apr_md5_validate $pass $hash";
		$ret = $output = false;
		exec($cmd, $output, $ret);
		return $ret === 0;
	}

	/**
	 * Validates a password using the given algorithm
	 */
	function valid_password($pass, $hash, $algo = '')
	{
		if ($algo === false || !is_string($algo))
			return false;
		if (empty($pass) || empty($hash))
			return false;
		if (!is_string($pass) || !is_string($hash))
			return false;

		switch ($algo) {
		 case 'sha1':
			return sha1($pass) === $hash;

		 case 'b64_sha1':
			# Passwords can be one of
			# ... base64 encoded raw sha1
			return base64_encode(sha1($pass, true)) === $hash;

		 case 'crypt':
			# ... crypt() encrypted
			return crypt($pass, $hash) === $hash;

		 case 'plain':
			# ... plaintext (stupid, but true)
			return $pass === $hash;

		 case 'apr_md5':
			# ... or a mad and weird aberration of md5
			return ninja_auth::apr_md5_validate($pass, $hash);
		 default:
			return false;
		}

		# not-reached
		return false;
	}
}
