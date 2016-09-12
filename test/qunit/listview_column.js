QUnit.module("Listview");

// Setup global variables required by lsfilter_list_table_desc().
var listview_renderer_table = {};
var listview_renderer_table_all = {
	"select" : {
		"order" : 15,
		"depends" : [ 'key' ]
	}
};
var ninja_manifest = { orm_structure : { services : {} } };
var listview_commands = { services : {} };
var lsfilter_list_columns = false;

QUnit.test("Test column order from lsfilter_list_table_desc()", function(assert) {
	listview_renderer_table.services = {
			"host_state" : {
				"order" : 10,
				"header" : '',
				"depends" : [ 'host.state_text', 'host.name' ],
				"sort" : [ 'host.state' ]
			},
			"host_name" : {
				"order" : 20,
				"header" : 'Host Name',
				"depends" : [ 'host.name', 'host.icon_image', 'host.address' ],
				"sort" : [ 'host.name', 'description' ]
			},
			"host_actions" : {
				"order" : 30,
				"header" : 'Host Actions',
				"depends" : [ 'host.name', 'host.action_url', 'host.config_url',
						'host.notes_url', 'host.config_allowed' ],
				"sort" : false
			}
	};

	// The function to test: lsfilter_list_table_desc()
	var table_desc = new lsfilter_list_table_desc(
		{ table: "services", columns: [] }, false
	);

	assert.deepEqual(
		table_desc.vis_columns,
		["host_state", "select", "host_name", "host_actions"],
		'Assert columns end up in the right order'
	);
});
