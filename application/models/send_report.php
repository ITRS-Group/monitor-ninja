<?php

/**
 * Model for sending out reports in different ways
 */
class Send_report_Model extends Model {
	/**
	 * @param $recipient one email or a string composed of comma separated strings
	 * @throws RuntimeException if file is not readable
	 * @return boolean
	 */
	public function send($data, $filename, $format, $recipient)
	{

		$to = $recipient;
		if (strstr($to, ',')) {
			$recipient = explode(',', $to);
			if (is_array($recipient) && !empty($recipient)) {
				unset($to);
				foreach ($recipient as $user) {
					$to[$user] = $user;
				}
			}
		}

		$config = Kohana::config('reports');
		$mail_sender_address = $config['from_email'];

		if (!empty($mail_sender_address)) {
			$from = $mail_sender_address;
		} else {
			$hostname = exec('hostname --long');
			$from = !empty($config['from']) ? $config['from'] : Kohana::config('config.product_name');
			$from = str_replace(' ', '', trim($from));
			if (empty($hostname) && $hostname != '(none)') {
				// unable to get a valid hostname
				$from = $from . '@localhost';
			} else {
				$from = $from . '@'.$hostname;
			}
		}

		$plain = sprintf(_('Scheduled report sent from %s'),!empty($config['from']) ? $config['from'] : $from);
		$subject = _('Scheduled report').": $filename";

		switch ($format) {
		 case 'pdf':
			$mime = 'application/pdf';
			break;
		 case 'csv':
			$mime = 'application/csv';
			break;
		 case 'html':
			$mime = 'application/csv';
			break;
		 default:
			$mime = 'application/binary';
			break;
		}

		# $mail_sent will contain the nr of mail sent - not used at the moment
		$mail_sent = email::send_report($to, $from, $subject, $plain, $mime, $filename, $data);

		return (boolean) $mail_sent;
	}

}
