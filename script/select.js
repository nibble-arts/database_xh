/*
 * The select method reads a selected item from a select tag
 * and writes a link reference to a link table
 *
 * <select name="{select_name}" class="db_select"
 *    select_table = "{table}"			# the table to get the data from
 *    link_table: 						# the table for the references
 *    source_field
 */

select = {

	cmsroot: "",

	init: function (cmsroot) {

		obj = this;
		this.cmsroot = cmsroot;

		// select button
		var select = (jQuery("input.db_select[type=submit]"));

		jQuery.each(select, function () {

			var select_table = jQuery(this).attr("select_table");
			var select_name = jQuery(this).attr("name");

			jQuery(this).bind("click", function () {

				var select_name = jQuery(this).attr("name");
				var select_item = jQuery("select[name="+select_name+"]");

				var select_option = select_item.find("option:selected");

				if (select_option) {
					var link_table = select_item.attr("link_table");

					var source_field = select_item.attr("source_field");
					var source_id = select_item.attr("source_id");
					var target_field = select_item.attr("target_field");

					var target_id = select_option.val();

					if (source_field != undefined
						&& source_id != undefined
						&& target_field != undefined
						&& target_id != undefined) {

						obj.write_link(obj.cmsroot, {
							"link_table": link_table,
							"source_field": source_field,
							"source_id": source_id,
							"target_field": target_field,
							"target_id": target_id
						});

					}
				}
			});
		});
	},



	write_link: function (url, data) {
console.log(data);

		var table = data.link_table;
		var field = data.source_field+","+data.target_field;
		var value = data.source_id+","+data.target_id;

		url += "?admin=database_save";
		url += "&action=database_insert";
		url += "&table=" + table;
		url += "&field=" + field;
		url += "&value=" + value;
		url += "&unique";
		url += "&normal";

console.log(url);
	}
}