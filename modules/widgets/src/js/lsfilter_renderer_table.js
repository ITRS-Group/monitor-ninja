/*
 * Listview table renderer for Dashboards
 */
listview_renderer_table.dashboards = {
	"name": {
		"header": "Name",
		"depends": ["name", "id"],
		"sort": ["name desc"],
		"cell": function (args) {
			return $("<td />").append(
				link('tac/index/' + args.obj.id)
					.update_text(args.obj.name)
			);
		}
	},
	"creator": {
		"header": "Created by",
		"depends": ["username"],
		"sort": false,
		"cell": function (args) {
			return $("<td />").update_text(args.obj.username)
		}
	}
}
