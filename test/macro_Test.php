<?php
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Metadata\DataProvider;
class macro_Test extends \PHPUnit\Framework\TestCase {

	public function test_preprocess_orm_object () {

		$service_props = array(
			'host' => array(
				'name' => 'Jabbaraj',
				'address' => '127.0.0.1',
				'groups' => array('Intini'),
				'display_name' => 'Jabbaraj',
				'alias' => 'Jabbo',
				'state' => 0,
				'state_type' => 1,
				'current_attempt' => 0,
				'max_check_attempts' => 3
			),
			'description' => 'Fooserv',
			'groups' => array('Servgroup'),
			'display_name' => 'Fooserv',
			'state' => 0
		);

		$export = array_keys($service_props);
		foreach ($service_props['host'] as $key => $val) {
			$export[] = 'host.' . $key;
		}

		$service = Service_Model::factory_from_array($service_props, $export);
		$properties = nagstat::preprocess_orm_object($service);

		$this->assertEquals((object) array(
			'host_name' => 'Jabbaraj',
			'host_address' => '127.0.0.1',
			'host_groups' => array('Intini'),
			'host_display_name' => 'Jabbaraj',
			'host_alias' => 'Jabbo',
			'host_state' => 0,
			'host_state_type' => 1,
			'host_current_attempt' => 0,
			'host_max_check_attempts' => 3,
			'description' => 'Fooserv',
			'groups' => array('Servgroup'),
			'display_name' => 'Fooserv',
			'state' => 0
		), $properties);

	}

	public function host_macro_provider () {

		$host_props = array(
			'name' => 'Jabbaraj',
			'address' => '127.0.0.1',
			'groups' => array('Intini'),
			'display_name' => 'Jabbaraj',
			'alias' => 'Jabbo',
			'state' => 0,
			'state_type' => 1,
			'current_attempt' => 0,
			'max_check_attempts' => 3
		);

		$host = Host_Model::factory_from_setiterator($host_props, '', array_keys($host_props));

		return array(
			array($host, '$HOSTNAME$', 'Jabbaraj'),
			array($host, '$HOSTADDRESS$', '127.0.0.1'),
			array($host, '$HOSTDISPLAYNAME$', 'Jabbaraj'),
			array($host, '$HOSTALIAS$', 'Jabbo'),
			array($host, '$HOSTSTATE$', 'UP'),
			array($host, '$HOSTSTATETYPE$', 'HARD'),
			array($host, '$HOSTATTEMPT$', '0'),
			array($host, '$MAXHOSTATTEMPTS$', '3'),
			array($host, '$HOSTGROUPNAME$', 'Intini')
		);

	}

	#[Depends('test_preprocess_orm_object')]
	#[DataProvider('host_macro_provider')]
	public function test_host_macro_expansion (Host_Model $host, $macro, $expect) {
		$properties = nagstat::preprocess_orm_object($host);
		$this->assertEquals($expect, nagstat::process_macros($macro, $properties, 'host'));
	}

	public function service_macro_provider () {

		$service_props = array(
			'host' => array(
				'name' => 'Jabbaraj',
				'address' => '127.0.0.1',
				'groups' => array('Intini'),
				'display_name' => 'Jabbaraj',
				'alias' => 'Jabbo',
				'state' => 0,
				'state_type' => 1,
				'current_attempt' => 0,
				'max_check_attempts' => 3
			),
			'description' => 'Fooserv',
			'groups' => array('Servgroup'),
			'display_name' => 'Fooserv',
			'state' => 0
		);

		$export = array_keys($service_props);
		foreach ($service_props['host'] as $key => $val) {
			$export[] = 'host.' . $key;
		}

		$service = Service_Model::factory_from_array($service_props, $export);

		return array(
			array($service, '$HOSTNAME$', 'Jabbaraj'),
			array($service, '$HOSTADDRESS$', '127.0.0.1'),
			array($service, '$HOSTDISPLAYNAME$', 'Jabbaraj'),
			array($service, '$HOSTALIAS$', 'Jabbo'),
			array($service, '$HOSTSTATE$', 'UP'),
			array($service, '$HOSTSTATETYPE$', 'HARD'),
			array($service, '$HOSTATTEMPT$', '0'),
			array($service, '$MAXHOSTATTEMPTS$', '3'),
			array($service, '$HOSTGROUPNAME$', 'Intini'),
			array($service, '$SERVICEDESC$', 'Fooserv'),
			array($service, '$SERVICEDISPLAYNAME$', 'Fooserv'),
			array($service, '$SERVICEGROUPNAME$', 'Servgroup'),
			array($service, '$SERVICESTATE$', 'OK'),
		);

	}

	#[Depends('test_preprocess_orm_object')]
	#[DataProvider('service_macro_provider')]
	public function test_service_macro_expansion (Service_Model $service, $macro, $expect) {
		$properties = nagstat::preprocess_orm_object($service);
		$this->assertEquals($expect, nagstat::process_macros($macro, $properties, 'service'));
	}

	public function hostgroup_macro_provider () {

		$hostgroup_props = array(
			'name' => 'Awesomesauce',
			'alias' => 'ASS',
		);

		$hostgroup = Hostgroup_Model::factory_from_setiterator($hostgroup_props, '', array_keys($hostgroup_props));

		return array(
			array($hostgroup, '$HOSTGROUPNAME$', 'Awesomesauce'),
			array($hostgroup, '$HOSTGROUPALIAS$', 'ASS'),
		);

	}

	#[Depends('test_preprocess_orm_object')]
	#[DataProvider('hostgroup_macro_provider')]
	public function test_hostgroup_macro_expansion (Hostgroup_Model $hostgroup, $macro, $expect) {
		$properties = nagstat::preprocess_orm_object($hostgroup);
		$this->assertEquals($expect, nagstat::process_macros($macro, $properties, 'hostgroup'));
	}

	public function servicegroup_macro_provider () {

		$servicegroup_props = array(
			'name' => 'Awesomesauce',
			'alias' => 'ASS',
		);

		$servicegroup = servicegroup_Model::factory_from_setiterator($servicegroup_props, '', array_keys($servicegroup_props));

		return array(
			array($servicegroup, '$SERVICEGROUPNAME$', 'Awesomesauce'),
			array($servicegroup, '$SERVICEGROUPALIAS$', 'ASS'),
		);

	}

	#[Depends('test_preprocess_orm_object')]
	#[DataProvider('servicegroup_macro_provider')]
	public function test_servicegroup_macro_expansion (Servicegroup_Model $servicegroup, $macro, $expect) {
		$properties = nagstat::preprocess_orm_object($servicegroup);
		$this->assertEquals($expect, nagstat::process_macros($macro, $properties, 'servicegroup'));
	}

	public function generic_group_macro_provider () {

		$servicegroup_props = array(
			'name' => 'AwesomesauceSG',
			'alias' => 'ASSSG',
		);

		$hostgroup_props = array(
			'name' => 'AwesomesauceHG',
			'alias' => 'ASSHG',
		);

		$servicegroup = servicegroup_Model::factory_from_setiterator($servicegroup_props, '', array_keys($servicegroup_props));

		$hostgroup = Hostgroup_Model::factory_from_setiterator($hostgroup_props, '', array_keys($hostgroup_props));

		return array(
			array($servicegroup, '$SERVICEGROUPNAME$', 'AwesomesauceSG'),
			array($servicegroup, '$SERVICEGROUPALIAS$', 'ASSSG'),
			array($hostgroup, '$HOSTGROUPNAME$', 'AwesomesauceHG'),
			array($hostgroup, '$HOSTGROUPALIAS$', 'ASSHG'),
		);

	}

	#[Depends('test_preprocess_orm_object')]
	#[DataProvider('generic_group_macro_provider')]
	public function test_generic_group_macro_expansion (Object_Model $servicegroup, $macro, $expect) {
		$properties = nagstat::preprocess_orm_object($servicegroup);
		$this->assertEquals($expect, nagstat::process_macros($macro, $properties));
	}


}
