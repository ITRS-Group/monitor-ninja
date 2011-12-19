<?php

class Send_report_Model extends Model {

	/**
	 * @var Zend_Translate
	 */
	private $translate;

	public function __construct() {
		$this->translate = zend::instance('Registry')->get('Zend_Translate');
	}

	/**
	 * @param string $recipient one email or a string composed of comma separated strings
	 * @param string $path either '/path/to/*.pdf' or '/path/to/*.csv'
	 * @param string $label_filename either '*.pdf' or '*.csv'
	 * @throws RuntimeException if file is not readable
	 * @return boolean
	 */
	public function send($recipient, $path_to_file, $label_filename) {
		if(!is_readable($path_to_file)) {
			throw new RuntimeException("Can not read '$path_to_file' in ".__METHOD__);
		}

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

		$plain = sprintf($this->translate->_('Scheduled report sent from %s'),!empty($config['from']) ? $config['from'] : $from);
		$subject = $this->translate->_('Scheduled report').": $label_filename";

		# $mail_sent will contain the nr of mail sent - not used at the moment
		$filetype = 'pdf';
		$dot_position = strpos($label_filename, '.');
		if(false !== $dot_position) {
			$filetype = substr($label_filename, $dot_position);
		}
		$mail_sent = email::send_multipart($to, $from, $subject, $plain, '', array($path_to_file => $filetype));

		return (boolean) $mail_sent;
	}

}
